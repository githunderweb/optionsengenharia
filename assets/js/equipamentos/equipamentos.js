function inicio() {

    $('#slcTipoEquipamento').change(function () {
        var tipo = $(this).val();

        var select = `SELECT * FROM campos_tipo_equipamento WHERE id_tipo_equipamento = ${tipo}`;
        $.getJSON(`./get-json.php?select=${select}`, function (dados) {
            var campos = '';

            if (!('erro' in dados) && dados.length > 0) {
                dados.forEach(function (campo) {
                    var obrigatorio = '';
                    var required = '';
                    if (campo.obrigatorio) {
                        obrigatorio = "<span>*</span>";
                        required = "required";
                    }

                    var valorCampoAtual = typeof valoresCampos !== 'undefined' && valoresCampos[campo.slug] != undefined ? valoresCampos[campo.slug] : '';

                    var campoAtual = `
                        <label class='form-label fw-semibold' for='txt_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                        <input id='txt_${campo.slug}' class='form-control' type='text' name='${campo.slug}' ${required} value='${valorCampoAtual}'>    
                    `;

                    switch (campo.tipo) {
                        case "Texto longo":
                            campoAtual = `
                                <label class='form-label fw-semibold' for='txt_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <textarea id='txt_${campo.slug}' class='form-control' name='${campo.slug}' rows='4' ${required}>${valorCampoAtual}</textarea>
                            `;
                            break;
                        case "Múltipla escolha":
                            var opcoes = "";
                            $.each(campo.opcoes.split(','), function (key, value) {
                                var checked = valorCampoAtual == value ? 'checked' : '';

                                opcoes += `
                                    <div class='form-check d-flex align-items-center gap-2 p-0 m-0'>
                                        <input class='form-check-input m-0' id='rdo_${campo.slug}_${key}' type='radio' name='${campo.slug}' value='${value}' ${required} ${checked}/>
                                        <label class='form-check-label m-0' for='rdo_${campo.slug}_${key}'>${value}</label>
                                    </div>
                                `;
                            });

                            campoAtual = `
                                <label class='form-label fw-semibold'>${campo.titulo_campo}${obrigatorio}</label>
                                <div class='d-flex flex-wrap gap-3'>${opcoes}</div>
                            `;
                            break;
                        case "Caixa de seleção":
                            var valoresSelecionados = valorCampoAtual.split(", ");

                            var opcoes = "";
                            $.each(campo.opcoes.split(','), function (key, value) {
                                var checked = valoresSelecionados.includes(value) ? 'checked' : '';

                                opcoes += `
                                    <div class='form-check m-0'>
                                        <input class='form-check-input' id='rdo_${campo.slug}_${key}' type='checkbox' name='${campo.slug}[]' value='${value}' ${required} ${checked}/>
                                        <label class='form-check-label' for='rdo_${campo.slug}_${key}'>${value}</label>
                                    </div>
                                `;
                            });

                            campoAtual = `
                                <label class='form-label fw-semibold'>${campo.titulo_campo}${obrigatorio}</label>
                                <div>${opcoes}</div>
                            `;
                            break;
                        case "Lista suspensa":
                            var opcoes = "<option value='' disabled selected>Selecione...</option>";
                            var dataOptions = { "minimumResultsForSearch": -1 };
                            var multiple = "";
                            var multiplo = "";

                            if (parseFloat(campo.multiplo)) {
                                multiple = "multiple";
                                multiplo = "[]";
                                opcoes = "";
                                dataOptions.placeholder = "Selecione...";
                            }

                            valoresSelecionados = valorCampoAtual.split(", ");

                            campo.opcoes.split(',').forEach(function (value) {
                                var selected = valoresSelecionados.includes(value) ? 'selected' : '';
                                opcoes += `<option value='${value}' ${selected}>${value}</option>`;
                            });

                            campoAtual = `
                                <label class='form-label fw-semibold' for='slc_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <select id='slc_${campo.slug}' class='form-select selectpicker' name='${campo.slug}${multiplo}' ${required} ${multiple} data-options='${JSON.stringify(dataOptions)}'>${opcoes}</select>
                            `;
                            break;
                        case "Upload de arquivo":
                            required = obrigatorio = ''; // Remover obrigatoriedade deste campo

                            var multiple = parseFloat(campo.multiplo) ? "multiple" : "";
                            var accept = campo.tipo_arquivo && campo.tipo_arquivo != "Qualquer tipo" ? `accept='${campo.tipo_arquivo}'` : "";
                            campoAtual = `
                                <label class='form-label fw-semibold' for='fle_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <input id='fle_${campo.slug}' class='form-control' type='file' name='${campo.slug}[]' ${required} ${multiple} ${accept}>
                            `;
                            break;
                        case "Data e Hora":
                            campoAtual = `
                                <label class='form-label fw-semibold' for='txt_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <input id='txt_${campo.slug}' class='form-control' type='datetime-local' name='${campo.slug}' ${required} value='${valorCampoAtual}'>
                            `;
                            break;
                        case "Data":
                            campoAtual = `
                                <label class='form-label fw-semibold' for='txt_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <input id='txt_${campo.slug}' class='form-control' type='date' name='${campo.slug}' ${required} value='${valorCampoAtual}'>
                            `;
                            break;
                        case "Horário":
                            campoAtual = `
                                <label class='form-label fw-semibold' for='txt_${campo.slug}'>${campo.titulo_campo}${obrigatorio}</label>
                                <input id='txt_${campo.slug}' class='form-control' type='time' name='${campo.slug}' ${required} value='${valorCampoAtual}'>
                            `;
                            break;
                    }

                    campos += `<div class='col-12 col-md-6 col-lg-4'>${campoAtual}</div>`;

                });

                campos = `<div class="row g-3 mb-3">${campos}</div>`;
            }

            $('#campos-nao-fixos').html(`${campos} <input type='hidden' name='campos' value='${JSON.stringify(dados)}'>`);
            $('#campos-nao-fixos select.selectpicker').each(function (index, elemento) {
                $(elemento).select2({ theme: 'bootstrap-5', ...$(elemento).data('options') });
            });
        });

        // if (tipo in camposNaoFixos) {
        //     var countCampos = 0;
        //     campos += `<div class="row g-3 mb-3">`;
        //     camposNaoFixos[tipo].forEach(function (element) {
        //         if (countCampos == 3) {
        //             campos += `</div><div class="row g-3 mb-3">`;
        //             countCampos = 0;
        //         }

        //         var nomeCampo = slugfy(element, "", false);
        //         campos += `
        //             <div class="col-12 col-md">
        //                 <label class="form-label" for="txt_${nomeCampo}">${element}</label>
        //                 <input type="text" id="txt_${nomeCampo}" class="form-control" name="${nomeCampo}" autocomplete="off" maxlength="250">
        //             </div>
        //         `;

        //         countCampos++;
        //     });
        //     campos += `</div>`;
        // }
    });

    $('#slcEmpresa, #slcUnidade').change(function () {
        $('#slcCategoria, #slcLocalInstalacao').html(`<option value='' selected disabled>Selecione...</option>`);
        var empresaAtual = $('#slcEmpresa').val();
        var unidadeAtual = $('#slcUnidade').val();

        if (empresaAtual != '' && unidadeAtual != '') {
            var select = `SELECT id, titulo_categoria_equipamento FROM categorias_equipamento WHERE id_empresa = ${empresaAtual} AND id_unidade = ${unidadeAtual}`;
            $.getJSON(`./get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var optionsCategorias = `<option value='' selected disabled>Selecione...</option>`;

                    dados.forEach(function (categoria) {
                        optionsCategorias += `<option value='${categoria.id}'>${categoria.titulo_categoria_equipamento}</option>`;
                    });

                    $('#slcCategoria').html(optionsCategorias);
                }
            });


            var select = `SELECT id, titulo_local_instalacao FROM locais_instalacao WHERE id_empresa = ${empresaAtual} AND id_unidade = ${unidadeAtual}`;
            $.getJSON(`./get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var optionsLocalInstalacao = `<option value='' selected disabled>Selecione...</option>`;

                    dados.forEach(function (local) {
                        optionsLocalInstalacao += `<option value='${local.id}'>${local.titulo_local_instalacao}</option>`;
                    });

                    $('#slcLocalInstalacao').html(optionsLocalInstalacao);
                }
            });
        }
    });

    // Busca novos produtos ao adicionar
    $('#btnAddCategoria, #btnAddLocal').click(function () {
        $(window).off('focus');
        var btn = $(this);

        $(window).focus(function (e) {
            var empresaAtual = $('#slcEmpresa').val();
            var unidadeAtual = $('#slcUnidade').val();

            if (empresaAtual != '' && unidadeAtual != '') {
                var select = null;
                var qr = '';

                if (btn.prop('id') == 'btnAddCategoria') {
                    var qr = `SELECT id, titulo_categoria_equipamento AS titulo FROM categorias_equipamento`;
                    select = $('#slcCategoria');
                } else if (btn.prop('id') == 'btnAddLocal') {
                    var qr = `SELECT id, titulo_local_instalacao AS titulo FROM locais_instalacao`;
                    select = $('#slcLocalInstalacao');
                }

                if (qr != '')
                    qr = `${qr} WHERE id_empresa = ${empresaAtual} AND id_unidade = ${unidadeAtual}`;

                $.getJSON(`./get-json.php?select=${qr}`, function (dados) {
                    if (!('erro' in dados) && dados.length > 0) {
                        var options = `<option value='' selected disabled>Selecione...</option>`;

                        dados.forEach(function (dado) {
                            options += `<option value='${dado.id}'>${dado.titulo}</option>`;
                        });

                        select.html(options);
                    }
                    $(window).off('focus');
                });
            }
        });
    });
}