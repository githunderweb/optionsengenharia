<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");

$results = [];

$qr = mysqli_query($connect, "SELECT id, nome_empresa FROM empresas");
while ($dado = mysqli_fetch_array($qr)) {
    $result = [
        "id" => $dado['id'],
        "nome" => $dado["nome_empresa"]
    ];
    $results[] = $result;
}

echo json_encode($results);
