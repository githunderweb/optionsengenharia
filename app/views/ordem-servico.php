<section class="p-4">

    <form id="frm-escolher-modelo" action="./index.php">

        <input type="hidden" name="p" value="nova-inspecao">

        <div class="row g-3">
            <div class="col-12">
                <h5 class="mb-0 text-center">Selecione o modelo de relatório</h5>
            </div>
        </div>

        <div id="lisagem-modelos" class="mt-4 py-3 px-2 rounded d-flex flex-column gap-3" style=" background: #f8f8f8; ">

            <?php
            $qrModelosRelatorioOS = mysqli_query(
                $connect,
                "SELECT mros.id,
                    mr.titulo_modelo_relatorio,
                    i.status_inspecao,
                    i.id AS id_inspecao
                FROM modelos_relatorio_os mros
                    INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
                    INNER JOIN ordens_servico os ON os.id = mros.id_os
                    LEFT JOIN inspecoes i ON i.id_modelo_relatorio_os = mros.id
                WHERE mros.id_os = '{$id}'
                ORDER BY mros.id"
            );
            while ($dadoModelosRelatorioOS = mysqli_fetch_array($qrModelosRelatorioOS)) {
                $disabled = $badge = "";

                if ($dadoModelosRelatorioOS["status_inspecao"] != "") {
                    $disabled = "disabled";

                    $status = [
                        "Pendente" => "warning",
                        "Reprovado" => "danger",
                        "Aprovado" => "success"
                    ];
                    $badge = "<div class='float-end' style='margin-bottom: -10px;'><span class='badge bg-{$status[$dadoModelosRelatorioOS["status_inspecao"]]} bg-opacity-25 text-{$status[$dadoModelosRelatorioOS["status_inspecao"]]} pt-1 text-uppercase rounded-1' style='font-size: 0.65rem;'>{$dadoModelosRelatorioOS["status_inspecao"]}</span></div>";
                }

                if ($dadoModelosRelatorioOS["status_inspecao"] == "Reprovado") {
                    echo "
                        <div class='px-1'>
                            <a href='./?p=editar-inspecao&modeloOS={$dadoModelosRelatorioOS["id"]}&idInspecao={$dadoModelosRelatorioOS["id_inspecao"]}' class='btn bg-white p-3 border-2 rounded shadow-sm w-100 opacity-100'>
                                <h6 class='m-0 text-start'>{$dadoModelosRelatorioOS["titulo_modelo_relatorio"]}</h6>
                                {$badge}
                            </a>
                        </div>
                    ";
                } else {
            ?>
                    <div class="d-flex flex-column align-items-center gap-3 px-1">
                        <input type="radio" class="btn-check" name="modeloOS" id="<?= "rdoModelo{$dadoModelosRelatorioOS["id"]}"; ?>" autocomplete="off" value="<?= $dadoModelosRelatorioOS["id"]; ?>" <?= $disabled; ?>>
                        <label class="btn bg-white p-3 border-2 rounded shadow-sm w-100 opacity-100" for="<?= "rdoModelo{$dadoModelosRelatorioOS["id"]}"; ?>">
                            <h6 class="m-0 text-start"><?= "{$dadoModelosRelatorioOS["titulo_modelo_relatorio"]}"; ?></h6>
                            <?= $badge; ?>
                        </label>
                    </div>
            <?php
                }
            }
            ?>

        </div>

        <button id="btn-escolher-modelo" class="btn btn-padrao w-100 mt-3" type="submit" disabled="">Escolher modelo</button>
    </form>


</section>

<script src="./assets/js/nova-inspecao.js<?= $version; ?>"></script>