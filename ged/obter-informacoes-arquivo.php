<?php
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$idArquivo = @$_GET["idArquivo"];
$dir = @$_GET["dir"];
if ($idArquivo != "" && $dir != "") {

    $qrArquivo = mysqli_query($connect, "SELECT a.arquivo, a.data_upload, COUNT(da.id_arquivo) AS qt_download FROM arquivos a INNER JOIN downloads_arquivo da ON da.id_arquivo = a.id WHERE a.id = '{$idArquivo}'");
    $dadoArquivo = mysqli_fetch_array($qrArquivo);

    if (mysqli_num_rows($qrArquivo) > 0) {
        $caminhoArquivo = ".{$dir}{$dadoArquivo["arquivo"]}";
        $file_info = stat($caminhoArquivo); // Obtém informações do arquivo

        // Separa extensão do nome do arquivo
        $arquivoAtual = explode(".", $dadoArquivo["arquivo"]);
        $extensaoArquivo = end($arquivoAtual);
        array_pop($arquivoAtual);
        $nomeArquivo = join('.', $arquivoAtual);

        if (infoTipoArquivo($extensaoArquivo))
            $extensaoArquivo = infoTipoArquivo($extensaoArquivo, "tipo") . " ($extensaoArquivo)";

        $downloadsUsuarios = [];
        if (intval($dadoArquivo["qt_download"]) > 0) {
            $qrDownloadsUsuarios = mysqli_query($connect, "SELECT u.nome, da.data_download FROM downloads_arquivo da INNER JOIN usuarios u ON u.id = da.id_usuario WHERE da.id_arquivo = '{$idArquivo}'");
            while ($dadoDownloadsUsuarios = mysqli_fetch_array($qrDownloadsUsuarios)) {
                $downloadsUsuarios[] = [
                    "nome" => $dadoDownloadsUsuarios["nome"],
                    "data_download" => date("d/m/Y H:i", strtotime($dadoDownloadsUsuarios["data_download"]))
                ];
            }
        }

        $arquivo = [
            "nome" => $nomeArquivo,
            "tipo" => $extensaoArquivo,
            "tamanho" => format_file_size($file_info["size"]),
            "dataUpload" => date("d/m/Y H:i", strtotime($dadoArquivo["data_upload"])),
            "qtDownload" => $dadoArquivo["qt_download"],
            "downloadsUsuarios" => $downloadsUsuarios,
        ];

        echo json_encode($arquivo);
    }
}
