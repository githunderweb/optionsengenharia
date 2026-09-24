<?php
include("./config.php");
include("./verifica-login.php");
include("./functions.php");

$link = "./index.php?";
$linkHome = "./";

$empresaAtual = @$_GET["empresa"];
if ($empresaAtual != "" && permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) {
    $link .= "empresa={$empresaAtual}&";
    $linkHome .= substr($link, 11, -1);
} else if ($sessaoUsuario["funcao"] == "Cliente") {
    $idsEmpresasCliente = idsEmpresasPermitidasUsuario($connect, $sessaoUsuario["id"]);
    $empresaSolicitada = $_GET["empresa"] ?? null;

    if ($empresaSolicitada === "todas")
        $empresaAtual = 0;
    elseif (intval($empresaSolicitada) > 0 && in_array(intval($empresaSolicitada), $idsEmpresasCliente, true))
        $empresaAtual = intval($empresaSolicitada);
    elseif (in_array(intval($sessaoUsuario["id_empresa"]), $idsEmpresasCliente, true))
        $empresaAtual = intval($sessaoUsuario["id_empresa"]);
    else
        $empresaAtual = 0;

    $empresaLinkCliente = $empresaAtual > 0 ? $empresaAtual : "todas";
    $link .= "empresa={$empresaLinkCliente}&";
    $linkHome .= substr($link, 11, -1);
}

$_SESSION["empresa_atual_link"] = $link;

$version = "?v=0.0.61";
?>
<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Options Engenharia | Sistema</title>

    <link rel="icon" href="./assets/img/favicons/favicon.png">

    <link rel="manifest" href="./assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="./assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="./assets/js/config.js"></script>
    <script src="./vendors/overlayscrollbars/OverlayScrollbars.min.js"></script>

    <link href="./vendors/flatpickr/flatpickr.min.css" rel="stylesheet">
    <link href="./vendors/dropzone/dropzone.min.css" rel="stylesheet">
    <link href="./vendors/prism/prism-okaidia.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="./vendors/overlayscrollbars/OverlayScrollbars.min.css" rel="stylesheet">
    <link href="./assets/css/user.min.css" rel="stylesheet" id="user-style-default">

    <link href="./vendors/choices/choices.min.css" rel="stylesheet" />
    <link href="./vendors/dataTables/datatables.min.css" rel="stylesheet" />
    <link href="./vendors/tagify-master/dist/tagify.css" rel="stylesheet" />
    <link href="./vendors/select2/select2.min.css" rel="stylesheet">
    <link href="./vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="./assets/css/theme.min.css" rel="stylesheet" id="style-default">
    <link href="./assets/css/main.css<?= $version; ?>" rel="stylesheet" />
</head>


<body>

    <main class="main" id="top">
        <div class="container-fluid" data-layout="container">
            <nav class="navbar navbar-light navbar-vertical navbar-expand-xl navbar-card">
                <div class="d-flex align-items-center">
                    <div class="toggle-icon-wrapper">

                        <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Alternar Navegação">
                            <span class="navbar-toggle-icon">
                                <span class="toggle-line"></span>
                            </span>
                        </button>

                    </div>
                    <a class="navbar-brand mr-0" href="<?= $linkHome ?>">
                        <div class="d-flex align-items-center" style="padding: 0.5rem 0 0.55rem 0;">
                            <img src="./assets/img/logomarca.png" width="70">
                            <!-- <img src="../sistemas/img/an7_digital_logo.png" width="70"> -->
                        </div>
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
                    <div class="navbar-vertical-content scrollbar">
                        <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                            <li class="nav-item">
                                <!-- <a href="<?= $linkHome ?>" class="nav-link dashboard">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <span class="fas fa-chart-pie"></span>
                                        </span>
                                        <span class="nav-link-text ps-1">Dashboard</span>
                                    </div>
                                </a> -->

                                <a href="<?= $linkHome ?>" class="nav-link home">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <span class="fas fa-home"></span>
                                        </span>
                                        <span class="nav-link-text ps-1">Home</span>
                                    </div>
                                </a>
                            </li>
                            <?php
                            if (permissaoUsuario(["Administrador"], $sessaoUsuario["funcao"])) { ?>
                                <li class="nav-item">
                                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                        <div class="col-auto navbar-vertical-label">Interface</div>
                                        <div class="col ps-0">
                                            <hr class="mb-0 navbar-vertical-divider">
                                        </div>
                                    </div>


                                    <!-- <a class="nav-link ged" href="<?= $link ?>p=ged">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <span class="fas fa-network-wired"></span>
                                            </span>
                                            <span class="nav-link-text ps-1">GED</span>
                                        </div>
                                    </a> -->

                                    <?php
                                    if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                        <a class="nav-link inspecoes" href="<?= $link ?>p=inspecoes">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-clipboard-list"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Inspeções</span>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php
                                    if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                        <a class="nav-link controle-equipamentos" href="<?= $link ?>p=controle-equipamentos">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-table"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Controle de equipamentos</span>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </li>

                            <?php }

                            if (permissaoUsuario("Todos", $sessaoUsuario["funcao"])) { ?>
                                <li class="nav-item">
                                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                        <div class="col-auto navbar-vertical-label">Cadastros</div>
                                        <div class="col ps-0">
                                            <hr class="mb-0 navbar-vertical-divider">
                                        </div>
                                    </div>

                                    <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                        <a class="nav-link dropdown-indicator" href="#empresas" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="empresas">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <i class="fas fa-building"></i>
                                                </span>
                                                <span class="nav-link-text ps-1">Empresas</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="empresas">
                                            <li class="nav-item">
                                                <a class="nav-link empresas" href="<?= $link ?>p=empresas">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Listar</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link empresa" href="<?= $link ?>p=empresa">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Adicionar</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link unidade unidades" href="<?= $link ?>p=unidades">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Unidades</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    <?php } ?>

                                    <!-- <a class="nav-link dropdown-indicator" href="#unidades" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="unidades">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <i class="fas fa-city"></i>
                                            </span>
                                            <span class="nav-link-text ps-1">Unidades</span>
                                        </div>
                                    </a>
                                    <ul class="nav collapse" id="unidades">
                                        <li class="nav-item">
                                            <a class="nav-link unidades" href="<?= $link ?>p=unidades">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Listar</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link unidade" href="<?= $link ?>p=unidade">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Adicionar</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul> -->

                                    <a class="nav-link dropdown-indicator" href="#equipamentos" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="equipamentos">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <i class="fas fa-tools"></i>
                                            </span>
                                            <span class="nav-link-text ps-1">Equipamentos</span>
                                        </div>
                                    </a>
                                    <ul class="nav collapse" id="equipamentos">
                                        <li class="nav-item">
                                            <a class="nav-link equipamentos" href="<?= $link ?>p=equipamentos">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Listar</span>
                                                </div>
                                            </a>
                                        </li>

                                        <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                            <li class="nav-item">
                                                <a class="nav-link equipamento" href="<?= $link ?>p=equipamento">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Adicionar</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link tipos-equipamento tipo-equipamento" href="<?= $link ?>p=tipos-equipamento">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Tipos de equipamento</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link categoria-equipamento categorias-equipamento" href="<?= $link ?>p=categorias-equipamento">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Categorias de equipamento</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link local-instalacao locais-instalacao" href="<?= $link ?>p=locais-instalacao">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Locais de instalação</span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>

                                <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                    <a class="nav-link dropdown-indicator" href="#modelos-relatorio" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="modelos-relatorio">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <span class="fas fa-file-alt"></span>
                                            </span>
                                            <span class="nav-link-text ps-1">Modelos de relatório</span>
                                        </div>
                                    </a>
                                    <ul class="nav collapse" id="modelos-relatorio">
                                        <li class="nav-item">
                                            <a class="nav-link modelos-relatorio" href="<?= $link ?>p=modelos-relatorio">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Listar</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link modelo-relatorio" href="<?= $link ?>p=modelo-relatorio">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Adicionar</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>

                                    <a class="nav-link dropdown-indicator" href="#inspetores" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="inspetores">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <span class="fas fa-address-card"></span>
                                            </span>
                                            <span class="nav-link-text ps-1">Inspetores</span>
                                        </div>
                                    </a>
                                    <ul class="nav collapse" id="inspetores">
                                        <li class="nav-item">
                                            <a class="nav-link inspetores" href="<?= $link ?>p=inspetores">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Listar</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link inspetor" href="<?= $link ?>p=inspetor">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Adicionar</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>

                                    <a class="nav-link dropdown-indicator" href="#ordens-servico" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="ordens-servico">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <span class="fas fa-clipboard"></span>
                                            </span>
                                            <span class="nav-link-text ps-1">Ordens de serviço</span>
                                        </div>
                                    </a>
                                    <ul class="nav collapse" id="ordens-servico">
                                        <li class="nav-item">
                                            <a class="nav-link ordens-servico" href="<?= $link ?>p=ordens-servico">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Listar</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link ordem-servico" href="<?= $link ?>p=ordem-servico">
                                                <div class="d-flex align-items-center">
                                                    <span class="nav-link-text ps-1">Adicionar</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                 <?php } ?>

                                </li>

                            <?php }
                            if (permissaoUsuario("Todos", $sessaoUsuario["funcao"])) { ?>
                                <li class="nav-item">
                                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                        <div class="col-auto navbar-vertical-label">Ajustes</div>
                                        <div class="col ps-0">
                                            <hr class="mb-0 navbar-vertical-divider" />
                                        </div>
                                    </div>
                                    <!-- <a class="nav-link" href="<?= $link ?>p=configuracoes">
                                    <div class="d-flex align-items-center">
                                        <span class="nav-link-icon">
                                            <span class="fa fa-cogs"></span>
                                        </span>
                                        <span class="nav-link-text ps-1">Configurações</span>
                                    </div>
                                </a> -->
                                    <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                                        <a class="nav-link dropdown-indicator" href="#authentication" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="authentication">
                                            <div class="d-flex align-items-center">
                                                <span class="nav-link-icon">
                                                    <span class="fas fa-users"></span>
                                                </span>
                                                <span class="nav-link-text ps-1">Usuários</span>
                                            </div>
                                        </a>
                                        <ul class="nav collapse" id="authentication">
                                            <li class="nav-item">
                                                <a class="nav-link usuarios" href="<?= $link ?>p=usuarios">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Listar</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link usuario" href="<?= $link ?>p=usuario">
                                                    <div class="d-flex align-items-center">
                                                        <span class="nav-link-text ps-1">Adicionar</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    <?php } ?>
                                    
                                    <!-- <a class="nav-link perfil" href="<?= $link ?>p=perfil">
                                        <div class="d-flex align-items-center">
                                            <span class="nav-link-icon">
                                                <span class="fas fa-user-circle"></span>
                                            </span>
                                            <span class="nav-link-text ps-1">Meu Perfil</span>
                                        </div>
                                    </a> -->
                                </li>
                            <?php }
                            ?>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="content">
                <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">

                    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
                    <a class="navbar-brand me-1 me-sm-3" href="../index.html">
                        <div class="d-flex align-items-center">
                            <img src="./assets/img/logomarca.png" width="70">
                            <!-- <img src="../sistemas/img/an7_digital_logo.png" width="70"> -->
                        </div>
                    </a>

                    <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                        <div class="empresa-atual" style="min-width: 195px;width: 30%; border-radius: 50rem;">
                            <select class="form-select js-choice" data-options='{"removeItemButton":true,"placeholder":true, "noResultsText": "Nenhum resultado encontrado"}'>
                                <?php
                                $optionsEmpresas =
                                    "<option value=''>Todas empresas</option>";
                                $qrEmpresas = mysqli_query($connect, 'SELECT * FROM empresas');
                                while ($dadoEmpresas = mysqli_fetch_assoc($qrEmpresas)) {
                                    $optionsEmpresas .= "<option value='" . $dadoEmpresas['id'] . "'>" . $dadoEmpresas['nome_empresa'] . '</option>';
                                }
                                $optionsEmpresas = str_replace("value='" . $empresaAtual . "'", "value='" . $empresaAtual . "' selected", $optionsEmpresas);
                                echo $optionsEmpresas;
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php if (permissaoUsuario("Cliente", $sessaoUsuario["funcao"])) {
                        $idUsuarioAtual = intval($sessaoUsuario["id"]);
                        $empresasCliente = [];
                        $maiorNomeEmpresa = mb_strlen("TODAS", "UTF-8");
                        $qrEmpresasAtuais = mysqli_query($connect, "SELECT e.id, e.nome_empresa FROM usuario_empresas ue INNER JOIN empresas e ON e.id = ue.id_empresa WHERE ue.id_usuario = '{$idUsuarioAtual}' ORDER BY e.nome_empresa");
                        while ($dadoEmpresaAtual = mysqli_fetch_assoc($qrEmpresasAtuais)) {
                            $empresasCliente[] = $dadoEmpresaAtual;
                            $maiorNomeEmpresa = max($maiorNomeEmpresa, mb_strlen($dadoEmpresaAtual["nome_empresa"], "UTF-8"));
                        }
                        $larguraSeletorEmpresa = min(700, max(320, ($maiorNomeEmpresa * 9) + 70));
                    ?>
                        <div class="empresa-cliente-atual me-2" style="--largura-seletor-empresa: <?= $larguraSeletorEmpresa; ?>px; border-radius: 50rem;">
                            <select class="form-select js-choice" aria-label="Empresa atual">
                                <?php
                                echo "<option value='todas'" . (intval($empresaAtual) === 0 ? " selected" : "") . ">TODAS</option>";
                                foreach ($empresasCliente as $dadoEmpresaAtual) {
                                    $selected = intval($dadoEmpresaAtual["id"]) === intval($empresaAtual) ? " selected" : "";
                                    echo "<option value='{$dadoEmpresaAtual["id"]}'{$selected}>" . htmlspecialchars($dadoEmpresaAtual["nome_empresa"], ENT_QUOTES, "UTF-8") . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                    <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
                        <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="avatar avatar-xl">
                                    <?php
                                    $imgPerfil = 'team/avatar.png';
                                    if ($sessaoUsuario['img'] != null) {
                                        $imgPerfil = "usuarios/{$sessaoUsuario['img']}";
                                    }

                                    echo "<img class='rounded-circle bg-white' src='./assets/img/{$imgPerfil}'>";
                                    ?>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
                                <div class="bg-white dark__bg-1000 rounded-2 py-2">
                                    <a class="dropdown-item" href="<?= $link ?>p=perfil">Perfil</a>
                                    <!-- <a class="dropdown-item" href="#">Histórico</a> -->
                                    <div class="dropdown-divider"></div>
                                    <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) {
                                        echo "<a class='dropdown-item' href='{$link}p=configuracoes'>Configurações</a>";
                                    } ?>
                                    <a class="dropdown-item" href="./logout.php">Sair</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>

                <?php
                include "./alertas.php";

                $pagina = @$_GET["p"];
                $acao = @$_GET["acao"];
                $paginas = [
                    // "dashboard" => ["./dashboard.php", "Todos"],
                    "home" => ["./dashboard.php", "Todos"],
                    "ged" => ["./ged/ged.php", "Todos"],
                    "inspecoes" => ["./inspecoes/inspecoes.php", "Todos"],
                    "controle-equipamentos" => ["./equipamentos/controle-equipamentos.php", "Administrador"],
                    "empresas" => ["./empresas/empresas.php", "Administrador"],
                    "empresa" => ["./empresas/empresa.php", "Administrador"],
                    "unidades" => ["./unidades/unidades.php", "Administrador"],
                    "unidade" => ["./unidades/unidade.php", "Administrador"],
                    "equipamentos" => ["./equipamentos/equipamentos.php", "Todos"],
                    "equipamento" => ["./equipamentos/equipamento.php", "Administrador"],
                    "tipos-equipamento" => ["./tipos-equipamento/tipos-equipamento.php", "Administrador"],
                    "tipo-equipamento" => ["./tipos-equipamento/tipo-equipamento.php", "Administrador"],
                    "categorias-equipamento" => ["./categorias-equipamento/categorias-equipamento.php", "Administrador"],
                    "categoria-equipamento" => ["./categorias-equipamento/categoria-equipamento.php", "Administrador"],
                    "locais-instalacao" => ["./locais-instalacao/locais-instalacao.php", "Administrador"],
                    "local-instalacao" => ["./locais-instalacao/local-instalacao.php", "Administrador"],
                    "modelos-relatorio" => ["./modelos-relatorio/modelos-relatorio.php", "Administrador"],
                    "modelo-relatorio" => ["./modelos-relatorio/modelo-relatorio.php", "Administrador"],
                    "inspetores" => ["./inspetores/inspetores.php", "Administrador"],
                    "inspetor" => ["./inspetores/inspetor.php", "Administrador"],
                    "ordens-servico" => ["./ordens-servico/ordens-servico.php", "Administrador"],
                    "ordem-servico" => ["./ordens-servico/ordem-servico.php", "Administrador"],
                    "usuarios" => ["./usuarios/usuarios.php", "Administrador"],
                    "usuario" => ["./usuarios/usuario.php", "Administrador"],
                    "perfil" => ["./usuarios/perfil.php", "Todos"],
                ];

                if ($pagina == "")
                    include "./dashboard.php";
                elseif (isset($paginas[$pagina][0]) && permissaoUsuario($paginas[$pagina][1], $sessaoUsuario["funcao"]))
                    include $paginas[$pagina][0];
                else
                    include "./404.php";
                ?>

                <footer class="footer">
                    <div class="row g-0 justify-content-between fs--1 mt-4 mb-3">
                        <div class="col-12 col-sm-auto text-center">
                            <p class="mb-0 text-600">
                                Copyright ©
                                <?= date("Y"); ?>
                                <span class="d-none d-sm-inline-block">|</span>
                                <br class="d-sm-none">Desenvolvido por <a href="https://sistema.optionsengenharia.com.br" target="_blank">Options Engenharia</a>
                            </p>
                        </div>
                        <div class="col-12 col-sm-auto text-center">
                            <p class="mb-0 text-600">v α 0.0.1</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </main>


    <script src="./vendors/popper/popper.min.js"></script>
    <script src="./vendors/bootstrap/bootstrap.min.js"></script>
    <script src="./vendors/anchorjs/anchor.min.js"></script>
    <script src="./vendors/is/is.min.js"></script>
    <script src="./vendors/tinymce/tinymce.min.js"></script>
    <script src="./vendors/choices/choices.min.js"></script>
    <script src="./assets/js/flatpickr.js"></script>
    <script src="./vendors/dropzone/dropzone.min.js"></script>
    <script src="./vendors/lottie/lottie.min.js"></script>
    <script src="./vendors/validator/validator.min.js"></script>
    <script src="./vendors/prism/prism.js"></script>
    <script src="./vendors/fontawesome/all.min.js"></script>
    <script src="./vendors/lodash/lodash.min.js"></script>
    <!--<script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>-->
    <script src="./vendors/list.js/list.min.js"></script>
    <script src="./assets/js/theme.js"></script>
    <script src="./vendors/jquery/jquery.min.js"></script>
    <script src="./vendors/dataTables/datatables.min.js?v=0.0.03"></script>
    <script src="./assets/js/dataTable.js?v=0.0.01"></script>
    <script src="./vendors/select2/select2.min.js"> </script>
    <script src="./vendors/select2/select2.full.min.js"> </script>
    <script src="./vendors/masks/jquery.mask.min.js"></script>
    <script src="./vendors/tagify-master/dist/jQuery.tagify.min.js"></script>

    <script src="./assets/js/main.js<?= $version; ?>"></script>

    <script src="./vendors/echarts/echarts.min.js"></script>

</body>

</html>
