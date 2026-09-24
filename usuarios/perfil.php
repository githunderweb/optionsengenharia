<?php

$id = $sessaoUsuario['id'];

$qr = mysqli_query($connect, "SELECT u.img, u.nome, u.email, u.funcao, (SELECT GROUP_CONCAT(e.nome_empresa ORDER BY e.nome_empresa SEPARATOR ', ') FROM usuario_empresas ue INNER JOIN empresas e ON e.id = ue.id_empresa WHERE ue.id_usuario = u.id) AS nome_empresa, (SELECT GROUP_CONCAT(te.titulo_tipo_equipamento ORDER BY te.titulo_tipo_equipamento SEPARATOR ', ') FROM usuario_tipos_equipamento ute INNER JOIN tipos_equipamento te ON te.id = ute.id_tipo_equipamento WHERE ute.id_usuario = u.id) AS tipos_equipamento FROM usuarios u WHERE u.id = '{$id}'");
$dado = mysqli_fetch_array($qr);

$imgUser = "team/avatar.png";
if ($dado['img'] != NULL)
    $imgUser = "usuarios/{$dado['img']}";

?>
<!-- <div class="row">
    <div class="col-12">
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative min-vh-25 mb-8">
                <div class="cover-image">
                    <div class="bg-holder rounded-3 rounded-bottom-0" style="background-color: black;">
                    </div>

                </div>
                <div class="avatar avatar-5xl avatar-profile shadow-sm img-thumbnail rounded-circle">
                    <div class="h-100 w-100 rounded-circle overflow-hidden position-relative"> <img src="./assets/img/<?= $imgUser ?>" width="200" alt="" data-dz-thumbnail="data-dz-thumbnail" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
<div class="row">


    <div class="col-lg-12 pe-lg-2">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Meu Perfil</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-auto d-flex justify-content-center">
                        <div class="avatar avatar-5xl shadow-sm img-thumbnail rounded-circle">
                            <div class="h-100 w-100 rounded-circle overflow-hidden position-relative"> <img src="./assets/img/<?= $imgUser ?>" width="200" alt="" data-dz-thumbnail="data-dz-thumbnail" />
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label" for="name">Nome</label>
                                <input class="form-control" id="name" type="text" value="<?= $dado['nome'] ?>" readonly />
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" type="text" value="<?= $dado['email'] ?>" readonly />
                            </div>
                            <?php
                            if ($dado['funcao'] == "Cliente") {
                            ?>
                                <div class="col-lg-6">
                                    <label class="form-label" for="empresa">Empresas</label>
                                    <input class="form-control" id="empresa" type="text" value="<?= $dado['nome_empresa'] ?>" readonly />
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label" for="tiposEquipamento">Tipos de equipamento</label>
                                    <input class="form-control" id="tiposEquipamento" type="text" value="<?= $dado['tipos_equipamento'] ?>" readonly />
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
