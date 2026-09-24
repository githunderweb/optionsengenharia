<?php
include("../config.php");
include("../verifica-login.php");

$idArquivo = @$_POST["idArquivo"];

if ($idArquivo != "") {
    $insertArquivo = "INSERT INTO downloads_arquivo (id_arquivo, id_usuario, data_download) VALUES ('{$idArquivo}', '{$sessaoUsuario["id"]}', NOW())";
    $connect->query($insertArquivo);
}
