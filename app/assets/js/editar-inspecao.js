function inicio() {
    buscarValoresCampos();

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

    $('button.btn-exibir-campo-arquivo').click(function () {
        $(this).addClass('d-none');

        var dadosCampo = $(this).data('campo');
        if (parseInt(dadosCampo.obrigatorio))
            $(`input[name="${dadosCampo.slug}[]"]`).prop('required', true);

        $(`input[name="${dadosCampo.slug}[]"]`).removeClass('d-none');
    });
}

function buscarValoresCampos() {
    $.getJSON(`./utils/get-json.php?select=SELECT * FROM inspecoes WHERE id = '${idInspecao}'`, function (dados) {
        if (!('erro' in dados) && dados.length > 0) {
            var dado = dados[0];

            $('#txtTagCPE').val(dado.tag);
            $('#campos-padrao-equipamento #txtNumeroPastaCPE').val(dado.numero_pasta);
            $('#campos-padrao-equipamento #txtNomeCPE').val(dado.nome_equipamento);
            $('#campos-padrao-equipamento #txtFabricanteCPE').val(dado.fabricante);
            $('#campos-padrao-equipamento #txtAnoFabricacaoCPE').val(dado.ano_fabricacao);
            $('#campos-padrao-equipamento #txtMaterialCPE').val(dado.material);
            $('input[name="id_equipamento"]').val(dado.id_equipamento);
        }
    });

    $.getJSON(`./utils/get-json.php?select=SELECT * FROM valores_inspecao WHERE id_inspecao = '${idInspecao}'`, function (dados) {
        if (!('erro' in dados)) {
            dados.forEach(function (campo) {
                if (campo.tipo == 'Múltipla escolha')
                    $(`input[name="${campo.slug}"][value="${campo.valor}"]`).prop('checked', true);
                else if (campo.tipo == 'Caixa de seleção') {
                    campo.valor.split(', ').forEach(function (valorAtual) {
                        $(`input[name="${campo.slug}[]"][value="${valorAtual}"]`).prop('checked', true);
                    });
                } else if (campo.tipo == 'Lista suspensa') {
                    $(`select[name="${campo.slug}"]`).val(campo.valor).change();
                    $(`select[name="${campo.slug}[]"]`).val(campo.valor.split(', ')).change();
                } else if (campo.tipo == 'Upload de arquivo') {
                    var arquivos = [];
                    campo.valor.split('|').forEach(function (arquivo) {
                        arquivos.push(`<a href='../inspecoes/arquivos/${idInspecao}/${campo.slug}/${arquivo}' download='${arquivo}'>${arquivo}</a>`);
                    });
                    $(`input[name="${campo.slug}[]"]`).parent().after(`<div>${arquivos.join(', ')}</div>`);
                } else
                    $(`[name="${campo.slug}"]`).val(campo.valor);
            });
        }
    });
}