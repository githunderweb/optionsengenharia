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

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM modelos_relatorio WHERE titulo_modelo_relatorio = '{$titulo}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma modelo de relatório com o título informado";
        header("Location: .{$link}p=modelo-relatorio");
        exit();
    }


    $tipoEquipamento = mysqli_real_escape_string($connect, trim($_POST["tipoEquipamento"]));

    $insertModeloRelatorio = "INSERT INTO modelos_relatorio (id_tipo_equipamento, titulo_modelo_relatorio, data_cadastro) VALUES ('{$tipoEquipamento}', '{$titulo}', NOW())";
    if ($connect->query($insertModeloRelatorio) === TRUE) {
        $_SESSION["alert_success"] = "Modelo de relatório cadastrado com sucesso!";

        $idModeloRelatorio = $connect->insert_id;
        $camposNaoInseridos = [];

        for ($i = 0; $i < count($_POST['tituloCampo']); $i++) {

            $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$i]));
            $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_modelo_relatorio", 0, "AND id_modelo_relatorio = '{$idModeloRelatorio}'"));
            $tipo = mysqli_real_escape_string($connect, trim($_POST['tipo'][$i]));
            $obrigatorio = 0;
            if (isset($_POST['obrigatorio'][$i]) && $_POST['obrigatorio'][$i] == "on")
                $obrigatorio = 1;
            $opcoes = "";
            if (isset($_POST['opcoes'][$i]))
                $opcoes = mysqli_real_escape_string($connect, str_replace(['[', ']', '{"value":"', '"}'], "", $_POST["opcoes"][$i]));
            $multiplo = 0;
            if (isset($_POST['multiplo'][$i]) && $_POST['multiplo'][$i] == "on")
                $multiplo = 1;
            $tipoArquivo = "";
            if (isset($_POST['tipoArquivo'][$i]))
                $tipoArquivo = mysqli_real_escape_string($connect, trim($_POST['tipoArquivo'][$i]));

            $insertCampoModeloRelatorio = "INSERT INTO campos_modelo_relatorio (id_modelo_relatorio, titulo_campo, slug, tipo, obrigatorio, opcoes, multiplo, tipo_arquivo) VALUES ('{$idModeloRelatorio}', '{$tituloCampo}', '{$slug}', '{$tipo}', '{$obrigatorio}', '{$opcoes}', '{$multiplo}', '{$tipoArquivo}')";
            if ($connect->query($insertCampoModeloRelatorio) !== TRUE)
                $camposNaoInseridos[] = $tituloCampo;
        }

        $qtCamposNaoInseridos = count($camposNaoInseridos);
        if ($qtCamposNaoInseridos > 0) {
            $s = "";
            if ($qtCamposNaoInseridos > 1)
                $s = "s";
            $_SESSION["alert_danger"] = "Erro ao cadastrar no modelo <span class='fw-bold'>{$titulo}<span> o$s seguinte$s campo$s: " . implode(", ", $camposNaoInseridos);
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar modelo de relatório";

    $connect->close();

    header("Location: .{$link}p=modelos-relatorio");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));

        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM modelos_relatorio WHERE titulo_modelo_relatorio = '{$titulo}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma modelo de relatório com o título informado";
            header("Location: .{$link}p=modelos-relatorio&acao=editar&id={$id}");
            exit();
        }

        $tipoEquipamento = mysqli_real_escape_string($connect, trim($_POST["tipoEquipamento"]));
        $status = mysqli_real_escape_string($connect, trim($_POST['status']));

        $updateModelosRelatorio = "UPDATE modelos_relatorio SET id_tipo_equipamento = '{$tipoEquipamento}', titulo_modelo_relatorio = '{$titulo}', status_modelo_relatorio = '{$status}' WHERE id = '{$id}'";
        if ($connect->query($updateModelosRelatorio) === TRUE) {
            $_SESSION["alert_success"] = "Modelo de relatório atualizado com sucesso!";

            $camposNaoInseridos = [];

            $qr = mysqli_query($connect, "SELECT id, titulo_campo FROM campos_modelo_relatorio WHERE id_modelo_relatorio  = '{$id}'");
            $campos = mysqli_fetch_all($qr, MYSQLI_ASSOC);

            $contagem = 0;
            while ($contagem < count($campos)) {

                if (isset($_POST['tituloCampo'][$contagem])) {
                    $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$contagem]));
                    if ($tituloCampo == $campos[$contagem]["titulo_campo"]) {
                        $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_modelo_relatorio", $campos[$contagem]["id"], "AND id_modelo_relatorio = '{$id}'"));
                        $tipo = mysqli_real_escape_string($connect, trim($_POST['tipo'][$contagem]));
                        $obrigatorio = 0;
                        if (isset($_POST['obrigatorio'][$contagem]) && $_POST['obrigatorio'][$contagem] == "on")
                            $obrigatorio = 1;
                        $opcoes = "";
                        if (isset($_POST['opcoes'][$contagem]))
                            $opcoes = mysqli_real_escape_string($connect, str_replace(['[', ']', '{"value":"', '"}'], "", $_POST["opcoes"][$contagem]));
                        $multiplo = 0;
                        if (isset($_POST['multiplo'][$contagem]) && $_POST['multiplo'][$contagem] == "on")
                            $multiplo = 1;
                        $tipoArquivo = "";
                        if (isset($_POST['tipoArquivo'][$contagem]))
                            $tipoArquivo = mysqli_real_escape_string($connect, trim($_POST['tipoArquivo'][$contagem]));

                        $sql = "UPDATE campos_modelo_relatorio SET titulo_campo = '{$tituloCampo}', slug = '{$slug}', tipo = '{$tipo}', obrigatorio = '{$obrigatorio}', opcoes = '{$opcoes}', multiplo = '{$multiplo}', tipo_arquivo = '{$tipoArquivo}' WHERE id = '{$campos[$contagem]["id"]}'";
                        if ($connect->query($sql) !== TRUE)
                            $camposNaoInseridos[] = $tituloCampo;
                    } else {
                        $connect->query("DELETE FROM campos_modelo_relatorio WHERE id = '{$campos[$contagem]["id"]}'");
                        array_splice($campos, $contagem, 1);
                        continue;
                    }
                } else
                    $connect->query("DELETE FROM campos_modelo_relatorio WHERE id = '{$campos[$contagem]["id"]}'");

                $contagem++;
            }

            if (count($campos) != count($_POST['tituloCampo'])) {
                while ($contagem < count($_POST['tituloCampo'])) {

                    $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$contagem]));
                    $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_modelo_relatorio", 0, "AND id_modelo_relatorio = '{$id}'"));
                    $tipo = mysqli_real_escape_string($connect, trim($_POST['tipo'][$contagem]));
                    $obrigatorio = 0;
                    if (isset($_POST['obrigatorio'][$contagem]) && $_POST['obrigatorio'][$contagem] == "on")
                        $obrigatorio = 1;
                    $opcoes = "";
                    if (isset($_POST['opcoes'][$contagem]))
                        $opcoes = mysqli_real_escape_string($connect, str_replace(['[', ']', '{"value":"', '"}'], "", $_POST["opcoes"][$contagem]));
                    $multiplo = 0;
                    if (isset($_POST['multiplo'][$contagem]) && $_POST['multiplo'][$contagem] == "on")
                        $multiplo = 1;
                    $tipoArquivo = "";
                    if (isset($_POST['tipoArquivo'][$contagem]))
                        $tipoArquivo = mysqli_real_escape_string($connect, trim($_POST['tipoArquivo'][$contagem]));

                    $insertCampoModeloRelatorio = "INSERT INTO campos_modelo_relatorio (id_modelo_relatorio, titulo_campo, slug, tipo, obrigatorio, opcoes, multiplo, tipo_arquivo) VALUES ('{$id}', '{$tituloCampo}', '{$slug}', '{$tipo}', '{$obrigatorio}', '{$opcoes}', '{$multiplo}', '{$tipoArquivo}')";
                    if ($connect->query($insertCampoModeloRelatorio) !== TRUE)
                        $camposNaoInseridos[] = $tituloCampo;

                    $contagem++;
                }
            }

            $qtCamposNaoInseridos = count($camposNaoInseridos);
            if ($qtCamposNaoInseridos > 0) {
                $s = "";
                if ($qtCamposNaoInseridos > 1)
                    $s = "s";
                $_SESSION["alert_danger"] = "Erro ao cadastrar/atualizar no modelo <span class='fw-bold'>{$titulo}<span> o$s seguinte$s campo$s: " . implode(", ", $camposNaoInseridos);
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao atualizar modelo de relatório";

        $connect->close();

        header("Location: .{$link}p=modelos-relatorio&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {

        $sql = "DELETE FROM modelos_relatorio WHERE id = '{$id}'";

        if ($connect->query($sql) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de modelo de relatório efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir modelo de relatório";

        $connect->close();

        header("Location: .{$link}p=modelos-relatorio");
        exit();
    }
}
