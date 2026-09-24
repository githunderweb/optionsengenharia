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

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM categorias_equipamento WHERE titulo_categoria_equipamento = '{$titulo}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma categoria de equipamento nesta unidade com o título informado";
        header("Location: .{$link}p=categoria-equipamento");
        exit();
    }

    $insertCategoriaEquipamento = "INSERT INTO categorias_equipamento (id_empresa, id_unidade, titulo_categoria_equipamento, data_cadastro) VALUES ('{$empresa}', '{$unidade}', '{$titulo}', NOW())";
    if ($connect->query($insertCategoriaEquipamento) === TRUE)
        $_SESSION["alert_success"] = "Categoria de equipamento cadastrada com sucesso!";
    else
        $_SESSION["alert_danger"] = "Erro ao cadastrar categoria de equipamento";

    $connect->close();

    header("Location: .{$link}p=categorias-equipamento");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));
        $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
        $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));

        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM categorias_equipamento WHERE titulo_categoria_equipamento = '{$titulo}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma categoria de equipamento nesta unidade com o título informado";
            header("Location: .{$link}p=categorias-equipamento&acao=editar&id={$id}");
            exit();
        }

        $updateCategoriaEquipamento = "UPDATE categorias_equipamento SET id_empresa = '{$empresa}', id_unidade = '{$unidade}', titulo_categoria_equipamento = '{$titulo}' WHERE id = '{$id}'";
        if ($connect->query($updateCategoriaEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Categoria de equipamento atualizada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao editar categoria de equipamento";

        $connect->close();

        header("Location: .{$link}p=categorias-equipamento&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {

        $deleteCategoriaEquipamento = "DELETE FROM categorias_equipamento WHERE id = '{$id}'";
        if ($connect->query($deleteCategoriaEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de categoria de equipamento efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir categoria de equipamento";

        $connect->close();

        header("Location: .{$link}p=categorias-equipamento");
        exit();
    }
}
