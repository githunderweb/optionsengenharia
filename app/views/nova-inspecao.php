<section class="p-4">

    <?php
    $modeloOS = @$_GET['modeloOS'];
    $qrModeloRelatorio = mysqli_query($connect, "SELECT mr.*, os.id_empresa, os.id_unidade FROM modelos_relatorio_os mros INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio INNER JOIN ordens_servico os ON os.id = mros.id_os WHERE mros.id = '{$modeloOS}'");
    $dadoModeloRelatorio = mysqli_fetch_assoc($qrModeloRelatorio);

    if (count($dadoModeloRelatorio) > 0) {

        echo "<h5 class='mb-4 text-center'>{$dadoModeloRelatorio["titulo_modelo_relatorio"]}</h5>";
    ?>
        <form action="./controllers/inspecao.php?modeloOS=<?= $modeloOS; ?>" method='POST' class='needs-validation' novalidate='novalidate' enctype="multipart/form-data">
            <div class="row g-3 mb-3">

                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtTagCPE">TAG<span class="text-danger">*</span></label>
                    <input type="text" id="txtTagCPE" class="form-control" name="tagCPE" required="required" autocomplete="off" maxlength="250">
                </div>
            </div>

            <div id="campos-padrao-equipamento" class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtNumeroPastaCPE">Nº pasta<span class="text-danger">*</span></label>
                    <input type="number" id="txtNumeroPastaCPE" class="form-control" name="numeroPastaCPE" required="required" autocomplete="off" min="1">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtNomeCPE">Nome<span class="text-danger">*</span></label>
                    <input type="text" id="txtNomeCPE" class="form-control" name="nomeCPE" required="required" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtFabricanteCPE">Fabricante</label>
                    <input type="text" id="txtFabricanteCPE" class="form-control" name="fabricanteCPE" autocomplete="off" maxlength="250">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtAnoFabricacaoCPE">Ano de Fabricação</label>
                    <input type="number" id="txtAnoFabricacaoCPE" class="form-control" name="anoFabricacaoCPE" min="1901" max="<?= date("Y"); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="txtMaterialCPE">Material</label>
                    <input type="text" id="txtMaterialCPE" class="form-control" name="materialCPE" autocomplete="off" maxlength="250">
                </div>

                <input type="hidden" name="id_equipamento">
            </div>

            <hr class="mt-4 mb-3 border-secondary">

            <div class="row g-3">
                <?php
                $dados = [
                    "modelo" => $dadoModeloRelatorio["id"],
                    // "equipamento" => $equipamento,
                    "campos" => [],
                ];

                $qrCamposTipoEquipamento = mysqli_query($connect, "SELECT cte.*, 'campos_tipo_equipamento' AS origem FROM modelos_relatorio mr INNER JOIN tipos_equipamento te ON te.id = mr.id_tipo_equipamento INNER JOIN campos_tipo_equipamento cte ON cte.id_tipo_equipamento = te.id WHERE mr.id = '{$dadoModeloRelatorio["id"]}'");
                $dadosCamposTipoEquipamento = mysqli_fetch_all($qrCamposTipoEquipamento, MYSQLI_ASSOC);

                $qrCamposModeloRelatorio = mysqli_query($connect, "SELECT cmr.*, 'campos_modelo_relatorio' AS origem FROM campos_modelo_relatorio cmr WHERE cmr.id_modelo_relatorio = '{$dadoModeloRelatorio["id"]}'");
                $dadosCamposModeloRelatorio = mysqli_fetch_all($qrCamposModeloRelatorio, MYSQLI_ASSOC);

                $dadosCampos = array_merge($dadosCamposTipoEquipamento, $dadosCamposModeloRelatorio);

                foreach ($dadosCampos as $dado) {
                    $dados["campos"][] = [
                        "id_campo" => $dado["id"],
                        "titulo_campo" => $dado["titulo_campo"],
                        "slug" => $dado["slug"],
                        "tipo" => $dado["tipo"],
                        "origem" => $dado["origem"],
                    ];

                    $obrigatorio = $required =  "";
                    if ($dado["obrigatorio"]) {
                        $obrigatorio = "<span class='text-danger'>*</span>";
                        $required = "required";
                    }

                    $campo = "
                        <label class='form-label fw-semibold' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                        <input id='txt_{$dado["slug"]}' class='form-control' type='text' name='{$dado["slug"]}' $required>    
                    ";
                    if ($dado["tipo"] == "Texto longo") {
                        $campo = "
                            <label class='form-label fw-semibold' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <textarea id='txt_{$dado["slug"]}' class='form-control' name='{$dado["slug"]}' rows='4' $required></textarea>    
                        ";
                    } else if ($dado["tipo"] == "Múltipla escolha") {
                        $opcoes = "";
                        foreach (explode(",", $dado["opcoes"]) as $key => $value) {
                            $opcoes .= "
                                <div class='form-check d-flex align-items-center gap-2 p-0 m-0'>
                                    <input class='form-check-input m-0' id='rdo_{$dado["slug"]}_{$key}' type='radio' name='{$dado["slug"]}' value='{$value}' $required />
                                    <label class='form-check-label m-0' for='rdo_{$dado["slug"]}_{$key}'>{$value}</label>
                                </div>
                            ";
                        }

                        $campo = "
                            <label class='form-label fw-semibold'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <div class='d-flex flex-wrap gap-3'> 
                                {$opcoes}
                            </div>   
                        ";
                    } else if ($dado["tipo"] == "Caixa de seleção") {
                        $opcoes = "";
                        foreach (explode(",", $dado["opcoes"]) as $key => $value) {
                            $opcoes .= "
                                <div class='form-check m-0'>
                                    <input class='form-check-input' id='rdo_{$dado["slug"]}_{$key}' type='checkbox' name='{$dado["slug"]}[]' value='{$value}' $required />
                                    <label class='form-check-label' for='rdo_{$dado["slug"]}_{$key}'>{$value}</label>
                                </div>
                            ";
                        }

                        $campo = "
                            <label class='form-label fw-semibold'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <div> 
                                {$opcoes}
                            </div>   
                        ";
                    } else if ($dado["tipo"] == "Lista suspensa") {
                        $opcoes = "<option value='' disabled selected>Selecione...</option>";
                        $dataOptions = '"minimumResultsForSearch": -1';

                        $multiple = $multiplo = "";
                        if ($dado["multiplo"]) {
                            $multiple = "multiple";
                            $multiplo = "[]";

                            $opcoes = "";
                            $dataOptions = '"placeholder": "Selecione..."';
                        }

                        foreach (explode(",", $dado["opcoes"]) as $value) {
                            $opcoes .= "<option value='{$value}'>{$value}</option>";
                        }

                        $campo = "
                            <label class='form-label fw-semibold' for='slc_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <select id='slc_{$dado["slug"]}' class='form-select selectpicker' name='{$dado["slug"]}$multiplo' $required $multiple data-options='{{$dataOptions}}'> 
                                {$opcoes}
                            </select>   
                        ";
                    } else if ($dado["tipo"] == "Upload de arquivo") {
                        $multiple = "";
                        if ($dado["multiplo"])
                            $multiple = "multiple";

                        $accept = "";
                        if ($dado["tipo_arquivo"] != "" && $dado["tipo_arquivo"] != "Qualquer tipo")
                            $accept = "accept='{$dado["tipo_arquivo"]}'";

                        $campo = "
                            <label class='form-label fw-semibold' for='fle_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <input id='fle_{$dado["slug"]}' class='form-control' type='file' name='{$dado["slug"]}[]' $required $multiple $accept>    
                        ";
                    } else if ($dado["tipo"] == "Data e Hora") {
                        $campo = "
                            <label class='form-label fw-semibold' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <input id='txt_{$dado["slug"]}' class='form-control' type='datetime-local' name='{$dado["slug"]}' $required>    
                        ";
                    } else if ($dado["tipo"] == "Data") {
                        $campo = "
                            <label class='form-label fw-semibold' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <input id='txt_{$dado["slug"]}' class='form-control' type='date' name='{$dado["slug"]}' $required>    
                        ";
                    } else if ($dado["tipo"] == "Horário") {
                        $campo = "
                            <label class='form-label fw-semibold' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                            <input id='txt_{$dado["slug"]}' class='form-control' type='time' name='{$dado["slug"]}' $required>    
                        ";
                    }

                    echo "<div class='col-12'>$campo</div>";
                }

                echo "<input type='hidden' name='dados' value='" . json_encode($dados) . "'>";
                ?>

                <div class="col-12">
                    <button class="btn btn-padrao w-100" type="submit">Salvar</button>
                </div>
            </div>
        </form>
    <?php
        echo "<script>var empresa = '{$dadoModeloRelatorio["id_empresa"]}', unidade = '{$dadoModeloRelatorio["id_unidade"]}';</script>";
    } else
        echo '<h6 class="m-0 p-2">Não foi possível obter dados do modelo de relatório</h6>';
    ?>

</section>

<script src="./assets/js/nova-inspecao.js<?= $version; ?>"></script>