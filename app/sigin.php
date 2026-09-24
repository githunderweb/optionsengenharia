<?php
session_start();
include('../config.php');

if ((isset($_POST['email']) && $_POST['email'] != "") || (isset($_POST['senha']) && $_POST['senha'] != "")) {

	$email = $connect->real_escape_string($_POST['email']);
	$senha = $connect->real_escape_string($_POST['senha']);

	$sql = mysqli_query($connect, "SELECT * FROM inspetores WHERE email = '{$email}' AND senha = md5('{$senha}') AND status_inspetor = 'Ativo'");
	$row = mysqli_num_rows($sql);
	$dado = mysqli_fetch_array($sql);

	if ($row > 0) {
		$_SESSION['sessao_usuario'] = $dado;

		// echo "success";
		header("Location: ./");
		exit();
	} else {
		// echo "unauthenticated";
		$_SESSION['alert_danger'] = "E-mail ou senha inválidos!";
		header("Location: ./login.php");
		exit();
	}
} else {
	// echo "empty";
	$_SESSION['alert_danger'] = "Preencha os campos corretamente!";
	header("Location: ./login.php");
	exit();
}
