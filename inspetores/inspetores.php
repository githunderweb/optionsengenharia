<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Inspetores</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=inspetor" class="btn btn-padrao btn-sm flex-fill">
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
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $qrInspetores = mysqli_query($connect, "SELECT * FROM inspetores");
                        while ($dadoInspetor = mysqli_fetch_array($qrInspetores)) {
                            echo "
                                <tr>
                                    <td>{$dadoInspetor['nome_inspetor']}</td>
                                    <td>{$dadoInspetor['email']}</td>
                                    <td>{$dadoInspetor['status_inspetor']}</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=inspetores&acao=editar&id={$dadoInspetor['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>Editar
                                            </a>

                                            <div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=inspetores&acao=excluir&id={$dadoInspetor['id']}'>
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

            $qr = mysqli_query($connect, "SELECT * FROM inspetores WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form action='./inspetores/inspetor-acao.php?acao=editar&id=" . $dado["id"] . "' method='POST' class='needs-validation' novalidate='novalidate' enctype='multipart/form-data'>";

            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar inspetor</h5>
                <a href="<?= "{$link}p=inspetores"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtNome">Nome*</label>
                        <input type="text" id="txtNome" class="form-control" name="nome" required="required" autocomplete="off" maxlength="250" value="<?= $dado["nome_inspetor"]; ?>">
                    </div>
                </div>
                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-12 col-md">
                        <label class="form-label" for="txtEmail">Email*</label>
                        <input type="email" id="txtEmail" class="form-control" name="email" required="required" autocomplete="off" pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" maxlength="250" readonly value="<?= $dado["email"]; ?>" />
                        <div class="invalid-feedback">Insira um email válido</div>
                    </div>
                    <div class="col-12 col-md">
                        <div class="position-relative d-none">
                            <label class="form-label" for="txtSenha">Senha*</label>
                            <input type="password" id="txtSenha" class="form-control" name="senha" autocomplete="off" style="padding-right: 44.1px;" />
                            <button type="button" class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="bottom: 1px; right: 1px; padding: 4px 10px; width: 42px;">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <button id="btnNovaSenha" type="button" class="btn btn-falcon-padrao w-100">Nova Senha</button>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label" for="slcStatus">Status*</label>
                        <select id="slcStatus" class="form-select" name="status" required="required">
                            <?php
                            $optionsStatus = '<option value="" disabled>Selecione...</option> <option value="Ativo">Ativo</option> <option value="Desativado">Desativado</option>';
                            $optionsStatus = str_replace('value="' . $dado["status_inspetor"]  . '"',  '" selected', $optionsStatus);
                            echo $optionsStatus;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 g-md-2 mb-3">
                    <div class="col-12">
                        <label class="form-label m-0" for="fleImgAssinatura">Assinatura</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="d-flex h-100 align-items-center justify-content-center" style="max-height: 160px;">
                            <img id="imgAssinatura" class="img-fluid rounded-1" src="./assets/img/<?= $dado["img_assinatura"] != "" ? "inspetores/assinaturas/{$dado["img_assinatura"]}" : "imagem-padrao.jpg"; ?>" style="max-height: 100%;" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <div class="file-arrasta-solta h-100">
                            <input type="file" id="fleImgAssinatura" class="form-control" name="imgAssinatura" accept="image/*" />
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtDescricaoAssinatura">Descrição assinatura</label>
                        <textarea id="txtDescricaoAssinatura" class="form-control" name="descricaoAssinatura" rows="5" autocomplete="off" maxlength="1000"><?= $dado["descricao_assinatura"]; ?></textarea>
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

        <script src="./assets/js/inspetores/inspetores.js<?= $version; ?>"></script>

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
                        <h3 class="text-padrao">Tem certeza de que deseja excluir este inspetor?</h3>
                        <?php
                        $qrInspetor = mysqli_query($connect, "SELECT * FROM inspetores WHERE id ='{$id}'");
                        $dadoInspetor = mysqli_fetch_array($qrInspetor);

                        echo "<form action='./inspetores/inspetor-acao.php?acao=excluir&id=" . $dadoInspetor["id"] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Título: " . $dadoInspetor["nome_inspetor"] . "<br>Email: " . $dadoInspetor["email"] . "<br>Status: " . $dadoInspetor["status_inspetor"];
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