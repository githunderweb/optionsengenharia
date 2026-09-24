<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$id = @$_GET["id"];
$permissaoClientePdf = "";
if ($sessaoUsuario["funcao"] == "Cliente")
    $permissaoClientePdf = " AND " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $sessaoUsuario["id_empresa"]);
$dados = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT i.*, 
        mros.id_modelo_relatorio, 
        os.numero_os, 
        mr.titulo_modelo_relatorio,
        e.nome_equipamento,
        em.nome_empresa,
        u.titulo_unidade,
        u.endereco, 
        u.cidade,
        u.estado, 
        u.cnpj, 
        e.nome_equipamento, 
        e.tag, 
        e.numero_pasta, 
        e.fabricante, 
        e.ano_fabricacao, 
        e.material, 
        te.titulo_tipo_equipamento, 
        ce.titulo_categoria_equipamento, 
        li.titulo_local_instalacao, 
        ir.nome_inspetor,
        ir.img_assinatura AS assinatura_inspetor,
        ir.descricao_assinatura AS descricao_assinatura_inspetor,
        us.nome AS nome_usuario,
        us.img_assinatura AS assinatura_usuario,
        us.descricao_assinatura AS descricao_assinatura_usuario
    FROM inspecoes i 
        INNER JOIN modelos_relatorio_os mros ON mros.id = i.id_modelo_relatorio_os
        INNER JOIN ordens_servico os ON os.id = mros.id_os
        INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
        LEFT JOIN inspetores ir ON ir.id = i.id_inspetor
        LEFT JOIN equipamentos e ON e.id = i.id_equipamento
        LEFT JOIN tipos_equipamento te ON e.id_tipo_equipamento = te.id
        LEFT JOIN categorias_equipamento ce ON e.id_categoria_equipamento = ce.id
        LEFT JOIN locais_instalacao li ON e.id_local_instalacao = li.id
        LEFT JOIN empresas em ON em.id = os.id_empresa
        LEFT JOIN unidades u ON u.id = os.id_unidade
        LEFT JOIN usuarios us ON us.id = i.id_usuario_validou
    WHERE i.id = '{$id}'{$permissaoClientePdf}"
));
if ($dados['status_inspecao'] == "Aprovado") {
    require('../classes/TCPDF/pdf-padrao.php');

    $pdf = new PDF();
    $pdf->SetTitle($dados["titulo_modelo_relatorio"]);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 0, '', 0, 1);


    $cabecalho = '
        <table border="1" cellspacing="0" cellpadding="5">
            <tbody>
                <tr style="text-align: center;">
                    <td rowspan="3" style="width: 100px;"><img src="../assets/img/logomarca.jpg"></td>
                    <td rowspan="3" style="width: 318px;">
                        <div style="font-size: 14px; font-weight: bold;">OPTIONS ENGENHARIA</div>
                        <br>Rua João Nutti, nº1082 - Ribeirão Preto - SP
                        <br><strong>Telefone:</strong> (16) 3617-9037
                        <br><strong>CNPJ:</strong> 28.643.996/0001-72
                        <br><a href="www.optionsengenharia.com.br" target="_blank">www.optionsengenharia.com.br</a>
                        <br>
                    </td>
                    <td style="width: 120px;">Nº CERTIFICADO</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="width: 120px;">' . $dados["numero_os"] . '</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="width: 120px;"><br><br><br><strong>Data/ Date:</strong> ' . date("d/m/Y") . '</td>
                </tr>
            </tbody>
        </table>
    ';
    $pdf->writeHTML($cabecalho);

    $html = '
        <table border="1" cellspacing="0" cellpadding="10">
            <tbody>
                <tr bgcolor="#eee" style="text-align: center; font-weight: bold;">
                    <td colspan="3"><h3>' . $dados["titulo_modelo_relatorio"] . '</h3></td>
                </tr>
            </tbody>
        </table>
        <table border="1" cellspacing="0" cellpadding="5">
            <tbody>
                <tr bgcolor="#ddd" style="text-align: center; font-weight: bold;">
                    <td colspan="3">DADOS CLIENTE</td>
                </tr>
                <tr bgcolor="#f6f6f6"  style="font-weight: bold; font-size: 8px;">
                    <td colspan="3">Cliente / Unidade</td>
                </tr>
                <tr>
                    <td colspan="3">' . "{$dados["nome_empresa"]} - {$dados["titulo_unidade"]}" . '</td>
                </tr>
                <tr bgcolor="#f6f6f6" style="font-weight: bold; font-size: 8px;">
                    <td>Endereço:</td>
                    <td>Cidade / UF:</td>
                    <td>CNPJ:</td>
                </tr>
                <tr>
                    <td>' . $dados["endereco"] . '</td>
                    <td>' . $dados["cidade"] . ($dados["estado"] ? " / {$dados["estado"]}" : "") .  '</td>
                    <td>' . $dados["cnpj"] . '</td>
                </tr>
                <tr bgcolor="#f6f6f6" style="text-align: center; font-weight: bold; font-size: 8px;">
                    <td colspan="3">Anexo a ART (Anotação de Responsabilidade Técnica)</td>
                </tr>
                <tr style="text-align: center;">
                    <td colspan="3">' . $dados["numero_art"] . '</td>
                </tr>
            </tbody>
        </table>
    ';

    $html .= '
        <table border="1" cellspacing="0" cellpadding="5">
            <tbody>
                <tr bgcolor="#ddd" style="text-align: center; font-weight: bold;">
                    <td colspan="4">Identificação e Características do Equipamento</td>
                </tr>
                <tr>
                    <td colspan="4">' . $dados["nome_equipamento"] . '</td>
                </tr>
                <tr>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">Nº PASTA:</td>
                    <td>' . $dados["numero_pasta"] . '</td>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">TAG:</td>
                    <td>' . $dados["tag"] . '</td>
                </tr>
                <tr>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">TIPO DE EQUIPAMENTO:</td>
                    <td>' . $dados["titulo_tipo_equipamento"] . '</td>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">CATEGORIA:</td>
                    <td>' . $dados["titulo_categoria_equipamento"] . '</td>
                </tr>
                <tr>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">LOCAL DE INSTALAÇÃO:</td>
                    <td>' . $dados["titulo_local_instalacao"] . '</td>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">FABRICANTE:</td>
                    <td>' . $dados["fabricante"] . '</td>
                </tr>
                <tr>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">ANO DE FABRICAÇÃO:</td>
                    <td>' . $dados["ano_fabricacao"] . '</td>
                    <td bgcolor="#f6f6f6" style="font-size: 8px; font-weight: bold;">MATERIAL:</td>
                    <td>' . $dados["material"] . '</td>
                </tr>';

    $qrCamposTipoEquipamento = mysqli_query($connect, "SELECT titulo, valor FROM valores_inspecao WHERE id_inspecao = '{$id}' AND origem = 'campos_tipo_equipamento'");
    while ($dadoCampoTipoEquipamento = mysqli_fetch_assoc($qrCamposTipoEquipamento)) {
        $html .= '
                <tr>
                    <td colspan="2" bgcolor="#f6f6f6" style="font-weight: bold;">' . $dadoCampoTipoEquipamento["titulo"] . '</td>
                    <td colspan="2">' . $dadoCampoTipoEquipamento["valor"] . '</td>
                </tr>
        ';
    }

    $html .= '
            </tbody>
        </table>
    ';

    $html .= '
        <table border="1" cellspacing="0" cellpadding="5">
            <tbody>
                <tr bgcolor="#ddd" style="font-weight: bold; text-align: center;">
                    <td colspan="4">Inspeção</td>
                </tr>';

    $qrCamposModeloRelatorio = mysqli_query($connect, "SELECT vi.titulo, vi.valor, vi.tipo, vi.slug, cmr.tipo_arquivo FROM valores_inspecao vi LEFT JOIN campos_modelo_relatorio cmr ON cmr.id_modelo_relatorio = '{$dados["id_modelo_relatorio"]}' AND cmr.tipo = vi.tipo AND cmr.slug = vi.slug WHERE vi.id_inspecao = '{$id}' AND vi.origem = 'campos_modelo_relatorio'");
    while ($dadoCampoModeloRelatorio = mysqli_fetch_assoc($qrCamposModeloRelatorio)) {
        $valorCampoModeloRelatorio = $dadoCampoModeloRelatorio["valor"];

        if ($dadoCampoModeloRelatorio["tipo"] == "Upload de arquivo") {
            $arquivos = [];
            foreach (explode("|", $dadoCampoModeloRelatorio['valor']) as $arquivo) {
                $conteudo = $arquivo;

                $caminho = "../inspecoes/arquivos/{$id}/{$dadoCampoModeloRelatorio["slug"]}/{$arquivo}";

                if ($dadoCampoModeloRelatorio["tipo_arquivo"] == "image/*")
                    $conteudo = $pdf->ImgDimensoesMaximas($caminho, 250);

                $arquivos[] = "<a href=\"{$caminho}\" download=\"{$arquivo}\">{$conteudo}</a>";
            }

            $valorCampoModeloRelatorio = implode(($dadoCampoModeloRelatorio["tipo_arquivo"] == "image/*" ? "<br><br>" : ", "), $arquivos);
        } else if ($dadoCampoModeloRelatorio["tipo"] == "Data" && $dadoCampoModeloRelatorio["valor"] != "") {
            $valorCampoModeloRelatorio = date("d/m/Y", strtotime($dadoCampoModeloRelatorio['valor']));
        } else if ($dadoCampoModeloRelatorio["tipo"] == "Data e Hora" && $dadoCampoModeloRelatorio["valor"] != "") {
            $valorCampoModeloRelatorio = date("d/m/Y H:i", strtotime($dadoCampoModeloRelatorio['valor']));
        }

        $html .= '
                <tr>
                    <td colspan="2" bgcolor="#f6f6f6" style="font-weight: bold;">' . $dadoCampoModeloRelatorio["titulo"] . '</td>
                    <td colspan="2" style="text-align: center;">' . $valorCampoModeloRelatorio . '</td>
                </tr>
        ';
    }

    $html .= '
            </tbody>
        </table>
    ';

    $pdf->writeHTML($html);

    $pdf->AddPage();
    $pdf->writeHTML($cabecalho);

    $pdf->writeHTML('
        <table border="1" cellspacing="0" cellpadding="5">
            <tbody>
                <tr bgcolor="#ddd">
                    <td colspan="2" style="font-weight: bold; text-align: center;">Responsáveis pelas Inspeções</td>
                </tr>
                <tr bgcolor="#ddd">
                    <td>Profissional Habilitado: ' . $dados["nome_usuario"] . '</td>
                    <td>Técnico Executante: ' . $dados["nome_inspetor"] . '</td>
                </tr>
            </tbody>
        </table>
        <table border="1" cellspacing="0" cellpadding="10">
            <tbody>
                <tr>
                    <td style="font-weight: bold;">
                        ' . ($dados["assinatura_usuario"] ? $pdf->ImgDimensoesMaximas("../assets/img/usuarios/assinaturas/{$dados["assinatura_usuario"]}", 100, 200) : '<br><br><br><br><br><br><br><br>') . '
                        <br>' . mb_strtoupper($dados["nome_usuario"]) . '<br>' . nl2br(mb_strtoupper($dados["descricao_assinatura_usuario"])) . '
                    </td>
                    <td>
                        ' . ($dados["assinatura_inspetor"] ? $pdf->ImgDimensoesMaximas("../assets/img/inspetores/assinaturas/{$dados["assinatura_inspetor"]}", 100, 200) : '<br><br><br><br><br><br><br><br>') . '
                        <br>' . mb_strtoupper($dados["nome_inspetor"]) . '<br>' . nl2br(mb_strtoupper($dados["descricao_assinatura_inspetor"])) . '
                    </td>
                </tr>
            </tbody>
        </table>
    ');

    $pdf->Output();
} else {
    echo "<script>window.close();</script>";
}
