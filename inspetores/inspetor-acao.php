<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];


if ($acao == "") {

    $nome = mysqli_real_escape_string($connect, trim($_POST["nome"]));
    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM inspetores WHERE nome_inspetor = '{$nome}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma inspetor com o nome informado";
        header("Location: .{$link}p=inspetor");
        exit();
    }

    $email = mysqli_real_escape_string($connect, trim($_POST["email"]));
    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM inspetores WHERE email = '{$email}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe um inspetor com o email informado";
        header("Location: .{$link}p=inspetor");
        exit();
    }

    $senha = mysqli_real_escape_string($connect, trim($_POST["senha"]));

    $imgAssinatura = "";
    if ($_FILES["imgAssinatura"]["tmp_name"]  != "") {
        $extensao = mb_strtolower(end(explode(".", strtolower($_FILES["imgAssinatura"]["name"]))));
        $imgAssinatura = md5(time()) . "." . $extensao;
        $diretorio = "../assets/img/inspetores/assinaturas/";

        move_uploaded_file($_FILES["imgAssinatura"]["tmp_name"], $diretorio . $imgAssinatura);
    }

    $descricaoAssinatura = mysqli_real_escape_string($connect, trim($_POST["descricaoAssinatura"]));

    $insertInspetor = "INSERT INTO inspetores (nome_inspetor, email, senha, img_assinatura, descricao_assinatura, status_inspetor, data_criacao) VALUES ('{$nome}', '{$email}', MD5('{$senha}'), '{$imgAssinatura}', '{$descricaoAssinatura}', 'Ativo', NOW())";
    if ($connect->query($insertInspetor) === TRUE)
        $_SESSION["alert_success"] = "Inspetor cadastrado com sucesso!";
    else
        $_SESSION["alert_danger"] = "Erro ao cadastrar inspetor";

    $connect->close();

    header("Location: .{$link}p=inspetores");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $nome = mysqli_real_escape_string($connect, trim($_POST["nome"]));
        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM inspetores WHERE nome_inspetor = '{$nome}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma inspetor com o nome informado";
            header("Location: .{$link}p=inspetores&acao=editar&id={$id}");
            exit();
        }

        $email = mysqli_real_escape_string($connect, trim($_POST["email"]));
        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM inspetores WHERE email = '{$email}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe um inspetor com o email informado";
            header("Location: .{$link}p=inspetores&acao=editar&id={$id}");
            exit();
        }

        $senha = mysqli_real_escape_string($connect, trim($_POST["senha"]));
        $status = mysqli_real_escape_string($connect, trim($_POST["status"]));

        $sqlSenha = "";
        if ($senha != "")
            $sqlSenha = "senha = MD5('{$senha}'),";


        $qr = mysqli_query($connect, "SELECT img_assinatura FROM inspetores WHERE id = '{$id}'");
        $dadoInspetor = mysqli_fetch_assoc($qr);

        $imgAssinatura = $dadoInspetor["img_assinatura"];
        if ($_FILES["imgAssinatura"]["tmp_name"] != "") {
            $diretorio = "../assets/img/inspetores/assinaturas/";

            $imgAssinatura = "";
            if ($dadoInspetor["img_assinatura"] != "" && is_file($diretorio . $dadoInspetor["img_assinatura"]))
                unlink($diretorio . $dadoInspetor["img_assinatura"]);

            $extensao = mb_strtolower(end(explode(".", strtolower($_FILES["imgAssinatura"]["name"]))));
            $imgAssinatura = md5(time()) . "." . $extensao;

            move_uploaded_file($_FILES["imgAssinatura"]["tmp_name"], $diretorio . $imgAssinatura);
        }

        $descricaoAssinatura = mysqli_real_escape_string($connect, trim($_POST["descricaoAssinatura"]));

        $updateInspetor = "UPDATE inspetores SET nome_inspetor = '{$nome}', {$sqlSenha} img_assinatura = '{$imgAssinatura}', descricao_assinatura = '{$descricaoAssinatura}', status_inspetor = '{$status}' WHERE id = '{$id}'";
        if ($connect->query($updateInspetor) === TRUE)
            $_SESSION["alert_success"] = "Inspetor atualizado com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao editar inspetor";

        $connect->close();

        header("Location: .{$link}p=inspetores&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {

        $deleteInspetor = "DELETE FROM inspetores WHERE id = '{$id}'";
        if ($connect->query($deleteInspetor) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de inspetor efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir inspetor";

        $connect->close();

        header("Location: .{$link}p=inspetores");
        exit();
    }
}
