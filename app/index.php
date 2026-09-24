<?php
include('../config.php');
include('./verifica-login.php');

$version = "?v=0.0.4";
$pagina = @$_GET["p"];
$id = @$_GET["id"];
?>
<!DOCTYPE html>
<html lang="pt-br">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Engenharia | Inspeções</title>

    <link rel="icon" href="./assets/img/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link href="./vendors/select2/select2.min.css" rel="stylesheet">
    <link href="./vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/main.css<?= $version; ?>">
</head>

<body>

    <div id="content" class="position-relative">

        <header class="sticky-top bg-padrao py-2 px-3 d-flex align-items-center justify-content-between">
            <div>
                <?php
                if ($pagina != '' && $pagina != 'home') {
                    echo '
                        <button class="btn text-white p-0 fs-4 border-0" type="button" onclick="window.history.back()">
                            <i class="bi bi-chevron-left d-flex"></i>
                        </button>
                    ';
                }
                ?>
            </div>

            <button class="btn text-white p-0 fs-2 lh-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideBar" aria-controls="sideBar">
                <i class="bi bi-list"></i>
            </button>
        </header>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="sideBar" aria-labelledby="sideBarLabel">
            <div class="offcanvas-header py-2 px-4">
                <h5 class="offcanvas-title" id="sideBarLabel">&nbsp;</h5>
                <button type="button" class="btn-close p-1" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <nav class="px-3 d-flex flex-column text-center">
                    <a href="#" class="text-secondary text-decoration-none px-1 py-4 border-secondary border-bottom">Meus dados</a>
                    <a class="text-secondary text-decoration-none px-1 py-4 border-secondary border-bottom" href="./index.php?p=historico">Histórico</a>
                    <a href="#" class="text-secondary text-decoration-none px-1 py-4 border-secondary border-bottom">Termos de uso</a>
                    <a href="#" class="text-secondary text-decoration-none px-1 py-4 border-secondary border-bottom">Política de privacidade</a>
                    <a href="#" class="text-secondary text-decoration-none px-1 py-4 border-secondary border-bottom">Contate-nos</a>
                    <a href="./sigout.php" class="text-secondary text-decoration-none px-1 py-4">Sair</a>
                </nav>
            </div>
        </div>

        <main style="padding-bottom: 50px;">
            <?php
            $paginas = [
                "home" => "./views/home.php",
                "nova-inspecao" => "./views/nova-inspecao.php",
                "editar-inspecao" => "./views/editar-inspecao.php",
                "historico" => "./views/historico.php",
                "historico-ordem" => "./views/historico-ordem.php",
                "ordem-servico" => "./views/ordem-servico.php",
            ];

            if ($pagina == '')
                include('./views/home.php');
            else if (isset($paginas[$pagina]))
                include($paginas[$pagina]);

            ?>
        </main>

        <footer class="fixed-bottom px-4 bg-padrao">

            <div class="position-absolute bottom-0 start-0 end-0 mb-5 d-flex flex-column align-items-center justify-content-end">
                <?php include('./utils/alertas.php'); ?>
            </div>

            <nav class="bottom-bar d-flex justify-content-around">
                <!-- <a class="<?php if ($pagina == 'ordem-servico') echo 'active' ?> bg-padrao text-white text-decoration-none fs-3 border border-2 border-padrao rounded-4" <?php if ($pagina != 'ordem-servico') echo 'href="./index.php?p=ordem-servico"' ?>>
                    <i class="bi bi-clipboard-plus"></i>
                </a> -->
                <a class="<?php if ($pagina == 'home' || $pagina == '') echo 'active' ?> bg-padrao text-white text-decoration-none fs-3 border border-2 border-padrao rounded-4" <?php if ($pagina != 'home' && $pagina != '') echo 'href="./"' ?>>
                    <i class="bi fs-3 bi-house"></i>
                </a>
                <a class="<?php if ($pagina == 'historico') echo 'active' ?> bg-padrao text-white text-decoration-none fs-3 border border-2 border-padrao rounded-4" <?php if ($pagina != 'historico') echo 'href="./index.php?p=historico"' ?>>
                    <i class="bi bi-clock-history"></i>
                </a>
            </nav>
        </footer>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="./vendors/select2/select2.min.js"> </script>
    <script src="./vendors/select2/select2.full.min.js"> </script>
    <script src="./assets/js/main.js<?= $version; ?>"></script>
</body>

</html>