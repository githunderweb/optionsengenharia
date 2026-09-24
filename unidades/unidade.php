<div class="card">
    <form action="./unidades/unidade-acao.php" method='POST' class='needs-validation' novalidate='novalidate'>

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Nova unidade</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md">
                    <label class="form-label" for="txtTitulo">Título unidade*</label>
                    <input class="form-control" type="text" name="titulo" required="required" id="txtTitulo" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-lg">
                    <label class="form-label" for="slcEmpresa">Empresa*</label>
                    <select class="form-select selectpicker" id="slcEmpresa" name="empresa" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                        <?php
                        $optionsEmpresas = "<option value='' selected disabled>Selecione...</option>";
                        $qrEmpresas = mysqli_query($connect, "SELECT id, nome_empresa FROM empresas");
                        while ($dadoEmpresas = mysqli_fetch_array($qrEmpresas)) {
                            $optionsEmpresas .= "<option value='" . $dadoEmpresas['id'] . "'>" . $dadoEmpresas['nome_empresa'] . "</option>";
                        }
                        $optionsEmpresas = str_replace("value='" . $empresaAtual  . "'", "value='" . $empresaAtual  . "' selected", $optionsEmpresas);
                        echo $optionsEmpresas;
                        ?>
                    </select>
                </div>
                <div class="col col-md-auto" style="width: 220px;">
                    <label class="form-label" for="txtCNPJ">CNPJ*</label>
                    <input class="form-control cnpj" type="text" name="cnpj" required="required" id="txtCNPJ" autocomplete="off" maxlength="18">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label" for="txtCEP">CEP</label>
                    <input class="form-control cep" id="txtCEP" name="cep" type="text" maxlength="9">
                </div>
                <div class="col-12 col-md-7 col-lg-9">
                    <label class="form-label" for="txtCidade">Cidade*</label>
                    <input class="form-control" id="txtCidade" name="cidade" required="required" type="text" autocomplete="off" maxlength="250">
                </div>
                <div class="col">
                    <label class="form-label" for="txtEstado">Estado</label>
                    <input class="form-control" id="txtEstado" name="estado" type="text" autocomplete="off" maxlength="2">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-5">
                    <label class="form-label" for="txtEndereco">Endereço</label>
                    <input class="form-control" type="text" name="endereco" id="txtEndereco" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label" for="txtBairro">Bairro</label>
                    <input class="form-control" type="text" name="bairro" id="txtBairro" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="txtNumero">Número</label>
                    <input class="form-control" type="text" name="numero" id="txtNumero" autocomplete="off" maxlength="11">
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