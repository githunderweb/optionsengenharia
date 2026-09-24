<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Usuários</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=usuario" class="btn btn-padrao btn-sm flex-fill">
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
                            <th>Função</th>
                            <th>Empresas</th>
                            <th>Tipos de equipamento</th>
                            <th>Último login</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Função</th>
                            <th>Empresas</th>
                            <th>Tipos de equipamento</th>
                            <th>Último login</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $qrEmpresaAtual = "";
                        if ($empresaAtual != "")
                            $qrEmpresaAtual = " WHERE EXISTS (SELECT 1 FROM usuario_empresas ue_filtro WHERE ue_filtro.id_usuario = u.id AND ue_filtro.id_empresa = '{$empresaAtual}')";

                        $qr = mysqli_query($connect, "SELECT u.id, u.nome, u.email, u.funcao, COALESCE(GROUP_CONCAT(DISTINCT e.nome_empresa ORDER BY e.nome_empresa SEPARATOR ', '), e_principal.nome_empresa) AS nome_empresa, GROUP_CONCAT(DISTINCT te.titulo_tipo_equipamento ORDER BY te.titulo_tipo_equipamento SEPARATOR ', ') AS tipos_equipamento, u.ultimo_login, u.status_usuario FROM usuarios u LEFT JOIN usuario_empresas ue ON ue.id_usuario = u.id LEFT JOIN empresas e ON e.id = ue.id_empresa LEFT JOIN empresas e_principal ON e_principal.id = u.id_empresa LEFT JOIN usuario_tipos_equipamento ute ON ute.id_usuario = u.id LEFT JOIN tipos_equipamento te ON te.id = ute.id_tipo_equipamento{$qrEmpresaAtual} GROUP BY u.id");
                        while ($dado = mysqli_fetch_array($qr)) {
                            $ultimoLogin = "";
                            if ($dado['ultimo_login'] != "")
                                $ultimoLogin = date("d/m/Y H:i", strtotime($dado['ultimo_login']));
                            echo "
                                <tr>
                                    <td>{$dado['nome']}</td>
                                    <td>{$dado['email']}</td>
                                    <td>{$dado['funcao']}</td>
                                    <td>{$dado['nome_empresa']}</td>
                                    <td>{$dado['tipos_equipamento']}</td>
                                    <td>{$ultimoLogin}</td>
                                    <td>{$dado['status_usuario']}</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=usuarios&acao=editar&id={$dado['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>Editar
                                            </a>

                                            <!--<div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>
                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=usuarios&acao=excluir&id={$dado['id']}'>
                                                        <i class='bi-trash me-2 dropdown-item-icon'></i>Excluir
                                                    </a>
                                                </div>
                                            </div>-->
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

            $qr = mysqli_query($connect, "SELECT * FROM usuarios WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form action='./usuarios/usuario-acao.php?acao=editar&id=" . $dado['id'] . "' method='POST'  enctype='multipart/form-data' class='needs-validation' novalidate='novalidate'>";

            ?>

            <div class="card-header d-flex justify-content-between align-items-center bg-light px-lg-6">
                <h5 class="mb-0">Editar usuário</h5>
                <a href="<?= "{$link}p=usuarios"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body px-lg-6 py-lg-4">

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtNome">Nome*</label>
                        <input class="form-control" type="text" name="nome" placeholder="Nome completo" required="required" id="txtNome" autocomplete="off" value="<?php echo $dado['nome']; ?>">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtUsuario">Usuário*</label>
                        <input class="form-control" type="text" name="usuario" placeholder="Nome de usuário" required="required" id="txtUsuario" autocomplete="off" value="<?php echo $dado['usuario']; ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtEmail">Email*</label>
                        <input class="form-control" type="email" name="email" placeholder="Endereço de e-mail" readonly required="required" id="txtEmail" pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" data-wizard-validate-email="true" autocomplete="off" value="<?php echo $dado['email']; ?>">
                        <div class="invalid-feedback">Insira um email válido</div>
                    </div>
                </div>
                <div id="alterar-senha" class="mb-3">
                    <div class="row g-3 d-none">
                        <div class="col-md-6">
                            <div class="position-relative"> <label class="form-label" for="txtSenha">Senha*</label>
                                <input class="form-control" type="password" name="senha" id="txtSenha" autocomplete="new-password" style="padding-right: 44.1px;" /> <button class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="bottom: 1px; right: 1px; padding: 4px 10px; width: 42px;" type="button"> <i class="far fa-eye"></i> </button>
                            </div>
                        </div>
                        <div class="col-md-6"> <label class="form-label" for="txtConfirmarSenha">Confirmar senha*</label>
                            <div class="position-relative"> <input class="form-control" type="password" id="txtConfirmarSenha" autocomplete="new-password" style="padding-right: 44.1px;" /> <button class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="top: 1px; right: 1px; padding: 4px 10px; width: 42px;" type="button"> <i class="far fa-eye"></i> </button> </div>
                            <div id="ifConfirmarSenha" class="invalid-feedback">As senhas não são iguais!</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <button id="btnNovaSenha" type="button" class="btn btn-falcon-padrao w-100">Nova Senha</button>
                        </div>
                    </div>
                </div>
                <div class="mb-3 py-3">
                    <div class="row">
                        <?php
                        $imgUser = "team/avatar.png";
                        if ($dado['img'] != NULL) {
                            $imgUser = "usuarios/{$dado['img']}";

                            echo "<div class='form-check d-none'><input class='form-check-input' name='removerImagem' type='checkbox' value='Remover' /></div>";
                        }
                        ?>

                        <div class="col-md-auto d-flex align-items-center justify-content-center px-5">
                            <div class="avatar avatar-4xl">
                                <img id="imgPerfil" class="rounded-circle border bg-white" src="./assets/img/<?= $imgUser; ?>">
                            </div>
                        </div>
                        <div class="col-md">
                            <label class="form-label" for="flImagemPerfil">Imagem de perfil</label>
                            <input id="flImagemPerfil" class="form-control" type="file" name="img" accept="image/*" />
                        </div>

                        <div class="col-md-auto d-flex align-items-center">
                            <button id="btnRemoverImagem" type="button" class="btn btn-falcon-danger">Remover imagem de perfil</button>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="slcFuncao">Nível de acesso*</label>
                        <select class="form-select" name="funcao" id="slcFuncao" required="required">
                            <?php
                            $optionsFuncao = '<option value="" disabled>Selecione...</option> <option value="Cliente">Cliente</option> <option value="Administrador">Administrador</option>';
                            $optionsFuncao = str_replace('value="' . $dado['funcao']  . '"', 'value="' . $dado['funcao']  . '" selected', $optionsFuncao);
                            echo $optionsFuncao;
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">

                        <label class="form-label" for="slcStatus">Status</label>
                        <select class="form-select" name="status" id="slcStatus" required="required">
                            <?php
                            $optionsStatus = '<option value="" disabled>Selecione...</option> <option value="Ativo">Ativo</option> <option value="Desativado">Desativado</option>';
                            $optionsStatus = str_replace('value="' . $dado['status_usuario']  . '"', 'value="' . $dado['status_usuario'] . '" selected', $optionsStatus);
                            echo $optionsStatus;
                            ?>
                        </select>

                    </div>
                    <!-- <div class="empresa-funcao col-12">
                        <?php
                        $slcEmpresa = '<div class="collapse';
                        if ($dado["funcao"] == "Cliente")
                            $slcEmpresa .= ' show';
                        $slcEmpresa .= '">';
                        $slcEmpresa .= '<label class="form-label" for="slcEmpresa">Empresa*</label><select class="form-select js-choice" name="empresa" id="slcEmpresa" required="required" data-options=\'{"removeItemButton":true,"placeholder":true, "noResultsText": "Nenhum resultado encontrado"}\'>';

                        $optionsEmpresas = "<option value='' disabled selected>Selecione...</option>";
                        $qrEmpresas = mysqli_query($connect, "SELECT * FROM empresas");
                        while ($dadoEmpresas = mysqli_fetch_array($qrEmpresas)) {
                            $optionsEmpresas .= "<option value='" . $dadoEmpresas['id'] . "'>" . $dadoEmpresas['nome_empresa'] . "</option>";
                        }
                        $slcEmpresa .= $optionsEmpresas . '</select></div>';

                        if ($dado['funcao'] == "Cliente") {
                            $slcEmpresa = str_replace("value='" . $dado['id_empresa']  . "'", "value='" . $dado['id_empresa']  . "' selected", $slcEmpresa);
                            echo $slcEmpresa;
                        } else
                            $slcEmpresa = str_replace("value='" . $empresaAtual  . "'", "value='" . $empresaAtual  . "' selected", $slcEmpresa);
                        ?>
                    </div> -->
                </div>
                <?php
                $usuarioCliente = false;
                if ($dado['funcao'] == "Cliente")
                    $usuarioCliente = true;
                ?>
                <div class="row g-3 mb-3 empresa-funcao collapse <?php if ($usuarioCliente) echo "show"; ?>">
                    <div class="col-12">
                        <label class="form-label" for="slcEmpresa">Empresas*</label>
                        <select class="form-select selectpicker" id="slcEmpresa" name="empresas[]" multiple data-options='{"placeholder":"Selecione uma ou mais empresas...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?php if ($usuarioCliente) echo "required"; ?>>
                            <?php
                            $optionsEmpresas = "";

                            if ($usuarioCliente) {
                                $empresasSelecionadas = [];
                                $qrEmpresasUsuario = mysqli_query($connect, "SELECT id_empresa FROM usuario_empresas WHERE id_usuario = '{$dado['id']}'");
                                while ($dadoEmpresaUsuario = mysqli_fetch_assoc($qrEmpresasUsuario))
                                    $empresasSelecionadas[] = (string) $dadoEmpresaUsuario["id_empresa"];

                                if (count($empresasSelecionadas) == 0 && $dado['id_empresa'] != "")
                                    $empresasSelecionadas[] = (string) $dado['id_empresa'];

                                $qr = mysqli_query($connect, "SELECT * FROM empresas");
                                while ($dadoEmpresas = mysqli_fetch_array($qr)) {
                                    $selected = in_array((string) $dadoEmpresas["id"], $empresasSelecionadas, true) ? " selected" : "";
                                    $optionsEmpresas .= "<option value='{$dadoEmpresas["id"]}'{$selected}>{$dadoEmpresas["nome_empresa"]}</option>";
                                }
                            }

                            echo $optionsEmpresas;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3 tipo-equipamento-funcao collapse <?php if ($usuarioCliente) echo "show"; ?>">
                    <div class="col-12">
                        <label class="form-label" for="slcTiposEquipamento">Tipos de equipamento*</label>
                        <select class="form-select selectpicker" id="slcTiposEquipamento" name="tiposEquipamento[]" multiple data-options='{"placeholder":"Selecione um ou mais tipos...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?php if ($usuarioCliente) echo "required"; ?>>
                            <?php
                            $tiposEquipamentoSelecionados = [];
                            if ($usuarioCliente) {
                                $qrTiposEquipamentoUsuario = mysqli_query($connect, "SELECT id_tipo_equipamento FROM usuario_tipos_equipamento WHERE id_usuario = '{$dado['id']}'");
                                while ($dadoTipoEquipamentoUsuario = mysqli_fetch_assoc($qrTiposEquipamentoUsuario))
                                    $tiposEquipamentoSelecionados[] = (string) $dadoTipoEquipamentoUsuario["id_tipo_equipamento"];
                            }

                            $qrTiposEquipamentoUsuario = mysqli_query($connect, "SELECT id, titulo_tipo_equipamento FROM tipos_equipamento ORDER BY titulo_tipo_equipamento");
                            while ($dadoTipoEquipamentoUsuario = mysqli_fetch_assoc($qrTiposEquipamentoUsuario)) {
                                $selected = in_array((string) $dadoTipoEquipamentoUsuario["id"], $tiposEquipamentoSelecionados, true) ? " selected" : "";
                                echo "<option value='{$dadoTipoEquipamentoUsuario["id"]}'{$selected}>" . htmlspecialchars($dadoTipoEquipamentoUsuario["titulo_tipo_equipamento"], ENT_QUOTES, "UTF-8") . "</option>";
                            }
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
                            <img id="imgAssinatura" class="img-fluid rounded-1" src="./assets/img/<?= $dado["img_assinatura"] != "" ? "usuarios/assinaturas/{$dado["img_assinatura"]}" : "imagem-padrao.jpg"; ?>" style="max-height: 100%;" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <div class="file-arrasta-solta h-100">
                            <input type="file" id="fleImgAssinatura" class="form-control" name="imgAssinatura" accept="image/*" />
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="txtDescricaoAssinatura">Descrição assinatura</label>
                        <textarea id="txtDescricaoAssinatura" class="form-control" name="descricaoAssinatura" rows="5" autocomplete="off" maxlength="1000"><?= $dado["descricao_assinatura"]; ?></textarea>
                    </div>
                </div>


            </div>

            <div class="card-footer bg-light px-lg-6">
                <button id="btnSalvar" class="btn btn-padrao" type="submit">Salvar usuário</button>
            </div>

            </form>
        </div>

        <!-- <script>
            var slcEmpresaAtual = `<?= $slcEmpresa ?>`;
        </script> -->

        <script src="./assets/js/usuarios/usuario.js<?= $version; ?>"></script>

    <?php
    } else if (/*$acao == 'excluir'*/false) {
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
                        <h3 class="text-padrao">Tem certeza de que deseja excluir este usuário?</h3>
                        <?php
                        $qr = mysqli_query($connect, "SELECT * FROM usuarios WHERE id ='{$id}'");
                        $dado = mysqli_fetch_array($qr);

                        echo "<form action='./usuarios/usuario-acao.php?acao=excluir&id=" . $dado['id'] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Nome: " . $dado['nome'] . "<br>Email: " . $dado['email'] . "<br>Função: " . $dado['funcao'];
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
