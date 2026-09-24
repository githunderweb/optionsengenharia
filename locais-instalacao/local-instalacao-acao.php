<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];


if ($acao == "") {

    $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));
    $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
    $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM locais_instalacao WHERE titulo_local_instalacao = '{$titulo}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma local de instalação nesta unidade com o título informado";
        header("Location: .{$link}p=local-instalacao");
        exit();
    }

    $insertCategoriaEquipamento = "INSERT INTO locais_instalacao (id_empresa, id_unidade, titulo_local_instalacao, data_cadastro) VALUES ('{$empresa}', '{$unidade}', '{$titulo}', NOW())";
    if ($connect->query($insertCategoriaEquipamento) === TRUE)
        $_SESSION["alert_success"] = "Local de instalação cadastrado com sucesso!";
    else
        $_SESSION["alert_danger"] = "Erro ao cadastrar local de instalação";

    $connect->close();

    header("Location: .{$link}p=locais-instalacao");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));
        $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
        $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));

        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM locais_instalacao WHERE titulo_local_instalacao = '{$titulo}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma local de instalação nesta unidade com o título informado";
            header("Location: .{$link}p=locais-instalacao&acao=editar&id={$id}");
            exit();
        }

        $updateCategoriaEquipamento = "UPDATE locais_instalacao SET id_empresa = '{$empresa}', id_unidade = '{$unidade}', titulo_local_instalacao = '{$titulo}' WHERE id = '{$id}'";
        if ($connect->query($updateCategoriaEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Local de instalação atualizado com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao editar local de instalação";

        $connect->close();

        header("Location: .{$link}p=locais-instalacao&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {

        $deleteCategoriaEquipamento = "DELETE FROM locais_instalacao WHERE id = '{$id}'";
        if ($connect->query($deleteCategoriaEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de local de instalação efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir local de instalação";

        $connect->close();

        header("Location: .{$link}p=locais-instalacao");
        exit();
    }
}
