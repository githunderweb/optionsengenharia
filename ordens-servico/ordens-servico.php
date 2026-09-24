<?php
if ($acao == "") {
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Ordens de serviço</h5>
                <div class="d-flex gap-2">
                    <a href="<?= $link; ?>p=ordem-servico" class="btn btn-padrao btn-sm flex-fill">
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
                            <th>Nº OS</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Inspetor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>Nº OS</th>
                            <th>Empresa</th>
                            <th>Unidade</th>
                            <th>Inspetor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php
                        $qrOrdensServico = mysqli_query($connect, "SELECT os.id, os.numero_os, e.nome_empresa, u.titulo_unidade, i.nome_inspetor, os.status_os FROM ordens_servico os INNER JOIN empresas e ON e.id = os.id_empresa INNER JOIN unidades u ON u.id = os.id_unidade INNER JOIN inspetores i ON i.id = os.id_inspetor");
                        while ($dadoOrdemServico = mysqli_fetch_array($qrOrdensServico)) {
                            echo "
                                <tr>
                                    <td>{$dadoOrdemServico['numero_os']}</td>
                                    <td>{$dadoOrdemServico['nome_empresa']}</td>
                                    <td>{$dadoOrdemServico['titulo_unidade']}</td>
                                    <td>{$dadoOrdemServico['nome_inspetor']}</td>
                                    <td>{$dadoOrdemServico['status_os']}</td>
                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=ordens-servico&acao=editar&id={$dadoOrdemServico['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>Editar
                                            </a>

                                            <div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=ordens-servico&acao=excluir&id={$dadoOrdemServico['id']}'>
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

            $qr = mysqli_query($connect, "SELECT * FROM ordens_servico WHERE id = '{$id}'");
            $dado = mysqli_fetch_array($qr);

            echo "<form action='./ordens-servico/ordem-servico-acao.php?acao=editar&id=" . $dado["id"] . "' method='POST' class='needs-validation' novalidate='novalidate'>";

            ?>

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar ordem de serviço</h5>
                <a href="<?= "{$link}p=ordens-servico"; ?>" class="btn btn-padrao btn-sm">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </a>
            </div>

            <div class="card-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-3 col-lg-2">
                        <label class="form-label" for="txtNumeroOS">Nº OS*</label>
                        <input type="text" id="txtNumeroOS" class="form-control" name="numeroOS" required="required" autocomplete="off" maxlength="250" value="<?= $dado["numero_os"]; ?>">
                    </div>
                    <div class="col-12 col-md-6 col-lg-7">
                        <label class="form-label" for="slcInspetor">Inspetor*</label>
                        <select id="slcInspetor" class="form-select selectpicker" name="inspetor" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                            <?php
                            $optionsInspetores = "<option value='' selected disabled>Selecione...</option>";
                            $qrInspetores = mysqli_query($connect, "SELECT id, nome_inspetor FROM inspetores");
                            while ($dadoInspetores = mysqli_fetch_array($qrInspetores)) {
                                $optionsInspetores .= "<option value='{$dadoInspetores['id']}'>{$dadoInspetores['nome_inspetor']}</option>";
                            }
                            $optionsInspetores = str_replace("value='{$dado['id_inspetor']}'", "value='{$dado['id_inspetor']}' selected", $optionsInspetores);
                            echo $optionsInspetores;
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="slcStatus">Status*</label>
                        <select id="slcStatus" class="form-select" name="status" required>
                            <?php
                            $optionsStatus = "<option value='' selected disabled>Selecione...</option> <option value='Em aberto'>Em aberto</option> <option value='Pendente'>Pendente</option> <option value='Concluída'>Concluída</option> <option value='Inativa'>Inativa</option>";
                            $optionsStatus = str_replace("value='{$dado["status_os"]}'", "value='{$dado["status_os"]}' selected", $optionsStatus);
                            echo $optionsStatus;
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="slcEmpresa">Empresa*</label>
                        <select id="slcEmpresa" class="form-select selectpicker" name="empresa" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                            <?php
                            $optionsEmpresas = "<option value='' selected disabled>Selecione...</option>";
                            $qrEmpresas = mysqli_query($connect, "SELECT id, nome_empresa FROM empresas");
                            while ($dadoEmpresas = mysqli_fetch_array($qrEmpresas)) {
                                $optionsEmpresas .= "<option value='{$dadoEmpresas['id']}'>{$dadoEmpresas['nome_empresa']}</option>";
                            }
                            $optionsEmpresas = str_replace("value='{$dado['id_empresa']}'", "value='{$dado['id_empresa']}' selected", $optionsEmpresas);
                            echo $optionsEmpresas;
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="slcUnidade">Unidade*</label>
                        <select id="slcUnidade" class="form-select selectpicker" name="unidade" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }'>
                            <?php
                            $optionsUnidades = "<option value='' selected disabled>Selecione...</option>";
                            $qrUnidades = mysqli_query($connect, "SELECT id, titulo_unidade FROM unidades WHERE id_empresa = '{$dado['id_empresa']}'");
                            while ($dadoUnidades = mysqli_fetch_array($qrUnidades)) {
                                $optionsUnidades .= "<option value='{$dadoUnidades['id']}'>{$dadoUnidades['titulo_unidade']}</option>";
                            }
                            $optionsUnidades = str_replace("value='{$dado['id_unidade']}'", "value='{$dado['id_unidade']}' selected", $optionsUnidades);
                            echo $optionsUnidades;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="txtDescricao">Descrição</label>
                        <textarea id="txtDescricao" class="form-control" name="descricao" rows="4" autocomplete="off" placeholder="Digite uma descrição para a ordem de serviço..."><?= htmlspecialchars($dado["descricao"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div id="modelos-receita" class="bg-white p-3 pb-0 border rounded-1">

                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label d-block mb-0">Relatórios</label>
                            </div>

                            <?php
                            $optionsModelos = "<option value='' selected disabled>Selecione...</option>";
                            $qrModelos = mysqli_query($connect, "SELECT id, titulo_modelo_relatorio FROM modelos_relatorio");
                            while ($dadoModelos = mysqli_fetch_array($qrModelos)) {
                                $optionsModelos .= "<option value='{$dadoModelos['id']}'>{$dadoModelos['titulo_modelo_relatorio']}</option>";
                            }


                            $nenhumModeloRelatorio = true;
                            $qrModelosRelatorioOS = mysqli_query($connect, "SELECT mros.id_modelo_relatorio, (SELECT COUNT(*) FROM modelos_relatorio_os WHERE id_os = mros.id_os AND id_modelo_relatorio = mros.id_modelo_relatorio) AS qt FROM modelos_relatorio_os mros WHERE mros.id_os = '{$dado["id"]}' GROUP BY mros.id_modelo_relatorio ORDER BY mros.id");
                            $dadosModelosRelatorioOS = mysqli_fetch_all($qrModelosRelatorioOS, MYSQLI_ASSOC);
                            foreach ($dadosModelosRelatorioOS as $key => $dadoModelosRelatorioOS) {
                                if ($nenhumModeloRelatorio)
                                    $nenhumModeloRelatorio = false;
                                $i = $key - 1;

                                $optionsModelosAtual = str_replace("value='{$dadoModelosRelatorioOS['id_modelo_relatorio']}'", "value='{$dadoModelosRelatorioOS['id_modelo_relatorio']}' selected", $optionsModelos);

                                echo "
                                    <hr style='margin: 20px 0 12px 0'>
                                    <div class='row row-modelo g-3 mb-3 position-relative'>
                                        <div class='position-absolute top-0 end-0 w-auto d-flex align-items-center gap-2'>
                                            <a role='button' class='link-padrao btn-novo-modelo'><i class='fas fa-plus-square'></i></a>
                                            <a role='button' class='link-danger btn-excluir-modelo'> <i class='fas fa-trash-alt'></i></a>
                                        </div>

                                        <div class='col-12 col-md-9 col-lg-10'>
                                            <label class='form-label' for='slcModelo{$i}'>Modelo relatório*</label>
                                            <select id='slcModelo{$i}' class='form-select selectpicker' name='modelosRelatorio[]' required='required' data-options='{\"placeholder\":\"Selecione...\", \"language\": { \"noResults\": \"Nenhum resultado encontrado\"} }'>
                                                {$optionsModelosAtual}
                                            </select>
                                        </div>
                                        <div class='col col-md-3 col-lg-2'>
                                            <label class='form-label' for='txtQuantidadeModelos{$i}'>Quantidade*</label> 
                                            <input class='form-control' type='number' name='quantidadeModelos[]' id='txtQuantidadeModelos{$i}' required='required' autocomplete='off' value='{$dadoModelosRelatorioOS['qt']}' min='1'>
                                        </div>
                                    </div>
                                ";
                            }

                            // Caso não seja encontrado nenhum modelo de relatório, exibe uma linha em branco para reiniciar a seleção de modelos
                            if ($nenhumModeloRelatorio) {
                                echo "
                                    <hr style='margin: 20px 0 12px 0'>
                                    <div class='row row-modelo g-3 mb-3 position-relative'>
                                        <div class='position-absolute top-0 end-0 w-auto d-flex align-items-center gap-2'>
                                            <a role='button' class='link-padrao btn-novo-modelo'><i class='fas fa-plus-square'></i></a>
                                            <a role='button' class='link-danger btn-excluir-modelo'> <i class='fas fa-trash-alt'></i></a>
                                        </div>

                                        <div class='col-12 col-md-9 col-lg-10'>
                                            <label class='form-label' for='slcModelo'>Modelo relatório*</label>
                                            <select id='slcModelo' class='form-select selectpicker' name='modelosRelatorio[]' required='required' data-options='{\"placeholder\":\"Selecione...\", \"language\": { \"noResults\": \"Nenhum resultado encontrado\"} }'>
                                                {$optionsModelos}
                                            </select>
                                        </div>
                                        <div class='col col-md-3 col-lg-2'>
                                            <label class='form-label' for='txtQuantidadeModelos'>Quantidade*</label> 
                                            <input class='form-control' type='number' name='quantidadeModelos[]' id='txtQuantidadeModelos' required='required' autocomplete='off' value='1' min='1'>
                                        </div>
                                    </div>
                                ";
                            }
                            ?>
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

        <script src="./assets/js/ordens-servico/ordens-servico.js<?= $version; ?>"></script>

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
                        <?php include("./assets/illustrations/svg-delete.php"); ?>
                    </div>
                    <div class="col-lg-6 ps-lg-4 my-5 text-center text-lg-start">
                        <h3 class="text-padrao">Tem certeza de que deseja excluir esta ordem de serviço?</h3>
                        <?php
                        $qrOrdemServico = mysqli_query($connect, "SELECT os.id, os.numero_os, e.nome_empresa, u.titulo_unidade, i.nome_inspetor, os.status_os FROM ordens_servico os INNER JOIN empresas e ON e.id = os.id_empresa INNER JOIN unidades u ON u.id = os.id_unidade INNER JOIN inspetores i ON i.id = os.id_inspetor WHERE os.id ='{$id}'");
                        $dadoOrdemServico = mysqli_fetch_array($qrOrdemServico);

                        echo "<form action='./ordens-servico/ordem-servico-acao.php?acao=excluir&id=" . $dadoOrdemServico["id"] . "' method='POST'>"
                        ?>
                        <p class="lead">
                            <?php
                            echo "Nº OS: {$dadoOrdemServico["numero_os"]}<br>Empresa: {$dadoOrdemServico["nome_empresa"]}<br>Unidade: {$dadoOrdemServico["titulo_unidade"]}<br>Inspetor: {$dadoOrdemServico["nome_inspetor"]}";
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
