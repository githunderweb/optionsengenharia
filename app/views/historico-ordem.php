<style>
    body {
        background: #f2f2f2;
    }
</style>

<!-- Modal visualizar inspeção -->
<div class="modal fade" id="modalVisualizarInspecao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalVisualizarInspecaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalVisualizarInspecaoLabel">Visualizar inspeção</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<section class="p-4">

    <div id="listagem-inspecoes" class="d-flex flex-column align-items-center gap-3">
        <?php
        $qr = mysqli_query(
            $connect,
            "SELECT i.*, mr.titulo_modelo_relatorio, e.nome_equipamento, em.nome_empresa, u.titulo_unidade, ir.nome_inspetor 
            FROM inspecoes i 
            INNER JOIN modelos_relatorio_os mros ON mros.id = i.id_modelo_relatorio_os 
            INNER JOIN modelos_relatorio mr ON mr.id = mros.id_modelo_relatorio
            INNER JOIN ordens_servico os ON os.id = mros.id_os
            LEFT JOIN equipamentos e ON e.id = i.id_equipamento 
            LEFT JOIN empresas em ON em.id = os.id_empresa 
            LEFT JOIN unidades u ON u.id = os.id_unidade 
            INNER JOIN inspetores ir ON ir.id = os.id_inspetor 
            WHERE os.id = '{$id}' AND os.id_inspetor = '{$sessaoUsuario["id"]}'
            ORDER BY i.id DESC"
        );
        while ($dado = mysqli_fetch_assoc($qr)) {
            $equipamento = $dado["nome_equipamento"] != "" ? "<p class='mt-1 mb-0'><span class='small fw-semibold'>Equipamento:</span> {$dado["nome_equipamento"]}</p>" : "";

            $htmlInspecoes .= "
                <button type='button' class='btn bg-white p-3 border-0 rounded shadow-sm w-100 text-start' data-options='" . json_encode($dado) . "'>
                    <p class='m-0 ff-inter fw-semibold'>{$dado["titulo_modelo_relatorio"]}</p>

                    <div class='d-flex gap-1 flex-wrap justify-content-between mt-1'>
                        <p class='m-0'><span class='small fw-semibold'>Empresa:</span> {$dado["nome_empresa"]}</p>
                        <p class='m-0'><span class='small fw-semibold'>Unidade:</span> {$dado["titulo_unidade"]}</p>
                    </div>

                    {$equipamento}

                    <p class='mb-0 small text-secondary text-end'>" . date("d/m/Y H:i", strtotime($dado["data_cadastro"])) . "</p>
                </button>
            ";
        }

        if ($htmlInspecoes == "")
            $htmlInspecoes = '<h6 class="m-0 p-2">Nenhuma inspeção disponível</h6>';

        echo $htmlInspecoes;
        ?>
    </div>
</section>

<script src="./assets/js/historico-ordem.js<?= $version; ?>"></script>