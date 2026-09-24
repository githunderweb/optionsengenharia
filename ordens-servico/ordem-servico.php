<div class="card">
    <form action="./ordens-servico/ordem-servico-acao.php" method='POST' class='needs-validation' novalidate='novalidate'>

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nova ordem de serviço</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label" for="txtNumeroOS">Nº OS*</label>
                    <input type="text" id="txtNumeroOS" class="form-control" name="numeroOS" required="required" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcInspetor">Inspetor*</label>
                    <select id="slcInspetor" class="form-select selectpicker" name="inspetor" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <?php
                        $optionsInspetores = "<option value='' selected disabled>Selecione...</option>";
                        $qrInspetores = mysqli_query($connect, "SELECT id, nome_inspetor FROM inspetores");
                        while ($dadoInspetores = mysqli_fetch_array($qrInspetores)) {
                            $optionsInspetores .= "<option value='{$dadoInspetores['id']}'>{$dadoInspetores['nome_inspetor']}</option>";
                        }
                        echo $optionsInspetores;
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcEmpresa">Empresa*</label>
                    <select id="slcEmpresa" class="form-select selectpicker" name="empresa" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <?php
                        $optionsEmpresas = "<option value='' selected disabled>Selecione...</option>";
                        $qrEmpresas = mysqli_query($connect, "SELECT id, nome_empresa FROM empresas");
                        while ($dadoEmpresas = mysqli_fetch_array($qrEmpresas)) {
                            $optionsEmpresas .= "<option value='{$dadoEmpresas['id']}'>{$dadoEmpresas['nome_empresa']}</option>";
                        }
                        $optionsEmpresas = str_replace("value='{$empresaAtual}'", "value='{$empresaAtual}' selected", $optionsEmpresas);
                        echo $optionsEmpresas;
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcUnidade">Unidade*</label>
                    <select id="slcUnidade" class="form-select selectpicker" name="unidade" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <?php
                        $optionsUnidades = "<option value='' selected disabled>Selecione...</option>";
                        $qrUnidades = mysqli_query($connect, "SELECT id, titulo_unidade FROM unidades WHERE id_empresa = '{$empresaAtual}'");
                        while ($dadoUnidades = mysqli_fetch_array($qrUnidades)) {
                            $optionsUnidades .= "<option value='{$dadoUnidades['id']}'>{$dadoUnidades['titulo_unidade']}</option>";
                        }
                        echo $optionsUnidades;
                        ?>
                    </select>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="txtDescricao">Descrição</label>
                    <textarea id="txtDescricao" class="form-control" name="descricao" rows="4" autocomplete="off" placeholder="Digite uma descrição para a ordem de serviço..."></textarea>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div id="modelos-receita" class="bg-white p-3 pb-0 border rounded-1">

                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label d-block mb-0">Relatórios</label>
                        </div>

                        <hr style="margin: 20px 0 12px 0">
                        <div class="row row-modelo g-3 mb-3 position-relative">
                            <div class="position-absolute top-0 end-0 w-auto d-flex align-items-center gap-2">
                                <a role="button" class="link-padrao btn-novo-modelo"><i class="fas fa-plus-square"></i></a>
                                <a role="button" class="link-danger btn-excluir-modelo"> <i class="fas fa-trash-alt"></i></a>
                            </div>

                            <div class="col-12 col-md-9 col-lg-10">
                                <label class="form-label" for="slcModelo">Modelo relatório*</label>
                                <select id="slcModelo" class="form-select selectpicker" name="modelosRelatorio[]" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                                    <?php
                                    $optionsModelos = "<option value='' selected disabled>Selecione...</option>";
                                    $qrModelos = mysqli_query($connect, "SELECT id, titulo_modelo_relatorio FROM modelos_relatorio");
                                    while ($dadoModelos = mysqli_fetch_array($qrModelos)) {
                                        $optionsModelos .= "<option value='{$dadoModelos['id']}'>{$dadoModelos['titulo_modelo_relatorio']}</option>";
                                    }
                                    echo $optionsModelos;
                                    ?>
                                </select>
                            </div>
                            <div class="col col-md-3 col-lg-2">
                                <label class="form-label" for="txtQuantidadeModelos">Quantidade*</label> <input class="form-control" type="number" name="quantidadeModelos[]" id="txtQuantidadeModelos" required="required" autocomplete="off" value="1" min="1">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <button class="btn btn-padrao" type="submit">Salvar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="./assets/js/ordens-servico/ordens-servico.js<?= $version; ?>"></script>
