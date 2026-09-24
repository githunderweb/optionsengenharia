<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../../config.php");
include("../verifica-login.php");
// include("../../functions.php");

$acao = @$_GET["acao"];
$modeloOS = @$_GET["modeloOS"];
$link = $_SESSION["empresa_atual_link"];

if ($acao == "") {
    $redirect = "../";
    $dados = json_decode($_POST["dados"]);

    $tag = mysqli_real_escape_string($connect, trim($_POST["tagCPE"]));
    $numeroPasta = mysqli_real_escape_string($connect, trim($_POST["numeroPastaCPE"]));
    $nome = mysqli_real_escape_string($connect, trim($_POST["nomeCPE"]));
    $fabricante = mysqli_real_escape_string($connect, trim($_POST["fabricanteCPE"]));
    $anoFabricacao = mysqli_real_escape_string($connect, trim($_POST["anoFabricacaoCPE"]));
    $material = mysqli_real_escape_string($connect, trim($_POST["materialCPE"]));

    $equipamento = mysqli_real_escape_string($connect, trim($_POST["id_equipamento"]));
    $equipamento = $equipamento ? "'{$equipamento}'" : "NULL";

    $insertInspecao = "INSERT INTO inspecoes (id_modelo_relatorio_os, id_equipamento, id_inspetor, tag, numero_pasta, nome_equipamento, fabricante, ano_fabricacao, material, data_cadastro) VALUES ('{$modeloOS}', {$equipamento}, '{$sessaoUsuario["id"]}', '{$tag}', '{$numeroPasta}', '{$nome}', '{$fabricante}', '{$anoFabricacao}', '{$material}', NOW())";
    if ($connect->query($insertInspecao) === TRUE) {
        $_SESSION["alert_success"] = "Inspeção cadastrada com sucesso!";

        $idInspecao = $connect->insert_id;

        /*// Atualiza a OS para "Pendente" se todos os relatórios tiverem sido preenchidos
        $connect->query(
            "UPDATE ordens_servico
            SET status_os = 'Pendente'
            WHERE id = (
                SELECT id_os
                FROM (
                        SELECT mros.id_os,
                            (
                                SELECT COUNT(*)
                                FROM inspecoes i
                                INNER JOIN modelos_relatorio_os mros2 ON mros2.id = i.id_modelo_relatorio_os
                                WHERE mros2.id_os = mros.id_os
                            ) AS total_inspecoes,
                            (
                                SELECT COUNT(*)
                                FROM modelos_relatorio_os
                                WHERE id_os = mros.id_os
                            ) AS total_mros
                        FROM modelos_relatorio_os mros
                        WHERE mros.id = '{$modeloOS}'
                    ) AS subquery
                WHERE total_inspecoes = total_mros
            );"
        );*/

        foreach ($dados->campos as $dado) {
            $valor = "";

            if ($dado->tipo == "Texto" || $dado->tipo == "Texto longo" || $dado->tipo == "Múltipla escolha" || $dado->tipo == "Data e Hora" || $dado->tipo == "Data" || $dado->tipo == "Horário") {
                $valor = mysqli_real_escape_string($connect, trim($_POST[$dado->slug]));
            }
            if ($dado->tipo == "Caixa de seleção") {
                $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$dado->slug]));
            }
            if ($dado->tipo == "Lista suspensa") {
                if (is_array($_POST[$dado->slug])) {
                    if (count($_POST[$dado->slug]) > 0)
                        $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$dado->slug]));
                } else
                    $valor = mysqli_real_escape_string($connect, trim($_POST[$dado->slug]));
            }
            if ($dado->tipo == "Upload de arquivo") {
                $arquivosEnviados = [];

                if (count($_FILES[$dado->slug]["tmp_name"]) > 1 || $_FILES[$dado->slug]["tmp_name"][0] != "") {

                    $caminhoPastaInspecao = "../../inspecoes/arquivos/{$idInspecao}/";
                    if (!is_dir($caminhoPastaInspecao))
                        mkdir($caminhoPastaInspecao, 0755, true);

                    $caminhoPasta = "../../inspecoes/arquivos/{$idInspecao}/{$dado->slug}/";
                    if (!is_dir($caminhoPasta))
                        mkdir($caminhoPasta, 0755, true);

                    // Executa para cada arquivo
                    for ($i = 0; $i < count($_FILES[$dado->slug]["tmp_name"]); $i++) {
                        if ($_FILES[$dado->slug]["tmp_name"][$i] != "") {
                            $caminhoArquivo = "";
                            $arquivoAtual = explode(".", $_FILES[$dado->slug]['name'][$i]);

                            // Separa extensão do nome do arquivo
                            $extensaoArquivo = strtolower(end($arquivoAtual));
                            array_pop($arquivoAtual);
                            $nomeArquivo = join('.', $arquivoAtual);

                            if (in_array($nomeArquivo, $arquivosEnviados))
                                $nomeArquivo = "{$nomeArquivo}{$i}";

                            $caminhoArquivo = "{$caminhoPasta}{$nomeArquivo}.{$extensaoArquivo}";

                            if (move_uploaded_file($_FILES[$dado->slug]['tmp_name'][$i], $caminhoArquivo) === TRUE)
                                $arquivosEnviados[] = "{$nomeArquivo}.{$extensaoArquivo}";
                        }
                    }
                }

                if (count($arquivosEnviados) > 0)
                    $valor = mysqli_real_escape_string($connect, implode("|", $arquivosEnviados));
            }
            if ($dado->tipo == "Data e Hora") {
                $valor = mysqli_real_escape_string($connect, str_replace("T", " ", $_POST[$dado->slug]));
            }

            $connect->query("INSERT INTO valores_inspecao (id_inspecao, id_campo, slug, tipo, titulo, valor, origem) VALUES ('{$idInspecao}', '{$dado->id_campo}', '{$dado->slug}', '{$dado->tipo}', '{$dado->titulo_campo}', '{$valor}', '{$dado->origem}')");
        }
    } else {
        $_SESSION["alert_danger"] = "Erro ao cadastrar inspeção";
        $redirect = "../index.php?p=nova-inspecao&modeloOS={$modeloOS}";
    }

    $connect->close();

    header("Location: {$redirect}");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
        $redirect = "../";
        $dados = json_decode($_POST["dados"]);

        $tag = mysqli_real_escape_string($connect, trim($_POST["tagCPE"]));
        $numeroPasta = mysqli_real_escape_string($connect, trim($_POST["numeroPastaCPE"]));
        $nome = mysqli_real_escape_string($connect, trim($_POST["nomeCPE"]));
        $fabricante = mysqli_real_escape_string($connect, trim($_POST["fabricanteCPE"]));
        $anoFabricacao = mysqli_real_escape_string($connect, trim($_POST["anoFabricacaoCPE"]));
        $material = mysqli_real_escape_string($connect, trim($_POST["materialCPE"]));

        $equipamento = mysqli_real_escape_string($connect, trim($_POST["id_equipamento"]));
        $equipamento = $equipamento ? "'{$equipamento}'" : "NULL";

        $insertInspecao = "UPDATE inspecoes SET id_equipamento = {$equipamento}, id_inspetor = '{$sessaoUsuario["id"]}', tag = '{$tag}', numero_pasta = '{$numeroPasta}', nome_equipamento = '{$nome}', fabricante = '{$fabricante}', ano_fabricacao = '{$anoFabricacao}', material = '{$material}', status_inspecao = 'Pendente', data_edicao = NOW() WHERE id = '{$id}'";
        if ($connect->query($insertInspecao) === TRUE) {
            $_SESSION["alert_success"] = "Inspeção alterada com sucesso!";

            $idInspecao = $id;

            /*// Atualiza a OS para "Pendente" se todos os relatórios tiverem sido preenchidos
            $connect->query(
                "UPDATE ordens_servico
                SET status_os = 'Pendente'
                WHERE id = (
                    SELECT id_os
                    FROM (
                            SELECT mros.id_os,
                                (
                                    SELECT COUNT(*)
                                    FROM inspecoes i
                                    INNER JOIN modelos_relatorio_os mros2 ON mros2.id = i.id_modelo_relatorio_os
                                    WHERE mros2.id_os = mros.id_os
                                ) AS total_inspecoes,
                                (
                                    SELECT COUNT(*)
                                    FROM modelos_relatorio_os
                                    WHERE id_os = mros.id_os
                                ) AS total_mros
                            FROM modelos_relatorio_os mros
                            WHERE mros.id = '{$modeloOS}'
                        ) AS subquery
                    WHERE total_inspecoes = total_mros
                );"
            );*/

            foreach ($dados->campos as $dado) {
                $valor = "";

                if ($dado->tipo == "Texto" || $dado->tipo == "Texto longo" || $dado->tipo == "Múltipla escolha" || $dado->tipo == "Data e Hora" || $dado->tipo == "Data" || $dado->tipo == "Horário") {
                    $valor = mysqli_real_escape_string($connect, trim($_POST[$dado->slug]));
                }
                if ($dado->tipo == "Caixa de seleção") {
                    $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$dado->slug]));
                }
                if ($dado->tipo == "Lista suspensa") {
                    if (is_array($_POST[$dado->slug])) {
                        if (count($_POST[$dado->slug]) > 0)
                            $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$dado->slug]));
                    } else
                        $valor = mysqli_real_escape_string($connect, trim($_POST[$dado->slug]));
                }
                if ($dado->tipo == "Upload de arquivo") {
                    $arquivosEnviados = [];

                    if (count($_FILES[$dado->slug]["tmp_name"]) > 1 || $_FILES[$dado->slug]["tmp_name"][0] != "") {

                        $caminhoPastaInspecao = "../../inspecoes/arquivos/{$idInspecao}/";
                        if (!is_dir($caminhoPastaInspecao))
                            mkdir($caminhoPastaInspecao, 0755, true);

                        $caminhoPasta = "../../inspecoes/arquivos/{$idInspecao}/{$dado->slug}/";
                        if (!is_dir($caminhoPasta))
                            mkdir($caminhoPasta, 0755, true);

                        // Executa para cada arquivo
                        for ($i = 0; $i < count($_FILES[$dado->slug]["tmp_name"]); $i++) {
                            if ($_FILES[$dado->slug]["tmp_name"][$i] != "") {
                                $caminhoArquivo = "";
                                $arquivoAtual = explode(".", $_FILES[$dado->slug]['name'][$i]);

                                // Separa extensão do nome do arquivo
                                $extensaoArquivo = strtolower(end($arquivoAtual));
                                array_pop($arquivoAtual);
                                $nomeArquivo = join('.', $arquivoAtual);

                                if (in_array($nomeArquivo, $arquivosEnviados))
                                    $nomeArquivo = "{$nomeArquivo}{$i}";

                                $caminhoArquivo = "{$caminhoPasta}{$nomeArquivo}.{$extensaoArquivo}";

                                if (move_uploaded_file($_FILES[$dado->slug]['tmp_name'][$i], $caminhoArquivo) === TRUE)
                                    $arquivosEnviados[] = "{$nomeArquivo}.{$extensaoArquivo}";
                            }
                        }
                    }

                    if (count($arquivosEnviados) > 0)
                        $valor = mysqli_real_escape_string($connect, implode("|", $arquivosEnviados));
                }
                if ($dado->tipo == "Data e Hora") {
                    $valor = mysqli_real_escape_string($connect, str_replace("T", " ", $_POST[$dado->slug]));
                }

                // $connect->query("INSERT INTO valores_inspecao (id_inspecao, id_campo, slug, tipo, titulo, valor, origem) VALUES ('{$idInspecao}', '{$dado->id_campo}', '{$dado->slug}', '{$dado->tipo}', '{$dado->titulo_campo}', '{$valor}', '{$dado->origem}')");

                if ($dado->tipo != "Upload de arquivo" || ($dado->tipo == "Upload de arquivo" && $valor != ""))
                    $connect->query("UPDATE valores_inspecao SET valor = '{$valor}' WHERE id_inspecao = '{$idInspecao}' AND id_campo = '{$dado->id_campo}' AND slug = '{$dado->slug}' AND tipo = '{$dado->tipo}' AND titulo = '{$dado->titulo_campo}' AND origem = '{$dado->origem}'");
            }
        } else {
            $_SESSION["alert_danger"] = "Erro ao alterar inspeção";
            $redirect = "../index.php?p=editar-inspecao&modeloOS={$modeloOS}&idInspecao={$idInspecao}";
        }

        $connect->close();

        header("Location: {$redirect}");
        exit();
    } else if ($acao == "excluir") {
        $dirBase = ".../../inspecoes/arquivos/";

        $deleteInspecao = "DELETE FROM inspecoes WHERE id = '{$id}'";
        if ($connect->query($deleteInspecao) === TRUE) {
            $_SESSION["alert_success"] = "Exclusão de inspeção efetuada com sucesso!";

            $dir = "{$dirBase}{$id}/";

            if (is_dir($dir))
                deleteDirectory($dir);
        } else
            $_SESSION["alert_danger"] = "Erro ao excluir inspeção";

        $connect->close();

        header("Location: ../index.php?p=historico");
        exit();
    }
}
