<style>
    body {
        background: #f2f2f2;
    }
</style>

<section class="p-4">

    <h5 class="mb-3 text-center">Histórico</h5>

    <div id="listagem-ordens-servico" class="d-flex flex-column align-items-center gap-3">

        <?php
        $qrOrdensServico = mysqli_query(
            $connect,
            "SELECT os.id, os.numero_os, e.nome_empresa, u.titulo_unidade, os.status_os 
            FROM ordens_servico os 
            INNER JOIN empresas e ON e.id = os.id_empresa 
            INNER JOIN unidades u ON u.id = os.id_unidade 
            INNER JOIN inspetores i ON i.id = os.id_inspetor 
            WHERE os.id_inspetor = '{$sessaoUsuario["id"]}' AND os.status_os != 'Em aberto'"
        );
        while ($dadoOrdemServico = mysqli_fetch_array($qrOrdensServico)) {
            echo "
                <a href='./index.php?p=historico-ordem&id={$dadoOrdemServico["id"]}' class='btn bg-white p-3 border-0 rounded shadow-sm w-100 text-start'>
                    <p class='m-0 ff-inter fw-semibold'>{$dadoOrdemServico["numero_os"]}</p>

                    <div class='d-flex gap-1 flex-wrap justify-content-between mt-1'>
                        <p class='m-0'><span class='small fw-semibold'>Empresa:</span> {$dadoOrdemServico["nome_empresa"]}</p>
                        <p class='m-0'><span class='small fw-semibold'>Unidade:</span> {$dadoOrdemServico["titulo_unidade"]}</p>
                    </div>
                </a>
            ";
        }
        ?>

    </div>

</section>