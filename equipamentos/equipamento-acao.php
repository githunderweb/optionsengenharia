<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];
$usuarioEhCliente = permissaoUsuario("Cliente", $sessaoUsuario["funcao"]);

function redirecionarEquipamentos($link, $destino = "equipamentos", $query = "")
{
    header("Location: .{$link}p={$destino}{$query}");
    exit();
}

function equipamentoPertenceAoCliente($connect, $idEquipamento, $sessaoUsuario)
{
    $idEquipamento = mysqli_real_escape_string($connect, $idEquipamento);
    $idUsuario = intval($sessaoUsuario["id"]);

    $sql = "SELECT COUNT(*) AS total FROM equipamentos e WHERE e.id = '{$idEquipamento}' AND " . condicaoEquipamentosPermitidosUsuario($connect, $idUsuario, "e", $sessaoUsuario["id_empresa"]);
    return intval(mysqli_fetch_assoc(mysqli_query($connect, $sql))["total"]) > 0;
}

if ($acao === "excluir-arquivo") {
    $idEquipamento = intval($_GET["id"] ?? 0);
    $destino = $idEquipamento > 0 ? "&acao=editar&id={$idEquipamento}" : "";
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) {
        $_SESSION["alert_danger"] = "Você não tem permissão para excluir este arquivo.";
        redirecionarEquipamentos($link, "equipamentos", $destino);
    }

    $tokenRecebido = (string) ($_POST["token_exclusao_arquivo"] ?? "");
    $tokenSessao = (string) ($_SESSION["token_exclusao_arquivo_equipamento"] ?? "");
    if ($tokenSessao === "" || !hash_equals($tokenSessao, $tokenRecebido)) {
        $_SESSION["alert_danger"] = "A solicitação expirou. Atualize a página e tente novamente.";
        redirecionarEquipamentos($link, "equipamentos", $destino);
    }

    $dadosArquivo = json_decode(base64_decode((string) ($_POST["excluir_arquivo"] ?? ""), true), true);
    $slugCampo = is_array($dadosArquivo) ? (string) ($dadosArquivo[0] ?? "") : "";
    $arquivo = is_array($dadosArquivo) ? (string) ($dadosArquivo[1] ?? "") : "";
    if ($idEquipamento <= 0 || !preg_match('/^[a-zA-Z0-9_-]+$/', $slugCampo) || $arquivo === "" || strpos($arquivo, "\\") !== false || strpos($arquivo, "\0") !== false || basename($arquivo) !== $arquivo) {
        $_SESSION["alert_danger"] = "Arquivo inválido.";
        redirecionarEquipamentos($link, "equipamentos", $destino);
    }

    $slugCampoSql = mysqli_real_escape_string($connect, $slugCampo);
    $qrArquivo = mysqli_query($connect, "SELECT v.id, v.valor FROM equipamentos e INNER JOIN valores_campos_personalizados_equipamento v ON v.id_equipamento = e.id WHERE e.id = '{$idEquipamento}' AND v.slug = '{$slugCampoSql}' AND v.tipo = 'Upload de arquivo' LIMIT 1");
    $dadoArquivo = $qrArquivo ? mysqli_fetch_assoc($qrArquivo) : null;
    $arquivosAtuais = $dadoArquivo ? array_values(array_filter(array_map("trim", explode("|", (string) $dadoArquivo["valor"])), "strlen")) : [];
    $posicaoArquivo = array_search($arquivo, $arquivosAtuais, true);
    if ($posicaoArquivo === false) {
        $_SESSION["alert_danger"] = "Arquivo não encontrado neste equipamento.";
        redirecionarEquipamentos($link, "equipamentos", $destino);
    }

    $diretorioCampo = realpath(__DIR__ . "/arquivos/{$idEquipamento}/{$slugCampo}");
    $caminhoArquivo = $diretorioCampo ? realpath($diretorioCampo . DIRECTORY_SEPARATOR . $arquivo) : false;
    if ($caminhoArquivo && (!is_file($caminhoArquivo) || dirname($caminhoArquivo) !== $diretorioCampo)) {
        $_SESSION["alert_danger"] = "Caminho do arquivo inválido.";
        redirecionarEquipamentos($link, "equipamentos", $destino);
    }

    unset($arquivosAtuais[$posicaoArquivo]);
    $novoValor = implode("|", array_values($arquivosAtuais));
    $idValor = intval($dadoArquivo["id"]);
    $stmt = mysqli_prepare($connect, "UPDATE valores_campos_personalizados_equipamento SET valor = ? WHERE id = ?");
    $atualizado = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $novoValor, $idValor);
        $atualizado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if (!$atualizado) {
        $_SESSION["alert_danger"] = "Não foi possível excluir o arquivo.";
    } elseif ($caminhoArquivo && !unlink($caminhoArquivo)) {
        $valorAnterior = (string) $dadoArquivo["valor"];
        $restaurar = mysqli_prepare($connect, "UPDATE valores_campos_personalizados_equipamento SET valor = ? WHERE id = ?");
        if ($restaurar) {
            mysqli_stmt_bind_param($restaurar, "si", $valorAnterior, $idValor);
            mysqli_stmt_execute($restaurar);
            mysqli_stmt_close($restaurar);
        }
        $_SESSION["alert_danger"] = "Não foi possível remover o arquivo do servidor.";
    } else {
        $_SESSION["alert_success"] = "Arquivo excluído com sucesso!";
    }

    redirecionarEquipamentos($link, "equipamentos", $destino);
}


if ($acao == "") {
    if ($usuarioEhCliente) {
        $_SESSION["alert_danger"] = "Você não tem permissão para editar este equipamento.";
        redirecionarEquipamentos($link);
    }

    $tag = mysqli_real_escape_string($connect, trim($_POST["tag"]));
    $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
    $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM equipamentos WHERE tag = '{$tag}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe um equipamento nesta unidade com a TAG informada";
        header("Location: .{$link}p=equipamento");
        exit();
    }

    $nome = mysqli_real_escape_string($connect, trim($_POST["nome"]));
    $tipoEquipamento = mysqli_real_escape_string($connect, trim($_POST["tipoEquipamento"]));
    $categoria = mysqli_real_escape_string($connect, trim($_POST["categoriaEquipamento"]));
    $localInstalacao = mysqli_real_escape_string($connect, trim($_POST["localInstalacaoEquipamento"]));
    $numeroPasta = mysqli_real_escape_string($connect, trim($_POST["numeroPasta"]));
    $fabricante = mysqli_real_escape_string($connect, trim($_POST["fabricante"]));
    $material = mysqli_real_escape_string($connect, trim($_POST["material"]));
    $anoFabricacao = mysqli_real_escape_string($connect, trim($_POST["anoFabricacao"]));
    if ($anoFabricacao != "")
        $anoFabricacao = "'{$anoFabricacao}'";
    else
        $anoFabricacao = "NULL";

    $insertEquipamento = "INSERT INTO equipamentos (id_tipo_equipamento, id_empresa, id_unidade, id_local_instalacao, id_categoria_equipamento, nome_equipamento, numero_pasta, tag, fabricante, ano_fabricacao, material, data_cadastro) VALUES ('{$tipoEquipamento}', '{$empresa}', '{$unidade}', '{$localInstalacao}', '{$categoria}', '{$nome}', '{$numeroPasta}', '{$tag}', '{$fabricante}', {$anoFabricacao}, '{$material}', NOW())";
    if ($connect->query($insertEquipamento) === TRUE) {
        $_SESSION["alert_success"] = "Equipamento cadastrado com sucesso!";
        $idEquipamento = $connect->insert_id;

        $campos = json_decode($_POST["campos"]);
        foreach ($campos as $campo) {
            $valor = "";

            if ($campo->tipo == "Texto" || $campo->tipo == "Texto longo" || $campo->tipo == "Múltipla escolha" || $campo->tipo == "Data e Hora" || $campo->tipo == "Data" || $campo->tipo == "Horário") {
                $valor = mysqli_real_escape_string($connect, trim($_POST[$campo->slug]));
            }
            if ($campo->tipo == "Caixa de seleção") {
                $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$campo->slug]));
            }
            if ($campo->tipo == "Lista suspensa") {
                if (is_array($_POST[$campo->slug])) {
                    if (count($_POST[$campo->slug]) > 0)
                        $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$campo->slug]));
                } else
                    $valor = mysqli_real_escape_string($connect, trim($_POST[$campo->slug]));
            }
            if ($campo->tipo == "Upload de arquivo") {
                $arquivosEnviados = [];

                if (count($_FILES[$campo->slug]["tmp_name"]) > 1 || $_FILES[$campo->slug]["tmp_name"][0] != "") {

                    $caminhoPastaInspecao = "./arquivos/{$idEquipamento}/";
                    if (!is_dir($caminhoPastaInspecao))
                        mkdir($caminhoPastaInspecao, 0755, true);

                    $caminhoPasta = "./arquivos/{$idEquipamento}/{$campo->slug}/";
                    if (!is_dir($caminhoPasta))
                        mkdir($caminhoPasta, 0755, true);

                    // Executa para cada arquivo
                    for ($i = 0; $i < count($_FILES[$campo->slug]["tmp_name"]); $i++) {
                        if ($_FILES[$campo->slug]["tmp_name"][$i] != "") {
                            $caminhoArquivo = "";
                            $arquivoAtual = explode(".", $_FILES[$campo->slug]['name'][$i]);

                            // Separa extensão do nome do arquivo
                            $extensaoArquivo = strtolower(end($arquivoAtual));
                            array_pop($arquivoAtual);
                            $nomeArquivo = join('.', $arquivoAtual);

                            if (in_array($nomeArquivo, $arquivosEnviados))
                                $nomeArquivo = "{$nomeArquivo}{$i}";

                            $caminhoArquivo = "{$caminhoPasta}{$nomeArquivo}.{$extensaoArquivo}";

                            if (move_uploaded_file($_FILES[$campo->slug]['tmp_name'][$i], $caminhoArquivo) === TRUE)
                                $arquivosEnviados[] = "{$nomeArquivo}.{$extensaoArquivo}";
                        }
                    }
                }

                if (count($arquivosEnviados) > 0)
                    $valor = mysqli_real_escape_string($connect, implode("|", $arquivosEnviados));
            }
            if ($campo->tipo == "Data e Hora") {
                $valor = mysqli_real_escape_string($connect, str_replace("T", " ", $_POST[$campo->slug]));
            }

            $connect->query("INSERT INTO valores_campos_personalizados_equipamento (id_equipamento, id_campo_tipo_equipamento, slug, tipo, titulo, valor) VALUES ('{$idEquipamento}', '{$campo->id}', '{$campo->slug}', '{$campo->tipo}', '{$campo->titulo_campo}', '{$valor}')");
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar equipamento";

    $connect->close();

    redirecionarEquipamentos($link);
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
        if ($usuarioEhCliente) {
            if (equipamentoPertenceAoCliente($connect, $id, $sessaoUsuario)) {
                $_SESSION["alert_danger"] = "Você não tem permissão para editar este equipamento.";
                redirecionarEquipamentos($link, "equipamentos", "&acao=editar&id={$id}");
            }

            $_SESSION["alert_danger"] = "Equipamento não encontrado.";
            redirecionarEquipamentos($link);
        }

        $tag = mysqli_real_escape_string($connect, trim($_POST["tag"]));
        $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
        $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));

        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM equipamentos WHERE tag = '{$tag}' AND id_empresa = '{$empresa}' AND id_unidade = '{$unidade}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe um equipamento nesta unidade com a TAG informada";
            redirecionarEquipamentos($link, "equipamentos", "&acao=editar&id={$id}");
        }

        $nome = mysqli_real_escape_string($connect, trim($_POST["nome"]));
        $tipoEquipamento = mysqli_real_escape_string($connect, trim($_POST["tipoEquipamento"]));
        $categoria = mysqli_real_escape_string($connect, trim($_POST["categoriaEquipamento"]));
        $localInstalacao = mysqli_real_escape_string($connect, trim($_POST["localInstalacaoEquipamento"]));
        $numeroPasta = mysqli_real_escape_string($connect, trim($_POST["numeroPasta"]));
        $fabricante = mysqli_real_escape_string($connect, trim($_POST["fabricante"]));
        $material = mysqli_real_escape_string($connect, trim($_POST["material"]));
        $anoFabricacao = mysqli_real_escape_string($connect, trim($_POST["anoFabricacao"]));
        
        if ($anoFabricacao != "")
            $anoFabricacao = "'{$anoFabricacao}'";
        else
            $anoFabricacao = "NULL";

        $updateEquipamento = "UPDATE equipamentos SET id_tipo_equipamento = '{$tipoEquipamento}', id_empresa = '{$empresa}', id_unidade = '{$unidade}', id_local_instalacao = '{$localInstalacao}', id_categoria_equipamento = '{$categoria}', nome_equipamento = '{$nome}', numero_pasta = '{$numeroPasta}', tag = '{$tag}', fabricante = '{$fabricante}', ano_fabricacao = {$anoFabricacao}, material = '{$material}' WHERE id = '{$id}'";
        if ($connect->query($updateEquipamento) === TRUE) {
            $_SESSION["alert_success"] = "Equipamento atualizado com sucesso!";

            $campos = json_decode($_POST["campos"]);
            foreach ($campos as $campo) {
                $valor = "";

                if ($campo->tipo == "Texto" || $campo->tipo == "Texto longo" || $campo->tipo == "Múltipla escolha" || $campo->tipo == "Data e Hora" || $campo->tipo == "Data" || $campo->tipo == "Horário") {
                    $valor = mysqli_real_escape_string($connect, trim($_POST[$campo->slug]));
                }
                if ($campo->tipo == "Caixa de seleção") {
                    $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$campo->slug]));
                }
                if ($campo->tipo == "Lista suspensa") {
                    if (is_array($_POST[$campo->slug])) {
                        if (count($_POST[$campo->slug]) > 0)
                            $valor = mysqli_real_escape_string($connect, implode(", ", $_POST[$campo->slug]));
                    } else
                        $valor = mysqli_real_escape_string($connect, trim($_POST[$campo->slug]));
                }
                if ($campo->tipo == "Upload de arquivo") {
                    $arquivosEnviados = [];
                    $slugArquivo = mysqli_real_escape_string($connect, $campo->slug);
                    $qrValorArquivoAtual = mysqli_query($connect, "SELECT valor FROM valores_campos_personalizados_equipamento WHERE id_equipamento = '{$id}' AND slug = '{$slugArquivo}' LIMIT 1");
                    if ($dadoValorArquivoAtual = mysqli_fetch_assoc($qrValorArquivoAtual))
                        $valor = mysqli_real_escape_string($connect, (string) $dadoValorArquivoAtual["valor"]);

                    if (count($_FILES[$campo->slug]["tmp_name"]) > 1 || $_FILES[$campo->slug]["tmp_name"][0] != "") {

                        $caminhoPastaInspecao = "./arquivos/{$id}/";
                        if (!is_dir($caminhoPastaInspecao))
                            mkdir($caminhoPastaInspecao, 0755, true);

                        $caminhoPasta = "./arquivos/{$id}/{$campo->slug}/";
                        if (!is_dir($caminhoPasta))
                            mkdir($caminhoPasta, 0755, true);

                        // Executa para cada arquivo
                        for ($i = 0; $i < count($_FILES[$campo->slug]["tmp_name"]); $i++) {
                            if ($_FILES[$campo->slug]["tmp_name"][$i] != "") {
                                $caminhoArquivo = "";
                                $arquivoAtual = explode(".", $_FILES[$campo->slug]['name'][$i]);

                                // Separa extensão do nome do arquivo
                                $extensaoArquivo = strtolower(end($arquivoAtual));
                                array_pop($arquivoAtual);
                                $nomeArquivo = join('.', $arquivoAtual);

                                if (in_array($nomeArquivo, $arquivosEnviados))
                                    $nomeArquivo = "{$nomeArquivo}{$i}";

                                $caminhoArquivo = "{$caminhoPasta}{$nomeArquivo}.{$extensaoArquivo}";

                                if (move_uploaded_file($_FILES[$campo->slug]['tmp_name'][$i], $caminhoArquivo) === TRUE)
                                    $arquivosEnviados[] = "{$nomeArquivo}.{$extensaoArquivo}";
                            }
                        }
                    }

                    if (count($arquivosEnviados) > 0)
                        $valor = mysqli_real_escape_string($connect, implode("|", $arquivosEnviados));
                }
                if ($campo->tipo == "Data e Hora") {
                    $valor = mysqli_real_escape_string($connect, str_replace("T", " ", $_POST[$campo->slug]));
                }

                if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM valores_campos_personalizados_equipamento WHERE id_equipamento = '{$id}' AND slug = '{$campo->slug}'"))["total"] > 0)
                    $connect->query("UPDATE valores_campos_personalizados_equipamento SET id_campo_tipo_equipamento = '{$campo->id}', tipo = '{$campo->tipo}', titulo = '{$campo->titulo_campo}', valor = '{$valor}' WHERE id_equipamento = '{$id}' AND slug = '{$campo->slug}'");
                else
                    $connect->query("INSERT INTO valores_campos_personalizados_equipamento (id_equipamento, id_campo_tipo_equipamento, slug, tipo, titulo, valor) VALUES ('{$id}', '{$campo->id}', '{$campo->slug}', '{$campo->tipo}', '{$campo->titulo_campo}', '{$valor}')");
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao editar equipamento";

        $connect->close();

        redirecionarEquipamentos($link, "equipamentos", "&acao=editar&id={$id}");
    } else if ($acao == "excluir") {
        if ($usuarioEhCliente) {
            if (equipamentoPertenceAoCliente($connect, $id, $sessaoUsuario)) {
                $_SESSION["alert_danger"] = "Você não tem permissão para excluir este equipamento.";
                redirecionarEquipamentos($link);
            }

            $_SESSION["alert_danger"] = "Equipamento nÃ£o encontrado.";
            redirecionarEquipamentos($link);
        }

        $deleteEquipamento = "DELETE FROM equipamentos WHERE id = '{$id}'";
        if ($connect->query($deleteEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de equipamento efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir equipamento";

        $connect->close();

        redirecionarEquipamentos($link);
    }
}
