function inicio() {
    slcTipo('.select-tipo');
    $('.select-tipo, select[name="tipoArquivo[]"]').select2({ ...optionSlc2Icon, placeholder: 'Selecione...', minimumResultsForSearch: -1 });

    // Funções dos módulos
    botoesCampo();

    // Corrigindo 
    $('form#frm-modelos-relatorio').submit(function (event) {
        if (this.checkValidity()) {
            event.preventDefault();

            $('.row-campo').each(function (index, element) {
                var row = $(this);
                row.find('input[name="obrigatorio[]"]').attr('name', `obrigatorio[${index}]`);
                row.find('input[name="opcoes[]"]').attr('name', `opcoes[${index}]`);
                row.find('select[name="tipoArquivo[]"]').attr('name', `tipoArquivo[${index}]`);
                row.find('input[name="multiplo[]"]').attr('name', `multiplo[${index}]`);
            });

            this.submit();
        }
    });
}

if (typeof qtCampos === "undefined")
    var qtCampos = 1;

function rowCampo(campoAtual) {
    return `
        <div id="${campoAtual}" class="row-campo">
            <hr style="margin: 20px 0 12px 0">
            <div class="row g-3 mb-3 position-relative">
                <div class="position-absolute top-0 end-0 w-auto d-flex gap-2">
                    <a role="button" class="link-principal btn-novo-campo"> <i class="fas fa-plus-square"></i> </a>
                    <a role="button" class="link-danger btn-excluir-campo"> <i class="fas fa-trash-alt"></i></a>
                </div>

                <div class="col-12 col-md">
                    <label class="form-label" for="txtTituloCampo${campoAtual}">Título campo*</label>
                    <input id="txtTituloCampo${campoAtual}" class="form-control" type="text" name="tituloCampo[]" required="required" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="form-group">
                        <label class="form-label" for="slcTipo${campoAtual}">Tipo*</label>
                        <select id="slcTipo${campoAtual}" class="form-select select-tipo" name="tipo[]" required>
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
                </div>
                <div class="col-12 col-md-auto d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input id="chkObrigatorio${campoAtual}" class="form-check-input" name="obrigatorio[]" type="checkbox" checked />
                        <label class="form-check-label m-0" for="chkObrigatorio${campoAtual}">Obrigatório</label>
                    </div>
                </div>
            </div>
        </div>
    `
}

function rowAtributos(tipoCampo, idCampo) {
    var estrutura = '';

    if (tipoCampo == "Múltipla escolha" || tipoCampo == "Caixa de seleção" || tipoCampo == "Lista suspensa") {
        estrutura += `
            <div class="col-12 col-md">
                <label class="form-label" for="txtOpcoes${idCampo}">Opções*</label>
                <input id="txtOpcoes${idCampo}" class="form-control input-tag" type="text" name="opcoes[]" required="required">
            </div>
        `;
    }
    if (tipoCampo == "Upload de arquivo") {
        estrutura += `
            <div class="col-12 col-md">
                <label class="form-label" for="slcTipoArquivo${idCampo}">Tipo de arquivo*</label>
                <select id="slcTipoArquivo${idCampo}" class="form-select" name="tipoArquivo[]" required>
                    <option value="" selected disabled>Selecione...</option>
                    <option data-icon="bi bi-file-earmark" value="Qualquer tipo">Qualquer tipo</option>
                    <option data-icon="bi bi-file-earmark-pdf" value=".pdf">Documento PDF</option>
                    <option data-icon="bi bi-file-earmark-word" value=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">Documento de texto</option>
                    <option data-icon="bi bi-file-earmark-ruled" value=".xls, .xlsx, .xlsb, .xltx, .xltm, .xlt">Planilha</option>
                    <option data-icon="bi bi-file-earmark-slides" value=".ppt, .pptx">Apresentação</option>
                    <option data-icon="bi bi-file-earmark-zip" value=".zip, .rar, .7z">Arquivo Compactado</option>
                    <option data-icon="bi bi-file-earmark-text" value=".txt">Arquivo de Texto</option>
                    <option data-icon="bi bi-file-earmark-image" value="image/*">Imagem</option>
                    <option data-icon="bi bi-file-earmark-music" value="audio/*">Áudio</option>
                    <option data-icon="bi bi-file-earmark-play" value="video/*">Vídeo</option>
                </select>
            </div>
        `;
    }
    if (tipoCampo == "Lista suspensa" || tipoCampo == "Upload de arquivo") {
        estrutura += `
            <div class="col-12 col-md-auto d-flex align-items-end">
                <div class="form-check form-switch">
                    <input id="chkMultiplo${idCampo}" class="form-check-input" name="multiplo[]" type="checkbox" />
                    <label class="form-check-label m-0" for="chkMultiplo${idCampo}">Múltipla seleção</label>
                </div>
            </div>
        `;
    }

    if (estrutura != '')
        estrutura = `
            <div class="row row-atributos g-3 mt-n4 pt-2 mb-3">${estrutura}</div>
        `;

    return estrutura
}

function slcTipo(slc) {
    $(slc).change(function () {
        var slcAtual = $(this);
        var rowAtual = slcAtual.parents('.row-campo');

        idAtual = rowAtual.attr('id');
        if (idAtual == undefined)
            idAtual = '';

        rowAtual.find('.row-atributos').remove();
        rowAtual.append(rowAtributos(slcAtual.val(), idAtual));

        $(`#txtOpcoes${idAtual}`).tagify();
        $(`#slcTipoArquivo${idAtual}`).select2({ ...optionSlc2Icon, placeholder: 'Selecione...', minimumResultsForSearch: -1 });
    });
}

// Funções dos botões de adicionar e remover módulo
function botoesCampo() {
    $('.btn-novo-campo, .btn-excluir-campo').off('click');

    $('.btn-novo-campo').click(function () {
        $(this).parents('.row-campo').after(rowCampo(qtCampos));

        slcTipo(`.row-campo#${qtCampos} .select-tipo`);
        $(`.row-campo#${qtCampos} .select-tipo`).select2({ ...optionSlc2Icon, placeholder: 'Selecione...', minimumResultsForSearch: -1 });

        botoesCampo();
        qtCampos++;
    });

    $('.btn-excluir-campo').click(function () {
        if ($('#campos-modelo-relatorio .row-campo').length > 1)
            $(this).parents('.row-campo').remove();
    });
}