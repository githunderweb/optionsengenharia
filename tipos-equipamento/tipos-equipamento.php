<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Tipos de equipamento</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=tipo-equipamento" class="btn btn-padrao btn-sm flex-fill">
                        <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Novo
                    </a>
                    <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>
            </div>

            <div class="table-responsive scrollbar pt-2 pe-md-1">
                <table class="dataTable table table-striped table-bordered mb-0" width="100%" cellspacing="0">
                    <thead class="bg-200 text-900">
                        <tr>
                            <th>Título</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Título</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $qrTiposEquipamento = mysqli_query($connect, "SELECT * FROM tipos_equipamento");
                        while ($dadoTipoEquipamento = mysqli_fetch_array($qrTiposEquipamento)) {
                            echo "
                                <tr>
                                    <td>{$dadoTipoEquipamento['titulo_tipo_equipamento']}</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=tipos-equipamento&acao=editar&id={$dadoTipoEquipamento['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>Editar
                                            </a>

                                            <div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=tipos-equipamento&acao=excluir&id={$dadoTipoEquipamento['id']}'>
                                                        <i class='bi-trash me-2 dropdown-item-icon'></i>Excluir
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            ";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
    ?>

        <div class="card">
            <?php

            $qr = mysqli_query($connect, "SELECT * FROM tipos_equipamento WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form id='frm-tipos-equipamento' action='./tipos-equipamento/tipo-equipamento-acao.php?acao=editar&id={$dado['id']}' method='POST' class='needs-validation' novalidate='novalidate'>";
            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar tipo de equipamento</h5>
                <a href="<?= "{$link}p=tipos-equipamento"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtTitulo">Título*</label>
                        <input type="text" id="txtTitulo" class="form-control" name="titulo" required="required" autocomplete="off" maxlength="250" value="<?= $dado['titulo_tipo_equipamento']; ?>">
                    </div>
                </div>

                <div id="campos-tipo-produto" class="my-3 bg-white p-3 pb-0 border rounded-1">

                    <h6 class="mb-0">Campos personalizados equipamento</h6>

                    <?php
                    $iCamposTipoEquipamento = "";
                    $qrCamposTipoEquipamento = mysqli_query($connect, "SELECT * FROM campos_tipo_equipamento WHERE id_tipo_equipamento = '{$id}'");
                    while ($dadoCamposTipoEquipamento = mysqli_fetch_array($qrCamposTipoEquipamento)) {
                    ?>
                        <div class="row-campo">
                            <hr style="margin: 20px 0 12px 0">
                            <div class="row g-3 mb-3 position-relative">
                                <div class="position-absolute top-0 end-0 w-auto d-flex gap-2">
                                    <a role="button" class="link-principal btn-novo-campo"> <i class="fas fa-plus-square"></i> </a>
                                    <a role="button" class="link-danger btn-excluir-campo"> <i class="fas fa-trash-alt"></i></a>
                                </div>

                                <div class="col-12 col-md">
                                    <label class="form-label" for="txtTituloCampo<?= $iCamposTipoEquipamento; ?>">Título campo*</label>
                                    <input id="txtTituloCampo<?= $iCamposTipoEquipamento; ?>" class="form-control" type="text" name="tituloCampo[]" required="required" autocomplete="off" maxlength="250" value="<?= $dadoCamposTipoEquipamento["titulo_campo"]; ?>">
                                </div>
                                <div class="col-12 col-md-4 col-lg-3">
                                    <label class="form-label" for="slcTipo<?= $iCamposTipoEquipamento; ?>">Tipo*</label>
                                    <select id="slcTipo<?= $iCamposTipoEquipamento; ?>" class="form-select select-tipo" name="tipo[]" required>
                                        <?php
                                        $optionsTipos = '<option value="" selected disabled>Selecione...</option> <option data-icon="bi bi-input-cursor-text" value="Texto">Texto</option> <option data-icon="bi bi-textarea-resize" value="Texto longo">Texto longo</option> <option data-icon="bi bi-ui-radios" value="Múltipla escolha">Múltipla escolha</option> <option data-icon="bi bi-ui-checks" value="Caixa de seleção">Caixa de seleção</option> <option data-icon="bi bi-menu-button" value="Lista suspensa">Lista suspensa</option> <option data-icon="bi bi-upload" value="Upload de arquivo">Upload de arquivo</option> <option data-icon="bi bi-calendar-event" value="Data">Data</option> <option data-icon="bi bi-clock" value="Horário">Horário</option> <option data-icon="bi bi-calendar2-event" value="Data e Hora">Data e Hora</option>';
                                        $optionsTipos = str_replace('value="' . $dadoCamposTipoEquipamento["tipo"] . '"', 'value="' . $dadoCamposTipoEquipamento["tipo"] . '" selected', $optionsTipos);
                                        echo $optionsTipos;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-auto d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input id="chkObrigatorio<?= $iCamposTipoEquipamento; ?>" class="form-check-input" name="obrigatorio[]" type="checkbox" <?php if ($dadoCamposTipoEquipamento["obrigatorio"]) echo "checked"; ?> />
                                        <label class="form-check-label m-0" for="chkObrigatorio<?= $iCamposTipoEquipamento; ?>">Obrigatório</label>
                                    </div>
                                </div>
                            </div>

                            <?php
                            if ($dadoCamposTipoEquipamento["tipo"] == "Múltipla escolha" || $dadoCamposTipoEquipamento["tipo"] == "Caixa de seleção" || $dadoCamposTipoEquipamento["tipo"] == "Lista suspensa" || $dadoCamposTipoEquipamento["tipo"] == "Upload de arquivo") {
                            ?>
                                <div class="row row-atributos g-3 mt-n4 pt-2 mb-3">
                                    <?php
                                    if ($dadoCamposTipoEquipamento["tipo"] == "Múltipla escolha" || $dadoCamposTipoEquipamento["tipo"] == "Caixa de seleção" || $dadoCamposTipoEquipamento["tipo"] == "Lista suspensa") {
                                    ?>
                                        <div class="col-12 col-md">
                                            <label class="form-label" for="txtOpcoes<?= $iCamposTipoEquipamento; ?>">Opções*</label>
                                            <input id="txtOpcoes<?= $iCamposTipoEquipamento; ?>" class="form-control input-tag" type="text" name="opcoes[]" required="required" value="<?= $dadoCamposTipoEquipamento["opcoes"]; ?>">
                                        </div>
                                    <?php
                                    }
                                    if ($dadoCamposTipoEquipamento["tipo"] == "Upload de arquivo") {
                                    ?>
                                        <div class="col-12 col-md">
                                            <label class="form-label" for="slcTipoArquivo<?= $iCamposTipoEquipamento; ?>">Tipo de arquivo*</label>
                                            <select id="slcTipoArquivo<?= $iCamposTipoEquipamento; ?>" class="form-select" name="tipoArquivo[]" required>
                                                <?php
                                                $optionsTipoArquivo = '<option value="" selected disabled>Selecione...</option> <option data-icon="bi bi-file-earmark" value="Qualquer tipo">Qualquer tipo</option> <option data-icon="bi bi-file-earmark-pdf" value=".pdf">Documento PDF</option> <option data-icon="bi bi-file-earmark-word" value=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">Documento de texto</option> <option data-icon="bi bi-file-earmark-ruled" value=".xls, .xlsx, .xlsb, .xltx, .xltm, .xlt">Planilha</option> <option data-icon="bi bi-file-earmark-slides" value=".ppt, .pptx">Apresentação</option> <option data-icon="bi bi-file-earmark-zip" value=".zip, .rar, .7z">Arquivo Compactado</option> <option data-icon="bi bi-file-earmark-text" value=".txt">Arquivo de Texto</option> <option data-icon="bi bi-file-earmark-image" value="image/*">Imagem</option> <option data-icon="bi bi-file-earmark-music" value="audio/*">Áudio</option> <option data-icon="bi bi-file-earmark-play" value="video/*">Vídeo</option>';
                                                $optionsTipoArquivo = str_replace('value="' . $dadoCamposTipoEquipamento["tipo_arquivo"] . '"', 'value="' . $dadoCamposTipoEquipamento["tipo_arquivo"] . '" selected', $optionsTipoArquivo);
                                                echo $optionsTipoArquivo;
                                                ?>
                                            </select>
                                        </div>
                                    <?php
                                    }
                                    if ($dadoCamposTipoEquipamento["tipo"] == "Lista suspensa" || $dadoCamposTipoEquipamento["tipo"] == "Upload de arquivo") {
                                    ?>
                                        <div class="col-12 col-md-auto d-flex align-items-end">
                                            <div class="form-check form-switch">
                                                <input id="chkMultiplo<?= $iCamposTipoEquipamento; ?>" class="form-check-input" name="multiplo[]" type="checkbox" <?php if ($dadoCamposTipoEquipamento["multiplo"]) echo "checked"; ?> />
                                                <label class="form-check-label m-0" for="chkMultiplo<?= $iCamposTipoEquipamento; ?>">Múltipla seleção</label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                        $iCamposTipoEquipamento++;
                    }

                    // Caso não seja encontrado nenhum modelo de relatório, exibe uma linha em branco para reiniciar a seleção de modelos
                    if ($iCamposTipoEquipamento == "") {
                        $iCamposTipoEquipamento = 1;
                    ?>
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
                    <?php
                    }
                    ?>
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

    <?php
    } else if ($acao == 'excluir') {
    ?>

        <div class="card">
            <div class="card-body overflow-hidden">

                <div class="d-flex justify-content-end">
                    <button class="btn btn-padrao btn-sm" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>

                <div class="row align-items-center p-lg-5 pt-lg-3">

                    <div class="col-lg-6">
                        <!-- <img class="img-fluid" src="./assets/img/illustrations/user_trash.png" alt=""> --> <?php include("./assets/illustrations/svg-delete.php"); ?>
                    </div>
                    <div class="col-lg-6 ps-lg-4 my-5 text-center text-lg-start">
                        <h3 class="text-padrao">Tem certeza de que deseja excluir este tipo de equipamento?</h3>
                        <?php
                        $qr = mysqli_query($connect, "SELECT * FROM tipos_equipamento WHERE id = '{$id}'");
                        $dado = mysqli_fetch_array($qr);

                        echo "<form action='./tipos-equipamento/tipo-equipamento-acao.php?acao=excluir&id={$dado['id']}' method='POST'>"
                        ?>
                        <p class="lead"><?= "Título: {$dado['titulo_tipo_equipamento']}"; ?></p>
                        <button id="btnExcluir" type="submit" class="btn btn-falcon-padrao">Confirmar exclusão</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
?>