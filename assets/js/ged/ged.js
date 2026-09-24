var pasta = '';
var selecao = { multiplo: false, itens: {} }

function inicio() {
    pasta = '';
    if ($.urlParam('pasta'))
        pasta = $.urlParam('pasta');

    // Ativar item ao clicar ou abre pasta/sub-pasta se já estiver ativa
    $('.arquivos-area .card-item').click(function (event) {
        $('.arquivos-area-opcoes').remove();
        var cardItem = $(this);
        var dataItem = cardItem.parents('.dropdown-center').find('.card-item').data('item');

        if (event.ctrlKey && cardItem.hasClass('arquivo') && $('.arquivos-area .card-item.arquivo.active-card').length > 0) {
            $('.arquivos-area .card-item.show').dropdown('hide');
            selecao.multiplo = true;

            if (!cardItem.hasClass('active-card')) {
                selecao.itens[dataItem.id] = dataItem
                cardItem.addClass('active-card');
            }
            else {
                delete selecao.itens[dataItem.id]
                cardItem.removeClass('active-card');
            }

            if ($('.arquivos-area .card-item.arquivo.active-card').length < 2)
                selecao.multiplo = false
        }
        else if (!selecao.multiplo && cardItem.hasClass('active-card')) {
            if (cardItem.hasClass('pasta') || cardItem.hasClass('sub-pasta')) {
                var idAtual = dataItem.id;
                var link = `./index.php?`;

                if (funcaoUsuarioAtual == 'Administrador') {
                    link += 'empresa=';

                    if (cardItem.hasClass('pasta'))
                        link += `${idAtual}&`;
                    else
                        link += `${empresa}&`;
                }

                link += 'p=ged';

                if (cardItem.hasClass('sub-pasta'))
                    link += `&pasta=${idAtual}`;

                window.location.href = link;
            }
            else if (cardItem.hasClass('arquivo'))
                cardItem.dropdown('toggle');
        }
        else /*if (!cardItem.hasClass('active-card')) */ {
            // Desativa e fecha opções do item ativo
            $('.active-card').removeClass('active-card');
            $('.arquivos-area .card-item.show').dropdown('hide');

            selecao = { multiplo: false, itens: {} }
            selecao.itens[dataItem.id] = dataItem
            cardItem.addClass('active-card');
        }
    });

    // Abre as opção do item ao clicar com o botão direito
    $('.arquivos-area .card-item').on('contextmenu', function (event) {
        $('.arquivos-area-opcoes').remove();
        event.preventDefault();
        event.stopPropagation();

        var cardItem = $(this);
        if (!cardItem.hasClass('active-card') || !selecao.multiplo) {
            selecao = { multiplo: false, itens: {} }
            $('.arquivos-area .card-item.show').dropdown('hide');
            $('.active-card').removeClass('active-card');
            cardItem.addClass('active-card');
            cardItem.dropdown('toggle');

            // event.stopPropagation();
        }
        else {
            cardItem.parent().append(`
                <ul class="dropdown-menu arquivos-area-opcoes">
                    <li> <button class="dropdown-item btn-excluir-massa"> <i class="bi bi-trash3 me-3"></i>Excluir</button> </li>
                </ul>
            `);

            btnsAcoesMassa();

            var stylesOpcoes = {
                display: 'block',
                fontSize: '0.9rem',
                top: `${event.offsetY}px`,
                left: `${event.offsetX}px`
            };
            $('.arquivos-area-opcoes').css(stylesOpcoes);
        }
    });

    // Ao clicar fora de um item desativa os ativos
    $('.arquivos-area').click(function (event) {
        $('.arquivos-area-opcoes').remove();
        $('.active-card').removeClass('active-card');
        $('.arquivos-area .dropdown-menu.show').dropdown('hide');
        selecao = { multiplo: false, itens: {} }
    }).find('.card-item').click(function () {
        return false;
    });

    // Ao clicar fora de um item com botão direito aparece opções
    $('.arquivos-area').on('contextmenu', function (event) {
        $('.arquivos-area-opcoes').remove();
        event.preventDefault();
        event.stopPropagation();
        $('.active-card').removeClass('active-card');
        $('.arquivos-area .dropdown-menu.show').dropdown('hide');
        selecao = { multiplo: false, itens: {} }

        if (typeof dirAtual !== "undefined") {
            $(this).append(`
                <ul class="dropdown-menu arquivos-area-opcoes">
                    <li> <button class="dropdown-item" data-bs-toggle="modal" type="button" data-bs-target="#modalUploadArquivo"> <i class="bi bi-upload me-3"></i>Upload</button> </li>
                    <li> <button class="dropdown-item" data-bs-toggle="modal" type="button" data-bs-target="#modalCriarPasta"> <i class="bi bi-folder-plus me-3"></i>Criar pasta</button> </li>
                </ul>
            `);

            $.getJSON(`./ged/copia-arquivo.php?acao=consultar`, function (dados) {
                if (!('erro' in dados)) {
                    if (dados.status == 'copiado') {
                        $('.arquivos-area-opcoes').prepend(`
                            <li> <a class="dropdown-item" href="./ged/ged-acao.php?acao=copiar-arquivo&empresa=${empresa}&pasta=${pasta}&dir=${dirAtual}"> <i class="bi bi-clipboard2-check me-3"></i>Colar</a> </li>
                        `);
                        posicaoArquivosAreaOpcoes(event);
                    }
                }
            });

            posicaoArquivosAreaOpcoes(event);
        }
    });

    // Ao clicar fora de um item com botão direito aparece opções
    $('.arquivos-area > nav ol.breadcrumb li.breadcrumb-item').on('contextmenu', function (event) {
        event.preventDefault();
        event.stopPropagation();
    });


    // Desativar botão do formulário ao enviar pra evitar duplicidade
    $('#modalCriarPasta form, #modalUploadArquivo form').submit(function () {
        if (this.checkValidity())
            $(this).find('button[type=submit]').prop('disabled', true);
    });

    $('#modalExcluir .btn-excluir').click(function () {
        $(this).attr('disabled', 'disabled');
    });


    // Copiar
    $('.arquivos-area .dropdown-center .btn-copiar').click(function () {
        var dataItem = $(this).parents('.dropdown-center').find('.card-item').data('item');

        $.ajax({
            type: 'POST',
            url: './ged/copia-arquivo.php',
            data: dataItem
        });
    });

    // Muda modal renomear de acordo com item atual
    $('.arquivos-area .dropdown-center .btn-renomear').click(function () {
        var cardItem = $(this).parents('.dropdown-center').find('.card-item');
        var dataItem = cardItem.data('item');
        var idAtual = dataItem.id;

        var label = '';
        var actionRenomear = `./ged/ged-acao.php?dir=${dirAtual}&pasta=${pasta}&acao=renomear-`;
        if (cardItem.hasClass('sub-pasta')) {
            label = ' pasta';
            actionRenomear += `pasta&id=${idAtual}`;
        }
        else if (cardItem.hasClass('arquivo')) {
            label = ' arquivo';
            actionRenomear += `arquivo&id=${idAtual}`;
        }

        $('#modalRenomear .modal-body #modalRenomearLabel').html(`Renomear${label}`);
        $('#modalRenomear form').attr('action', actionRenomear);
        $('#modalRenomear form #txtNome').val(dataItem.nome);
        $('#modalRenomear').modal('show');
    });

    // Muda modal excluir de acordo com item atual
    $('.arquivos-area .dropdown-center .btn-excluir').click(function () {
        var cardItem = $(this).parents('.dropdown-center').find('.card-item');
        var dataItem = cardItem.data('item');

        var label = '';
        var btnExcluir = `./index.php?empresa=${empresa}&p=ged&pasta=${pasta}`;
        if (cardItem.hasClass('sub-pasta')) {
            label = ' esta pasta'
            btnExcluir = `./ged/ged-acao.php?dir=${dirAtual}&pasta=${pasta}&acao=excluir-pasta&id=${dataItem.id}`;
        }
        else if (cardItem.hasClass('arquivo')) {
            label = ' este arquivo'
            btnExcluir = `./ged/ged-acao.php?acao=excluir-arquivo&pasta=${pasta}&arquivos=` + JSON.stringify({ dataItem });
        }

        $('#modalExcluir .modal-body #modalExcluirLabel').html(`Tem certeza que deseja excluir${label}?`);
        $('#modalExcluir .btn-excluir').attr('href', btnExcluir);
        $('#modalExcluir').modal('show');
    });

    // Aumenta contagem de dowloads
    $('.arquivos-area .dropdown-center .btn-download').click(function () {
        var idAtual = $(this).parents('.dropdown-center').find('.card-item').data('item').id;

        $.ajax({
            type: 'POST',
            url: './ged/registro-download-arquivo.php',
            data: { 'idArquivo': idAtual }
        });
    });

    // Exibe informações do arquivo
    $('.arquivos-area .dropdown-center .btn-informacoes').click(function () {
        $('#modalInformacoes .modal-body .modal-conteudo').html(`<div class="d-flex justify-content-center"> <div class="spinner-border text-padrao" role="status"> <span class="visually-hidden">Loading...</span> </div> </div>`);
        $('#modalInformacoes').modal('show');

        var idAtual = $(this).parents('.dropdown-center').find('.card-item').data('item').id;

        $.getJSON(`./ged/obter-informacoes-arquivo.php?idArquivo=${idAtual}&dir=${dirAtual}`, function (dados) {
            if (!('erro' in dados)) {
                var downloads = 'Nenhum';
                var downloadsUsuarios = '';
                if (Number(dados.qtDownload) > 0) {
                    downloads = `<div class="btnOffcanvasDownloadsUsuarios text-padrao fw-semi-bold w-auto cursor-pointer w-fit-content text-underline-hover">${dados.qtDownload}</div>`;

                    dados.downloadsUsuarios.forEach(function (item) {
                        downloadsUsuarios += `<tr> <td class="p-2"> <input type="text" readonly value="${item.nome}" </td> <td class="p-2 white-space-nowrap"> ${item.data_download} </td> </tr>`;
                    });
                    downloadsUsuarios = `<div class="offcanvas offcanvas-start position-absolute rounded-bottom-lg" id="offcanvasDownloadsUsuarios" tabindex="-1" aria-labelledby="offcanvasDownloadsUsuariosLabel"> <div class="offcanvas-header"> <h5 id="offcanvasDownloadsUsuariosLabel" class="m-0">Downloads</h5><button class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button> </div> <div class="offcanvas-body scrollbar"> <table class="table table-sm fw-medium m-0"> <tbody class="overflow-hidden"> ${downloadsUsuarios} </tbody> </table> </div> </div>`;
                }

                $('#modalInformacoes .modal-body .modal-conteudo').html(`
                    ${downloadsUsuarios}
                    <table class="table table-sm fw-semi-bold m-0"> <tbody class="overflow-hidden"> 
                        <tr> <td class="p-2">Nome</td> <td class="p-2"><input type="text" readonly value="${dados.nome}" /></td> </tr> 
                        <tr> <td class="p-2">Tipo</td> <td class="p-2"><input type="text" readonly value="${dados.tipo}" /></td> </tr> 
                        <tr> <td class="p-2">Tamanho</td> <td class="p-2"><input type="text" readonly value="${dados.tamanho}" /></td> </tr> 
                        <tr> <td class="p-2">Data upload</td> <td class="p-2"><input type="text" readonly value="${dados.dataUpload}" /></td> </tr> 
                        <tr> <td class="p-2">Downloads</td> <td class="p-2"> ${downloads} </td> </tr> 
                    </tbody> </table>
                `);
                btnOffcanvasDownloadsUsuarios();
            }
            else
                $('#modalInformacoes .modal-body .modal-conteudo').html(`<p class="text-center p-0">Erro ao buscar informações</p>`);
        });
    });


    // Foca no campo de texto quando o modal abrir
    $('#modalCriarPasta, #modalRenomear').on('shown.bs.modal', function () {
        $(this).find('#txtNomePasta, #txtNome').focus();
    });
}



// Exibe lista de downloads de usuários
function btnOffcanvasDownloadsUsuarios() {
    $('.btnOffcanvasDownloadsUsuarios').click(function () {
        $('#offcanvasDownloadsUsuarios').offcanvas('show');
    });
}

// Ajusta a posição do dropdown de opções
function posicaoArquivosAreaOpcoes(event) {
    var arquivosArea = $('.arquivos-area');
    var arquivosAreaOpcoes = $('.arquivos-area-opcoes');

    var arquivosAreaWidth = arquivosArea.outerWidth();
    var arquivosAreaOpcoesWidth = arquivosAreaOpcoes.outerWidth();
    var stylesOpcoesLeft = event.offsetX;
    if (event.offsetX > (arquivosAreaWidth - arquivosAreaOpcoesWidth))
        stylesOpcoesLeft = arquivosAreaWidth - arquivosAreaOpcoesWidth;

    var arquivosAreaHeight = arquivosArea.outerHeight();
    var arquivosAreaOpcoesHeight = arquivosAreaOpcoes.outerHeight();
    var stylesOpcoesTop = event.offsetY;
    if (event.offsetY > (arquivosAreaHeight - arquivosAreaOpcoesHeight))
        stylesOpcoesTop = arquivosAreaHeight - arquivosAreaOpcoesHeight;

    var stylesOpcoes = {
        display: 'block',
        fontSize: '0.9rem',
        top: `${stylesOpcoesTop}px`,
        left: `${stylesOpcoesLeft}px`
    };
    arquivosAreaOpcoes.css(stylesOpcoes);
}


// Configura ações em massa
function btnsAcoesMassa() {
    // Muda modal excluir de acordo com itens selecionados
    $('.arquivos-area .arquivos-area-opcoes .btn-excluir-massa').click(function () {
        var labelArquivos = '';
        // $.each(selecao.itens, function (key, item) {
        //     labelArquivos += `<br>${item.nome}.${item.extensao}`
        // });

        $('#modalExcluir .modal-body #modalExcluirLabel').html(`Tem certeza que deseja excluir os arquivos selecionados?${labelArquivos}`);
        $('#modalExcluir .btn-excluir').attr('href', `./ged/ged-acao.php?acao=excluir-arquivo&pasta=${pasta}&arquivos=` + JSON.stringify(selecao.itens));
        $('#modalExcluir').modal('show');
    });
}