<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");

$empresas = array_values(array_unique(array_filter(array_map('intval', explode(',', $_GET['empresa'] ?? '')))));
$results = [];

if (count($empresas) > 0) {
    /*$qr = mysqli_query($connect, "SELECT unidades FROM empresas WHERE id = '{$empresa}'");
    $dado = mysqli_fetch_array($qr);
    $unidades = explode("|", trim($dado['unidades'], "|"));
    for ($i = 0; $i < count($unidades); $i++) {
        $results[] = $unidades[$i];
    }*/

    $idsEmpresas = implode(',', $empresas);
    $qr = mysqli_query($connect, "SELECT u.id, u.titulo_unidade, e.nome_empresa FROM unidades u INNER JOIN empresas e ON e.id = u.id_empresa WHERE u.id_empresa IN ({$idsEmpresas}) ORDER BY e.nome_empresa, u.titulo_unidade");
    while ($dado = mysqli_fetch_array($qr)) {
        $result = [
            "id" => $dado['id'],
            "titulo" => $dado["titulo_unidade"],
            "empresa" => $dado["nome_empresa"]
        ];
        $results[] = $result;
    }

    echo json_encode($results);
}
