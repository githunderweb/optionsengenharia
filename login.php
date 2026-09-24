<?php
session_start();

if (isset($_SESSION['sessao_usuario']['email'])) {
    header('Location: ./');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Options Engenharia | Login</title>

    <!-- <link rel="apple-touch-icon" sizes="180x180" href="./assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="./assets/img/favicons/favicon.ico"> -->

    <link rel="icon" href="./assets/img/favicons/favicon.png">

    <link rel="manifest" href="./assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="./assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="./assets/js/config.js"></script>
    <script src="./vendors/overlayscrollbars/OverlayScrollbars.min.js"></script>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
    <link href="./vendors/overlayscrollbars/OverlayScrollbars.min.css" rel="stylesheet">
    <link href="./assets/css/theme-rtl.min.css" rel="stylesheet" id="style-rtl">
    <link href="./assets/css/theme.min.css" rel="stylesheet" id="style-default">
    <link href="./assets/css/user-rtl.min.css" rel="stylesheet" id="user-style-rtl">
    <link href="./assets/css/user.min.css" rel="stylesheet" id="user-style-default">

    <link href="./assets/css/main.css" rel="stylesheet" />
    <script>
        var isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            var linkDefault = document.getElementById('style-default');
            var userLinkDefault = document.getElementById('user-style-default');
            linkDefault.setAttribute('disabled', true);
            userLinkDefault.setAttribute('disabled', true);
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            var linkRTL = document.getElementById('style-rtl');
            var userLinkRTL = document.getElementById('user-style-rtl');
            linkRTL.setAttribute('disabled', true);
            userLinkRTL.setAttribute('disabled', true);
        }
    </script>
</head>

<body>

    <main class="main" id="top">
        <div class="container" data-layout="container">
            <div class="row flex-center min-vh-100 py-6">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                    <!-- <a class="d-flex flex-center mb-4" href="../../../index.html">
                        <img class="me-2" src="./assets/img/icons/spot-illustrations/falcon.png" alt="" width="58" />
                        <span class="font-sans-serif fw-bolder fs-5 d-inline-block">falcon</span>
                    </a> -->
                    <div class="text-center">
                        <img class="mb-4" src="./assets/img/logomarca.png" alt="Logo">
                        <!-- <img class="mb-4" src="../sistemas/img/an7_digital_logo.png" alt="Logo" width="200"> -->
                    </div>

                    <?php
                    if (isset($_SESSION['nao_autenticado'])) {
                        echo "<div class='alert text-white bg-danger text-center' role='alert'>{$_SESSION['nao_autenticado']}</div>";
                        unset($_SESSION['nao_autenticado']);
                    }
                    ?>

                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
                            <form action="./loging.php" method="POST">
                                <div class="mb-3">
                                    <input name="usuarioOuEmail" class="form-control" type="text" placeholder="Usuário ou e-mail" />
                                </div>
                                <!-- <div class="mb-3 position-relative">
                                    <input name="senha" class="form-control" type="password" placeholder="Senha" />
                                    <div class="btnTogglePassword position-absolute" style="top: 1.2px; right: 2px; padding: 15.6px;"><i class="fa fa-eye-slash" aria-hidden="true"></i></div>
                                </div> -->
                                <div class="mb-3 position-relative">
                                    <input name="senha" class="form-control" type="password" placeholder="Senha" style="padding-right: 44.1px;" />
                                    <button class="btn btn-outline-white link-padrao btnTogglePassword position-absolute" style="top: 0; right: 0; padding: 5px 10px; width: 42px;" type="button">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                                <button class="btn btn-padrao d-block w-100 mt-3" type="submit" name="submit">Entrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="./vendors/popper/popper.min.js"></script>
    <script src="./vendors/bootstrap/bootstrap.min.js"></script>
    <script src="./vendors/anchorjs/anchor.min.js"></script>
    <script src="./vendors/is/is.min.js"></script>
    <script src="./vendors/fontawesome/all.min.js"></script>
    <script src="./vendors/lodash/lodash.min.js"></script>
    <!--<script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>-->
    <script src="./vendors/list.js/list.min.js"></script>
    <script src="./assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="./assets/js/login/login.js"></script>

</body>

</html>