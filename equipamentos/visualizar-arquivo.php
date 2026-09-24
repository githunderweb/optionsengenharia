<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

function arquivoNaoEncontrado()
{
    http_response_code(404);
    exit("Arquivo não encontrado.");
}

$idEquipamento = intval($_GET["id"] ?? 0);
$slugCampo = trim((string) ($_GET["campo"] ?? ""));
$arquivo = trim((string) ($_GET["arquivo"] ?? ""));

if ($idEquipamento <= 0 || !preg_match('/^[a-zA-Z0-9_-]+$/', $slugCampo) || $arquivo === "" || strpos($arquivo, "\\") !== false || strpos($arquivo, "\0") !== false || basename($arquivo) !== $arquivo)
    arquivoNaoEncontrado();

$slugCampoSql = mysqli_real_escape_string($connect, $slugCampo);
$permissaoEquipamento = "";
if (permissaoUsuario("Cliente", $sessaoUsuario["funcao"]))
    $permissaoEquipamento = " AND " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $sessaoUsuario["id_empresa"]);

$qrArquivo = mysqli_query(
    $connect,
    "SELECT v.valor FROM equipamentos e
        INNER JOIN valores_campos_personalizados_equipamento v ON v.id_equipamento = e.id
        WHERE e.id = '{$idEquipamento}' AND v.slug = '{$slugCampoSql}' AND v.tipo = 'Upload de arquivo'{$permissaoEquipamento}
        LIMIT 1"
);
$dadoArquivo = $qrArquivo ? mysqli_fetch_assoc($qrArquivo) : null;

if (!$dadoArquivo)
    arquivoNaoEncontrado();

$arquivosPermitidos = array_values(array_filter(array_map("trim", explode("|", (string) $dadoArquivo["valor"]))));
if (!in_array($arquivo, $arquivosPermitidos, true))
    arquivoNaoEncontrado();

$diretorioCampo = realpath(__DIR__ . "/arquivos/{$idEquipamento}/{$slugCampo}");
$caminhoArquivo = $diretorioCampo ? realpath($diretorioCampo . DIRECTORY_SEPARATOR . $arquivo) : false;
if (!$diretorioCampo || !$caminhoArquivo || !is_file($caminhoArquivo) || dirname($caminhoArquivo) !== $diretorioCampo)
    arquivoNaoEncontrado();

$tipoConteudo = "application/octet-stream";
if (function_exists("finfo_open")) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoDetectado = $finfo ? finfo_file($finfo, $caminhoArquivo) : false;
    if ($finfo)
        finfo_close($finfo);
    if ($tipoDetectado)
        $tipoConteudo = $tipoDetectado;
}

session_write_close();
header("Content-Type: {$tipoConteudo}");
header("Content-Length: " . filesize($caminhoArquivo));
header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($arquivo));
header("X-Content-Type-Options: nosniff");
readfile($caminhoArquivo);
exit();
