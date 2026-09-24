<div class="card">
    <form id="frm-tipos-equipamento" action="./tipos-equipamento/tipo-equipamento-acao.php" method="POST" class="needs-validation" novalidate="novalidate">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Novo tipo de equipamento</h5>
            <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
            </button>
        </div>

        <div class="card-body bg-light">
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="txtTitulo">Título*</label>
                    <input type="text" id="txtTitulo" class="form-control" name="titulo" required="required" autocomplete="off" maxlength="250">
                </div>
            </div>

            <div id="campos-tipo-produto" class="my-3 bg-white p-3 pb-0 border rounded-1">

                <h6 class="mb-0">Campos personalizados equipamento</h6>

                <div class="row-campo">
                    <hr style="margin: 20px 0 12px 0">
                    <div class="row g-3 mb-3 position-relative">
                        <div class="position-absolute top-0 end-0 w-auto d-flex gap-2">
                            <a role="button" class="link-principal btn-novo-campo"> <i class="fas fa-plus-square"></i> </a>
                            <a role="button" class="link-danger btn-excluir-campo"> <i class="fas fa-trash-alt"></i></a>
                        </div>

                        <div class="col-12 col-md">
                            <label class="form-label" for="txtTituloCampo">Título campo*</label>
                            <input id="txtTituloCampo" class="form-control" type="text" name="tituloCampo[]" required="required" autocomplete="off" maxlength="250">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label" for="slcTipo">Tipo*</label>
                            <select id="slcTipo" class="form-select select-tipo" name="tipo[]" required>
                                <option value="" selected disabled>Selecione...</option>
                                <option data-icon="bi bi-input-cursor-text" value="Texto">Texto</option>
                                <option data-icon="bi bi-textarea-resize" value="Texto longo">Texto longo</option>
                                <option data-icon="bi bi-ui-radios" value="Múltipla escolha">Múltipla escolha</option>
                                <option data-icon="bi bi-ui-checks" value="Caixa de seleção">Caixa de seleção</option>
                                <option data-icon="bi bi-menu-button" value="Lista suspensa">Lista suspensa</option>
                                <option data-icon="bi bi-upload" value="Upload de arquivo">Upload de arquivo</option>
                                <option data-icon="bi bi-calendar-event" value="Data">Data</option>
                                <option data-icon="bi bi-clock" value="Horário">Horário</option>
                                <option data-icon="bi bi-calendar2-event" value="Data e Hora">Data e Hora</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-auto d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input id="chkObrigatorio" class="form-check-input" name="obrigatorio[]" type="checkbox" checked />
                                <label class="form-check-label m-0" for="chkObrigatorio">Obrigatório</label>
                            </div>
                        </div>
                    </div>
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

<script src="./assets/js/tipos-equipamento/tipos-equipamento.js<?= $version; ?>"></script>