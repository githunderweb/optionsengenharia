<div class="card">
    <form action="./categorias-equipamento/categoria-equipamento-acao.php" method="POST" class="needs-validation" novalidate="novalidate">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nova categoria de equipamento</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg">
                    <label class="form-label" for="txtTitulo">Título categoria*</label>
                    <input type="text" id="txtTitulo" class="form-control" name="titulo" required="required" autocomplete="off" maxlength="250">
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

            <div class="row g-3">
                <div class="col-12">
                    <button class="btn btn-padrao" type="submit">Salvar</button>
                </div>
            </div>
        </div>
    </form>
</div>