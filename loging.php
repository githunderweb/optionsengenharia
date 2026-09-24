<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include('./config.php');

if (empty($_POST['usuarioOuEmail']) || empty($_POST['senha'])) {
	$_SESSION['nao_autenticado'] = "Preencha os campos corretamente!";
	header('Location: ./login.php');
	exit();
}

$usuarioOuEmail = $connect->real_escape_string($_POST['usuarioOuEmail']);
$senha = $connect->real_escape_string($_POST['senha']);

$sql = mysqli_query($connect, "SELECT * FROM usuarios WHERE (email = '{$usuarioOuEmail}' OR usuario = '{$usuarioOuEmail}') AND senha = md5('{$senha}')");
$row = mysqli_num_rows($sql);
$dado = mysqli_fetch_array($sql);

if ($row > 0) {
	$_SESSION['sessao_usuario'] = [
		"usuario" => $dado["usuario"],
		"email" => $dado["email"],
		"senha" => $dado["senha"],
		"funcao" => $dado["funcao"],
		"id_empresa" => $dado["id_empresa"],
		"id_unidade" => $dado["id_unidade"]
	];
	$connect->query("UPDATE usuarios SET ultimo_login = NOW() WHERE id = '{$dado['id']}'");
} else
	$_SESSION['nao_autenticado'] = "Erro: usuário ou senha inválidos!";

header('Location: ./');
exit();
