<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica_login.php");
include("../functions.php");

$link = $_SESSION["empresa_atual_link"];
$acao = @$_GET["acao"];

if ($acao == "criar-pasta") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    // Verifica se o diretório que chegou existe antes de tentar criar a pasta
    if (is_dir($diretorio)) {
        $empresa = @$_GET["empresa"];
        $nomePasta = @$_POST["nomePasta"];

        $tituloPasta = $caminhoPasta = "";
        $qt = 1;
        // Incrementa o número no título da pasta caso necessário. Ex: "Pasta (2)"
        while (true) {
            $tituloPasta = $nomePasta;
            if ($qt > 1)
                $tituloPasta .= " ({$qt})";

            $caminhoPasta =  "{$diretorio}{$tituloPasta}/";

            if (!is_dir($caminhoPasta))
                break;

            $qt++;
        }

        // Insere no banco e cria pasta
        $insertPasta = "INSERT INTO pastas (id_empresa, nome_pasta) VALUES ('{$empresa}', '{$tituloPasta}')";
        if ($pastaAtual != "")
            $insertPasta = "INSERT INTO pastas (id_empresa, id_pasta_ascendente, nome_pasta) VALUES ('{$empresa}', '{$pastaAtual}', '{$tituloPasta}')";

        if ($connect->query($insertPasta) === TRUE) {
            mkdir($caminhoPasta, 0755, true);
            $_SESSION["alert_success"] = "Pasta criada com sucesso";
        } else
            $_SESSION["alert_danger"] = "Erro ao criar a pasta {$nomePasta}";

        $connect->close();
    } else
        $_SESSION["alert_danger"] = "Erro ao criar a pasta {$nomePasta}";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "renomear-pasta") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    $id = @$_GET["id"];
    $nomePastaAtual = mysqli_fetch_array(mysqli_query($connect, "SELECT nome_pasta FROM pastas WHERE id = '{$id}'"))["nome_pasta"];
    $caminhoPastaAtual =  "{$diretorio}{$nomePastaAtual}/";

    // Verifica se o o diretorio da pasta atual existe antes de renomear
    if (is_dir($caminhoPastaAtual)) {
        $nomePasta = $_POST["nome"];
        $nomePastaMudou = true;

        $tituloPasta = $caminhoPasta = "";
        $qt = 1;
        // Incrementa o número no título da pasta caso necessário. Ex: "Pasta (2)"
        while (true) {
            $tituloPasta =  $nomePasta;
            if ($qt > 1)
                $tituloPasta .= " ({$qt})";

            $caminhoPasta =  "{$diretorio}{$tituloPasta}/";

            if (!is_dir($caminhoPasta))
                break;
            else  if ($caminhoPasta == $caminhoPastaAtual) { // Verifica se o título da pasta realmente mudou
                $nomePastaMudou = false;
                break;
            }

            $qt++;
        }

        if ($nomePastaMudou) {
            // Muda o nome da pasta no banco e diretório
            $updatePasta = "UPDATE pastas SET nome_pasta = '{$tituloPasta}' WHERE id = '{$id}'";
            if ($connect->query($updatePasta) === TRUE) {
                rename($caminhoPastaAtual, $caminhoPasta);
                $_SESSION["alert_success"] = "Pasta renomeada com sucesso!";
            } else
                $_SESSION["alert_danger"] = "Erro ao renomear pasta {$nomePastaAtual}";

            $connect->close();
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao renomear pasta {$nomePastaAtual}";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "excluir-pasta") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    $id = @$_GET["id"];
    $nomePastaAtual = mysqli_fetch_array(mysqli_query($connect, "SELECT nome_pasta FROM pastas WHERE id = '{$id}'"))["nome_pasta"];
    $caminhoPastaAtual =  "{$diretorio}{$nomePastaAtual}/";

    // Verifica se o diretorio da pasta atual existe antes de excluir
    if (is_dir($caminhoPastaAtual)) {

        // Deleta pasta do banco e diretório
        $deletePasta = "DELETE FROM pastas WHERE id = '{$id}'";
        if ($connect->query($deletePasta) === TRUE) {
            deleteDirectory($caminhoPastaAtual);
            $_SESSION["alert_success"] = "Pasta excluída com sucesso!";
        } else
            $_SESSION["alert_danger"] = "Erro ao excluir a pasta {$nomePastaAtual}";

        $connect->close();
    } else
        $_SESSION["alert_danger"] = "Erro ao excluir a pasta {$nomePastaAtual}";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "upload-arquivo") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    // Verifica se o diretório que chegou existe antes de tentar fazer upload de arquivo
    if (is_dir($diretorio)) {
        // Verifica se foi selecionado pelo menos um arquivo
        if (count($_FILES["arquivos"]["tmp_name"]) > 1 || $_FILES["arquivos"]["tmp_name"][0] != "") {
            $empresa = @$_GET["empresa"];

            $arquivosEnviados = [];

            // Executa para cada arquivo
            for ($i = 0; $i < count($_FILES["arquivos"]["tmp_name"]); $i++) {
                if ($_FILES["arquivos"]["tmp_name"][$i] != "") {
                    $arquivoAtual = explode(".", $_FILES['arquivos']['name'][$i]);

                    // Separa extensão do nome do arquivo
                    $extensaoArquivo = strtolower(end($arquivoAtual));
                    array_pop($arquivoAtual);
                    $nomeArquivo = join('.', $arquivoAtual);

                    $tituloArquivo = $caminhoArquivo = "";
                    $qt = 1;
                    // Incrementa o número no título do arquivo caso necessário. Ex: "Arquivo (2)"
                    while (true) {
                        $tituloArquivo = $nomeArquivo;
                        if ($qt > 1)
                            $tituloArquivo .= " ({$qt})";

                        $caminhoArquivo = "{$diretorio}{$tituloArquivo}.{$extensaoArquivo}";

                        if (!is_file($caminhoArquivo))
                            break;

                        $qt++;
                    }

                    // Insere no banco e cria arquivo
                    $insertArquivo = "INSERT INTO arquivos (id_empresa, arquivo, data_upload) VALUES ('{$empresa}', '{$tituloArquivo}.{$extensaoArquivo}', NOW())";
                    if ($pastaAtual != "")
                        $insertArquivo = "INSERT INTO arquivos (id_empresa, id_pasta_ascendente, arquivo, data_upload) VALUES ('{$empresa}', '{$pastaAtual}', '{$tituloArquivo}.{$extensaoArquivo}', NOW())";

                    if ($connect->query($insertArquivo) === TRUE) {
                        $_SESSION["alert_success"] = "Arquivo enviado com sucesso";
                        move_uploaded_file($_FILES['arquivos']['tmp_name'][$i], $caminhoArquivo);
                        $arquivosEnviados[] = "{$tituloArquivo}.{$extensaoArquivo}";
                    } else
                        $_SESSION["alert_danger"] = "Erro ao enviar arquivo";
                }
            }

            // Dispara e-mail notificando usuários que terão acesso ao arquivo
            if (isset($_SESSION["alert_success"]) && $_SESSION["alert_success"] == "Arquivo enviado com sucesso") {
                $arquivosDisponiveis = implode("\r\n", $arquivosEnviados);
                $diretorios = explode("/", $diretorio);

                $sqlUsuariosNotificar = "SELECT u.email, u.nome FROM usuarios u WHERE u.id_empresa = '{$empresa}' AND (u.id_unidade = '' OR u.id_unidade IS NULL) AND u.status_usuario = 'Ativo'";
                if (isset($diretorios[3]) && $diretorios[3] != "")
                    $sqlUsuariosNotificar = "SELECT u.email, u.nome FROM usuarios u INNER JOIN unidades un ON un.id_empresa = '{$empresa}' AND un.titulo_unidade = '{$diretorios[3]}' AND un.id = u.id_unidade WHERE u.status_usuario = 'Ativo' UNION {$sqlUsuariosNotificar}";
                $qrUsuariosNotificar = mysqli_query($connect, $sqlUsuariosNotificar);
                while ($dadoUsuario = mysqli_fetch_array($qrUsuariosNotificar)) {
                    $assunto = "Um novo arquivo está disponível para download";
                    if (count($arquivosEnviados) > 1)
                        $assunto = "Novos arquivos estão disponíveis para download";
                    $mensagem = "Prezado {$dadoUsuario["nome"]},\r\n\r\n{$assunto}.\r\n{$arquivosDisponiveis}\r\nQuaisquer dúvidas/problemas, por favor entre em contato no e-mail: ti@optionsengenharia.com.br\r\n\r\nAtenciosamente, Options Engenharia";
                    enviarEmail($dadoUsuario["email"], $assunto, $mensagem);
                }
            }

            $connect->close();
        } else
            $_SESSION["alert_danger"] = "Selecione pelo menos 1 arquivo!";
    } else
        $_SESSION["alert_danger"] = "Erro ao fazer upload";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "copiar-arquivo") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    // Verifica se o diretório que chegou existe antes de tentar copiar o arquivo
    if (is_dir($diretorio)) {
        $arquivoCopiado = $_SESSION["copiar_arquivo"];
        $arquivoCopiado["dir"] = ".{$arquivoCopiado["dir"]}";

        // Verifica se o arquivo que chegou existe antes de tentar copiar
        if (is_file($arquivoCopiado["dir"])) {
            $empresa = @$_GET["empresa"];

            $arquivoEnviado = "";

            $extensaoArquivo = $arquivoCopiado["extensao"];
            $nomeArquivo = $arquivoCopiado["nome"];

            $tituloArquivo = $caminhoArquivo = "";
            $qt = 1;
            // Incrementa o número no título do arquivo caso necessário. Ex: "Arquivo (2)"
            while (true) {
                $tituloArquivo = $nomeArquivo;
                if ($qt > 1)
                    $tituloArquivo .= " ({$qt})";

                $caminhoArquivo = "{$diretorio}{$tituloArquivo}.{$extensaoArquivo}";

                if (!is_file($caminhoArquivo))
                    break;

                $qt++;
            }

            // Insere no banco e cria arquivo
            $insertArquivo = "INSERT INTO arquivos (id_empresa, arquivo, data_upload) VALUES ('{$empresa}', '{$tituloArquivo}.{$extensaoArquivo}', NOW())";
            if ($pastaAtual != "")
                $insertArquivo = "INSERT INTO arquivos (id_empresa, id_pasta_ascendente, arquivo, data_upload) VALUES ('{$empresa}', '{$pastaAtual}', '{$tituloArquivo}.{$extensaoArquivo}', NOW())";

            if ($connect->query($insertArquivo) === TRUE) {
                unset($_SESSION["copiar_arquivo"]);
                copy($arquivoCopiado["dir"], $caminhoArquivo);
                $_SESSION["alert_success"] = "Arquivo copiado com sucesso";
                $arquivoEnviado = "{$tituloArquivo}.{$extensaoArquivo}";
            } else
                $_SESSION["alert_danger"] = "Erro ao gerar cópia! Não foi possível cadastrar o arquivo no banco de dados";


            // Dispara e-mail notificando usuários que terão acesso ao arquivo
            if (isset($_SESSION["alert_success"]) && $_SESSION["alert_success"] == "Arquivo enviado com sucesso") {
                $diretorios = explode("/", $diretorio);

                $sqlUsuariosNotificar = "SELECT u.email, u.nome FROM usuarios u WHERE u.id_empresa = '{$empresa}' AND (u.id_unidade = '' OR u.id_unidade IS NULL) AND u.status_usuario = 'Ativo'";
                if (isset($diretorios[3]) && $diretorios[3] != "")
                    $sqlUsuariosNotificar = "SELECT u.email, u.nome FROM usuarios u INNER JOIN unidades un ON un.id_empresa = '{$empresa}' AND un.titulo_unidade = '{$diretorios[3]}' AND un.id = u.id_unidade WHERE u.status_usuario = 'Ativo' UNION {$sqlUsuariosNotificar}";
                $qrUsuariosNotificar = mysqli_query($connect, $sqlUsuariosNotificar);
                while ($dadoUsuario = mysqli_fetch_array($qrUsuariosNotificar)) {
                    $assunto = "Um novo arquivo está disponível para download";
                    $mensagem = "Prezado {$dadoUsuario["nome"]},\r\n\r\n{$assunto}.\r\n{$arquivoEnviado}\r\nQuaisquer dúvidas/problemas, por favor entre em contato no e-mail: ti@optionsengenharia.com.br\r\n\r\nAtenciosamente, Options Engenharia";
                    enviarEmail($dadoUsuario["email"], $assunto, $mensagem);
                }
            }

            $connect->close();
        } else
            $_SESSION["alert_danger"] = "Erro ao gerar cópia! Arquivo original não encontrado";
    } else
        $_SESSION["alert_danger"] = "Erro ao gerar cópia! Diretório de destino não encontrado";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "renomear-arquivo") {
    $diretorio = ".{$_GET['dir']}";
    $pastaAtual = @$_GET["pasta"];

    $id = @$_GET["id"];
    $nomeArquivoAtual = mysqli_fetch_array(mysqli_query($connect, "SELECT arquivo FROM arquivos WHERE id = '{$id}'"))["arquivo"];
    $caminhoPastaAtual =  "{$diretorio}{$nomeArquivoAtual}";

    // Verifica se o arquivo atual existe antes de renomear
    if (is_file($caminhoPastaAtual)) {
        // Separa extensão do nome do arquivo atual
        $arquivoAtual = explode(".", $nomeArquivoAtual);
        $extensaoArquivo = strtolower(end($arquivoAtual));
        array_pop($arquivoAtual);
        $nomeArquivoAtual = join('.', $arquivoAtual);


        $nomeArquivo = $_POST["nome"];
        $nomeArquivoMudou = true;

        $tituloArquivo = $caminhoPasta = "";
        $qt = 1;
        // Incrementa o número no título do arquivo caso necessário. Ex: "Arquivo (2)"
        while (true) {
            $tituloArquivo =  $nomeArquivo;
            if ($qt > 1)
                $tituloArquivo .= " ({$qt})";

            $caminhoPasta =  "{$diretorio}{$tituloArquivo}.{$extensaoArquivo}";

            if (!is_file($caminhoPasta))
                break;
            else  if ($caminhoPasta == $caminhoPastaAtual) { // Verifica se o título do arquivo realmente mudou
                $nomeArquivoMudou = false;
                break;
            }

            $qt++;
        }

        if ($nomeArquivoMudou) {
            // Muda o nome da pasta no banco e diretório
            $updateArquivo = "UPDATE arquivos SET arquivo = '{$tituloArquivo}.{$extensaoArquivo}' WHERE id = '{$id}'";
            if ($connect->query($updateArquivo) === TRUE) {
                rename($caminhoPastaAtual, $caminhoPasta);
                $_SESSION["alert_success"] = "Arquivo renomeado com sucesso!";
            } else
                $_SESSION["alert_danger"] = "Erro ao renomear arquivo {$nomeArquivoAtual}";

            $connect->close();
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao renomear arquivo";

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
} else if ($acao == "excluir-arquivo") {
    $pastaAtual = @$_GET["pasta"];
    $arquivos = json_decode($_GET["arquivos"]);

    $arquivosExcluidos = $arquivosNaoExcluidos = [];

    foreach ($arquivos as $arquivoAtual) {
        $caminhoArquivoAtual =  ".{$arquivoAtual->dir}";
        // Verifica se o arquivo atual existe antes de excluir
        if (is_file($caminhoArquivoAtual)) {
            // Deleta arquivo do banco e diretório
            $deleteArquivo = "DELETE FROM arquivos WHERE id = '{$arquivoAtual->id}'";
            if ($connect->query($deleteArquivo) === TRUE) {
                unlink($caminhoArquivoAtual);
                $arquivosExcluidos[] = $arquivoAtual->id;
            } else
                $arquivosNaoExcluidos[] = $arquivoAtual->nome;
        } else
            $arquivosNaoExcluidos[] = $arquivoAtual->nome;
    }

    $connect->close();

    $qtArquivosExcluidos = count($arquivosExcluidos);
    if ($qtArquivosExcluidos > 0) {
        $_SESSION["alert_success"] = "Arquivo excluído com sucesso!";
        if ($qtArquivosExcluidos > 1)
            $_SESSION["alert_success"] = "{$qtArquivosExcluidos} arquivos excluídos com sucesso!";
    }

    $qtArquivosNaoExcluidos = count($arquivosNaoExcluidos);
    if ($qtArquivosNaoExcluidos > 0) {
        $_SESSION["alert_danger"] = "Erro ao excluir o arquivo!";
        if ($qtArquivosNaoExcluidos > 1)
            $_SESSION["alert_danger"] = "Erro ao excluir os arquivos: " . implode(", ", $arquivosNaoExcluidos);
    }

    if ($pastaAtual != "")
        $pastaAtual = "&pasta={$pastaAtual}";

    header("Location: .{$link}p=ged{$pastaAtual}");
    exit();
}
