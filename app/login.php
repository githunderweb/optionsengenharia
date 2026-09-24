<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Engenharia | Inspeções</title>

    <link rel="icon" href="./assets/img/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/main.css">
</head>

<body>

    <div class="position-absolute top-0 start-0 end-0 d-flex flex-column align-items-center pt-3">
        <?php include('./utils/alertas.php'); ?>
    </div>

    <div class="position-absolute top-0 bottom-0 start-0 end-0 small p-0">

        <form id="sigin" class="h-100 px-3 py-4 d-flex flex-column justify-content-center needs-validation" action="./sigin.php" method="POST" novalidate>
            <div class="row">
                <div class="col text-center">
                    <img class="no-events mb-2" src="../assets/img/logomarca.png">
                    <h6 class="m-0 text-secondary fw-semibold">Iniciar sessão</h6>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col">
                    <div class="form-floating">
                        <input type="email" id="txtEmailUsuarioApp" class="form-control rounded-5 ps-4 pe-3" name="email" placeholder="E-mail" required maxlength="250" autocomplete="off">
                        <label for="txtEmailUsuarioApp" class="text-uppercase text-secondary ps-4 pe-3">E-mail</label>
                        <div class="invalid-tooltip rounded-4 p-2 px-3 lh-1">Insira um email válido</div>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="password" id="txtSenhaUsuarioApp" class="form-control rounded-5 ps-4 pe-3" name="senha" placeholder="Senha" required maxlength="250" autocomplete="off">
                        <label for="txtSenhaUsuarioApp" class="text-uppercase text-secondary ps-4 pe-3">Senha</label>
                    </div>
                    <button type="submit" class="btn btn-padrao mt-3 py-3 text-uppercase w-100 rounded-5">Entrar</button>
                </div>
            </div>

            <!-- <div class="row mt-4">
                <div class="col">
                    <p class="text-center text-secondary m-0">Não possui uma conta? <a class="btn-register link-secondary" href="./cadastro.php">Cadastre-se</a>.</p>
                </div>
            </div> -->
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="./assets/js/main.js"></script>
</body>

</html>