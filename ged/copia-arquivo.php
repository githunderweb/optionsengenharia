<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);

$acao = @$_GET["acao"];
if ($acao == "")
    $_SESSION["copiar_arquivo"] = $_POST;
else if ($acao == "consultar") {
    if (isset($_SESSION["copiar_arquivo"])) {
        echo json_encode([
            "status" => "copiado",
        ]);
    }
}
