function inicio() {
    buscaUnidades();

    $('#slcEmpresa').change(function () {
        buscaUnidades();
    });

    $('#slcEmpresa, #slcUnidade').change(function () {
        $('#slcEquipamento').html(`<option value='' selected disabled>Selecione...</option>`);

        $('button#btn-escolher-modelo').prop('disabled', true);
        $('#lisagem-modelos > div').html(`<h6 class="m-0 py-3 px-2">Nenhum modelo disponível</h6>`);

        var empresaAtual = $('#slcEmpresa').val();
        var unidadeAtual = $('#slcUnidade').val();

        if (empresaAtual != '' && unidadeAtual != '') {
            var select = `SELECT id, nome_equipamento AS nome FROM equipamentos WHERE id_empresa = '${empresaAtual}' AND id_unidade = ${unidadeAtual}`;
            $.getJSON(`./utils/get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var optionsEquipamentos = `<option value='' selected disabled>Selecione...</option>`;

                    dados.forEach(function (equipamento) {
                        optionsEquipamentos += `<option value='${equipamento.id}'>${equipamento.nome}</option>`;
                    });

                    $('#slcEquipamento').html(optionsEquipamentos);
                }
            });
        }
    });

    $('#slcEquipamento').change(function () {
        $('button#btn-escolher-modelo').prop('disabled', true);
        $('#lisagem-modelos > div').html(`<h6 class="m-0 py-3 px-2">Nenhum modelo disponível</h6>`);
        var equipamentoAtual = $('#slcEquipamento').val();

        if (equipamentoAtual != '') {
            var select = `SELECT mr.id, mr.titulo_modelo_relatorio AS titulo FROM modelos_relatorio mr INNER JOIN equipamentos e ON e.id = '${equipamentoAtual}' WHERE mr.id_tipo_equipamento = e.id_tipo_equipamento AND mr.status_modelo_relatorio = 'Ativo'`;
            $.getJSON(`./utils/get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var modelosRelatorio = '';

                    dados.forEach(function (modelo) {
                        modelosRelatorio += `
                            <input type="radio" class="btn-check" name="modelo" id="rdoModelo${modelo.id}" autocomplete="off" value="${modelo.id}">
                            <label class="btn bg-white p-3 border-2 rounded shadow-sm w-100" for="rdoModelo${modelo.id}">
                                <h6 class="m-0">${modelo.titulo}</h6>
                            </label>
                        `;
                    });

                    $('#lisagem-modelos > div').html(modelosRelatorio);
                    btnEscolherModelo();
                }
            });
        }
    });

    btnEscolherModelo();

    /*$('form#frm-escolher-modelo').submit(function (event) {
        event.preventDefault();
        event.stopPropagation();

        var dataForm = new FormData($(this)[0]);
        var modelo = dataForm.get('modelo');
        var equipamento = dataForm.get('equipamento');

        window.location.href = `${window.location.href}&modelo=${modelo}&equipamento=${equipamento}`;
    });*/


    $('#txtTagCPE').change(function () {
        var tagAtual = $(this).val();
        $('#campos-padrao-equipamento input').val('');

        if (tagAtual != '') {
            var select = `SELECT * FROM equipamentos WHERE id_empresa = '${empresa}' AND id_unidade = '${unidade}' AND tag = '${tagAtual}'`;
            $.getJSON(`./utils/get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var dado = dados[0];

                    $('#campos-padrao-equipamento #txtNumeroPastaCPE').val(dado.numero_pasta);
                    $('#campos-padrao-equipamento #txtNomeCPE').val(dado.nome_equipamento);
                    $('#campos-padrao-equipamento #txtFabricanteCPE').val(dado.fabricante);
                    $('#campos-padrao-equipamento #txtAnoFabricacaoCPE').val(dado.ano_fabricacao);
                    $('#campos-padrao-equipamento #txtMaterialCPE').val(dado.material);
                    $('input[name="id_equipamento"]').val(dado.id);
                }
            });
        }
    });
}

function buscaUnidades() {
    $('#slcUnidade').html(`<option value='' selected disabled>Selecione...</option>`);
    var empresaAtual = $('#slcEmpresa').val();

    if (empresaAtual != '') {
        var select = `SELECT id, titulo_unidade as titulo FROM unidades WHERE id_empresa = '${empresaAtual}'`;
        $.getJSON(`./utils/get-json.php?select=${select}`, function (dados) {
            if (!('erro' in dados) && dados.length > 0) {
                var optionsUnidades = `<option value='' selected disabled>Selecione...</option>`;

                dados.forEach(function (unidade) {
                    optionsUnidades += `<option value='${unidade.id}'>${unidade.titulo}</option>`;
                });

                $('#slcUnidade').html(optionsUnidades);
            }
        });
    }
}

function btnEscolherModelo() {
    $('input[type="radio"][name="modeloOS"]').change(function () {
        $('button#btn-escolher-modelo').prop('disabled', false);
    });
}