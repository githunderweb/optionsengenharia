<?php
function permissaoUsuario($atual, $usuario = "")
{
    $permissao = false;

    if (is_array($atual)) {
        for ($i = 0; $i < count($atual); $i++) {
            if ($atual[$i] == $usuario) {
                $permissao = true;
                break;
            }
        }
    } else if ($atual == "Todos" || $atual == $usuario)
        $permissao = true;

    return $permissao;
}

function idsEmpresasPermitidasUsuario($connect, $idUsuario)
{
    $ids = [];
    $idUsuario = intval($idUsuario);
    $qr = mysqli_query($connect, "SELECT id_empresa FROM usuario_empresas WHERE id_usuario = '{$idUsuario}' ORDER BY id_empresa");

    if ($qr) {
        while ($dado = mysqli_fetch_assoc($qr))
            $ids[] = intval($dado["id_empresa"]);
    }

    return array_values(array_unique(array_filter($ids)));
}

function condicaoEmpresasPermitidasUsuario($connect, $idUsuario, $coluna, $empresaAtual = 0)
{
    $ids = idsEmpresasPermitidasUsuario($connect, $idUsuario);
    $empresaAtual = intval($empresaAtual);

    if ($empresaAtual > 0 && in_array($empresaAtual, $ids, true))
        return "{$coluna} = '{$empresaAtual}'";

    if (count($ids) > 0)
        return "{$coluna} IN (" . implode(",", $ids) . ")";

    return "1 = 0";
}

function idsTiposEquipamentoPermitidosUsuario($connect, $idUsuario)
{
    $ids = [];
    $idUsuario = intval($idUsuario);
    $qr = mysqli_query($connect, "SELECT id_tipo_equipamento FROM usuario_tipos_equipamento WHERE id_usuario = '{$idUsuario}' ORDER BY id_tipo_equipamento");

    if ($qr) {
        while ($dado = mysqli_fetch_assoc($qr))
            $ids[] = intval($dado["id_tipo_equipamento"]);
    }

    return array_values(array_unique(array_filter($ids)));
}

function condicaoTiposEquipamentoPermitidosUsuario($connect, $idUsuario, $coluna)
{
    $ids = idsTiposEquipamentoPermitidosUsuario($connect, $idUsuario);
    return count($ids) > 0 ? "{$coluna} IN (" . implode(",", $ids) . ")" : "1 = 0";
}

function condicaoEquipamentosPermitidosUsuario($connect, $idUsuario, $alias = "e", $empresaAtual = 0)
{
    $condicaoEmpresas = condicaoEmpresasPermitidasUsuario($connect, $idUsuario, "{$alias}.id_empresa", $empresaAtual);
    $condicaoTipos = condicaoTiposEquipamentoPermitidosUsuario($connect, $idUsuario, "{$alias}.id_tipo_equipamento");
    return "({$condicaoEmpresas}) AND ({$condicaoTipos})";
}

// Converte data pt-br para date
function dataToDate($data)
{
    $date = "";
    if ($data != "") {
        if (strlen($data) > 10) {
            $date = explode(" ", $data)[1];
            $data = explode(" ", $data)[0];
        }
        $date = substr($data, 6) . "-" . substr($data, 3, -5) . "-" . substr($data, 0, -8) . " " . $date;
    }
    return $date;
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function field_formater($text)
{
    global $connect;

    // Remover espaços do início e fim da string
    $text = trim($text);
    // Remove
    $text = stripslashes($text);
    $text = mysqli_real_escape_string($connect,  $text);
    $text = htmlspecialchars($text);

    return $text;
}


// Deleta todos os itens de uma pasta para poder deletar ela
function deleteDirectory($dir)
{
    if (!file_exists($dir))
        return true;

    if (!is_dir($dir))
        return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..')
            continue;

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
            return false;
    }

    return rmdir($dir);
}


// Função que recebe tamanho em bytes e formata para KB, MB, GB ou TB
function format_file_size($size)
{
    $units = array('bytes', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . ' ' . $units[$i];
}


// Função que retorna ícone ou tipo do arquivo
function infoTipoArquivo($tipo, $info = "")
{

    $tipos = [
        'zip' => [
            'icon' => 'bi bi-file-earmark-zip',
            'tipo' => 'Arquivo compactado'
        ],
        'rar' => [
            'icon' => 'bi bi-file-earmark-zip',
            'tipo' => 'Arquivo compactado'
        ],
        'jpg' => [
            'icon' => 'bi bi-file-earmark-image',
            'tipo' => 'Imagem'
        ],
        'jpeg' => [
            'icon' => 'bi bi-file-earmark-image',
            'tipo' => 'Imagem'
        ],
        'png' => [
            'icon' => 'bi bi-file-earmark-image',
            'tipo' => 'Imagem'
        ],
        'pneg' => [
            'icon' => 'bi bi-file-earmark-image',
            'tipo' => 'Imagem'
        ],
        'gif' => [
            'icon' => 'bi bi-file-earmark-image',
            'tipo' => 'Imagem'
        ],
        'doc' => [
            'icon' => 'bi bi-file-earmark-word',
            'tipo' => 'Documento de texto'
        ],
        'docx' => [
            'icon' => 'bi bi-file-earmark-word',
            'tipo' => 'Documento de texto'
        ],
        'pdf' => [
            'icon' => 'bi bi-file-earmark-pdf',
            'tipo' => 'Documento PDF'
        ],
        'xlsx' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'xlsb' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'xltx' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'xltm' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'xls' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'xlt' => [
            'icon' => 'bi bi-file-earmark-ruled',
            'tipo' => 'Planilha'
        ],
        'wmv' => [
            'icon' => 'bi bi-file-earmark-play',
            'tipo' => 'Vídeo'
        ],
        'mp4' => [
            'icon' => 'bi bi-file-earmark-play',
            'tipo' => 'Vídeo'
        ],
        'mov' => [
            'icon' => 'bi bi-file-earmark-play',
            'tipo' => 'Vídeo'
        ],
        'mkv' => [
            'icon' => 'bi bi-file-earmark-play',
            'tipo' => 'Vídeo'
        ],
        'mp3' => [
            'icon' => 'bi bi-file-earmark-music',
            'tipo' => 'Áudio'
        ],
        'txt' => [
            'icon' => 'bi bi-file-earmark-text',
            'tipo' => 'Arquivo de texto'
        ],
    ];

    if ($info == "") {
        if (isset($tipos[$tipo]))
            return true;
        else
            return false;
    }

    if (isset($tipos[$tipo][$info]))
        return $tipos[$tipo][$info];
}



function enviarEmail($para, $assunto, $mensagem)
{
    $cabeçalho = "From: Options Engenharia | Sistema naoresponda@sistema.optionsengenharia.com.br\r\n";
    // $cabeçalho .= "Reply-To: naoresponda@sistema.optionsengenharia.com.br\r\n";
    // $cabeçalho .= "CC: cc@example.com\r\n";
    // $cabeçalho .= "BCC: bcc@example.com\r\n";
    $cabeçalho .= "X-Mailer: PHP/" . phpversion();

    if (mail($para, $assunto, $mensagem, $cabeçalho))
        return true;
    else
        return false;
}


function slugfy($text, $divider = "-", $lowerAll = true, $table = "", $currentID = 0, $addWhere = "")
{
    $text = mb_strtolower($text);
    if (!$lowerAll) {
        $text = ucwords($text);
        $text = lcfirst($text);
    }
    // replace non letter or digits by divider
    $text = preg_replace("~[^\pL\d]+~u", $divider, $text);
    // transliterate
    $text = iconv("utf-8", "us-ascii//TRANSLIT", $text);
    // remove unwanted characters
    $text = preg_replace("~[^-\w]+~", "", $text);
    // trim
    $text = trim($text, $divider);
    // remove duplicate divider
    $text = preg_replace("~-+~", $divider, $text);

    if (empty($text))
        return "";
    else if ($table != "") {
        global $connect;
        if (isset($connect)) {
            $addWhere = "AND {$addWhere}";

            $contagemSlug = 1;
            $textAtual = $text;
            while (true) {
                if (intval(mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS qt FROM {$table} WHERE slug = '$textAtual' AND id != '$currentID'"))["qt"]) > 0)
                    $contagemSlug += 1;
                else {
                    $text = $textAtual;
                    break;
                }
                $textAtual = "$text{$divider}$contagemSlug";
            }
        }
    }

    return $text;
}

function slugCampoModeloRelatorio($text, $idModeloAtual = 0, $idAtual = 0)
{
    global $connect;
    $divider = '_';

    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, $divider);
    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);

    if (empty($text))
        return 'n-a';
    else {
        $contagemSlug = 1;
        $textAtual = $text;
        while (true) {
            if (intval(mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM campos_modelo_relatorio WHERE slug = '$textAtual' AND id_modelo_relatorio = '$idModeloAtual' AND id != '$idAtual'"))['0']) > 0)
                $contagemSlug += 1;
            else {
                $text = $textAtual;
                break;
            }
            $textAtual = "{$text}_{$contagemSlug}";
        }
    }

    return $text;
}
