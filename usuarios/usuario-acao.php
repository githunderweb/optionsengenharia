<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include('../verifica-login.php');
include("../functions.php");

function empresasValidasUsuario($connect, $empresas)
{
    if (!is_array($empresas))
        return [];

    $ids = array_values(array_unique(array_filter(array_map('intval', $empresas))));
    if (count($ids) == 0)
        return [];

    $listaIds = implode(',', $ids);
    $idsValidos = [];
    $qrEmpresas = mysqli_query($connect, "SELECT id FROM empresas WHERE id IN ({$listaIds})");
    while ($dadoEmpresa = mysqli_fetch_assoc($qrEmpresas))
        $idsValidos[] = intval($dadoEmpresa['id']);

    $idsOrdenados = $ids;
    sort($idsValidos);
    sort($idsOrdenados);
    return $idsValidos === $idsOrdenados ? $ids : [];
}

function tiposEquipamentoValidosUsuario($connect, $tiposEquipamento)
{
    if (!is_array($tiposEquipamento))
        return [];

    $ids = array_values(array_unique(array_filter(array_map('intval', $tiposEquipamento))));
    if (count($ids) == 0)
        return [];

    $listaIds = implode(',', $ids);
    $idsValidos = [];
    $qrTipos = mysqli_query($connect, "SELECT id FROM tipos_equipamento WHERE id IN ({$listaIds})");
    while ($dadoTipo = mysqli_fetch_assoc($qrTipos))
        $idsValidos[] = intval($dadoTipo['id']);

    $idsOrdenados = $ids;
    sort($idsValidos);
    sort($idsOrdenados);
    return $idsValidos === $idsOrdenados ? $ids : [];
}

function salvarVinculosUsuario($connect, $idUsuario, $empresas, $tiposEquipamento)
{
    $idUsuario = intval($idUsuario);
    $connect->query("DELETE FROM usuario_empresas WHERE id_usuario = '{$idUsuario}'");
    $connect->query("DELETE FROM usuario_tipos_equipamento WHERE id_usuario = '{$idUsuario}'");

    foreach ($empresas as $idEmpresa) {
        $idEmpresa = intval($idEmpresa);
        $connect->query("INSERT INTO usuario_empresas (id_usuario, id_empresa) VALUES ('{$idUsuario}', '{$idEmpresa}')");
    }

    foreach ($tiposEquipamento as $idTipoEquipamento) {
        $idTipoEquipamento = intval($idTipoEquipamento);
        $connect->query("INSERT INTO usuario_tipos_equipamento (id_usuario, id_tipo_equipamento) VALUES ('{$idUsuario}', '{$idTipoEquipamento}')");
    }
}

$acao = @$_GET["acao"];
$perfil = @$_GET['perfil'];
$link = $_SESSION["empresa_atual_link"];

if ($acao == "") {

    $nome = mysqli_real_escape_string($connect, trim($_POST['nome']));
    $usuario = mysqli_real_escape_string($connect, trim($_POST['usuario']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $senha = mysqli_real_escape_string($connect, trim($_POST['senha']));
    $funcao = mysqli_real_escape_string($connect, trim($_POST['funcao']));
    $empresas = $funcao == "Cliente" ? empresasValidasUsuario($connect, $_POST["empresas"] ?? []) : [];
    $tiposEquipamento = $funcao == "Cliente" ? tiposEquipamentoValidosUsuario($connect, $_POST["tiposEquipamento"] ?? []) : [];
    if ($funcao == "Cliente" && count($empresas) == 0) {
        $_SESSION["alert_danger"] = "Selecione pelo menos uma empresa para o cliente.";
        header("Location: .{$link}p=usuario");
        exit();
    }
    if ($funcao == "Cliente" && count($tiposEquipamento) == 0) {
        $_SESSION["alert_danger"] = "Selecione pelo menos um tipo de equipamento para o cliente.";
        header("Location: .{$link}p=usuario");
        exit();
    }

    $empresa = count($empresas) > 0 ? $empresas[0] : "";

    $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE email = '{$email}'";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe um usuário com o email informado";
        header("Location: .{$link}p=usuario");
        exit();
    }

    $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE usuario = '{$usuario}'";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! O nome de usuário informado já está em uso";
        header("Location: .{$link}p=usuario");
        exit();
    }

    $img = NULL;
    if ($_FILES['img']['size'] > 0) {
        $extensao = explode(".", strtolower($_FILES['img']['name']));
        $extensao = "." . $extensao[count($extensao) - 1];
        $img = md5(time()) . $extensao;
        $diretorio = "../assets/img/usuarios/";

        move_uploaded_file($_FILES['img']['tmp_name'], $diretorio . $img);
    }

    $imgAssinatura = "";
    if ($_FILES["imgAssinatura"]["tmp_name"]  != "") {
        $extensao = mb_strtolower(end(explode(".", strtolower($_FILES["imgAssinatura"]["name"]))));
        $imgAssinatura = md5(time()) . "." . $extensao;
        $diretorio = "../assets/img/usuarios/assinaturas/";

        move_uploaded_file($_FILES["imgAssinatura"]["tmp_name"], $diretorio . $imgAssinatura);
    }

    $descricaoAssinatura = mysqli_real_escape_string($connect, trim($_POST["descricaoAssinatura"]));

    $insertUsuario = "INSERT INTO usuarios (nome, usuario, status_usuario, email, senha, funcao, id_empresa, id_unidade, img, img_assinatura, descricao_assinatura) VALUES ('{$nome}', '{$usuario}', 'Ativo','{$email}', MD5('{$senha}'), '{$funcao}', '{$empresa}', NULL, '{$img}', '{$imgAssinatura}', '{$descricaoAssinatura}')";
    if ($connect->query($insertUsuario) === TRUE) {
        salvarVinculosUsuario($connect, $connect->insert_id, $empresas, $tiposEquipamento);
        $_SESSION["alert_success"] = "Usuário cadastrado com sucesso!";

        // Dispara e-mail notificando o novo usuário
        $para = $email;
        $assunto = "Acesso liberado";
        $mensagem = "Prezado {$nome},\r\n\r\nSeu acesso ao GED foi liberado, segue abaixo suas credenciais de acesso.\r\nUsuário: {$usuario}\r\nSenha: {$senha}\r\nQuaisquer dúvidas/problemas, por favor entre em contato no e-mail: ti@optionsengenharia.com.br\r\n\r\nAtenciosamente, Options Engenharia";
        enviarEmail($para, $assunto, $mensagem);
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar usuário";

    $connect->close();

    header("Location: .{$link}p=usuarios");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $nome = mysqli_real_escape_string($connect, trim($_POST['nome']));
        $usuario = mysqli_real_escape_string($connect, trim($_POST['usuario']));
        $email = mysqli_real_escape_string($connect, trim($_POST['email']));
        $senha = mysqli_real_escape_string($connect, trim(md5($_POST['senha'] ?? "")));
        $funcao = mysqli_real_escape_string($connect, trim($_POST['funcao']));
        $empresas = $funcao == "Cliente" ? empresasValidasUsuario($connect, $_POST["empresas"] ?? []) : [];
        $tiposEquipamento = $funcao == "Cliente" ? tiposEquipamentoValidosUsuario($connect, $_POST["tiposEquipamento"] ?? []) : [];
        if ($funcao == "Cliente" && count($empresas) == 0) {
            $_SESSION["alert_danger"] = "Selecione pelo menos uma empresa para o cliente.";
            header("Location: .{$link}p=usuarios&acao=editar&id={$id}");
            exit();
        }
        if ($funcao == "Cliente" && count($tiposEquipamento) == 0) {
            $_SESSION["alert_danger"] = "Selecione pelo menos um tipo de equipamento para o cliente.";
            header("Location: .{$link}p=usuarios&acao=editar&id={$id}");
            exit();
        }

        $empresa = count($empresas) > 0 ? $empresas[0] : "";
        $status = mysqli_real_escape_string($connect, trim($_POST['status']));

        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE usuario = '{$usuario}' AND id != '{$id}'";
        $result = mysqli_query($connect, $sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Este nome de usuário já está sendo usado";
            header("Location: .{$link}p=usuarios&acao=editar&id={$id}");
            exit();
        }

        $qr = mysqli_query($connect, "SELECT * FROM usuarios WHERE id = '{$id}'");
        $dado = mysqli_fetch_array($qr);

        $img = $dado['img'];
        if ($_FILES['img']['tmp_name'] != "") {

            if ($dado['img'] != NULL) {
                if (is_file("../assets/img/usuarios/" . $dado['img']))
                    unlink("../assets/img/usuarios/" . $dado['img']);
            }

            $extensao = explode(".", strtolower($_FILES['img']['name']));
            $extensao = "." . $extensao[count($extensao) - 1];
            $img = md5(time()) . $extensao;
            $diretorio = "../assets/img/usuarios/";

            move_uploaded_file($_FILES['img']['tmp_name'], $diretorio . $img);
        } else if (isset($_POST['removerImagem'])) {

            if ($dado['img'] != NULL) {
                if (is_file("../assets/img/usuarios/" . $dado['img']))
                    unlink("../assets/img/usuarios/" . $dado['img']);
            }

            $img = "";
        }

        $sqlSenha = "";
        if ($senha != mysqli_real_escape_string($connect, trim(md5(""))))
            $sqlSenha = ", senha = '{$senha}'";


        $imgAssinatura = $dado["img_assinatura"];
        if ($_FILES["imgAssinatura"]["tmp_name"] != "") {
            $diretorio = "../assets/img/usuarios/assinaturas/";

            $imgAssinatura = "";
            if ($dado["img_assinatura"] != "" && is_file($diretorio . $dado["img_assinatura"]))
                unlink($diretorio . $dado["img_assinatura"]);

            $extensao = mb_strtolower(end(explode(".", strtolower($_FILES["imgAssinatura"]["name"]))));
            $imgAssinatura = md5(time()) . "." . $extensao;

            move_uploaded_file($_FILES["imgAssinatura"]["tmp_name"], $diretorio . $imgAssinatura);
        }

        $descricaoAssinatura = mysqli_real_escape_string($connect, trim($_POST["descricaoAssinatura"]));

        $updateUsuario = "UPDATE usuarios SET nome = '{$nome}', status_usuario = '{$status}', usuario = '{$usuario}'{$sqlSenha}, funcao = '{$funcao}', id_empresa = '{$empresa}', id_unidade = NULL, img = '{$img}', img_assinatura = '{$imgAssinatura}', descricao_assinatura = '{$descricaoAssinatura}' WHERE id = '{$id}'";
        if ($connect->query($updateUsuario) === TRUE) {
            salvarVinculosUsuario($connect, $id, $empresas, $tiposEquipamento);

            $connect->close();

            if ($perfil == "true") {
                $_SESSION["alert_success"] = "Perfil atualizado com sucesso!";

                header("Location: .{$link}p=perfil");
                exit();
            } else {
                $_SESSION["alert_success"] = "Usuário alterado com sucesso!";

                header("Location: .{$link}p=usuarios&acao=editar&id={$id}");
                exit();
            }
        } else {

            $connect->close();

            if ($perfil == "true") {
                $_SESSION["alert_danger"] = "Erro ao editar seu perfil";

                header("Location: .{$link}p=perfil");
                exit();
            } else {
                $_SESSION["alert_danger"] = "Erro ao editar usuário";

                header("Location: .{$link}p=usuarios&acao=editar&id={$id}");
                exit();
            }
        }
    } else if (/*$acao == "excluir"*/false) {

        $qr = mysqli_query($connect, "SELECT * FROM usuarios WHERE id = '{$id}'");
        $dado = mysqli_fetch_array($qr);

        if ($dado['img'] != NULL) {
            if (is_file("../assets/img/usuarios/" . $dado['img']))
                unlink("../assets/img/usuarios/" . $dado['img']);
        }

        $deleteUsuario = "DELETE FROM usuarios WHERE id = '{$id}'";
        if ($connect->query($deleteUsuario) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de usuário efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir usuário";

        $connect->close();

        header("Location: .{$link}p=usuarios");
        exit();
    }
}
