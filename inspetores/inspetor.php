<div class="card">
    <form action="./inspetores/inspetor-acao.php" method='POST' class='needs-validation' novalidate='novalidate' enctype='multipart/form-data'>

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Novo inspetor</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="txtNome">Nome*</label>
                    <input type="text" id="txtNome" class="form-control" name="nome" required="required" autocomplete="off" maxlength="250">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="txtEmail">Email*</label>
                    <input type="email" id="txtEmail" class="form-control" name="email" required="required" autocomplete="off" pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" maxlength="250" />
                    <div class="invalid-feedback">Insira um email válido</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="position-relative">
                        <label class="form-label" for="txtSenha">Senha*</label>
                        <input type="password" id="txtSenha" class="form-control" name="senha" required="required" autocomplete="off" style="padding-right: 44.1px;" />
                        <button type="button" class="btn btn-outline-white bg-white link-padrao btnTogglePassword position-absolute" style="bottom: 1px; right: 1px; padding: 4px 10px; width: 42px;">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
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
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="txtDescricaoAssinatura">Descrição assinatura</label>
                    <textarea id="txtDescricaoAssinatura" class="form-control" name="descricaoAssinatura" rows="5" autocomplete="off" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <button class="btn btn-padrao" type="submit">Salvar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="./assets/js/inspetores/inspetores.js<?= $version; ?>"></script>