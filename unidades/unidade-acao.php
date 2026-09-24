<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];


if ($acao == "") {

    $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));
    $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));

    $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM unidades WHERE titulo_unidade = '{$titulo}' AND id_empresa = '{$empresa}'");
    $row = mysqli_fetch_assoc($result);
    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma unidade nesta empresa com o título informado";
        header("Location: .{$link}p=unidade");
        exit();
    }

    $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM pastas WHERE nome_pasta = '{$titulo}' AND id_empresa = '{$empresa}' AND id_pasta_ascendente IS NULL");
    $row = mysqli_fetch_assoc($result);
    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma pasta no diretório raíz desta empresa com o título informado";
        header("Location: .{$link}p=unidade");
        exit();
    }

    $cnpj = mysqli_real_escape_string($connect, trim($_POST['cnpj']));

    $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM unidades WHERE cnpj = '{$cnpj}' AND id_empresa = '{$empresa}'");
    $row = mysqli_fetch_assoc($result);
    if ($row['total'] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma unidade nesta empresa com o CNPJ informado";
        header("Location: .{$link}p=unidade");
        exit();
    }

    $cep = mysqli_real_escape_string($connect, trim($_POST['cep']));
    $cidade = mysqli_real_escape_string($connect, trim($_POST['cidade']));
    $estado = mysqli_real_escape_string($connect, trim($_POST['estado']));
    $endereco = mysqli_real_escape_string($connect, trim($_POST['endereco']));
    $bairro = mysqli_real_escape_string($connect, trim($_POST['bairro']));
    $numero = mysqli_real_escape_string($connect, trim($_POST['numero']));


    $nomeEmpresa = mysqli_fetch_array(mysqli_query($connect, "SELECT nome_empresa FROM empresas WHERE id = '{$empresa}'"))['nome_empresa'];

    $caminhoPastaunidade = "../arquivos/{$nomeEmpresa}/{$titulo}";
    mkdir($caminhoPastaunidade, 0755, true);

    if (is_dir($caminhoPastaunidade)) {
        $insertUnidade = "INSERT INTO unidades (titulo_unidade, id_empresa, cnpj, cep, cidade, estado, endereco, bairro, numero) VALUES ('{$titulo}', '{$empresa}', '{$cnpj}', '{$cep}', '{$cidade}', '{$estado}', '{$endereco}', '{$bairro}', '{$numero}')";
        if ($connect->query($insertUnidade) === TRUE) {
            $idUnidade = $connect->insert_id;

            $pastaUnidade = "INSERT INTO pastas (id_empresa, id_unidade, nome_pasta) VALUES ('{$empresa}', '{$idUnidade}', '{$titulo}')";
            if ($connect->query($pastaUnidade) === TRUE)
                $_SESSION["alert_success"] = "Unidade cadastrada com sucesso!";
            else
                $_SESSION["alert_danger"] = "Erro ao cadastrar unidade";
        } else
            $_SESSION["alert_danger"] = "Erro ao cadastrar unidade";

        $connect->close();
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar unidade! Tente novamente";

    header("Location: .{$link}p=unidades");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));
        $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));

        $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM unidades WHERE titulo_unidade = '{$titulo}' AND id_empresa = '{$empresa}' AND id != '{$id}'");
        $row = mysqli_fetch_assoc($result);
        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma unidade nesta empresa com o título informado";
            header("Location: .{$link}p=unidades&acao=editar&id={$id}");
            exit();
        }

        $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM pastas WHERE nome_pasta = '{$titulo}' AND id_empresa = '{$empresa}' AND (id_unidade != '{$id}' OR id_unidade IS NULL) AND id_pasta_ascendente IS NULL");
        $row = mysqli_fetch_assoc($result);
        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma pasta no diretório raíz desta empresa com o título informado";
            header("Location: .{$link}p=unidades&acao=editar&id={$id}");
            exit();
        }

        $cnpj = mysqli_real_escape_string($connect, trim($_POST['cnpj']));

        $result = mysqli_query($connect, "SELECT COUNT(*) AS total FROM unidades WHERE cnpj = '{$cnpj}' AND id_empresa = '{$empresa}' AND id != '{$id}'");
        $row = mysqli_fetch_assoc($result);
        if ($row['total'] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma unidade nesta empresa com o CNPJ informado";
            header("Location: .{$link}p=unidades&acao=editar&id={$id}");
            exit();
        }

        $cep = mysqli_real_escape_string($connect, trim($_POST['cep']));
        $cidade = mysqli_real_escape_string($connect, trim($_POST['cidade']));
        $estado = mysqli_real_escape_string($connect, trim($_POST['estado']));
        $endereco = mysqli_real_escape_string($connect, trim($_POST['endereco']));
        $bairro = mysqli_real_escape_string($connect, trim($_POST['bairro']));
        $numero = mysqli_real_escape_string($connect, trim($_POST['numero']));

        $sql = "UPDATE unidades SET titulo_unidade = '{$titulo}', id_empresa = '{$empresa}', cnpj = '{$cnpj}', cep = '{$cep}', cidade = '{$cidade}', estado = '{$estado}', endereco = '{$endereco}', bairro = '{$bairro}', numero = '{$numero}' WHERE id = '{$id}'";

        $qr = mysqli_query($connect, "SELECT * FROM unidades WHERE id = '{$id}'");
        $dado = mysqli_fetch_array($qr);

        if ($connect->query($sql) === TRUE) {

            $nomeEmpresa = mysqli_fetch_array(mysqli_query($connect, "SELECT nome_empresa FROM empresas WHERE id = '{$empresa}'"))['nome_empresa'];

            if ($titulo != $dado['titulo_unidade']) {
                $dirBase = "../arquivos/{$nomeEmpresa}/";
                if (is_dir($dirBase . $dado['titulo_unidade']))
                    rename($dirBase . $dado['titulo_unidade'], $dirBase . $titulo);

                $connect->query("UPDATE pastas SET nome_pasta = '{$titulo}' WHERE id_unidade = '{$id}'");
            }

            $_SESSION["alert_success"] = "Unidade atualizada com sucesso!";
        } else
            $_SESSION["alert_danger"] = "Erro ao editar unidade";

        $connect->close();

        header("Location: .{$link}p=unidades&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {
        $qr = mysqli_query($connect, "SELECT u.titulo_unidade, e.nome_empresa FROM unidades u INNER JOIN empresas e ON e.id = u.id_empresa WHERE u.id = '{$id}'");
        $dadounidade = mysqli_fetch_array($qr);

        $sql = "DELETE FROM unidades WHERE id = '{$id}'";
        if ($connect->query($sql) === TRUE) {
            $_SESSION["alert_success"] = "Exclusão de unidade efetuada com sucesso!";

            $dir = "../arquivos/{$dadounidade['nome_empresa']}/{$dadounidade['titulo_unidade']}/";

            if (is_dir($dir)) {
                deleteDirectory($dir);

                $sql = "DELETE FROM pastas WHERE id_unidade = '{$id}'";
                if ($connect->query($sql) !== TRUE)
                    echo  "Erro ao excluir arquivo na unidade excluida";
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao excluir unidade";

        $connect->close();

        header("Location: .{$link}p=unidades");
        exit();
    }
}
