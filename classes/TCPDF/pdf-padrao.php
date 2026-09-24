<?php
require(__DIR__ . '/tcpdf.php');

// Crie uma classe que herda da classe TCPDF
class PDF extends TCPDF
{
    public $totalPages; // Variável para armazenar o número total de páginas

    public function __construct()
    {
        parent::__construct();
        // $this->SetMargins(10, 60, 10); // Define as margens do PDF
    }

    // Sobrescreva o método Header para adicionar um cabeçalho ao PDF
    public function Header()
    {
        // Adicionar a imagem no canto esquerdo do cabeçalho
        // $this->Image(__DIR__ . '/../../assets/img/logomarca.png');

        // $this->SetY(10);
        // $this->SetFont('helvetica', '', 10);
        /*$this->writeHTML('
            <table border="1" cellspacing="0" cellpadding="5">
                <tbody>
                    <tr style="text-align: center;">
                        <td rowspan="3" style="width: 100px;"><img src="' . __DIR__ . '/../../assets/img/logomarca.png" width="90" height="74"></td>
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
                        <td style="width: 120px;">Opt-2X.000XXX-000</td>
                    </tr>
                    <tr style="text-align: center;">
                        <td style="width: 120px;"><br><br><br><strong>Data/ Date:</strong> ' . date("d/m/Y") . '</td>
                    </tr>
                </tbody>
            </table>
        ');*/




        // Adiciona a imagem como marca d'água
        $this->Image(__DIR__ . '/../../assets/img/marca-dagua.jpg', 37, 110);
    }

    // Sobrescreva o método Footer para adicionar um rodapé ao PDF
    public function Footer()
    {
        // Se o número total de páginas ainda não foi definido, defina-o
        if (!$this->totalPages) {
            $this->totalPages = $this->getAliasNbPages();
        }

        // var_dump($this->totalPages);

        // Define o conteúdo do rodapé
        //$totalPagesText = $this->totalPages . ' (' . $this->numberToWords($this->totalPages) . ')'; // Aqui estamos usando uma função fictícia 'numberToWords' para converter o número em palavras

        // Obtém o número da página atual
        $currentPage = $this->getAliasNumPage();

        // Define a posição vertical do texto no rodapé
        $this->SetY(-15);

        // $this->SetFont('helvetica', 'U', 9);
        $this->SetFont('helvetica', '', 9);

        // Largura da célula onde o texto será exibido
        $cellWidth = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Adiciona o texto fixo no canto esquerdo do rodapé
        // $this->MultiCell($cellWidth, 9, 'O conteúdo deste documento é confidencial e protegido por direitos autorais. Seu conteúdo não poderá ser divulgado sob nenhuma circustância sem autorização prévia da OPTIONS ENGENHARIA', 0, 'C');

        $this->writeHTML('<p style="text-align: center;">O conteúdo deste documento é confidencial e protegido por direitos autorais. Seu conteúdo não poderá ser divulgado sob nenhuma circustância sem autorização prévia da OPTIONS ENGENHARIA</p>');


        $this->SetFont('helvetica', '', 10);

        // Define a posição vertical da numeração da página no rodapé
        $this->SetY(-10);
        // Adiciona a numeração da página no canto direito do rodapé
        $pageText = $currentPage . ' de ' . $this->totalPages;
        $this->Cell($cellWidth + 16, 9, $pageText, 0, 0, 'R');

        // Desenhar uma borda ao redor de toda a página
        // $this->Rect(5, 5, 200, 287); // Coordenadas X, Y, Largura, Altura
    }



    public function ImgDimensoesMaximas($caminhoImg, $alturaMaxima, $larguraMaxima = 0)
    {
        if (!file_exists($caminhoImg)) {
            return "";
        }

        list($larguraOriginal, $alturaOriginal) = getimagesize($caminhoImg);

        // Mantém a proporção da imagem
        $novaLargura = $larguraOriginal;
        $novaAltura = $alturaOriginal;

        // Se a altura original for maior que a altura máxima, redimensiona proporcionalmente
        if ($alturaOriginal > $alturaMaxima) {
            $novaAltura = $alturaMaxima;
            $novaLargura = ($alturaMaxima / $alturaOriginal) * $larguraOriginal;
        }

        // Se houver uma largura máxima definida e a nova largura ultrapassá-la, ajusta proporcionalmente
        if ($larguraMaxima > 0 && $novaLargura > $larguraMaxima) {
            $novaLargura = $larguraMaxima;
            $novaAltura = ($larguraMaxima / $larguraOriginal) * $alturaOriginal;
        }

        // Retorna a tag <img> com as dimensões ajustadas
        return "<img src=\"{$caminhoImg}\" width=\"{$novaLargura}\" height=\"{$novaAltura}\">";
    }
}
