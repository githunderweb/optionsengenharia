<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];

if ($acao == "") {

    $dados = json_decode($_POST["dados"]);

    $insertInspecao = "INSERT INTO inspecoes (id_modelo_relatorio, data_cadastro) VALUES ('{$dados->modelo}', NOW())";
    if ($connect->query($insertInspecao) === TRUE) {
        $_SESSION["alert_success"] = "Inspeção cadastrada com sucesso!";

        $idInspecao = $connect->insert_id;

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

                    $caminhoPastaInspecao = "./arquivos/{$idInspecao}/";
                    if (!is_dir($caminhoPastaInspecao))
                        mkdir($caminhoPastaInspecao, 0755, true);

                    $caminhoPasta = "./arquivos/{$idInspecao}/{$dado->slug}/";
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

            $connect->query("INSERT INTO valores_inspecao (id_inspecao, id_campo, slug, tipo, titulo, valor) VALUES ('{$idInspecao}', '{$dado->id_campo}', '{$dado->slug}', '{$dado->tipo}', '{$dado->titulo_campo}', '{$valor}')");
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar inspeção";

    $connect->close();

    header("Location: .{$link}p=inspecoes");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $nome = mysqli_real_escape_string($connect, trim($_POST["nome"]));
        $numeroART = mysqli_real_escape_string($connect, trim($_POST["numeroART"]));
        $numeroPasta = mysqli_real_escape_string($connect, trim($_POST["numeroPasta"]));
        $tag = mysqli_real_escape_string($connect, trim($_POST["tag"]));
        $fabricante = mysqli_real_escape_string($connect, trim($_POST["fabricante"]));
        $anoFabricacao = mysqli_real_escape_string($connect, trim($_POST["anoFabricacao"]));
        $material = mysqli_real_escape_string($connect, trim($_POST["material"]));

        $updateInspecoes = "UPDATE inspecoes SET nome_equipamento = '{$nome}', numero_art = '{$numeroART}', numero_pasta = '{$numeroPasta}', tag = '{$tag}', fabricante = '{$fabricante}', ano_fabricacao = '{$anoFabricacao}', material = '{$material}' WHERE id = '{$id}'";
        if ($connect->query($updateInspecoes) === TRUE) {
            $_SESSION["alert_success"] = "Inspeção atualizado com sucesso!";

            $camposNaoAtualizados = [];
            foreach ($_POST as $campo => $valorCampo) {
                if (!in_array($campo, ["nome", "numeroART", "numeroPasta", "tag", "fabricante", "anoFabricacao", "material"])) {
                    $updateValorInspecao = "UPDATE valores_inspecao SET valor = '{$valorCampo}' WHERE id_inspecao = '{$id}' AND slug = '{$campo}'";
                    if ($connect->query($updateValorInspecao) !== TRUE)
                        $camposNaoAtualizados[] = $campo;
                }
            }

            $qtCamposNaoAtualizados = count($camposNaoAtualizados);
            if ($qtCamposNaoAtualizados) {
                $s = $qtCamposNaoAtualizados > 1 ? "s" : "";
                $_SESSION["alert_danger"] = "{$qtCamposNaoAtualizados} campo$s não atualizados";
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao editar inspeção";

        $connect->close();

        header("Location: .{$link}p=inspecoes&acao=ver&id={$id}");
        exit();
    } else if ($acao == "excluir") {
        $dirBase = "../arquivos/";

        $qr = mysqli_query($connect, "SELECT nome_inspecao FROM inspecoes WHERE id = '{$id}'");
        $dadoinspecao = mysqli_fetch_array($qr);

        $sql = "DELETE FROM inspecoes WHERE id = '{$id}'";
        if ($connect->query($sql) === TRUE) {
            $_SESSION["alert_success"] = "Exclusão de inspecao efetuada com sucesso!";

            $dir = $dirBase . $dadoinspecao['nome_inspecao'] . '/';

            if (is_dir($dir)) {

                deleteDirectory($dir);

                $sql = "DELETE FROM unidades WHERE id_inspecao = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir unidades na inspeção excluída";

                $sql = "DELETE FROM pastas WHERE id_inspecao = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir pastas na inspeção excluída";

                $sql = "DELETE FROM arquivos WHERE id_inspecao = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir arquivos na inspeção excluída";

                // $qr = mysqli_query($connect, "SELECT * FROM arquivos WHERE id_inspecao = '{$id}'");
                // while ($dadoArquivo = mysqli_fetch_array($qr)) {
                //     if (is_file($dir . $dadoArquivo['arquivo'])) {
                //         $sql = "DELETE FROM arquivos WHERE id = '{$dadoArquivo['id']}'";
                //         if ($connect->query($sql) === TRUE)
                //             unlink($dir . $dadoArquivo['arquivo']);
                //     }
                // }

                // rmdir($dir);
                // rename($dirBase . $dado['nome_inspecao'], $dirBase . $dado['nome_inspecao'] . " - Excluído {$id}");
            }



            if (preg_replace('/[^0-9]/', '', $link) == $id)
                $link = "./index.php?";
        } else
            $_SESSION["alert_danger"] = "Erro ao excluir inspeção";

        $connect->close();

        header("Location: .{$link}p=inspecoes");
        exit();
    } else if ($acao == "aprovar") {
        $redirect = ".{$link}p=inspecoes";
        if (isset($_SERVER["HTTP_REFERER"]))
            $redirect = $_SERVER["HTTP_REFERER"];

        $updateInspecao = "UPDATE inspecoes SET status_inspecao = 'Aprovado', data_validacao = NOW(), id_usuario_validou = '{$sessaoUsuario["id"]}' WHERE id = '{$id}'";
        if ($connect->query($updateInspecao) === TRUE) {
            $_SESSION['alert_success'] = "Inspeção aprovada com sucesso!";

            $qrInspecao = mysqli_query(
                $connect,
                "SELECT i.*, e.id AS equipamento, mr.id_tipo_equipamento, os.id_empresa, os.id_unidade, mros.id_os, mros.id AS id_modelo_relatorio_os 
                FROM inspecoes i 
                INNER JOIN modelos_relatorio_os mros ON mros.id = i.id_modelo_relatorio_os 
                INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
                INNER JOIN ordens_servico os ON os.id = mros.id_os 
                LEFT JOIN equipamentos e ON e.tag = i.tag AND e.id_empresa = os.id_empresa AND e.id_unidade = os.id_unidade 
                WHERE i.id = '{$id}'"
            );
            $dadoInspecao = mysqli_fetch_array($qrInspecao);

            // Atualiza a OS para "Pendente" se todos os relatórios tiverem sido preenchidos
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
                                    WHERE mros2.id_os = mros.id_os AND i.status_inspecao = 'Aprovado'
                                ) AS total_inspecoes,
                                (
                                    SELECT COUNT(*)
                                    FROM modelos_relatorio_os
                                    WHERE id_os = mros.id_os
                                ) AS total_mros
                            FROM modelos_relatorio_os mros
                            WHERE mros.id = '{$dadoInspecao["id_modelo_relatorio_os"]}'
                        ) AS subquery
                    WHERE total_inspecoes = total_mros
                );"
            );

            // Se o equipamento existir, apenas atualiza os campos. Se não cria um novo equipamento
            if ($dadoInspecao["equipamento"] != "") {
                $updateEquipamento = "UPDATE equipamentos SET tag = '{$dadoInspecao["tag"]}', numero_pasta = '{$dadoInspecao["numero_pasta"]}', nome_equipamento = '{$dadoInspecao["nome_equipamento"]}', fabricante = '{$dadoInspecao["fabricante"]}', ano_fabricacao = '{$dadoInspecao["ano_fabricacao"]}', material = '{$dadoInspecao["material"]}' WHERE id = '{$dadoInspecao["equipamento"]}'";
                if ($connect->query($updateEquipamento) !== TRUE)
                    $_SESSION['alert_danger'] = "Erro ao atualizar campo(s) do equipamento com base na inspeção";

                $qrValoresInspecao = mysqli_query(
                    $connect,
                    "SELECT * FROM valores_inspecao vi 
                    WHERE vi.id_inspecao = '{$id}' AND vi.origem = 'campos_tipo_equipamento'"
                );
                while ($dadosValorInspecao = mysqli_fetch_array($qrValoresInspecao)) {

                    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM valores_campos_personalizados_equipamento WHERE id_equipamento = '{$dadoInspecao["equipamento"]}' AND slug = '{$dadosValorInspecao["slug"]}'"))["total"] > 0)
                        $connect->query("UPDATE valores_campos_personalizados_equipamento SET id_campo_tipo_equipamento = '{$dadosValorInspecao["id_campo"]}', tipo = '{$dadosValorInspecao["tipo"]}', titulo = '{$dadosValorInspecao["titulo"]}', valor = '{$dadosValorInspecao["valor"]}' WHERE id_equipamento = '{$dadoInspecao["equipamento"]}' AND slug = '{$dadosValorInspecao["slug"]}'");
                    else
                        $connect->query("INSERT INTO valores_campos_personalizados_equipamento (id_equipamento, id_campo_tipo_equipamento, slug, tipo, titulo, valor) VALUES ('{$dadoInspecao["equipamento"]}', '{$dadosValorInspecao["id_campo"]}', '{$dadosValorInspecao["slug"]}', '{$dadosValorInspecao["tipo"]}', '{$dadosValorInspecao["titulo"]}', '{$dadosValorInspecao["valor"]}')");
                }
            } else {
                $insertEquipamento = "INSERT INTO equipamentos (id_tipo_equipamento, id_empresa, id_unidade, nome_equipamento, numero_pasta, tag, fabricante, ano_fabricacao, material, data_cadastro) VALUES ('{$dadoInspecao["id_tipo_equipamento"]}', '{$dadoInspecao["id_empresa"]}', '{$dadoInspecao["id_unidade"]}', '{$dadoInspecao["nome_equipamento"]}', '{$dadoInspecao["numero_pasta"]}', '{$dadoInspecao["tag"]}', '{$dadoInspecao["fabricante"]}', '{$dadoInspecao["ano_fabricacao"]}', '{$dadoInspecao["material"]}', NOW())";
                if ($connect->query($insertEquipamento) === TRUE) {
                    $idEquipamento = $connect->insert_id;
                    $connect->query("UPDATE inspecoes SET id_equipamento = '{$idEquipamento}' WHERE id = '{$id}'");

                    $qrValoresInspecao = mysqli_query(
                        $connect,
                        "SELECT * FROM valores_inspecao vi 
                        WHERE vi.id_inspecao = '{$id}' AND vi.origem = 'campos_tipo_equipamento'"
                    );
                    while ($dadosValorInspecao = mysqli_fetch_array($qrValoresInspecao)) {
                        $connect->query("INSERT INTO valores_campos_personalizados_equipamento (id_equipamento, id_campo_tipo_equipamento, slug, tipo, titulo, valor) VALUES ('{$idEquipamento}', '{$dadosValorInspecao["id_campo"]}', '{$dadosValorInspecao["slug"]}', '{$dadosValorInspecao["tipo"]}', '{$dadosValorInspecao["titulo"]}', '{$dadosValorInspecao["valor"]}')");
                    }
                } else
                    $_SESSION["alert_danger"] = "Erro ao cadastrar novo equipamento com base na inspeção";
            }
        } else
            $_SESSION['alert_danger'] = "Erro ao aprovar inspeção!";

        $connect->close();

        header('Location: ' . $redirect);
        exit();
    } else if ($acao == "reprovar") {
        $redirect = ".{$link}p=inspecoes";
        if (isset($_SERVER["HTTP_REFERER"]))
            $redirect = $_SERVER["HTTP_REFERER"];

        $motivo = field_formater($_POST["motivo"]);

        $updateInspecao = "UPDATE inspecoes SET status_inspecao = 'Reprovado', data_validacao = NOW(), id_usuario_validou = '{$sessaoUsuario["id"]}', motivo_reprovacao = '{$motivo}' WHERE id = '{$id}'";
        if ($connect->query($updateInspecao) === TRUE) {
            $_SESSION['alert_success'] = "Inspeção reprovado com sucesso!";
        } else
            $_SESSION['alert_danger'] = "Erro ao reprovar inspeção!";

        $connect->close();

        header('Location: ' . $redirect);
        exit();
    }
}
