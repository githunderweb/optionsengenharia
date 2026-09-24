<div class="row flex-center">
    <div class="col-12 col-md-8 col-lg-5">
        <!-- <a class="d-flex flex-center mb-4" href="./"><img class="me-2" src="../../assets/img/icons/spot-illustrations/falcon.png" alt="" width="58" />
        <span class="font-sans-serif fw-bolder fs-5 d-inline-block">falcon</span></a> -->
        <div class="card">
            <div class="card-body" style="min-height: 510px;">

                <?php
                $qrModeloRelatorio = mysqli_query($connect, "SELECT * FROM modelos_relatorio WHERE id = '{$id}'");
                $dadoModeloRelatorio = mysqli_fetch_array($qrModeloRelatorio);

                echo "<h5 class='mb-4 text-center'>{$dadoModeloRelatorio["titulo_modelo_relatorio"]}</h5>";
                ?>

                <form action="./inspecoes/inspecao-acao.php?acao=" method='POST' class='needs-validation' novalidate='novalidate' enctype="multipart/form-data">
                    <div class="row g-3">
                        <?php
                        $dados = [
                            "modelo" => $id,
                            "campos" => [],
                        ];

                        $qr = mysqli_query($connect, "SELECT * FROM campos_modelo_relatorio WHERE id_modelo_relatorio = '{$id}'");
                        while ($dado = mysqli_fetch_array($qr)) {
                            $dados["campos"][] = [
                                "id_campo" => $dado["id"],
                                "id_modelo_relatorio" => $dado["id_modelo_relatorio"],
                                "titulo_campo" => $dado["titulo_campo"],
                                "slug" => $dado["slug"],
                                "tipo" => $dado["tipo"],
                            ];

                            $obrigatorio = $required =  "";
                            if ($dado["obrigatorio"]) {
                                $obrigatorio = "*";
                                $required = "required";
                            }

                            $campo = "
                                <label class='form-label' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                                <input id='txt_{$dado["slug"]}' class='form-control' type='text' name='{$dado["slug"]}' $required>    
                            ";
                            if ($dado["tipo"] == "Texto longo") {
                                $campo = "
                                    <label class='form-label' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
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
                                    <label class='form-label'>{$dado["titulo_campo"]}$obrigatorio</label>
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
                                    <label class='form-label'>{$dado["titulo_campo"]}$obrigatorio</label>
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
                                    <label class='form-label' for='slc_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
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
                                    <label class='form-label' for='fle_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                                    <input id='fle_{$dado["slug"]}' class='form-control' type='file' name='{$dado["slug"]}[]' $required $multiple $accept>    
                                ";
                            } else if ($dado["tipo"] == "Data e Hora") {
                                $campo = "
                                    <label class='form-label' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                                    <input id='txt_{$dado["slug"]}' class='form-control' type='datetime-local' name='{$dado["slug"]}' $required>    
                                ";
                            } else if ($dado["tipo"] == "Data") {
                                $campo = "
                                    <label class='form-label' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                                    <input id='txt_{$dado["slug"]}' class='form-control' type='date' name='{$dado["slug"]}' $required>    
                                ";
                            } else if ($dado["tipo"] == "Horário") {
                                $campo = "
                                    <label class='form-label' for='txt_{$dado["slug"]}'>{$dado["titulo_campo"]}$obrigatorio</label>
                                    <input id='txt_{$dado["slug"]}' class='form-control' type='time' name='{$dado["slug"]}' $required>    
                                ";
                            }

                            echo "<div class='col-12'>$campo</div>";
                        }

                        echo "<input type='hidden' name='dados' value='" . json_encode($dados) . "'>";
                        ?>

                        <div class="col-12">
                            <button class="btn btn-padrao" type="submit">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>