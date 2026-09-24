<?php
require('../classes/TCPDF/pdf-padrao.php');

$pdf = new PDF();
$pdf->SetTitle('MODELO DE CERTIFICADO CALIBRAÇÃO VÁLVULA');
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 0, '', 0, 1);

$pdf->writeHTML('
    <table border="1" cellspacing="0" cellpadding="10">
        <tbody>
            <tr bgcolor="#eee" style="text-align: center; font-weight: bold;">
                <td colspan="3"><h3>CERTIFICADO DE CALIBRAÇÃO DE VÁLVULA</h3></td>
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
            <tr style="font-weight: bold; font-size: 8px;">
                <td colspan="3"></td>
            </tr>
            <tr bgcolor="#f6f6f6" style="font-weight: bold; font-size: 8px;">
                <td>Endereço:</td>
                <td>Cidade / UF:</td>
                <td>CNPJ:</td>
            </tr>
            <tr style="font-weight: bold; font-size: 8px;">
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr bgcolor="#f6f6f6" style="text-align: center; font-weight: bold; font-size: 8px;">
                <td colspan="3">Anexo a ART (Anotação de Responsabilidade Técnica)</td>
            </tr>
            <tr style="font-weight: bold; font-size: 8px;">
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
    <table border="1" cellspacing="0" cellpadding="5">
        <tbody>
            <tr bgcolor="#ddd" style="text-align: center; font-weight: bold;">
                <td colspan="4">Identificação e Características da Válvula</td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">TAG DA VALVULA</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">FABRICANTE:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">CÓD. DO EQUIP. / N° SERIE</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">NUMERO DE SÉRIE:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">DATA CALIBRAÇÃO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">MODELO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRÓX. CALIBRAÇÃO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">TIPO DE CONEXÃO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRESSÃO VEDAÇÃO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">DIÂMETRO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">"PRESSÃO ABERTURA:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRESSÃO AJUSTE:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PMTA</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">MATERIAL:</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <table border="1" cellspacing="0" cellpadding="5">
        <tbody>
            <tr bgcolor="#ddd" style="text-align: center; font-weight: bold;">
                <td colspan="4">Identificação da Instalação</td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">ÁREA:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRESSÃO DE OPERAÇÃO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">EQUIPAMENTO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">TOLERÂNCIA DO PROCESSO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">TAG DO EQUIPAMENTO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">DENOMINADOR:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">CASCO OU TUBO</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">LIMITE DE ERRO:</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">FLUIDO DE OPERAÇÃO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">N° DO RELATÓRIO DE INSPEÇÃO NR-13 ATUAL</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PMTA DO VASO:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">DATA DE INSPEÇÃO:</td>
                <td></td>
            </tr>

            <tr bgcolor="#ddd" style="text-align: center; font-weight: bold;">
                <td colspan="4">Teste inicial</td>
            </tr>
            <tr style="font-size: 8px;">
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRESSÃO DE AJUSTE:</td>
                <td></td>
                <td bgcolor="#f6f6f6" style="font-weight: bold;">PRESSÃO DE VEDAÇÃO:</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <table border="1" cellspacing="0" cellpadding="5">
        <tbody>
            <tr bgcolor="#ddd" style="font-weight: bold;">
                <td colspan="4">1 - Tipo de Inspeção</td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold; text-align: center;">
                <td bgcolor="#f6f6f6">ITEM</td>
                <td>COMPONENTES</td>
                <td bgcolor="#f6f6f6">CONDIÇÕES ENCONTRADAS</td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">I</td>
                <td>LACRE</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">2</td>
                <td>PINTURA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">3</td>
                <td>CONDIÇÕES DO CORPO E CASTELO</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">4</td>
                <td>CONDIÇÕES FÍSICA DOS FLANGES</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">5</td>
                <td>CONDIÇÔES DA PORCA DE TRAVA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">6</td>
                <td>ROSCAS DA VALVULA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">7</td>
                <td>CAPÔ/PARAFUSO DE REGULAGEM</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">8</td>
                <td>SEDE</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">9</td>
                <td>CONTRA-SEDE</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">10</td>
                <td>GUIA DA CONTRA-SEDE</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">11</td>
                <td>HASTE</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">12</td>
                <td>MOLA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">13</td>
                <td>SUPORTE SUPERIOR DA MOLA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
            <tr style="font-size: 8px; font-weight: bold;">
                <td bgcolor="#f6f6f6" style="text-align: center;">14</td>
                <td>CALIBRAÇÃO APROVADA / REPROVADA</td>
                <td bgcolor="#f6f6f6"></td>
                <td></td>
            </tr>
        </tbody>
    </table>
');

$pdf->writeHTML('
    <table border="1" cellspacing="0" cellpadding="5">
        <tbody>
            <tr bgcolor="#ddd">
                <td colspan="2" style="font-weight: bold;">2 - Fotos</td>
            </tr>
            <tr>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
            </tr>
            <tr style="text-align: center; font-weight: bold;">
                <td>FOTO 01: BANCADA DE CALIBRAÇÃO</td>
                <td>FOTO 02: MENSURADO UTILIZADO</td>
            </tr>
            <tr>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
            </tr>
            <tr style="text-align: center; font-weight: bold;">
                <td>FOTO 03: PLACA DE CALIBRAÇÃO</td>
                <td>FOTO 04: CALIBRAÇÃO</td>
            </tr>
            <tr>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
                <td><br><br><br><br><br><br><br><br><br><br><br></td>
            </tr>
            <tr style="text-align: center; font-weight: bold;">
                <td>FOTO 05: BASE</td>
                <td>FOTO 06: VÁLVULA</td>
            </tr>
            <tr bgcolor="#ddd">
                <td colspan="2" style="font-weight: bold;">3 - Responsáveis pelas Inspeções</td>
            </tr>
            <tr bgcolor="#ddd">
                <td>Profissional Habilitado: Franciele Soares Balduino</td>
                <td>Técnico Executante: Douglas Rodrigo Bertolazzo</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">
                    <br><br><br><br><br>FRANCIELE SOARES BALDUINO
                    <br>ENGENHEIRA MECÂNICA
                    <br>CREA: 2621406297
                </td>
                <td>
                    <br><br><br><br><br>DOUGLAS RODRIGO BERTOLAZZO
                    <br>COORDENADOR DE CAMPO
                    <br>INSPETOR DE EQUIPAMENTO NR-13
                    <br>INSTRUMENTAÇÃO E AUTOMAÇÃO
                </td>
            </tr>
        </tbody>
    </table>
');


$pdf->Output();
