<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
include("../config.php");
include("../verifica-login.php");
include("../functions.php");

$acao = @$_GET["acao"];
$link = $_SESSION["empresa_atual_link"];


if ($acao == "") {
    $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
    $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));
    $inspetor = mysqli_real_escape_string($connect, trim($_POST["inspetor"]));
    $descricao = mysqli_real_escape_string($connect, trim($_POST["descricao"] ?? ""));

    $numeroOS = mysqli_real_escape_string($connect, trim($_POST["numeroOS"]));
    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM ordens_servico WHERE id_empresa = '{$empresa}' AND id_unidade = '{$unidade}' AND id_inspetor = '{$inspetor}' AND numero_os = '{$numeroOS}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma ordem de serviço com o Nº OS informado";
        header("Location: .{$link}p=ordem-servico");
        exit();
    }

    $insertOrdemServico = "INSERT INTO ordens_servico (id_empresa, id_unidade, id_inspetor, numero_os, descricao, data_cadastro, id_usuario_cadastro) VALUES ('{$empresa}', '{$unidade}', '{$inspetor}', '{$numeroOS}', '{$descricao}', NOW(), '{$sessaoUsuario["id"]}')";
    if ($connect->query($insertOrdemServico) === TRUE) {
        $_SESSION["alert_success"] = "Ordem de serviço cadastrada com sucesso!";
        $idOrdemServico = $connect->insert_id;

        foreach ($_POST["modelosRelatorio"] as $key => $modelo) {
            $modelo = mysqli_real_escape_string($connect, trim($modelo));

            $quantidade = "1";
            if (isset($_POST["quantidadeModelos"][$key]))
                $quantidade = mysqli_real_escape_string($connect, trim($_POST["quantidadeModelos"][$key]));

            for ($i = 0; $i < intval($quantidade); $i++) {
                $insertModeloOrdemServico = "INSERT INTO modelos_relatorio_os (id_os, id_modelo_relatorio) VALUES ('{$idOrdemServico}', '{$modelo}')";
                $connect->query($insertModeloOrdemServico);
            }
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar ordem de serviço";

    $connect->close();

    header("Location: .{$link}p=ordens-servico&acao=editar&id={$idOrdemServico}");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
        $redirect = ".{$link}p=ordens-servico&acao=editar&id={$id}";

        $empresa = mysqli_real_escape_string($connect, trim($_POST["empresa"]));
        $unidade = mysqli_real_escape_string($connect, trim($_POST["unidade"]));
        $inspetor = mysqli_real_escape_string($connect, trim($_POST["inspetor"]));
        $descricao = mysqli_real_escape_string($connect, trim($_POST["descricao"] ?? ""));

        $numeroOS = mysqli_real_escape_string($connect, trim($_POST["numeroOS"]));
        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM ordens_servico WHERE id_empresa = '{$empresa}' AND id_unidade = '{$unidade}' AND id_inspetor = '{$inspetor}' AND numero_os = '{$numeroOS}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma ordem de serviço com o Nº OS informado";
            header("Location: {$redirect}");
            exit();
        }

        $status = mysqli_real_escape_string($connect, trim($_POST["status"]));

        $updateInspetor = "UPDATE ordens_servico SET id_empresa = '{$empresa}', id_unidade = '{$unidade}', id_inspetor = '{$inspetor}', numero_os = '{$numeroOS}', descricao = '{$descricao}', status_os = '{$status}' WHERE id = '{$id}'";
        if ($connect->query($updateInspetor) === TRUE) {
            $_SESSION["alert_success"] = "Ordem de serviço atualizada com sucesso!";

            $qtsModelosRelatorio = [];
            foreach ($_POST["modelosRelatorio"] as $key => $modeloRelatorio) {
                $modeloRelatorio = mysqli_real_escape_string($connect, trim($modeloRelatorio));
                $qtsModelosRelatorio[$modeloRelatorio] = isset($qtsModelosRelatorio[$modeloRelatorio]) ? $qtsModelosRelatorio[$modeloRelatorio] + intval($_POST["quantidadeModelos"][$key]) : intval($_POST["quantidadeModelos"][$key]);
            }

            foreach ($qtsModelosRelatorio as $key => $qt) {
                $qtAtualBanco = intval(mysqli_fetch_assoc(mysqli_query(
                    $connect,
                    "SELECT COUNT(*) AS total FROM modelos_relatorio_os 
                    WHERE id_os = '{$id}' AND id_modelo_relatorio = '{$key}'"
                ))["total"]);

                if ($qtAtualBanco < $qt) {
                    $quantidade = $qt - $qtAtualBanco;

                    for ($i = 0; $i < $quantidade; $i++) {
                        $insertModeloRelatorioOS = "INSERT INTO modelos_relatorio_os (id_os, id_modelo_relatorio) VALUES ('{$id}', '{$key}')";
                        $connect->query($insertModeloRelatorioOS);
                    }
                } else if ($qtAtualBanco > $qt) {
                    $quantidade = $qtAtualBanco - $qt;

                    $qrModelosRelatorioOSDeletar = mysqli_query($connect, "SELECT mros.id FROM modelos_relatorio_os mros WHERE mros.id_os = '{$id}' AND mros.id_modelo_relatorio = '{$key}' AND NOT EXISTS ( SELECT 1 FROM inspecoes WHERE id_modelo_relatorio_os = mros.id ) ORDER BY mros.id DESC LIMIT {$quantidade}");
                    while ($dadoModeloRelatorioOSDeletar = mysqli_fetch_array($qrModelosRelatorioOSDeletar)) {
                        $connect->query("DELETE FROM modelos_relatorio_os WHERE id = '{$dadoModeloRelatorioOSDeletar["id"]}'");
                    }
                }
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao editar ordem de serviço";

        $connect->close();

        header("Location: {$redirect}");
        exit();
    } else if ($acao == "excluir") {

        $deleteOrdemServico = "DELETE FROM ordens_servico WHERE id = '{$id}'";
        if ($connect->query($deleteOrdemServico) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de ordem de serviço efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir ordem de serviço";

        $connect->close();

        header("Location: .{$link}p=ordens-servico");
        exit();
    }
}
