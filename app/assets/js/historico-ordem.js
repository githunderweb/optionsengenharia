function inicio() {

    $('#listagem-inspecoes > button').click(function () {
        var inspecao = $(this).data('options');

        if (inspecao.id != null) {
            var select = `SELECT * FROM valores_inspecao vi WHERE id_inspecao = '${inspecao.id}'`;
            $.getJSON(`./utils/get-json.php?select=${select}`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var modalBody = '';

                    dados.forEach(function (dadoValoresInspecao) {
                        var valor = dadoValoresInspecao.valor;

                        if (dadoValoresInspecao.tipo == "Upload de arquivo") {
                            arquivos = [];
                            dadoValoresInspecao.valor.split("|").forEach(function (arquivo) {
                                arquivos.push(`<a href='../inspecoes/arquivos/${inspecao.id}/${dadoValoresInspecao.slug}/${arquivo}' download='${arquivo}'>${arquivo}</a>`);
                            });

                            valor = arquivos.join(", ");
                        } else if (dadoValoresInspecao.tipo == "Data" && dadoValoresInspecao.valor != "") {
                            valor = new Date(dadoValoresInspecao.valor).toLocaleDateString('pt-BR');
                        } else if (dadoValoresInspecao.tipo == "Data e Hora" && dadoValoresInspecao.valor != "") {
                            valor = new Date(dadoValoresInspecao.valor).toLocaleDateString('pt-BR', { year: "numeric", month: "numeric", day: "numeric", hour: "numeric", minute: "numeric" }).replaceAll(',', '');
                        }

                        modalBody += `
                            <div class='col-12 px-2'>
                                <label class='fw-semibold small'>${dadoValoresInspecao.titulo}:</label>
                                <p class='m-0'>${valor}</p>
                            </div>
                        `;
                    });

                    if (modalBody != '') {
                        var equipamento = inspecao.nome_equipamento != null && inspecao.nome_equipamento != '' ? `<p class='mt-1 mb-0'><span class='small fw-semibold'>Equipamento:</span> ${inspecao.nome_equipamento}</p>` : '';

                        modalBody = `
                            <div class='row g-3 px-2'>
                                <div class='col-12 px-2'>
                                    <p class='m-0 ff-inter fw-semibold'>${inspecao.titulo_modelo_relatorio}</p>

                                    <div class='d-flex gap-1 flex-wrap justify-content-between mt-1'>
                                        <p class='m-0'><span class='small fw-semibold'>Empresa:</span> ${inspecao.nome_empresa}</p>
                                        <p class='m-0'><span class='small fw-semibold'>Unidade:</span> ${inspecao.titulo_unidade}</p>
                                    </div>
                
                                    ${equipamento}
                
                                    <p class='mb-0 small text-secondary text-end'>${new Date(inspecao.data_cadastro).toLocaleDateString('pt-BR', { year: "numeric", month: "numeric", day: "numeric", hour: "numeric", minute: "numeric" }).replaceAll(',', '')}</p>
                                </div>
                            </div>

                            <hr class='divider'>
                            
                            <div class='row g-3 px-2'>${modalBody}</div>
                        `;
                    }
                    else
                        modalBody = 'Erro ao buscar dados';

                    $('#modalVisualizarInspecao .modal-body').html(modalBody);
                    $('#modalVisualizarInspecao').modal('show');
                }
            });
        }
    });
}