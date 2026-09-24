<div class="card">
    <form action="./equipamentos/equipamento-acao.php" method="POST" class="needs-validation" novalidate="novalidate" enctype="multipart/form-data">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Novo equipamento</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg">
                    <label class="form-label" for="txtNome">Nome*</label>
                    <input type="text" id="txtNome" class="form-control" name="nome" required="required" autocomplete="off" maxlength="250">
                </div>
                <div class="col col-lg-auto" style="width: 120px;">
                    <label class="form-label" for="txtNumeroPasta">Nº pasta*</label>
                    <input type="number" id="txtNumeroPasta" class="form-control" name="numeroPasta" required="required" autocomplete="off" min="1">
                </div>
                <div class="col col-lg-auto" style="width: 150px;">
                    <label class="form-label" for="txtTag">TAG*</label>
                    <input type="text" id="txtTag" class="form-control" name="tag" required="required" autocomplete="off" maxlength="250">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcTipoEquipamento">Tipo equipamento*</label>
                    <select id="slcTipoEquipamento" class="form-select selectpicker" name="tipoEquipamento" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <?php
                        $optionsTiposEquipamento = "<option value='' selected disabled>Selecione...</option>";
                        $qrTiposEquipamento = mysqli_query($connect, "SELECT id, titulo_tipo_equipamento FROM tipos_equipamento");
                        while ($dadoTipoEquipamento = mysqli_fetch_array($qrTiposEquipamento)) {
                            $optionsTiposEquipamento .= "<option value='{$dadoTipoEquipamento['id']}'>{$dadoTipoEquipamento['titulo_tipo_equipamento']}</option>";
                        }
                        echo $optionsTiposEquipamento;
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
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcCategoria">Categoria*</label>
                    <select id="slcCategoria" class="form-select selectpicker" name="categoriaEquipamento" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <option value='' selected disabled>Selecione...</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <a id="btnAddCategoria" class="btn btn-falcon-padrao w-100" href="<?= $link ?>p=categoria-equipamento" target="_blank">Adicionar Categoria</a>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <label class="form-label" for="slcLocalInstalacao">Local de instalação*</label>
                    <select id="slcLocalInstalacao" class="form-select selectpicker" name="localInstalacaoEquipamento" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <option value='' selected disabled>Selecione...</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <a id="btnAddLocal" class="btn btn-falcon-padrao w-100" href="<?= $link ?>p=local-instalacao" target="_blank">Adicionar Local</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md">
                    <label class="form-label" for="txtFabricante">Fabricante</label>
                    <input type="text" id="txtFabricante" class="form-control" name="fabricante" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label" for="txtAnoFabricacao">Ano de Fabricação</label>
                    <input type="number" id="txtAnoFabricacao" class="form-control" name="anoFabricacao" min="1901" max="<?= date("Y"); ?>">
                </div>
                <div class="col-12 col-md">
                    <label class="form-label" for="txtMaterial">Material</label>
                    <input type="text" id="txtMaterial" class="form-control" name="material" autocomplete="off" maxlength="250">
                </div>
            </div>

            <div id="campos-nao-fixos"></div>

            <div class="row g-3">
                <div class="col-12">
                    <button class="btn btn-padrao" type="submit">Salvar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="./assets/js/equipamentos/equipamentos.js<?= $version; ?>"></script>
