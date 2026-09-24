<div class="card">
    <div class="card-body pe-md-3">

        <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
            <h5 class="mb-2 mb-md-0">Controle de equipamentos</h5>
            <div class="d-flex gap-2">
                <!-- <a href="<?= $link; ?>p=equipamento" class="btn btn-padrao btn-sm flex-fill">
                    <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Novo
                </a> -->
                <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </button>
            </div>
        </div>

        <div class="table-responsive scrollbar pt-2 pe-md-1">
            <table class="dataTable table table-striped table-bordered mb-0" width="100%" cellspacing="0">
                <thead class="bg-200 text-900">
                    <tr>
                        <th>Nº pasta</th>
                        <th>Nome</th>
                        <th>Empresa</th>
                        <th>Unidade</th>
                        <th>Local instalção</th>
                        <th>TAG</th>
                        <th>Categoria</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tfoot class="bg-200 text-900">
                    <tr>
                        <th>Nº pasta</th>
                        <th>Nome</th>
                        <th>Empresa</th>
                        <th>Unidade</th>
                        <th>Local instalção</th>
                        <th>TAG</th>
                        <th>Categoria</th>
                        <th>Situação</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    <?php
                    $badgeColors = [
                        "Pendente" => "warning",
                        "Válido" => "success",
                    ];

                    $whereEmpresaAtual = "";
                    if ($empresaAtual != "")
                        $whereEmpresaAtual = "WHERE e.id_empresa = '{$empresaAtual}'";

                    $qrEquipamentos = mysqli_query(
                        $connect,
                        "SELECT e.id,
                            e.numero_pasta,
                            e.nome_equipamento,
                            li.titulo_local_instalacao,
                            e.tag,
                            ce.titulo_categoria_equipamento,
                            em.nome_empresa,
                            u.titulo_unidade,
                            CASE
                                WHEN i.status_inspecao = 'Aprovado' THEN 'Válido'
                                ELSE 'Pendente'
                            END AS situacao
                        FROM equipamentos e
                            INNER JOIN empresas em ON em.id = e.id_empresa
                            INNER JOIN unidades u ON u.id = e.id_unidade
                            LEFT JOIN locais_instalacao li ON li.id = e.id_local_instalacao
                            LEFT JOIN categorias_equipamento ce ON ce.id = e.id_categoria_equipamento
                            LEFT JOIN (
                                SELECT * 
                                FROM inspecoes 
                                WHERE id IN (
                                        SELECT MAX(id) 
                                        FROM inspecoes 
                                        GROUP BY id_equipamento
                                )
                            ) i ON i.id_equipamento = e.id
                        {$whereEmpresaAtual}"
                    );
                    while ($dadoEquipamento = mysqli_fetch_array($qrEquipamentos)) {
                        $status = "<span style='font-size: 0.8rem;' class='cursor-default badge d-block p-2 rounded-pill bg-{$badgeColors[$dadoEquipamento['situacao']]}'>{$dadoEquipamento['situacao']}</span>";

                        echo "
                            <tr>
                                <td>{$dadoEquipamento['numero_pasta']}</td>
                                <td>{$dadoEquipamento['nome_equipamento']}</td>
                                <td>{$dadoEquipamento['nome_empresa']}</td>
                                <td>{$dadoEquipamento['titulo_unidade']}</td>
                                <td>{$dadoEquipamento['titulo_local_instalacao']}</td>
                                <td>{$dadoEquipamento['tag']}</td>
                                <td>{$dadoEquipamento['titulo_categoria_equipamento']}</td>
                                <td>{$status}</td>
                            </tr>
                        ";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>