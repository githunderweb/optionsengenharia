<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Te peguei</title>

    <link rel="icon" href="./assets/img/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./assets/css/main-beta.css">
</head>

<body>

    <div class="position-absolute top-0 start-0 end-0 d-flex flex-column align-items-center pt-3">
        <?php include('./utils/alertas.php'); ?>
    </div>

    <div class="position-relative small p-0">
        <form id="register" action="../usuarios-app/usuario-app-acao.php" method="POST" class="px-3 py-4 d-flex flex-column">
            <div class="row">
                <div class="col text-center">
                    <img class="no-events mb-5" width="180" src="./assets/img/logomarca.png">
                    <h6 class="m-0 text-secondary fw-semibold">Olá, vamos começar!</h6>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col">
                    <div class="form-floating">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtNome" name="nome" placeholder="Nome Completo" maxlength="250" autocomplete="off">
                        <label for="txtNome" class="text-uppercase text-secondary ps-4 pe-3">Nome Completo</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="email" class="form-control rounded-5 ps-4 pe-3" name="email" id="txtEmail" placeholder="E-mail" maxlength="250" autocomplete="off">
                        <label for="txtEmail" class="text-uppercase text-secondary ps-4 pe-3">E-mail</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="password" class="form-control rounded-5 ps-4 pe-3" name="senha" id="txtSenha" placeholder="Senha" maxlength="250" autocomplete="off">
                        <label for="txtSenha" class="text-uppercase text-secondary ps-4 pe-3">Senha</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control cep rounded-5 ps-4 pe-3" id="txtCEP" name="cep" placeholder="CEP" maxlength="9" autocomplete="off">
                        <label for="txtCEP" class="text-uppercase text-secondary ps-4 pe-3">CEP</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtCidade" name="cidade" placeholder="Cidade" maxlength="250" autocomplete="off">
                        <label for="txtCidade" class="text-uppercase text-secondary ps-4 pe-3">Cidade</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtEstado" name="estado" placeholder="Estado" maxlength="2" autocomplete="off">
                        <label for="txtEstado" class="text-uppercase text-secondary ps-4 pe-3">Estado</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtEndereco" name="endereco" placeholder="Endereço" maxlength="250" autocomplete="off">
                        <label for="txtEndereco" class="text-uppercase text-secondary ps-4 pe-3">Endereço</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtBairro" name="bairro" placeholder="Bairro" maxlength="250" autocomplete="off">
                        <label for="txtBairro" class="text-uppercase text-secondary ps-4 pe-3">Bairro</label>
                    </div>
                    <div class="form-floating mt-3">
                        <input type="text" class="form-control rounded-5 ps-4 pe-3" id="txtNumero" name="numero" placeholder="Número" maxlength="11" autocomplete="off">
                        <label for="txtNumero" class="text-uppercase text-secondary ps-4 pe-3">Número</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3 py-3 text-uppercase w-100 rounded-5">Cadastrar</button>
                </div>
            </div>
            <div class="row mt-4 pb-2">
                <div class="col">
                    <p class="text-center text-secondary m-0">Ao continuar você declara estar ciente da nossa <a href="#" class="link-secondary">Política de privacidade</a>.</p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-center">
                    <a href="./login.php" class="btn btn-secondary opacity-50 rounded-circle p-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-chevron-left lh-1 fs-2"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="../assets/js/main.js?v=0.0.0.1"></script>
    <script src="./assets/js/historico.js?v=0.0.0.1"></script>
</body>

</html>