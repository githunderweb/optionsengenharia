function inicio() {
    var qtModelos = 1;

    function htmlRowModelo(options, modeloAtual) {
        return `<hr style="margin: 20px 0 12px 0"> <div class="row row-modelo g-3 mb-3 position-relative"> <div class="position-absolute top-0 end-0 w-auto d-flex align-items-center gap-2"> <a role="button" class="link-padrao btn-novo-modelo"><i class="fas fa-plus-square"></i></a> <a role="button" class="link-danger btn-excluir-modelo"> <i class="fas fa-trash-alt"></i></a> </div> <div class="col-12 col-md-9 col-lg-10"> <label class="form-label" for="slcModelo${modeloAtual}">Modelo relatório*</label> <select id="slcModelo${modeloAtual}" class="form-select selectpicker" name="modelosRelatorio[]" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'> ${options} </select> </div> <div class="col col-md-3 col-lg-2"> <label class="form-label" for="txtQuantidadeModelos${modeloAtual}">Quantidade*</label> <input class="form-control" type="number" name="quantidadeModelos[]" id="txtQuantidadeModelos${modeloAtual}" required="required" autocomplete="off" value="1" min="1"> </div> </div>`;
    }

    $('#modelos-receita').on('click', '.btn-novo-modelo', function () {
        var btn = $(this);
        $.getJSON('./get-json.php?select=SELECT id, titulo_modelo_relatorio FROM modelos_relatorio', function (dados) {
            var optionsModelos = '<option value="" selected disabled>Selecione...</option>';
            if (!('erro' in dados)) {
                dados.forEach(function (modelo) {
                    optionsModelos += `<option value="${modelo.id}">${modelo.titulo_modelo_relatorio}</option>`;
                });
            }
            btn.parents('.row-modelo').after(htmlRowModelo(optionsModelos, qtModelos));
            $(`#slcModelo${qtModelos}`).select2($.extend({
                theme: 'bootstrap-5'
            }, $(`#slcModelo${qtModelos}`).data('options')));
            qtModelos++;
        });
    });

    $('#modelos-receita').on('click', '.btn-excluir-modelo', function () {
        if ($("#modelos-receita .row").length > 1) {
            $(this).parents('.row-modelo').prev().remove();
            $(this).parents('.row-modelo').remove();
        }
    });
}