var empresa = '';
var pagina = '';
var acao = '';

var optionSlc2Icon = {
    theme: 'bootstrap-5',
    templateResult: function (option) {
        if (!option.id)
            return option.text;

        return $(`<span><i class="${option.element.dataset.icon} ms-1 me-2"></i>${option.text}</span>`);
    },
    templateSelection: function (option) {
        if (!option.id)
            return option.text;

        return $(`<span><i class="${option.element.dataset.icon} me-2"></i>${option.text}</span>`);
    }
};

$(document).ready(function () {
    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results != null) {
            return results[1] || 0;
        }
    }
    empresa = $.urlParam('empresa');
    linkSideBar();
    cep();
    mascaras();
    $('input.input-tag').tagify();

    $('input[type=number]').change(function () {
        var valorMinimo = Number($(this).attr('min'));
        if (!isNaN(valorMinimo) && $(this).val() < valorMinimo)
            $(this).val(valorMinimo);

        var valorMaximo = Number($(this).attr('max'));
        if (!isNaN(valorMaximo) && $(this).val() > valorMaximo)
            $(this).val(valorMaximo);
    });

    // Select empresa
    $(".empresa-atual select").change(function () {
        var url = window.location.origin + window.location.pathname;
        var searchAtual = window.location.search;
        var urlSearch = "";

        if (typeof pasta !== 'undefined' && pasta !== undefined && pasta !== null)
            searchAtual = removeUrlParam(searchAtual, `pasta=${pasta}`, true);

        if ($(this).val() == "")
            urlSearch = removeUrlParam(searchAtual, `empresa=${empresa}`);
        else {
            if (searchAtual != "") {
                if (searchAtual.includes("empresa=")) {
                    urlSearch = removeUrlParam(searchAtual, `empresa=${empresa}`);
                    if (urlSearch != "")
                        urlSearch = "&" + urlSearch;
                    urlSearch = "empresa=" + $(this).val() + urlSearch;
                }
                else
                    urlSearch += "empresa=" + $(this).val() + "&" + searchAtual.split("?")[1];
            }
            else {
                urlSearch += "empresa=" + $(this).val();
            }
        }

        if (urlSearch != "")
            url += "?" + urlSearch;

        window.location.href = url;
    });

    // Atualiza a empresa ativa do cliente. O evento "choice" garante o
    // funcionamento com o componente Choices, que substitui o select visual.
    var selectEmpresaCliente = document.querySelector('.empresa-cliente-atual select');
    if (selectEmpresaCliente) {
        var navegandoParaEmpresa = false;
        var selecionarEmpresaCliente = function (valorEmpresa) {
            if (navegandoParaEmpresa || valorEmpresa === undefined || valorEmpresa === null || valorEmpresa === '')
                return;

            navegandoParaEmpresa = true;
            var urlEmpresa = new URL(window.location.href);
            urlEmpresa.searchParams.set('empresa', valorEmpresa);
            urlEmpresa.searchParams.delete('unidade');
            urlEmpresa.searchParams.delete('categoria');
            urlEmpresa.searchParams.delete('situacao');
            urlEmpresa.searchParams.delete('f_tipo');
            urlEmpresa.searchParams.delete('f_categoria');
            urlEmpresa.searchParams.delete('f_local');
            urlEmpresa.searchParams.delete('f_situacao');
            urlEmpresa.searchParams.delete('f_campo');
            urlEmpresa.searchParams.delete('f_valor_campo');
            urlEmpresa.searchParams.delete('f_personalizado[]');
            window.location.assign(urlEmpresa.toString());
        };

        selectEmpresaCliente.addEventListener('change', function () {
            selecionarEmpresaCliente(this.value);
        });

        selectEmpresaCliente.addEventListener('choice', function (evento) {
            if (evento.detail && evento.detail.choice)
                selecionarEmpresaCliente(evento.detail.choice.value);
        });
    }

    //Exibir ocultar senha
    $('.btnTogglePassword').click(function () {
        $(this).find('svg').toggleClass('fa-eye-slash');
        $(this).find('svg').toggleClass('fa-eye');
        $(this).parent().find('input').attr('type', function (index, attr) {
            return attr == 'password' ? 'text' : 'password';
        });
    });


    $('#slcEmpresa').change(function () {
        if (!$('#slcUnidade').length)
            return;

        $('#slcUnidade').html('').trigger('change');
        var empresaAtual = $('#slcEmpresa').val();

        if (empresaAtual != '') {
            $.getJSON(`./unidades/busca-unidades.php?empresa=${empresaAtual}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var optionsUnidades = '';

                    dados.forEach(function (unidade) {
                        var tituloUnidade = Array.isArray(empresaAtual) && empresaAtual.length > 1 ? `${unidade.empresa} - ${unidade.titulo}` : unidade.titulo;
                        optionsUnidades += `<option value='${unidade.id}'>${tituloUnidade}</option>`;
                    });

                    $('#slcUnidade').html(optionsUnidades).trigger('change');
                }
            });
        }
    });


    $('textarea.textarea-autosize').each(function () {
        this.setAttribute('style', `height: ${this.scrollHeight}px; overflow-y: hidden; resize: none;`);
    }).on('input', function () {
        this.style.height = 'auto';
        this.style.height = `${this.scrollHeight}px`;
    });


    if (typeof inicio === 'function')
        inicio();
});



// Ativando link atual sidebar
function linkSideBar() {
    pagina = $.urlParam('p');
    acao = $.urlParam('acao');

    if (pagina != undefined && pagina != '') {
        $('a.nav-link.' + pagina).addClass('active');
        $('a.nav-link.' + pagina).parents('ul.nav').addClass('show');
        $('a.nav-link.' + pagina).parents('ul.nav').prev().attr('aria-expanded', 'true');
    }
    else
        $("a.nav-link.dashboard").addClass('active');
}


function cep() {
    $('.cep').off('keyup');
    $('.cep').off('blur');

    // Máscara campo CEP
    $('.cep').keyup(function () {
        var cep = $(this).val().replace(/[^0-9]/g, '');
        if (cep != '') {
            if (cep.length > 5)
                cep = cep.substring(0, 5) + '-' + cep.substring(5);
        }
        else
            cep = '';
        $(this).val(cep);
    });

    // Busca CEP
    $('.cep').blur(function () {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep != '') {
            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;
            //Valida o formato do CEP.
            if (validacep.test(cep)) {
                //Preenche os campos com '...' enquanto consulta webservice
                $('#txtCidade, #txtEstado, #txtEndereco, #txtBairro').val('...');

                $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function (dados) {
                    if (!('erro' in dados)) {
                        $('#txtCidade').val(dados.localidade);
                        $('#txtEstado').val(dados.uf);
                        $('#txtEndereco').val(dados.logradouro);
                        $('#txtBairro').val(dados.bairro);
                    }
                    else
                        $('#txtCidade, #txtEstado, #txtEndereco, #txtBairro').val('');
                });
            }
            else
                $('#txtCidade, #txtEstado, #txtEndereco, #txtBairro').val('');
        }
        else
            $('#txtCidade, #txtEstado, #txtEndereco, #txtBairro').val('');
    });
}

function mascaras() {
    $('.cpf').mask('000.000.000-00', { reverse: true });
    $('.cnpj').mask('00.000.000/0000-00', { reverse: true });
    $('.celular').mask('(00) 00000-0000');
    /*$('.date').mask('00/00/0000');
    $('.time').mask('00:00:00');
    $('.date_time').mask('00/00/0000 00:00:00');
    $('.cep').mask('00000-000');
    $('.phone').mask('0000-0000');
    $('.phone_with_ddd').mask('(00) 0000-0000');
    $('.phone_us').mask('(000) 000-0000');
    $('.mixed').mask('AAA 000-S0S');
    $('.cpf').mask('000.000.000-00', { reverse: true });
    $('.cnpj').mask('00.000.000/0000-00', { reverse: true });
    $('.money').mask('000.000.000.000.000,00', { reverse: true });
    $('.money2').mask("#.##0,00", { reverse: true });
    $('.ip_address').mask('0ZZ.0ZZ.0ZZ.0ZZ', {
        translation: {
            'Z': {
                pattern: /[0-9]/, optional: true
            }
        }
    });
    $('.ip_address').mask('099.099.099.099');
    $('.percent').mask('##0,00%', { reverse: true });
    $('.clear-if-not-match').mask("00/00/0000", { clearIfNotMatch: true });
    $('.placeholder').mask("00/00/0000", { placeholder: "__/__/____" });
    $('.fallback').mask("00r00r0000", {
        translation: {
            'r': {
                pattern: /[\/]/,
                fallback: '/'
            },
            placeholder: "__/__/____"
        }
    });
    $('.selectonfocus').mask("00/00/0000", { selectOnFocus: true });*/
}

// Função que remove um parâmetro da url atual
function removeUrlParam(searchAtual, remove, useBeforeSearch = false) {
    var vSearch = searchAtual.split('?')[1].split('&');

    vSearch = $.grep(vSearch, function (value) {
        return value != remove;
    });

    var beforeSearch = '';
    if (useBeforeSearch)
        beforeSearch = searchAtual.split('?')[0] + '?';

    return beforeSearch + vSearch.join('&');
}


function slugfy(text, divider = '-', lowerAll = true) {
    text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    text = text.toLowerCase();
    if (!lowerAll) {
        text = text.replace(/\b\w/g, function (l) { return l.toUpperCase() });
        text = text[0].toLowerCase() + text.slice(1);
    }
    text = text.replace(/[^a-zA-Z0-9\s-]/g, '');
    text = text.trim();
    text = text.replace(/\s+/g, divider);

    if (text.length === 0)
        return 'n-a';

    return text;
}


function updateImgChangeFile(idInputFile, idImg) {
    $(`input[type="file"]#${idInputFile}`).change(function () {
        if (this.files[0] != undefined)
            $(`img#${idImg}`).attr('src', window.URL.createObjectURL(this.files[0]));
    });
}
