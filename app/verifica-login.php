<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);

$url = explode(basename(__DIR__), "$_SERVER[PHP_SELF]")[0] . basename(__DIR__) . "/";
$link = "//$_SERVER[HTTP_HOST]" . $url;

$usuarioAppLogado = false;

if (!isset($_SESSION['sessao_usuario'])) {
    header('Location: ' . $link . 'login.php');
    exit();
} else {
    $sessaoUsuario = $_SESSION['sessao_usuario'];

    $sql = mysqli_query($connect, "SELECT * FROM inspetores WHERE email = '{$sessaoUsuario["email"]}' AND senha = '{$sessaoUsuario["senha"]}' AND status_inspetor = '{$sessaoUsuario["status_inspetor"]}'");
    $row = mysqli_num_rows($sql);

    if (!$row > 0) {
        header('Location: ' . $link . 'sigout.php');
        exit();
    } else {
        $sessaoUsuario = mysqli_fetch_assoc($sql);
        $usuarioAppLogado = true;
    }
}
