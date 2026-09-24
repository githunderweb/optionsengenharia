<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Inspeções</h5>
                <div class="d-flex gap-2">
                    <!-- <a href="<?= $link; ?>p=inspecao" class="btn btn-padrao btn-sm flex-fill">
                        <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Nova
                    </a> -->
                    <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>
            </div>

            <div class="table-responsive scrollbar pt-2 pe-md-1">
                <table class="dataTable table table-striped table-bordered mb-0" width="100%" cellspacing="0" data-table='{ "order": [] }'>
                    <thead class="bg-200 text-900">
                        <tr>
                            <th>OS</th>
                            <th>Modelo de relatório</th>
                            <th>Equipamento</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Inspetor</th>
                            <th>Data</th>
                            <th>Edição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>OS</th>
                            <th>Modelo de relatório</th>
                            <th>Equipamento</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Inspetor</th>
                            <th>Data</th>
                            <th>Edição</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $whereInspecoes = "";
                        if ($sessaoUsuario["funcao"] == "Cliente") {
                            $whereInspecoes = "WHERE " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $sessaoUsuario["id_empresa"]);
                        }

                        $qr = mysqli_query(
                            $connect,
                            "SELECT i.*,
                                os.numero_os, 
                                mr.titulo_modelo_relatorio,
                                e.nome_equipamento AS nome_equipamento_cadastrado,
                                em.nome_empresa,
                                u.titulo_unidade,
                                ir.nome_inspetor
                            FROM inspecoes i
                                INNER JOIN modelos_relatorio_os mros ON mros.id = i.id_modelo_relatorio_os
                                INNER JOIN ordens_servico os ON os.id = mros.id_os
                                INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
                                LEFT JOIN inspetores ir ON ir.id = i.id_inspetor
                                LEFT JOIN equipamentos e ON e.id = i.id_equipamento
                                LEFT JOIN empresas em ON em.id = os.id_empresa
                                LEFT JOIN unidades u ON u.id = os.id_unidade
                            {$whereInspecoes}
                            ORDER BY 
                                CASE
                                    WHEN i.data_edicao IS NOT NULL THEN i.data_edicao
                                    ELSE i.data_cadastro
                                END
                            DESC"
                        );
                        while ($dado = mysqli_fetch_assoc($qr)) {
                            $btnsPendente = "";
                            /*if ($dado['status_inspecao'] == "Pendente") {
                                $btnsPendente = "
                                    <a class='dropdown-item' href='./inspecoes/inspecao-acao.php?acao=aprovar&id={$dado['id']}'>
                                        <i class='bi-check-square me-2 dropdown-item-icon'></i>Aprovar
                                    </a>
                                    <a class='dropdown-item' href='./inspecoes/inspecao-acao.php?acao=reprovar&id={$dado['id']}'>
                                        <i class='bi-x-square me-2 dropdown-item-icon'></i>Reprovar
                                    </a>
                                ";
                            }*/

                            if ($dado['status_inspecao'] == "Aprovado") {
                                $btnGroup = "
                                    <div class='btn-group'>
                                        <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                        <div class='dropdown-menu dropdown-menu-end mt-1'>
                                            <a class='dropdown-item' href='./inspecoes/gerar-pdf.php?id={$dado['id']}' target='_blank'>
                                                <i class='bi bi-file-earmark-text me-2 dropdown-item-icon'></i>Gerar PDF
                                            </a>
                                            {$btnsPendente}
                                            <!--<a class='dropdown-item' href='{$link}p=inspecoes&acao=excluir&id={$dado['id']}'>
                                                <i class='bi-trash me-2 dropdown-item-icon'></i>Excluir
                                            </a>-->
                                        </div>
                                    </div>
                                ";
                            }

                            $equipamento = $dado['nome_equipamento_cadastrado'] != "" ? "<a href='{$link}p=equipamentos&acao=editar&id={$dado['id_equipamento']}'>{$dado['nome_equipamento_cadastrado']}</a>" : $dado['nome_equipamento'];
                            echo "
                                <tr>
                                    <td>{$dado['numero_os']}</td>
                                    <td>{$dado['titulo_modelo_relatorio']}</td>
                                    <td>{$equipamento}</td>
                                    <td>{$dado['nome_empresa']}</td>
                                    <td>{$dado['titulo_unidade']}</td>
                                    <td>{$dado['nome_inspetor']}</td>
                                    <td>" . date("d/m/Y H:i", strtotime($dado['data_cadastro'])) . "</td>
                                    <td>" . ($dado['data_edicao'] ? date("d/m/Y H:i", strtotime($dado['data_edicao'])) : "") . "</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=inspecoes&acao=ver&id={$dado['id']}'>
                                                <i class='bi-eye-fill me-2'></i>Ver
                                            </a>
                                            {$btnGroup}
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

    if ($acao == "ver") {
    ?>

        <div class="card">
            <?php

            $qr = mysqli_query(
                $connect,
                "SELECT * FROM inspecoes i 
                INNER JOIN modelos_relatorio_os mros ON mros.id = i.id_modelo_relatorio_os 
                INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
                INNER JOIN ordens_servico os ON os.id = mros.id_os
                WHERE i.id = '{$id}'" . ($sessaoUsuario["funcao"] == "Cliente" ? " AND EXISTS (SELECT 1 FROM equipamentos e_permitido WHERE e_permitido.id = i.id_equipamento AND " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e_permitido", $sessaoUsuario["id_empresa"]) . ")" : "")
            );
            $dado = mysqli_fetch_array($qr);

            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= $dado["titulo_modelo_relatorio"]; ?></h5>
                <div class="d-flex gap-2">
                    <?php
                    if ($dado['status_inspecao'] == "Aprovado") {
                        echo "
                            <a href='./inspecoes/gerar-pdf.php?id={$id}' target='_blank' class='btn btn-outline-padrao btn-sm'>
                                <i class='bi bi-file-earmark-text me-2 dropdown-item-icon'></i>Gerar PDF
                            </a>
                        ";
                    }
                    ?>
                    <a href="<?= "{$link}p=inspecoes"; ?>" class="btn btn-padrao btn-sm">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form action="./inspecoes/inspecao-acao.php?acao=editar&id=<?= $id; ?>" method="POST" class="needs-validation" novalidate="novalidate" enctype="multipart/form-data">

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg">
                            <label class="form-label" for="txtNome">Nome equipamento*</label>
                            <input type="text" id="txtNome" class="form-control" name="nome" required="required" autocomplete="off" maxlength="250" value="<?= $dado['nome_equipamento']; ?>">
                        </div>
                        <div class="col col-lg-auto" style="width: 200px;">
                            <label class="form-label" for="txtNumeroART">Nº ART</label>
                            <input type="text" id="txtNumeroART" class="form-control" name="numeroART" required="required" autocomplete="off" maxlength="250" value="<?= $dado['numero_art']; ?>">
                        </div>
                        <div class="col col-lg-auto" style="width: 120px;">
                            <label class="form-label" for="txtNumeroPasta">Nº pasta*</label>
                            <input type="number" id="txtNumeroPasta" class="form-control" name="numeroPasta" required="required" autocomplete="off" min="1" value="<?= $dado['numero_pasta']; ?>">
                        </div>
                        <div class="col col-lg-auto" style="width: 150px;">
                            <label class="form-label" for="txtTag">TAG*</label>
                            <input type="text" id="txtTag" class="form-control" name="tag" required="required" autocomplete="off" maxlength="250" value="<?= $dado['tag']; ?>">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md">
                            <label class="form-label" for="txtFabricante">Fabricante</label>
                            <input type="text" id="txtFabricante" class="form-control" name="fabricante" autocomplete="off" maxlength="250" value="<?= $dado['fabricante']; ?>">
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="form-label" for="txtAnoFabricacao">Ano de Fabricação</label>
                            <input type="number" id="txtAnoFabricacao" class="form-control" name="anoFabricacao" min="1901" max="<?= date("Y"); ?>" value="<?= $dado['ano_fabricacao'] != "" ? $dado['ano_fabricacao'] : ""; ?>">
                        </div>
                        <div class="col-12 col-md">
                            <label class="form-label" for="txtMaterial">Material</label>
                            <input type="text" id="txtMaterial" class="form-control" name="material" autocomplete="off" maxlength="250" value="<?= $dado['material']; ?>">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col">

                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $qrValoresInspecao = mysqli_query($connect, "SELECT * FROM valores_inspecao vi WHERE id_inspecao = '{$id}'");
                                    while ($dadoValoresInspecao = mysqli_fetch_array($qrValoresInspecao)) {
                                        $valor = $dadoValoresInspecao["valor"];

                                        if ($dadoValoresInspecao["tipo"] == "Upload de arquivo") {
                                            $arquivos = [];
                                            foreach (explode("|", $dadoValoresInspecao['valor']) as $arquivo) {
                                                $arquivos[] = "<a href='./inspecoes/arquivos/{$id}/{$dadoValoresInspecao["slug"]}/{$arquivo}' download='{$arquivo}'>{$arquivo}</a>";
                                            }

                                            $valor = implode(", ", $arquivos);
                                        } else if ($dadoValoresInspecao["tipo"] == "Data" && $dadoValoresInspecao["valor"] != "") {
                                            $valor = date("d/m/Y", strtotime($dadoValoresInspecao['valor']));
                                        } else if ($dadoValoresInspecao["tipo"] == "Data e Hora" && $dadoValoresInspecao["valor"] != "") {
                                            $valor = date("d/m/Y H:i", strtotime($dadoValoresInspecao['valor']));
                                        }

                                        if ($dadoValoresInspecao["tipo"] == "Upload de arquivo") {
                                            echo "
                                                <tr>
                                                    <td class='fw-semi-bold'>{$dadoValoresInspecao["titulo"]}</td>
                                                    <td>{$valor}</td>
                                                </tr>
                                            ";
                                        } else {
                                            echo "
                                                <tr>
                                                    <td class='fw-semi-bold'>{$dadoValoresInspecao["titulo"]}</td>
                                                    <td><input type='text' class='form-control' name='{$dadoValoresInspecao["slug"]}' value='{$valor}'></td>
                                                </tr>
                                            ";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>

                        </div>
                    </div>


                    <?php
                    if ($dado["status_inspecao"] == "Pendente") {
                    ?>
                        <div class="row g-3">
                            <div class="col-12 d-flex gap-2 flex-wrap justify-content-between">
                                <button class="btn btn-padrao w-100 w-md-auto" type="submit">Salvar</button>

                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <button class="btn btn-danger text-nowrap w-100 w-md-auto" type="button" data-bs-toggle="modal" data-bs-target="#modalReprovar"><i class='bi-x-square me-2 dropdown-item-icon'></i>Reprovar</button>
                                    <button class="btn btn-success text-nowrap w-100 w-md-auto" type="button" data-bs-toggle="modal" data-bs-target="#modalAprovar"><i class='bi-check-square me-2 dropdown-item-icon'></i>Aprovar</button>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>

                </form>
            </div>

        </div>


        <?php
        if ($dado["status_inspecao"] == "Pendente") {
        ?>
            <div class="modal fade" id="modalReprovar" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content position-relative">
                        <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                            <button type="button" class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="./inspecoes/inspecao-acao.php?acao=reprovar&id=<?= $id; ?>" method="POST" class="needs-validation" novalidate="novalidate" autocomplete="no">
                            <div class="modal-body p-0">
                                <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                    <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill text-danger fw-bold me-2"></i>Atenção!</h4>
                                </div>
                                <div class="p-3">
                                    <p id="modalReprovarLabel">Tem certeza que deseja reprovar esta inspeção?</p>
                                    <div class="form-floating">
                                        <!-- <textarea id="txtMotivo" class="form-control textarea-autosize" name="motivo" placeholder="Motivo" maxlength="1000"></textarea> -->
                                        <textarea id="txtMotivo" class="form-control" name="motivo" placeholder="Motivo" maxlength="1000"></textarea>
                                        <label for="txtMotivo">Motivo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer flex-nowrap">
                                <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger w-100">Reprovar</button>
                                <!-- <?= "<a class='btn btn-danger w-100' href='./inspecoes/inspecao-acao.php?acao=reprovar&id={$id}'>Reprovar</a>"; ?> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalAprovar" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content position-relative">
                        <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                            <button type="button" class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill text-secondary fw-bold me-2"></i>Atenção!</h4>
                            </div>
                            <div class="p-3">
                                <p id="modalAprovarLabel" class="m-0 text-center">Tem certeza que deseja aprovar esta inspeção?</p>
                            </div>
                        </div>
                        <div class="modal-footer flex-nowrap">
                            <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cancelar</button>
                            <?= "<a class='btn btn-success w-100' href='./inspecoes/inspecao-acao.php?acao=aprovar&id={$id}'>Aprovar</a>"; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>

    <?php
    } else if (false && $acao == 'excluir') {
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
                        <h3 class="text-padrao">Tem certeza de que deseja excluir esta inspecao?</h3>
                        <?php
                        $qr = mysqli_query($connect, "SELECT * FROM inspecoes WHERE id ='{$id}'");
                        $dado = mysqli_fetch_array($qr);

                        echo "<form action='./inspecoes/inspecao-acao.php?acao=excluir&id=" . $dado['id'] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Título: " . $dado['nome_inspecao'] . "<br>CNPJ: " . $dado['cnpj'];
                            ?>
                        </p>
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
