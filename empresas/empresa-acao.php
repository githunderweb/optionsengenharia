<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];


if ($acao == "") {

    $nome = mysqli_real_escape_string($connect, trim($_POST['nome']));

    $sql = "SELECT COUNT(*) AS total FROM empresas WHERE nome_empresa = '{$nome}'";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma empresa com o nome informado";
        header("Location: .{$link}p=empresa");
        exit();
    }

    $cnpj = mysqli_real_escape_string($connect, trim($_POST['cnpj']));

    $sql = "SELECT COUNT(*) AS total FROM empresas WHERE cnpj != '' AND cnpj = '{$cnpj}'";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma empresa com o CNPJ informado";
        header("Location: .{$link}p=empresa");
        exit();
    }

    $cep = mysqli_real_escape_string($connect, trim($_POST['cep']));
    $cidade = mysqli_real_escape_string($connect, trim($_POST['cidade']));
    $estado = mysqli_real_escape_string($connect, trim($_POST['estado']));
    $endereco = mysqli_real_escape_string($connect, trim($_POST['endereco']));
    $bairro = mysqli_real_escape_string($connect, trim($_POST['bairro']));
    $numero = mysqli_real_escape_string($connect, trim($_POST['numero']));

    $caminhoPastaEmpresa = "../arquivos/{$nome}";
    mkdir($caminhoPastaEmpresa, 0755, true);

    if (is_dir($caminhoPastaEmpresa)) {
        $insertEmpresa = "INSERT INTO empresas (nome_empresa, cnpj, cep, cidade, estado, endereco, bairro, numero) VALUES ('{$nome}', '{$cnpj}', '{$cep}', '{$cidade}', '{$estado}', '{$endereco}', '{$bairro}', '{$numero}')";
        if ($connect->query($insertEmpresa) === TRUE)
            $_SESSION["alert_success"] = "Empresa cadastrada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao cadastrar empresa";

        $connect->close();
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar empresa! Tente novamente";

    header("Location: .{$link}p=empresas");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $nome = mysqli_real_escape_string($connect, trim($_POST['nome']));

        $sql = "SELECT COUNT(*) AS total FROM empresas WHERE nome_empresa = '{$nome}' AND id != '{$id}'";
        $result = mysqli_query($connect, $sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma empresa com o nome informado";
            header("Location: .{$link}p=empresas&acao=editar&id={$id}");
            exit();
        }

        $cnpj = mysqli_real_escape_string($connect, trim($_POST['cnpj']));

        $sql = "SELECT COUNT(*) AS total FROM empresas WHERE cnpj != '' AND cnpj = '{$cnpj}' AND id != '{$id}'";
        $result = mysqli_query($connect, $sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma empresa com o CNPJ informado";
            header("Location: .{$link}p=empresas&acao=editar&id={$id}");
            exit();
        }

        $cep = mysqli_real_escape_string($connect, trim($_POST['cep']));
        $cidade = mysqli_real_escape_string($connect, trim($_POST['cidade']));
        $estado = mysqli_real_escape_string($connect, trim($_POST['estado']));
        $endereco = mysqli_real_escape_string($connect, trim($_POST['endereco']));
        $bairro = mysqli_real_escape_string($connect, trim($_POST['bairro']));
        $numero = mysqli_real_escape_string($connect, trim($_POST['numero']));

        $sql = "UPDATE empresas SET nome_empresa = '{$nome}', cnpj = '{$cnpj}', cep = '{$cep}', cidade = '{$cidade}', estado = '{$estado}', endereco = '{$endereco}', bairro = '{$bairro}', numero = '{$numero}' WHERE id = '{$id}'";

        $qr = mysqli_query($connect, "SELECT * FROM empresas WHERE id = '{$id}'");
        $dado = mysqli_fetch_array($qr);

        if ($connect->query($sql) === TRUE) {

            $connect->close();

            $dirBase = "../arquivos/";
            if (is_dir($dirBase . $dado['nome_empresa']))
                rename($dirBase . $dado['nome_empresa'], $dirBase . $nome);

            $_SESSION["alert_success"] = "Empresa atualizada com sucesso!";
        } else
            $_SESSION["alert_danger"] = "Erro ao editar empresa";

        header("Location: .{$link}p=empresas&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {
        $dirBase = "../arquivos/";

        $qr = mysqli_query($connect, "SELECT nome_empresa FROM empresas WHERE id = '{$id}'");
        $dadoEmpresa = mysqli_fetch_array($qr);

        $sql = "DELETE FROM empresas WHERE id = '{$id}'";
        if ($connect->query($sql) === TRUE) {
            $_SESSION["alert_success"] = "Exclusão de empresa efetuada com sucesso!";

            $dir = $dirBase . $dadoEmpresa['nome_empresa'] . '/';

            if (is_dir($dir)) {

                deleteDirectory($dir);

                $sql = "DELETE FROM unidades WHERE id_empresa = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir unidades na empresa excluída";

                $sql = "DELETE FROM pastas WHERE id_empresa = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir pastas na empresa excluída";

                $sql = "DELETE FROM arquivos WHERE id_empresa = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir arquivos na empresa excluída";

                // $qr = mysqli_query($connect, "SELECT * FROM arquivos WHERE id_empresa = '{$id}'");
                // while ($dadoArquivo = mysqli_fetch_array($qr)) {
                //     if (is_file($dir . $dadoArquivo['arquivo'])) {
                //         $sql = "DELETE FROM arquivos WHERE id = '{$dadoArquivo['id']}'";
                //         if ($connect->query($sql) === TRUE)
                //             unlink($dir . $dadoArquivo['arquivo']);
                //     }
                // }

                // rmdir($dir);
                // rename($dirBase . $dado['nome_empresa'], $dirBase . $dado['nome_empresa'] . " - Excluído {$id}");
            }



            if (preg_replace('/[^0-9]/', '', $link) == $id)
                $link = "./index.php?";
        } else
            $_SESSION["alert_danger"] = "Erro ao excluir empresa";

        $connect->close();

        header("Location: .{$link}p=empresas");
        exit();
    }
}
