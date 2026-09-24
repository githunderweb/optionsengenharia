<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Unidades</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=unidade" class="btn btn-padrao btn-sm flex-fill">
                        <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Nova
                    </a>
                    <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>
            </div>

            <div class="table-responsive scrollbar pt-2 pe-md-1">
                <table class="dataTable table table-striped table-bordered mb-0" width="100%" cellspacing="0">
                    <thead class="bg-200 text-900">
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Cidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <!-- <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Cidade</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot> -->
                    <tbody class="list">
                        <?php
                        $qrEmpresaAtual = "";
                        if ($empresaAtual != "")
                            $qrEmpresaAtual = " WHERE id_empresa = '{$empresaAtual}'";

                        $qr = mysqli_query($connect, "SELECT * FROM unidades{$qrEmpresaAtual}");
                        while ($dado = mysqli_fetch_array($qr)) {
                            echo "
                            <tr>
                                <td>{$dado['titulo_unidade']}</td>
                                <td>{$dado['cnpj']}</td>
                                <td>{$dado['cidade']}</td>
                                <td style='text-align: center; width: 150px!important;'>
                                    <div class='btn-group' role='group'>
                                        <a class='btn btn-white btn-sm' href='{$link}p=unidades&acao=editar&id={$dado['id']}'>
                                            <i class='bi-pencil-fill me-2'></i>Editar
                                        </a>

                                        <div class='btn-group'>
                                            <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                            <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                <a class='dropdown-item' href='{$link}p=unidades&acao=excluir&id={$dado['id']}'>
                                                    <i class='bi-trash me-2 dropdown-item-icon'></i>Excluir
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        ";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
    ?>

        <div class="card">
            <?php

            $qr = mysqli_query($connect, "SELECT * FROM unidades WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form action='./unidades/unidade-acao.php?acao=editar&id=" . $dado['id'] . "' method='POST' class='needs-validation' novalidate='novalidate'>";

            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar unidade</h5>
                <a href="<?= "{$link}p=unidades"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md">
                        <label class="form-label" for="txtTitulo">Título unidade*</label>
                        <input class="form-control" type="text" name="titulo" required="required" id="txtTitulo" autocomplete="off" value="<?= $dado['titulo_unidade']; ?>">
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
                            $optionsEmpresas = str_replace("value='" . $dado['id_empresa']  . "'", "value='" . $dado['id_empresa']  . "' selected", $optionsEmpresas);
                            echo $optionsEmpresas;
                            ?>
                        </select>
                    </div>
                    <div class="col col-md-auto" style="width: 220px;">
                        <label class="form-label" for="txtCNPJ">CNPJ*</label>
                        <input class="form-control cnpj" type="text" name="cnpj" required="required" id="txtCNPJ" autocomplete="off" maxlength="18" value="<?= $dado['cnpj']; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-3 col-lg-2">
                        <label class="form-label" for="txtCEP">CEP</label>
                        <input class="form-control cep" id="txtCEP" name="cep" type="text" maxlength="9" value="<?= $dado['cep']; ?>" />
                    </div>
                    <div class="col-12 col-md-7 col-lg-9">
                        <label class="form-label" for="txtCidade">Cidade*</label>
                        <input class="form-control" id="txtCidade" name="cidade" required="required" type="text" value="<?= $dado['cidade']; ?>" />
                    </div>
                    <div class="col">
                        <label class="form-label" for="txtEstado">Estado</label>
                        <input class="form-control" id="txtEstado" name="estado" type="text" value="<?= $dado['estado']; ?>" />
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="txtEndereco">Endereço</label>
                        <input class="form-control" type="text" name="endereco" id="txtEndereco" autocomplete="off" value="<?= $dado['endereco']; ?>">
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="txtBairro">Bairro</label>
                        <input class="form-control" type="text" name="bairro" id="txtBairro" autocomplete="off" value="<?= $dado['bairro']; ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label" for="txtNumero">Número</label>
                        <input class="form-control" type="text" name="numero" id="txtNumero" autocomplete="off" value="<?= $dado['numero']; ?>">
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

    <?php
    } else if ($acao == 'excluir') {
    ?>

        <div class="card">
            <div class="card-body overflow-hidden">

                <div class="d-flex justify-content-end">
                    <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>

                <div class="row align-items-center p-lg-5 pt-lg-3">

                    <div class="col-lg-6">
                        <!-- <img class="img-fluid" src="./assets/img/illustrations/user_trash.png" alt=""> --> <?php include("./assets/illustrations/svg-delete.php"); ?>
                    </div>
                    <div class="col-lg-6 ps-lg-4 my-5 text-center text-lg-start">
                        <h3 class="text-padrao">Tem certeza de que deseja excluir esta unidade?</h3>
                        <?php
                        $qr = mysqli_query($connect, "SELECT * FROM unidades WHERE id ='{$id}'");
                        $dado = mysqli_fetch_array($qr);

                        echo "<form action='./unidades/unidade-acao.php?acao=excluir&id=" . $dado['id'] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Título: " . $dado['titulo_unidade'] . "<br>CNPJ: " . $dado['cnpj'];
                            ?>
                        </p>
                        <button id="btnExcluir" type="submit" class="btn btn-falcon-padrao">Confirmar exclusão</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
?>