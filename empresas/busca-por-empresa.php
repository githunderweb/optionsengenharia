<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");

$empresa = $_GET['empresa'];
$get = $_GET['get'];

$results = [];

if ($empresa != "" && $get != "") {
    $qr = mysqli_query($connect, "SELECT * FROM {$get}s WHERE id_empresa = '{$empresa}'");
    while ($dado = mysqli_fetch_array($qr)) {
        $result = [
            "id" => $dado['id'],
            "titulo" => $dado["titulo_{$get}"]
        ];
        $results[] = $result;
    }

    echo json_encode($results);
}
