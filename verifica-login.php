<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);

$link = "//$_SERVER[HTTP_HOST]";
$documentRoot = realpath($_SERVER["DOCUMENT_ROOT"] ?? "");
$applicationRoot = realpath(__DIR__);
if ($documentRoot && $applicationRoot && stripos($applicationRoot, $documentRoot) === 0) {
    $caminhoAplicacao = trim(str_replace("\\", "/", substr($applicationRoot, strlen($documentRoot))), "/");
    if ($caminhoAplicacao !== "")
        $link .= "/{$caminhoAplicacao}";
}

if (!isset($_SESSION['sessao_usuario'])) {
    header('Location: ' . $link . '/login.php');
    exit();
} else {
    $sessaoUsuario = $_SESSION['sessao_usuario'];

    $sql = mysqli_query($connect, "SELECT * FROM usuarios WHERE email = '{$sessaoUsuario["email"]}' AND usuario = '{$sessaoUsuario["usuario"]}' AND senha = '{$sessaoUsuario["senha"]}' AND funcao = '{$sessaoUsuario["funcao"]}'");
    $row = mysqli_num_rows($sql);

    if (!$row > 0) {
        header('Location: ' . $link . '/logout.php');
        exit();
    } else {
        // $sessaoUsuario = $_SESSION['sessao_usuario'];
        $sessaoUsuario = mysqli_fetch_array($sql);

        if ($sessaoUsuario["funcao"] == "Cliente") {
            $idUsuario = intval($sessaoUsuario["id"]);
            $empresaAtual = intval($_SESSION["empresa_atual"] ?? $sessaoUsuario["id_empresa"]);

            if (array_key_exists("empresa", $_GET)) {
                $empresaSolicitada = strtolower(trim((string) $_GET["empresa"]));
                if ($empresaSolicitada === "todas" || $empresaSolicitada === "0" || $empresaSolicitada === "") {
                    $empresaAtual = 0;
                } else {
                    $idEmpresaSolicitada = intval($empresaSolicitada);
                    $qrEmpresaSolicitada = mysqli_query($connect, "SELECT 1 FROM usuario_empresas WHERE id_usuario = '{$idUsuario}' AND id_empresa = '{$idEmpresaSolicitada}' LIMIT 1");
                    if ($idEmpresaSolicitada > 0 && mysqli_num_rows($qrEmpresaSolicitada) > 0)
                        $empresaAtual = $idEmpresaSolicitada;
                }
            }

            if ($empresaAtual > 0) {
                $qrEmpresaAtual = mysqli_query($connect, "SELECT 1 FROM usuario_empresas WHERE id_usuario = '{$idUsuario}' AND id_empresa = '{$empresaAtual}' LIMIT 1");
            } else {
                $qrEmpresaAtual = mysqli_query($connect, "SELECT 1 FROM usuario_empresas WHERE id_usuario = '{$idUsuario}' LIMIT 1");
            }

            if (mysqli_num_rows($qrEmpresaAtual) == 0) {
                $qrPrimeiraEmpresa = mysqli_query($connect, "SELECT e.id FROM usuario_empresas ue INNER JOIN empresas e ON e.id = ue.id_empresa WHERE ue.id_usuario = '{$idUsuario}' ORDER BY e.nome_empresa LIMIT 1");
                $dadoPrimeiraEmpresa = mysqli_fetch_assoc($qrPrimeiraEmpresa);
                $empresaAtual = intval($dadoPrimeiraEmpresa["id"] ?? 0);
            }

            $_SESSION["empresa_atual"] = $empresaAtual;
            $sessaoUsuario["id_empresa"] = $empresaAtual;
            unset($_SESSION["unidade_atual"]);
            $sessaoUsuario["id_unidade"] = null;
        } else {
            unset($_SESSION["empresa_atual"]);
            unset($_SESSION["unidade_atual"]);
        }
    }
}
