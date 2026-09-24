<div class="row mt-3">
    <div class="col h-100">
        <div class="d-flex mb-4"><span class="fa-stack me-2 ms-n1"><i class="fas fa-circle fa-stack-2x text-300"></i><i class="fa-inverse fa-stack-1x text-padrao fas fa-tasks"></i></span>
            <div class="col">
                <h5 class="mb-0 text-padrao position-relative">
                    <span class="bg-200 dark__bg-1100 pe-3">Adicionar usuário</span>
                    <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
                </h5>
                <p class="mb-0">Crie um usuário novo e o adicione a este sistema.</p>
            </div>
        </div>
        <div class="card theme-wizard h-100">
            <div class="card-header bg-light pt-3 pb-2">
                <ul class="nav nav-pills mb-3" role="tablist" id="pill-tab2">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-wizard-step="data-wizard-step" data-bs-toggle="pill" data-bs-target="#tabAcesso" type="button" role="tab" aria-controls="tabAcesso" aria-selected="true"><span class="fas fa-lock me-2" data-fa-transform="shrink-2"></span><span class="d-none d-md-inline-block fs--1">Acesso</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-wizard-step="data-wizard-step" data-bs-toggle="pill" data-bs-target="#tabPersonalisacao" type="button" role="tab" aria-controls="tabPersonalisacao" aria-selected="false"><span class="fas fa-user me-2" data-fa-transform="shrink-2"></span><span class="d-none d-md-inline-block fs--1">Personalização</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-wizard-step="data-wizard-step" data-bs-toggle="pill" data-bs-target="#tabConcluir" type="button" role="tab" aria-controls="tabConcluir" aria-selected="false"><span class="fas fa-thumbs-up me-2" data-fa-transform="shrink-2"></span><span class="d-none d-md-inline-block fs--1">Concluir</span></button>
                    </li>
                </ul>
            </div>
            <div class="progress" style="height: 2px;">
                <div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="card-body py-4">
                <!-- <form action="./usuarios/usuario-acao.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate="novalidate" autocomplete="off"> -->
                <form action="./usuarios/usuario-acao.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate="novalidate" autocomplete="off">
                    <div class="tab-content">
                        <div class="tab-pane active px-sm-3 px-md-5" role="tabpanel" aria-labelledby="tabAcesso" id="tabAcesso">
                            <div class="mb-3">
                                <label class="form-label" for="txtNome">Nome*</label>
                                <input class="form-control" type="text" name="nome" placeholder="Nome completo" required="required" id="txtNome" autocomplete="off" />
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="txtUsuario">Usuário*</label>
                                        <input class="form-control" type="text" name="usuario" placeholder="Nome de usuário" required="required" id="txtUsuario" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="txtEmail">Email*</label>
                                        <input class="form-control" type="email" name="email" placeholder="Endereço de e-mail" required="required" id="txtEmail" pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" data-wizard-validate-email="true" autocomplete="off" />
                                        <div class="invalid-feedback">Insira um email válido</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <label class="form-label" for="txtSenha">Senha*</label>
                                        <input class="form-control" type="password" name="senha" required="required" id="txtSenha" data-wizard-validate-password="true" autocomplete="off" style="padding-right: 44.1px;" />
                                        <button class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="bottom: 1px; right: 1px; padding: 4px 10px; width: 42px;" type="button">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="txtConfirmarSenha">Confirmar senha*</label>
                                    <div class="position-relative">
                                        <input class="form-control" type="password" required="required" id="txtConfirmarSenha" data-wizard-validate-confirm-password="true" style="padding-right: 44.1px;" />
                                        <button class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="top: 1px; right: 1px; padding: 4px 10px; width: 42px;" type="button">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="ifConfirmarSenha" class="invalid-feedback">As senhas não são iguais!</div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane px-sm-3 px-md-5" role="tabpanel" aria-labelledby="tabPersonalisacao" id="tabPersonalisacao">
                            <div class="mb-3">
                                <label class="form-label" for="flImagemPerfil">Imagem de perfil</label>
                                <input id="flImagemPerfil" class="form-control" type="file" name="img" accept="image/*" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="slcFuncao">Nível de acesso*</label>
                                <select class="form-select" name="funcao" id="slcFuncao" required="required">
                                    <option value="" disabled selected>Selecione...</option>
                                    <option value="Cliente">Cliente</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>
                            <!-- <div class="empresa-funcao">
                                <?php
                                $slcEmpresa = '<div class="collapse"><label class="form-label" for="slcEmpresa">Empresa*</label><select class="form-select js-choice" name="empresa" id="slcEmpresa" required="required" data-options=\'{"removeItemButton":true,"placeholder":true, "noResultsText": "Nenhum resultado encontrado"}\'>';

                                $optionsEmpresas = "<option value='' disabled selected>Selecione...</option>";
                                $qrEmpresas = mysqli_query($connect, "SELECT * FROM empresas");
                                while ($dadoEmpresas = mysqli_fetch_array($qrEmpresas)) {
                                    $optionsEmpresas .= "<option value='" . $dadoEmpresas['id'] . "'>" . $dadoEmpresas['nome_empresa'] . "</option>";
                                }
                                $optionsEmpresas = str_replace("value='" . $empresaAtual  . "'", "value='" . $empresaAtual  . "' selected", $optionsEmpresas);
                                $slcEmpresa .= $optionsEmpresas . '</select></div>';
                                ?>
                            </div> -->
                            <div class="empresa-funcao collapse mb-3">
                                <label class="form-label" for="slcEmpresa">Empresas*</label>
                                <select class="form-select selectpicker" id="slcEmpresa" name="empresas[]" multiple data-options='{"placeholder":"Selecione uma ou mais empresas...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                                </select>

                            </div>
                            <div class="tipo-equipamento-funcao collapse mb-3">
                                <label class="form-label" for="slcTiposEquipamento">Tipos de equipamento*</label>
                                <select class="form-select selectpicker" id="slcTiposEquipamento" name="tiposEquipamento[]" multiple data-options='{"placeholder":"Selecione um ou mais tipos...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                                    <?php
                                    $qrTiposEquipamentoUsuario = mysqli_query($connect, "SELECT id, titulo_tipo_equipamento FROM tipos_equipamento ORDER BY titulo_tipo_equipamento");
                                    while ($dadoTipoEquipamentoUsuario = mysqli_fetch_assoc($qrTiposEquipamentoUsuario))
                                        echo "<option value='{$dadoTipoEquipamentoUsuario["id"]}'>" . htmlspecialchars($dadoTipoEquipamentoUsuario["titulo_tipo_equipamento"], ENT_QUOTES, "UTF-8") . "</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="row g-3 g-md-2 mb-3">
                                <div class="col-12">
                                    <label class="form-label m-0" for="fleImgAssinatura">Assinatura</label>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="d-flex h-100 align-items-center justify-content-center" style="max-height: 160px;">
                                        <img id="imgAssinatura" class="img-fluid rounded-1" src="./assets/img/imagem-padrao.jpg" style="max-height: 100%;" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg">
                                    <div class="file-arrasta-solta h-100">
                                        <input type="file" id="fleImgAssinatura" class="form-control" name="imgAssinatura" accept="image/*" />
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="txtDescricaoAssinatura">Descrição assinatura</label>
                                    <textarea id="txtDescricaoAssinatura" class="form-control" name="descricaoAssinatura" rows="5" autocomplete="off" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane text-center px-sm-3 px-md-5 pb-4" role="tabpanel" aria-labelledby="tabConcluir" id="tabConcluir">
                            <div class="wizard-lottie-wrapper">
                                <div class="lottie wizard-lottie mx-auto my-3" data-options='{"path":"./assets/img/animated-icons/celebration.json"}'></div>
                            </div>
                            <h4 class="mb-1">Tudo pronto!</h4>
                            <p>Confirmar cadastro de usuário?</p>

                            <button class="btn btn-padrao px-5 my-3" type="submit">Concluir</button>


                            <!-- <div class="bg-light position-absolute end-0 bottom-0 start-0 p-3 rounded-3">
                                <div class="px-sm-3 px-md-5 text-start">
                                    <button id="btnVoltarConcluir" class="btn btn-link ps-1" type="button"><span class="fas fa-chevron-left me-2" data-fa-transform="shrink-3"></span>Voltar</button>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light">
                <div class="px-sm-3 px-md-5">
                    <ul class="pager wizard list-inline mb-0">
                        <li class="previous">
                            <button id="btnVoltar" class="btn btn-link ps-0" type="button"><span class="fas fa-chevron-left me-2" data-fa-transform="shrink-3"></span>Voltar</button>
                        </li>
                        <li class="next">
                            <button id="btnProximo" class="btn btn-padrao px-5 px-sm-6" type="submit">Próximo<span class="fas fa-chevron-right ms-2" data-fa-transform="shrink-3"> </span></button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var slcEmpresaAtual = `<?= $slcEmpresa ?>`;
</script>

<script src="./assets/js/usuarios/usuario.js<?= $version; ?>"></script>
