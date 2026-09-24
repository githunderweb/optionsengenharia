<!--<section class="d-flex flex-column justify-content-center py-3 px-4" style="height: 83vh;">

    <div class="row mb-5 no-events">
        <div class="col text-center">
            <img class="no-events" src="./assets/img/logomarca.png">
        </div>
    </div>

    <div class="row g-3 menu-cards ff-inter">
        <div class="col-12">
            <a href="./index.php?p=nova-inspecao" class="card text-decoration-none">
                <div class="card-body d-flex gap-2 align-items-center justify-content-center">
                    <h6 class="fw-semibold m-0 lh-1">Nova Inspeção</h6>
                    <i class="bi bi-clipboard-plus"></i>
                </div>
            </a>
        </div>
        <div class="col-12">
            <a href="./index.php?p=historico" class="card text-decoration-none">
                <div class="card-body d-flex gap-2 align-items-center justify-content-center">
                    <h6 class="fw-semibold m-0 lh-1">Histórico</h6>
                    <i class="bi bi-clock-history"></i>
                </div>
            </a>
        </div>
    </div>

</section>-->

<style>
    body {
        background: #f2f2f2;
    }
</style>

<section class="p-4">

    <h5 class="mb-3 text-center">Ordens de serviço abertas</h5>

    <div id="listagem-ordens-servico" class="d-flex flex-column align-items-center gap-3">

        <?php
        $qrOrdensServico = mysqli_query(
            $connect,
            "SELECT os.id, os.numero_os, os.descricao, e.nome_empresa, u.titulo_unidade, os.status_os 
            FROM ordens_servico os 
            INNER JOIN empresas e ON e.id = os.id_empresa 
            INNER JOIN unidades u ON u.id = os.id_unidade 
            INNER JOIN inspetores i ON i.id = os.id_inspetor 
            WHERE os.id_inspetor = '{$sessaoUsuario["id"]}' AND os.status_os = 'Em aberto'"
        );
        while ($dadoOrdemServico = mysqli_fetch_array($qrOrdensServico)) {
            $descricaoOrdemServico = trim((string) ($dadoOrdemServico["descricao"] ?? ""));
            $descricaoOrdemServicoHtml = "";

            if ($descricaoOrdemServico !== "") {
                $descricaoSegura = nl2br(htmlspecialchars($descricaoOrdemServico, ENT_QUOTES, "UTF-8"));
                $descricaoOrdemServicoHtml = "
                    <p class='m-0 mt-2 text-break'>
                        <span class='small fw-semibold'>Descri&ccedil;&atilde;o:</span> {$descricaoSegura}
                    </p>
                ";
            }

            echo "
                <a href='./index.php?p=ordem-servico&id={$dadoOrdemServico["id"]}' class='btn bg-white p-3 border-0 rounded shadow-sm w-100 text-start'>
                    <p class='m-0 ff-inter fw-semibold'>{$dadoOrdemServico["numero_os"]}</p>

                    {$descricaoOrdemServicoHtml}

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
