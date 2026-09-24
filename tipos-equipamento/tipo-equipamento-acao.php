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

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM tipos_equipamento WHERE titulo_tipo_equipamento = '{$titulo}'"))["total"] > 0) {
        $_SESSION["alert_danger"] = "Atenção! Já existe uma tipo de equipamento com o título informado";
        header("Location: .{$link}p=tipo-equipamento");
        exit();
    }

    $insertTipoEquipamento = "INSERT INTO tipos_equipamento (titulo_tipo_equipamento, data_cadastro) VALUES ('{$titulo}', NOW())";
    if ($connect->query($insertTipoEquipamento) === TRUE) {
        $_SESSION["alert_success"] = "Tipo de equipamento cadastrado com sucesso!";

        $idTipoEquipamento = $connect->insert_id;
        $camposNaoInseridos = [];

        for ($i = 0; $i < count($_POST['tituloCampo']); $i++) {

            $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$i]));
            $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_tipo_equipamento", 0, "AND id_tipo_equipamento = '{$idTipoEquipamento}'"));
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

            $insertCampoTipoEquipamento = "INSERT INTO campos_tipo_equipamento (id_tipo_equipamento, titulo_campo, slug, tipo, obrigatorio, opcoes, multiplo, tipo_arquivo) VALUES ('{$idTipoEquipamento}', '{$tituloCampo}', '{$slug}', '{$tipo}', '{$obrigatorio}', '{$opcoes}', '{$multiplo}', '{$tipoArquivo}')";
            if ($connect->query($insertCampoTipoEquipamento) !== TRUE)
                $camposNaoInseridos[] = $tituloCampo;
        }

        $qtCamposNaoInseridos = count($camposNaoInseridos);
        if ($qtCamposNaoInseridos > 0) {
            $s = "";
            if ($qtCamposNaoInseridos > 1)
                $s = "s";
            $_SESSION["alert_danger"] = "Erro ao cadastrar <span class='fw-bold'>{$titulo}<span> o$s seguinte$s campo$s: " . implode(", ", $camposNaoInseridos);
        }
    } else
        $_SESSION["alert_danger"] = "Erro ao cadastrar tipo de equipamento";

    $connect->close();

    header("Location: .{$link}p=tipos-equipamento");
    exit();
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {

        $titulo = mysqli_real_escape_string($connect, trim($_POST["titulo"]));

        if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS total FROM tipos_equipamento WHERE titulo_tipo_equipamento = '{$titulo}' AND id != '{$id}'"))["total"] > 0) {
            $_SESSION["alert_danger"] = "Atenção! Já existe uma tipo de equipamento com o título informado";
            header("Location: .{$link}p=tipos-equipamento&acao=editar&id={$id}");
            exit();
        }

        $updateTipoEquipamento = "UPDATE tipos_equipamento SET titulo_tipo_equipamento = '{$titulo}' WHERE id = '{$id}'";
        if ($connect->query($updateTipoEquipamento) === TRUE) {
            $_SESSION["alert_success"] = "Tipo de equipamento atualizado com sucesso!";

            $camposNaoInseridos = [];

            $qr = mysqli_query($connect, "SELECT id, titulo_campo FROM campos_tipo_equipamento WHERE id_tipo_equipamento  = '{$id}'");
            $campos = mysqli_fetch_all($qr, MYSQLI_ASSOC);

            $contagem = 0;
            while ($contagem < count($campos)) {

                if (isset($_POST['tituloCampo'][$contagem])) {
                    $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$contagem]));
                    if ($tituloCampo == $campos[$contagem]["titulo_campo"]) {
                        $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_tipo_equipamento", $campos[$contagem]["id"], "AND id_tipo_equipamento = '{$id}'"));
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

                        $sql = "UPDATE campos_tipo_equipamento SET titulo_campo = '{$tituloCampo}', slug = '{$slug}', tipo = '{$tipo}', obrigatorio = '{$obrigatorio}', opcoes = '{$opcoes}', multiplo = '{$multiplo}', tipo_arquivo = '{$tipoArquivo}' WHERE id = '{$campos[$contagem]["id"]}'";
                        if ($connect->query($sql) !== TRUE)
                            $camposNaoInseridos[] = $tituloCampo;
                    } else {
                        $connect->query("DELETE FROM campos_tipo_equipamento WHERE id = '{$campos[$contagem]["id"]}'");
                        array_splice($campos, $contagem, 1);
                        continue;
                    }
                } else
                    $connect->query("DELETE FROM campos_tipo_equipamento WHERE id = '{$campos[$contagem]["id"]}'");

                $contagem++;
            }

            if (count($campos) != count($_POST['tituloCampo'])) {
                while ($contagem < count($_POST['tituloCampo'])) {

                    $tituloCampo = mysqli_real_escape_string($connect, trim($_POST['tituloCampo'][$contagem]));
                    $slug = mysqli_real_escape_string($connect, slugfy($tituloCampo, "-", true, "campos_tipo_equipamento", 0, "AND id_tipo_equipamento = '{$id}'"));
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

                    $insertCampoTipoEquipamento = "INSERT INTO campos_tipo_equipamento (id_tipo_equipamento, titulo_campo, slug, tipo, obrigatorio, opcoes, multiplo, tipo_arquivo) VALUES ('{$id}', '{$tituloCampo}', '{$slug}', '{$tipo}', '{$obrigatorio}', '{$opcoes}', '{$multiplo}', '{$tipoArquivo}')";
                    if ($connect->query($insertCampoTipoEquipamento) !== TRUE)
                        $camposNaoInseridos[] = $tituloCampo;

                    $contagem++;
                }
            }

            $qtCamposNaoInseridos = count($camposNaoInseridos);
            if ($qtCamposNaoInseridos > 0) {
                $s = "";
                if ($qtCamposNaoInseridos > 1)
                    $s = "s";
                $_SESSION["alert_danger"] = "Erro ao cadastrar/atualizar <span class='fw-bold'>{$titulo}<span> o$s seguinte$s campo$s: " . implode(", ", $camposNaoInseridos);
            }
        } else
            $_SESSION["alert_danger"] = "Erro ao atualizar tipo de equipamento";

        $connect->close();

        header("Location: .{$link}p=tipos-equipamento&acao=editar&id={$id}");
        exit();
    } else if ($acao == "excluir") {
        $deleteTipoEquipamento = "DELETE FROM tipos_equipamento WHERE id = '{$id}'";
        if ($connect->query($deleteTipoEquipamento) === TRUE)
            $_SESSION["alert_success"] = "Exclusão de tipo de equipamento efetuada com sucesso!";
        else
            $_SESSION["alert_danger"] = "Erro ao excluir tipo de equipamento";

        $connect->close();

        header("Location: .{$link}p=tipos-equipamento");
        exit();
    }
}
