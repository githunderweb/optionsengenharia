<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Locais de instalação</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=local-instalacao" class="btn btn-padrao btn-sm flex-fill">
                        <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Novo
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
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $qrEmpresaAtual = "";
                        if ($empresaAtual != "")
                            $qrEmpresaAtual = "WHERE ce.id_empresa = '{$empresaAtual}'";

                        $qr = mysqli_query($connect, "SELECT ce.id, ce.titulo_local_instalacao, e.nome_empresa, u.titulo_unidade FROM locais_instalacao ce INNER JOIN empresas e ON e.id = ce.id_empresa INNER JOIN unidades u ON u.id = ce.id_unidade {$qrEmpresaAtual}");
                        while ($dado = mysqli_fetch_array($qr)) {
                            echo "
                                <tr>
                                    <td>{$dado['titulo_local_instalacao']}</td>
                                    <td>{$dado['nome_empresa']}</td>
                                    <td>{$dado['titulo_unidade']}</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=locais-instalacao&acao=editar&id={$dado['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>Editar
                                            </a>

                                            <div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=locais-instalacao&acao=excluir&id={$dado['id']}'>
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

            $qr = mysqli_query($connect, "SELECT * FROM locais_instalacao WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form action='./locais-instalacao/local-instalacao-acao.php?acao=editar&id=" . $dado['id'] . "' method='POST' class='needs-validation' novalidate='novalidate'>";

            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar local de instalação</h5>
                <a href="<?= "{$link}p=locais-instalacao"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-lg">
                        <label class="form-label" for="txtTitulo">Título*</label>
                        <input type="text" id="txtTitulo" class="form-control" name="titulo" required="required" autocomplete="off" maxlength="250" value="<?= $dado['titulo_local_instalacao']; ?>">
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
                            $optionsEmpresas = str_replace("value='{$dado['id_empresa']}'", "value='{$dado['id_empresa']}' selected", $optionsEmpresas);
                            echo $optionsEmpresas;
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label" for="slcUnidade">Unidade*</label>
                        <select id="slcUnidade" class="form-select selectpicker" name="unidade" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                            <?php
                            $optionsUnidades = "<option value='' selected disabled>Selecione...</option>";
                            $qrUnidades = mysqli_query($connect, "SELECT id, titulo_unidade FROM unidades WHERE id_empresa = '{$dado['id_empresa']}'");
                            while ($dadoUnidades = mysqli_fetch_array($qrUnidades)) {
                                $optionsUnidades .= "<option value='{$dadoUnidades['id']}'>{$dadoUnidades['titulo_unidade']}</option>";
                            }
                            $optionsUnidades = str_replace("value='{$dado['id_unidade']}'", "value='{$dado['id_unidade']}' selected", $optionsUnidades);
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
                        <h3 class="text-padrao">Tem certeza de que deseja excluir este local de instalação?</h3>
                        <?php
                        $qr = mysqli_query($connect, "SELECT ce.id, ce.titulo_local_instalacao, e.nome_empresa, u.titulo_unidade FROM locais_instalacao ce INNER JOIN empresas e ON e.id = ce.id_empresa INNER JOIN unidades u ON u.id = ce.id_unidade WHERE ce.id ='{$id}'");
                        $dado = mysqli_fetch_array($qr);

                        echo "<form action='./locais-instalacao/local-instalacao-acao.php?acao=excluir&id=" . $dado['id'] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Título: " . $dado['titulo_local_instalacao'] . "<br>Empresa: " . $dado['nome_empresa'] . "<br>Unidade: " . $dado['titulo_unidade'];
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