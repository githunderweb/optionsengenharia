<?php

$disabledCliente = permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : "";

function textoFiltroEquipamentos($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function dadosSemaforoEquipamento($vencimentoInterno, $vencimentoExterno, $proximaInspecao = "", $proximaCalibracao = "", $vencimentoGeral = "")
{
    $datas = [];
    foreach (["vencimento interno" => $vencimentoInterno, "vencimento externo" => $vencimentoExterno, "próxima inspeção" => $proximaInspecao, "próxima calibração" => $proximaCalibracao, "vencimento" => $vencimentoGeral] as $tipo => $valor) {
        $valor = trim((string) $valor);
        if ($valor === "")
            continue;

        $data = null;
        foreach (["!Y-m-d", "!d/m/Y"] as $formato) {
            $dataTentativa = DateTime::createFromFormat($formato, $valor);
            $erros = DateTime::getLastErrors();
            if ($dataTentativa && ($erros === false || ($erros["warning_count"] === 0 && $erros["error_count"] === 0))) {
                $data = $dataTentativa;
                break;
            }
        }

        if ($data)
            $datas[$tipo] = $data;
    }

    if (count($datas) === 0)
        return ["cor" => "#6c757d", "texto" => "Sem vencimento informado"];

    asort($datas);
    $tipoPrimeiroVencimento = array_key_first($datas);
    $primeiroVencimento = $datas[$tipoPrimeiroVencimento];
    $hoje = new DateTime("today");
    $limiteAtencao = (clone $hoje)->modify("+3 months");
    $dataFormatada = $primeiroVencimento->format("d/m/Y");
    $origem = $tipoPrimeiroVencimento;

    if ($primeiroVencimento < $hoje)
        return ["cor" => "#dc3545", "texto" => "Vencido em {$dataFormatada} ({$origem})"];
    if ($primeiroVencimento <= $limiteAtencao)
        return ["cor" => "#fd7e14", "texto" => "Vence em até 3 meses: {$dataFormatada} ({$origem})"];

    return ["cor" => "#28a745", "texto" => "Em dia. Próximo vencimento: {$dataFormatada} ({$origem})"];
}

function linksArquivosEquipamento($idEquipamento, $slugCampo, $valorCampo, $podeExcluir = false)
{
    $links = [];
    foreach (explode("|", (string) $valorCampo) as $arquivo) {
        $arquivo = trim($arquivo);
        if ($arquivo === "")
            continue;

        $url = "./equipamentos/visualizar-arquivo.php?id=" . intval($idEquipamento)
            . "&campo=" . rawurlencode($slugCampo)
            . "&arquivo=" . rawurlencode($arquivo);
        $nomeArquivo = textoFiltroEquipamentos($arquivo);
        $linkVisualizar = "<a class='btn btn-outline-primary btn-sm d-flex align-items-center gap-2 flex-grow-1 overflow-hidden' href='{$url}' target='_blank' rel='noopener' title='{$nomeArquivo}'><span class='fas fa-eye flex-shrink-0'></span><span class='text-truncate'>Visualizar: {$nomeArquivo}</span></a>";
        $botaoExcluir = "";
        if ($podeExcluir) {
            $identificadorArquivo = htmlspecialchars(base64_encode(json_encode([$slugCampo, $arquivo], JSON_UNESCAPED_UNICODE)), ENT_QUOTES, "UTF-8");
            $botaoExcluir = "<button type='submit' form='formExcluirArquivoEquipamento' class='btn btn-outline-danger btn-sm flex-shrink-0' name='excluir_arquivo' value='{$identificadorArquivo}' onclick=\"return confirm('Excluir este arquivo anexo? Esta ação não pode ser desfeita.');\" title='Excluir {$nomeArquivo}'><span class='fas fa-trash'></span> Excluir</button>";
        }
        $links[] = "<div class='d-flex align-items-stretch gap-2'>{$linkVisualizar}{$botaoExcluir}</div>";
    }

    return count($links) > 0
        ? "<div class='d-flex flex-column align-items-stretch gap-2 mb-2'>" . implode("", $links) . "</div>"
        : "<p class='text-600 fs--1 mb-2'>Nenhum arquivo anexado.</p>";
}

function tokenFiltroPersonalizadoEquipamentos($idsCampos, $valor)
{
    $json = json_encode([array_values($idsCampos), $valor], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return rtrim(strtr(base64_encode($json), "+/", "-_"), "=");
}

function dadosTokenFiltroPersonalizadoEquipamentos($token)
{
    $token = strtr((string) $token, "-_", "+/");
    $token .= str_repeat("=", (4 - strlen($token) % 4) % 4);
    $dados = json_decode(base64_decode($token, true), true);
    if (!is_array($dados) || count($dados) !== 2 || !is_array($dados[0]))
        return null;

    $idsCampos = array_values(array_unique(array_filter(array_map("intval", $dados[0]))));
    $valor = trim((string) $dados[1]);
    sort($idsCampos);
    return count($idsCampos) > 0 && $valor !== "" ? ["ids" => $idsCampos, "valor" => $valor] : null;
}

function expressaoDataSemaforoEquipamentoSql($aliasEquipamento = "e")
{
    return "(SELECT MIN(CASE
        WHEN LOWER(fle_sem.slug) LIKE 'vencimento-interno%' OR UPPER(TRIM(fle_sem.titulo)) = 'VENCIMENTO INTERNO' THEN STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%Y-%m-%d')
        WHEN LOWER(fle_sem.slug) LIKE 'vencimento-externo%' OR UPPER(TRIM(fle_sem.titulo)) = 'VENCIMENTO EXTERNO' THEN STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%Y-%m-%d')
        WHEN LOWER(fle_sem.slug) LIKE 'proxima-inspecao%' OR UPPER(TRIM(fle_sem.titulo)) = 'PRÓXIMA INSPEÇÃO' THEN STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%Y-%m-%d')
        WHEN LOWER(fle_sem.slug) LIKE 'proxima-calibracao%' OR UPPER(TRIM(fle_sem.titulo)) = 'PRÓXIMA CALIBRAÇÃO' THEN CASE
            WHEN LEFT(TRIM(fle_sem.valor), 10) REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%d/%m/%Y')
            ELSE STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%Y-%m-%d')
        END
        WHEN LOWER(TRIM(fle_sem.slug)) REGEXP '^vencimento(-[0-9]+)?$' OR UPPER(TRIM(fle_sem.titulo)) = 'VENCIMENTO' THEN STR_TO_DATE(LEFT(TRIM(fle_sem.valor), 10), '%Y-%m-%d')
    END) FROM valores_campos_personalizados_equipamento fle_sem WHERE fle_sem.id_equipamento = {$aliasEquipamento}.id)";
}

function condicaoFiltrosListaEquipamentos($connect, $condicaoBase, $filtros, $alias = "e")
{
    $condicoes = ["({$condicaoBase})"];
    foreach (["tipo" => "id_tipo_equipamento", "categoria" => "id_categoria_equipamento", "local" => "id_local_instalacao"] as $filtro => $coluna) {
        $valor = intval($filtros[$filtro] ?? 0);
        if ($valor > 0)
            $condicoes[] = "{$alias}.{$coluna} = '{$valor}'";
    }

    if (($filtros["situacao"] ?? "") === "valido")
        $condicoes[] = "EXISTS (SELECT 1 FROM inspecoes fle_i WHERE fle_i.id = (SELECT MAX(fle_i2.id) FROM inspecoes fle_i2 WHERE fle_i2.id_equipamento = {$alias}.id) AND fle_i.status_inspecao = 'Aprovado')";
    elseif (($filtros["situacao"] ?? "") === "pendente")
        $condicoes[] = "NOT EXISTS (SELECT 1 FROM inspecoes fle_i WHERE fle_i.id = (SELECT MAX(fle_i2.id) FROM inspecoes fle_i2 WHERE fle_i2.id_equipamento = {$alias}.id) AND fle_i.status_inspecao = 'Aprovado')";
    elseif (($filtros["situacao"] ?? "") === "apto")
        $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fle_status_v INNER JOIN campos_tipo_equipamento fle_status_c ON fle_status_c.id = fle_status_v.id_campo_tipo_equipamento WHERE fle_status_v.id_equipamento = {$alias}.id AND UPPER(TRIM(fle_status_c.titulo_campo)) = 'STATUS DO EQUIPAMENTO' AND UPPER(TRIM(fle_status_v.valor)) = 'EQUIPAMENTO APTO')";
    elseif (($filtros["situacao"] ?? "") === "inapto")
        $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fle_status_v INNER JOIN campos_tipo_equipamento fle_status_c ON fle_status_c.id = fle_status_v.id_campo_tipo_equipamento WHERE fle_status_v.id_equipamento = {$alias}.id AND UPPER(TRIM(fle_status_c.titulo_campo)) = 'STATUS DO EQUIPAMENTO' AND UPPER(TRIM(fle_status_v.valor)) = 'EQUIPAMENTO INAPTO')";

    $filtroSemaforo = $filtros["semaforo"] ?? "";
    if (in_array($filtroSemaforo, ["verde", "laranja", "vermelho", "cinza"], true)) {
        $dataSemaforo = expressaoDataSemaforoEquipamentoSql($alias);
        if ($filtroSemaforo === "vermelho")
            $condicoes[] = "{$dataSemaforo} < CURDATE()";
        elseif ($filtroSemaforo === "laranja")
            $condicoes[] = "{$dataSemaforo} BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
        elseif ($filtroSemaforo === "verde")
            $condicoes[] = "{$dataSemaforo} > DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
        else
            $condicoes[] = "{$dataSemaforo} IS NULL";
    }

    foreach (($filtros["campos_personalizados"] ?? []) as $filtroPersonalizado) {
        $idsCampos = array_values(array_filter(array_map("intval", $filtroPersonalizado["ids"] ?? [])));
        $valorCampo = mysqli_real_escape_string($connect, trim($filtroPersonalizado["valor"] ?? ""));
        if (count($idsCampos) > 0 && $valorCampo !== "")
            $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fle_v WHERE fle_v.id_equipamento = {$alias}.id AND fle_v.id_campo_tipo_equipamento IN (" . implode(",", $idsCampos) . ") AND (fle_v.valor = '{$valorCampo}' OR FIND_IN_SET('{$valorCampo}', REPLACE(fle_v.valor, ', ', ',')) > 0))";
    }

    return implode(" AND ", $condicoes);
}

if ($acao == "") {
    $filtrosPersonalizadosEquipamentos = [];
    foreach ((array) ($_GET["f_personalizado"] ?? []) as $tokenFiltroPersonalizado) {
        $dadosFiltroPersonalizado = dadosTokenFiltroPersonalizadoEquipamentos($tokenFiltroPersonalizado);
        if ($dadosFiltroPersonalizado !== null)
            $filtrosPersonalizadosEquipamentos[] = $dadosFiltroPersonalizado;
    }

    $situacoesPermitidasEquipamentos = $sessaoUsuario["funcao"] == "Cliente" ? ["apto", "inapto"] : ["valido", "pendente"];
    $semaforosPermitidosEquipamentos = ["verde", "laranja", "vermelho", "cinza"];
    $filtrosEquipamentos = [
        "tipo" => intval($_GET["f_tipo"] ?? 0),
        "categoria" => intval($_GET["f_categoria"] ?? 0),
        "local" => intval($_GET["f_local"] ?? 0),
        "situacao" => in_array($_GET["f_situacao"] ?? "", $situacoesPermitidasEquipamentos, true) ? $_GET["f_situacao"] : "",
        "semaforo" => in_array($_GET["f_semaforo"] ?? "", $semaforosPermitidosEquipamentos, true) ? $_GET["f_semaforo"] : "",
        "campos_personalizados" => $filtrosPersonalizadosEquipamentos
    ];

    if ($sessaoUsuario["funcao"] == "Cliente")
        $condicaoBaseEquipamentos = condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $empresaAtual);
    else
        $condicaoBaseEquipamentos = intval($empresaAtual) > 0 ? "e.id_empresa = '" . intval($empresaAtual) . "'" : "1 = 1";

    $condicaoEquipamentosFiltrados = condicaoFiltrosListaEquipamentos($connect, $condicaoBaseEquipamentos, $filtrosEquipamentos);
    $qrTotalEquipamentosFiltrados = mysqli_query($connect, "SELECT COUNT(DISTINCT e.id) AS total FROM equipamentos e INNER JOIN empresas em ON em.id = e.id_empresa INNER JOIN unidades u ON u.id = e.id_unidade INNER JOIN locais_instalacao l ON l.id = e.id_local_instalacao WHERE {$condicaoEquipamentosFiltrados}");
    $dadoTotalEquipamentosFiltrados = $qrTotalEquipamentosFiltrados ? mysqli_fetch_assoc($qrTotalEquipamentosFiltrados) : ["total" => 0];
    $totalEquipamentosFiltrados = intval($dadoTotalEquipamentosFiltrados["total"] ?? 0);
    $quantidadeFiltrosEquipamentos = count($filtrosEquipamentos["campos_personalizados"]);
    foreach (["tipo", "categoria", "local", "situacao", "semaforo"] as $nomeFiltroEquipamento) {
        if ($filtrosEquipamentos[$nomeFiltroEquipamento] !== "" && $filtrosEquipamentos[$nomeFiltroEquipamento] !== 0)
            $quantidadeFiltrosEquipamentos++;
    }
    $linkLimparFiltrosEquipamentos = intval($empresaAtual) > 0
        ? "./index.php?empresa=" . intval($empresaAtual) . "&p=equipamentos"
        : ($sessaoUsuario["funcao"] == "Cliente" ? "./index.php?empresa=todas&p=equipamentos" : "./index.php?p=equipamentos");

    $opcoesFiltroSemaforo = [
        "" => ["Todos", "#adb5bd"],
        "verde" => ["Verde — em dia", "#28a745"],
        "laranja" => ["Laranja — vence em até 3 meses", "#fd7e14"],
        "vermelho" => ["Vermelho — vencido", "#dc3545"],
        "cinza" => ["Cinza — sem data", "#6c757d"]
    ];
    $parametrosFiltroSemaforo = $_GET;
    unset($parametrosFiltroSemaforo["acao"], $parametrosFiltroSemaforo["id"]);
    $parametrosFiltroSemaforo["p"] = "equipamentos";

    $tiposFiltroEquipamentos = [];
    $qrTiposFiltro = mysqli_query($connect, "SELECT DISTINCT t.id, t.titulo_tipo_equipamento AS titulo FROM equipamentos e INNER JOIN tipos_equipamento t ON t.id = e.id_tipo_equipamento WHERE {$condicaoBaseEquipamentos} ORDER BY titulo");
    while ($qrTiposFiltro && $dadoFiltro = mysqli_fetch_assoc($qrTiposFiltro))
        $tiposFiltroEquipamentos[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

    $categoriasFiltroEquipamentos = [];
    $qrCategoriasFiltro = mysqli_query($connect, "SELECT DISTINCT c.id, c.titulo_categoria_equipamento AS titulo FROM equipamentos e INNER JOIN categorias_equipamento c ON c.id = e.id_categoria_equipamento WHERE {$condicaoBaseEquipamentos} ORDER BY titulo");
    while ($qrCategoriasFiltro && $dadoFiltro = mysqli_fetch_assoc($qrCategoriasFiltro))
        $categoriasFiltroEquipamentos[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

    $locaisFiltroEquipamentos = [];
    $qrLocaisFiltro = mysqli_query($connect, "SELECT DISTINCT l.id, l.titulo_local_instalacao AS titulo FROM equipamentos e INNER JOIN locais_instalacao l ON l.id = e.id_local_instalacao WHERE {$condicaoBaseEquipamentos} ORDER BY titulo");
    while ($qrLocaisFiltro && $dadoFiltro = mysqli_fetch_assoc($qrLocaisFiltro))
        $locaisFiltroEquipamentos[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

    $camposFiltroEquipamentos = [];
    $qrCamposFiltro = mysqli_query($connect, "SELECT c.id AS id_campo, c.titulo_campo AS titulo, c.opcoes, c.tipo, v.valor FROM valores_campos_personalizados_equipamento v INNER JOIN campos_tipo_equipamento c ON c.id = v.id_campo_tipo_equipamento INNER JOIN equipamentos e ON e.id = v.id_equipamento WHERE {$condicaoBaseEquipamentos} AND v.valor IS NOT NULL AND TRIM(v.valor) <> '' AND c.tipo IN ('Múltipla escolha', 'Caixa de seleção') ORDER BY c.titulo_campo, c.opcoes, v.valor");
    while ($qrCamposFiltro && $dadoFiltro = mysqli_fetch_assoc($qrCamposFiltro)) {
        $tituloFiltro = trim($dadoFiltro["titulo"]);
        $tituloNormalizado = mb_strtoupper($tituloFiltro, "UTF-8");
        if ($sessaoUsuario["funcao"] == "Cliente" && $tituloNormalizado === "STATUS DO EQUIPAMENTO")
            continue;
        if (in_array($tituloNormalizado, ["TIPO DE INSPEÇÃO EXECUTADA", "TIPOS DE INSPEÇÃO EXECUTADA"], true)) {
            $tituloFiltro = "TIPO DE INSPEÇÃO EXECUTADA";
            $tituloNormalizado = $tituloFiltro;
        }
        $opcoesFiltro = trim($dadoFiltro["opcoes"] ?? "");
        $chaveFiltro = $tituloNormalizado . "|" . mb_strtoupper(preg_replace('/\s+/u', '', $opcoesFiltro), "UTF-8");
        if (!isset($camposFiltroEquipamentos[$chaveFiltro]))
            $camposFiltroEquipamentos[$chaveFiltro] = ["titulo" => $tituloFiltro, "titulo_normalizado" => $tituloNormalizado, "opcoes" => $opcoesFiltro, "ids" => [], "valores" => []];
        $idCampo = intval($dadoFiltro["id_campo"]);
        $camposFiltroEquipamentos[$chaveFiltro]["ids"][$idCampo] = $idCampo;
        $valores = stripos($dadoFiltro["tipo"], "Caixa") === 0 ? preg_split('/,\s*/u', $dadoFiltro["valor"]) : [$dadoFiltro["valor"]];
        foreach ($valores as $valor) {
            $valor = trim($valor);
            if ($valor !== "")
                $camposFiltroEquipamentos[$chaveFiltro]["valores"][$valor] = $valor;
        }
    }
    uasort($camposFiltroEquipamentos, function ($a, $b) {
        return strnatcasecmp($a["titulo"] . $a["opcoes"], $b["titulo"] . $b["opcoes"]);
    });
    $quantidadeCamposPorTitulo = [];
    foreach ($camposFiltroEquipamentos as $campoFiltro)
        $quantidadeCamposPorTitulo[$campoFiltro["titulo_normalizado"]] = ($quantidadeCamposPorTitulo[$campoFiltro["titulo_normalizado"]] ?? 0) + 1;
    $valoresPersonalizadosSelecionados = [];
    foreach ($filtrosEquipamentos["campos_personalizados"] as $filtroSelecionado)
        $valoresPersonalizadosSelecionados[implode(",", $filtroSelecionado["ids"])] = $filtroSelecionado["valor"];
?>

    <div class="card">
        <div class="card-body pe-md-3">

            <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
                <h5 class="mb-2 mb-md-0">Equipamentos</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-fill" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltrosEquipamentos" aria-controls="offcanvasFiltrosEquipamentos">
                        <span class="fas fa-filter me-1"></span>Filtros<?php if ($quantidadeFiltrosEquipamentos > 0) { ?><span class="badge bg-primary ms-1"><?= $quantidadeFiltrosEquipamentos; ?></span><?php } ?>
                    </button>
                    <div class="dropdown flex-fill">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= textoFiltroEquipamentos($opcoesFiltroSemaforo[$filtrosEquipamentos["semaforo"]][0]); ?>">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: <?= $opcoesFiltroSemaforo[$filtrosEquipamentos["semaforo"]][1]; ?>;"></span>Semáforo
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($opcoesFiltroSemaforo as $valorSemaforo => $opcaoSemaforo) {
                                $parametrosOpcaoSemaforo = $parametrosFiltroSemaforo;
                                if ($valorSemaforo === "")
                                    unset($parametrosOpcaoSemaforo["f_semaforo"]);
                                else
                                    $parametrosOpcaoSemaforo["f_semaforo"] = $valorSemaforo;
                                $linkOpcaoSemaforo = "./index.php?" . http_build_query($parametrosOpcaoSemaforo);
                            ?>
                                <a class="dropdown-item d-flex align-items-center<?= $filtrosEquipamentos["semaforo"] === $valorSemaforo ? " active" : ""; ?>" href="<?= textoFiltroEquipamentos($linkOpcaoSemaforo); ?>">
                                    <span class="d-inline-block rounded-circle me-2 flex-shrink-0" style="width: 12px; height: 12px; background-color: <?= $opcaoSemaforo[1]; ?>;"></span><?= textoFiltroEquipamentos($opcaoSemaforo[0]); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                        <a href="<?= $link; ?>p=equipamento" class="btn btn-padrao btn-sm flex-fill">
                            <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Novo
                        </a>
                    <?php } ?>
                    <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-start align-items-center gap-2 bg-light rounded px-3 py-2 mt-3 mb-2 me-md-1">
                <span class="text-700">Equipamentos encontrados</span>
                <span class="badge bg-primary fs--1"><?= number_format($totalEquipamentosFiltrados, 0, ",", "."); ?></span>
                <?php if ($quantidadeFiltrosEquipamentos > 0) { ?>
                    <a class="btn btn-outline-secondary btn-sm ms-2" href="<?= $linkLimparFiltrosEquipamentos; ?>">
                        <span class="fas fa-times me-1"></span>Limpar filtros
                    </a>
                <?php } ?>
            </div>

            <div class="table-responsive scrollbar pt-2 pe-md-1">
                <table class="dataTable table table-striped table-bordered mb-0" width="100%" cellspacing="0">
                    <thead class="bg-200 text-900">
                        <tr>
                            <th>N°</th>
                            <th>Nome</th>
                            <th>Local de Instalação</th>
                            <th>TAG</th>
                            <th>Categoria/Classe</th>
                            <th>Vencimento Interno</th>
                            <th>Vencimento Externo</th>
                            <th>Ações</th>
                            <th>Semáforo</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-200 text-900">
                        <tr>
                            <th>N°</th>
                            <th>Nome</th>
                            <th>Local de Instalação</th>
                            <th>TAG</th>
                            <th>Categoria/Classe</th>
                            <th>Vencimento Interno</th>
                            <th>Vencimento Externo</th>
                            <th>Ações</th>
                            <th>Semáforo</th>
                        </tr>
                    </tfoot>
                    <tbody class="list">
                        <?php

                        $where = "WHERE {$condicaoEquipamentosFiltrados}";

                        $sql = " SELECT e.id, e.numero_pasta, e.nome_equipamento, e.tag, l.titulo_local_instalacao, em.nome_empresa, u.titulo_unidade, c.titulo_categoria_equipamento, DATE_FORMAT(MIN(CASE WHEN LOWER(v.slug) LIKE 'vencimento-interno%' OR UPPER(TRIM(v.titulo)) = 'VENCIMENTO INTERNO' THEN STR_TO_DATE(LEFT(v.valor, 10), '%Y-%m-%d') END), '%d/%m/%Y') AS vencimento_interno, DATE_FORMAT(MIN(CASE WHEN LOWER(v.slug) LIKE 'vencimento-externo%' OR UPPER(TRIM(v.titulo)) = 'VENCIMENTO EXTERNO' THEN STR_TO_DATE(LEFT(v.valor, 10), '%Y-%m-%d') END), '%d/%m/%Y') AS vencimento_externo, DATE_FORMAT(MIN(CASE WHEN LOWER(v.slug) LIKE 'proxima-inspecao%' OR UPPER(TRIM(v.titulo)) = 'PRÓXIMA INSPEÇÃO' THEN STR_TO_DATE(LEFT(v.valor, 10), '%Y-%m-%d') END), '%d/%m/%Y') AS proxima_inspecao, DATE_FORMAT(MIN(CASE WHEN LOWER(v.slug) LIKE 'proxima-calibracao%' OR UPPER(TRIM(v.titulo)) = 'PRÓXIMA CALIBRAÇÃO' THEN CASE WHEN LEFT(TRIM(v.valor), 10) REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN STR_TO_DATE(LEFT(TRIM(v.valor), 10), '%d/%m/%Y') ELSE STR_TO_DATE(LEFT(TRIM(v.valor), 10), '%Y-%m-%d') END END), '%d/%m/%Y') AS proxima_calibracao, DATE_FORMAT(MIN(CASE WHEN LOWER(TRIM(v.slug)) REGEXP '^vencimento(-[0-9]+)?$' OR UPPER(TRIM(v.titulo)) = 'VENCIMENTO' THEN STR_TO_DATE(LEFT(TRIM(v.valor), 10), '%Y-%m-%d') END), '%d/%m/%Y') AS vencimento_geral FROM equipamentos e INNER JOIN empresas em ON em.id = e.id_empresa INNER JOIN unidades u ON u.id = e.id_unidade INNER JOIN locais_instalacao l ON l.id = e.id_local_instalacao LEFT JOIN categorias_equipamento c ON c.id = e.id_categoria_equipamento LEFT JOIN valores_campos_personalizados_equipamento v ON v.id_equipamento = e.id {$where} GROUP BY e.id ";

                        $qrEquipamentos = mysqli_query($connect, $sql);




                        $titleButton = permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "Ver " : "Editar";
                        while ($dadoEquipamento = mysqli_fetch_array($qrEquipamentos)) {

                            $dadosSemaforo = dadosSemaforoEquipamento($dadoEquipamento['vencimento_interno'], $dadoEquipamento['vencimento_externo'], $dadoEquipamento['proxima_inspecao'], $dadoEquipamento['proxima_calibracao'], $dadoEquipamento['vencimento_geral']);
                            $corSemaforo = $dadosSemaforo["cor"];
                            $textoTooltip = $dadosSemaforo["texto"];
                            echo "
                                <tr>
                                    <td>{$dadoEquipamento['numero_pasta']}</td>                                
                                    <td>{$dadoEquipamento['nome_equipamento']}</td>
                                    <td>{$dadoEquipamento['titulo_local_instalacao']}</td>                                  
                                    <td>{$dadoEquipamento['tag']}</td>
                                    <td>{$dadoEquipamento['titulo_categoria_equipamento']}</td>
                                    <td>{$dadoEquipamento['vencimento_interno']}</td>
                                    <td>{$dadoEquipamento['vencimento_externo']}</td>

                                    <td style='text-align: center; width: 150px!important;'>
                                        <div class='btn-group' role='group'>
                                            <a class='btn btn-white btn-sm' href='{$link}p=equipamentos&acao=editar&id={$dadoEquipamento['id']}'>
                                                <i class='bi-pencil-fill me-2'></i>{$titleButton}
                                            </a>

                                            " . (permissaoUsuario("Administrador", $sessaoUsuario["funcao"]) ? "
                                            <div class='btn-group'>
                                                <button type='button' class='btn btn-white dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false'></button>

                                                <div class='dropdown-menu dropdown-menu-end mt-1'>
                                                    <a class='dropdown-item' href='{$link}p=equipamentos&acao=excluir&id={$dadoEquipamento['id']}'>
                                                        <i class='bi-trash me-2 dropdown-item-icon'></i>Excluir
                                                    </a>
                                                </div>
                                            </div>" : "") . "
                                        </div>
                                    </td>
                                        <td style='text-align:center;'>

                                            <div 
                                                data-bs-toggle='tooltip' 
                                                data-bs-placement='top' 
                                                title='{$textoTooltip}'
                                                style='
                                                    cursor: pointer;
                                                    width:25px;
                                                    height:25px;
                                                    border-radius:50%;
                                                    background:{$corSemaforo};
                                                    display:inline-block;
                                                '
                                            ></div>

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

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosEquipamentos" aria-labelledby="tituloFiltrosEquipamentos">
        <div class="offcanvas-header border-bottom">
            <div>
                <h5 class="offcanvas-title" id="tituloFiltrosEquipamentos">Filtros dos equipamentos</h5>
                <p class="text-600 fs--1 mb-0">A lista exibirá somente os equipamentos encontrados.</p>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="./index.php" id="formFiltrosEquipamentos">
                <input type="hidden" name="p" value="equipamentos">
                <input type="hidden" name="empresa" value="<?= intval($empresaAtual) > 0 ? intval($empresaAtual) : ($sessaoUsuario["funcao"] == "Cliente" ? "todas" : ""); ?>">
                <?php if ($filtrosEquipamentos["semaforo"] !== "") { ?>
                    <input type="hidden" name="f_semaforo" value="<?= textoFiltroEquipamentos($filtrosEquipamentos["semaforo"]); ?>">
                <?php } ?>

                <?php
                $selectsFiltroEquipamentos = [
                    ["f_tipo", "Tipo de equipamento", $tiposFiltroEquipamentos, $filtrosEquipamentos["tipo"]],
                    ["f_categoria", "Categoria", $categoriasFiltroEquipamentos, $filtrosEquipamentos["categoria"]],
                    ["f_local", "Local de instalação", $locaisFiltroEquipamentos, $filtrosEquipamentos["local"]]
                ];
                foreach ($selectsFiltroEquipamentos as $selectFiltro) { ?>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $selectFiltro[0]; ?>"><?= $selectFiltro[1]; ?></label>
                        <select class="form-select" id="<?= $selectFiltro[0]; ?>" name="<?= $selectFiltro[0]; ?>">
                            <option value="">Todos</option>
                            <?php foreach ($selectFiltro[2] as $opcaoFiltro) { ?>
                                <option value="<?= $opcaoFiltro["id"]; ?>"<?= intval($selectFiltro[3]) === $opcaoFiltro["id"] ? " selected" : ""; ?>><?= textoFiltroEquipamentos($opcaoFiltro["titulo"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="mb-3">
                    <label class="form-label" for="f_situacao">Situação</label>
                    <select class="form-select" id="f_situacao" name="f_situacao">
                        <option value="">Todas</option>
                        <?php if ($sessaoUsuario["funcao"] == "Cliente") { ?>
                            <option value="apto"<?= $filtrosEquipamentos["situacao"] === "apto" ? " selected" : ""; ?>>Aptos</option>
                            <option value="inapto"<?= $filtrosEquipamentos["situacao"] === "inapto" ? " selected" : ""; ?>>Inaptos</option>
                        <?php } else { ?>
                            <option value="valido"<?= $filtrosEquipamentos["situacao"] === "valido" ? " selected" : ""; ?>>Válidos</option>
                            <option value="pendente"<?= $filtrosEquipamentos["situacao"] === "pendente" ? " selected" : ""; ?>>Pendentes</option>
                        <?php } ?>
                    </select>
                </div>

                <hr>
                <h6>Campos do equipamento</h6>
                <p class="text-600 fs--1">É possível combinar respostas de vários campos.</p>

                <?php foreach ($camposFiltroEquipamentos as $chaveCampo => $campoFiltro) {
                    $idsCampo = array_values($campoFiltro["ids"]);
                    sort($idsCampo);
                    $assinaturaCampo = implode(",", $idsCampo);
                    $valorSelecionado = $valoresPersonalizadosSelecionados[$assinaturaCampo] ?? "";
                    $idSelectCampo = "fle_" . substr(sha1($chaveCampo), 0, 10);
                    $tituloCampo = $campoFiltro["titulo"];
                    if ($quantidadeCamposPorTitulo[$campoFiltro["titulo_normalizado"]] > 1 && $campoFiltro["opcoes"] !== "")
                        $tituloCampo .= " — " . str_replace(",", " / ", $campoFiltro["opcoes"]);
                ?>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $idSelectCampo; ?>"><?= textoFiltroEquipamentos($tituloCampo); ?></label>
                        <select class="form-select filtro-personalizado-equipamentos" id="<?= $idSelectCampo; ?>" name="f_personalizado[]">
                            <option value="">Todos</option>
                            <?php foreach ($campoFiltro["valores"] as $valorCampo) {
                                $tokenCampo = tokenFiltroPersonalizadoEquipamentos($idsCampo, $valorCampo);
                            ?>
                                <option value="<?= textoFiltroEquipamentos($tokenCampo); ?>"<?= $valorSelecionado === $valorCampo ? " selected" : ""; ?>><?= textoFiltroEquipamentos($valorCampo); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="d-flex gap-2 sticky-bottom bg-white py-2">
                    <a class="btn btn-outline-secondary flex-fill" href="<?= $linkLimparFiltrosEquipamentos; ?>">Limpar</a>
                    <button class="btn btn-padrao flex-fill" type="submit"><span class="fas fa-filter me-1"></span>Aplicar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('formFiltrosEquipamentos').addEventListener('submit', function () {
            this.querySelectorAll('.filtro-personalizado-equipamentos').forEach(function (campo) {
                campo.disabled = campo.value === '';
            });
        });
    </script>

    <?php
} else {

    $id = @$_GET['id'];

    if ($acao == "editar") {
    ?>

        <div class="card">
            <?php

            $whereEquipamento = "";
            if (permissaoUsuario("Cliente", $sessaoUsuario["funcao"])) {
                $whereEquipamento = " AND " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $empresaAtual);
            }

            $qr = mysqli_query($connect, "SELECT e.* FROM equipamentos e WHERE e.id = '{$id}'{$whereEquipamento}");
            $dado = mysqli_fetch_array($qr);
            if (!$dado) {
            ?>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Equipamento nÃ£o encontrado</h5>
                    <a href="<?= "{$link}p=equipamentos"; ?>" class="btn btn-padrao btn-sm">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </a>
                </div>
                <div class="card-body bg-light">
                    <p class="mb-0">VocÃª nÃ£o tem acesso a este equipamento.</p>
                </div>
            <?php
            } else {
                if (empty($_SESSION["token_exclusao_arquivo_equipamento"]))
                    $_SESSION["token_exclusao_arquivo_equipamento"] = bin2hex(random_bytes(32));
                $tokenExclusaoArquivo = htmlspecialchars($_SESSION["token_exclusao_arquivo_equipamento"], ENT_QUOTES, "UTF-8");
                echo "<form action='./equipamentos/equipamento-acao.php?acao=editar&id=" . $dado['id'] . "' method='POST' class='needs-validation' novalidate='novalidate' enctype='multipart/form-data'>";
            ?>

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar equipamento</h5>
                    <a href="<?= "{$link}p=equipamentos"; ?>" class="btn btn-padrao btn-sm">
                        <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                    </a>
                </div>

                <?php

                $qrCamposVencimento = mysqli_query(
                    $connect,
                    "SELECT
                        DATE_FORMAT(MIN(CASE WHEN LOWER(slug) LIKE 'vencimento-interno%' OR UPPER(TRIM(titulo)) = 'VENCIMENTO INTERNO' THEN STR_TO_DATE(LEFT(valor, 10), '%Y-%m-%d') END), '%Y-%m-%d') AS vencimento_interno,
                        DATE_FORMAT(MIN(CASE WHEN LOWER(slug) LIKE 'vencimento-externo%' OR UPPER(TRIM(titulo)) = 'VENCIMENTO EXTERNO' THEN STR_TO_DATE(LEFT(valor, 10), '%Y-%m-%d') END), '%Y-%m-%d') AS vencimento_externo,
                        DATE_FORMAT(MIN(CASE WHEN LOWER(slug) LIKE 'proxima-inspecao%' OR UPPER(TRIM(titulo)) = 'PRÓXIMA INSPEÇÃO' THEN STR_TO_DATE(LEFT(valor, 10), '%Y-%m-%d') END), '%Y-%m-%d') AS proxima_inspecao,
                        DATE_FORMAT(MIN(CASE WHEN LOWER(slug) LIKE 'proxima-calibracao%' OR UPPER(TRIM(titulo)) = 'PRÓXIMA CALIBRAÇÃO' THEN CASE WHEN LEFT(TRIM(valor), 10) REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN STR_TO_DATE(LEFT(TRIM(valor), 10), '%d/%m/%Y') ELSE STR_TO_DATE(LEFT(TRIM(valor), 10), '%Y-%m-%d') END END), '%Y-%m-%d') AS proxima_calibracao,
                        DATE_FORMAT(MIN(CASE WHEN LOWER(TRIM(slug)) REGEXP '^vencimento(-[0-9]+)?$' OR UPPER(TRIM(titulo)) = 'VENCIMENTO' THEN STR_TO_DATE(LEFT(TRIM(valor), 10), '%Y-%m-%d') END), '%Y-%m-%d') AS vencimento_geral
            FROM valores_campos_personalizados_equipamento 
            WHERE id_equipamento = '{$dado["id"]}'"
                );

                $vencimentosEquipamento = $qrCamposVencimento ? mysqli_fetch_assoc($qrCamposVencimento) : [];

                $dadosSemaforo = dadosSemaforoEquipamento($vencimentosEquipamento["vencimento_interno"] ?? "", $vencimentosEquipamento["vencimento_externo"] ?? "", $vencimentosEquipamento["proxima_inspecao"] ?? "", $vencimentosEquipamento["proxima_calibracao"] ?? "", $vencimentosEquipamento["vencimento_geral"] ?? "");
                $corSemaforo = $dadosSemaforo["cor"];
                $textoSemaforo = $dadosSemaforo["texto"];

                ?>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">


                        <h5 class="mb-0">Semáforo de vencimento:</h5>
                        <div
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="<?= $textoSemaforo; ?>"
                            style="
                    width:20px;
                    height:20px;
                    border-radius:50%;
                    background-color:<?= $corSemaforo; ?>;
                    display:inline-block;
                "></div>

                    </div>

                </div>

                <div class="card-body bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg">
                            <label class="form-label" for="txtNome">Nome*</label>
                            <input type="text" id="txtNome" class="form-control" name="nome" required="required" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?> autocomplete="off" maxlength="250" value="<?= $dado['nome_equipamento']; ?>">
                        </div>
                        <div class="col col-lg-auto" style="width: 120px;">
                            <label class="form-label" for="txtNumeroPasta">Nº pasta*</label>
                            <input type="number" id="txtNumeroPasta" class="form-control" name="numeroPasta" required="required" autocomplete="off" min="1" value="<?= $dado['numero_pasta']; ?>" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                        </div>
                        <div class="col col-lg-auto" style="width: 150px;">
                            <label class="form-label" for="txtTag">TAG*</label>
                            <input type="text" id="txtTag" class="form-control" name="tag" required="required" autocomplete="off" maxlength="250" value="<?= $dado['tag']; ?>" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6 col-lg">
                            <label class="form-label" for="slcTipoEquipamento">Tipo equipamento*</label>
                            <select id="slcTipoEquipamento" class="form-select selectpicker" name="tipoEquipamento" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                                <?php
                                $optionsTiposEquipamento = "<option value='' selected disabled>Selecione...</option>";
                                $qrTiposEquipamento = mysqli_query($connect, "SELECT id, titulo_tipo_equipamento FROM tipos_equipamento");
                                while ($dadoTipoEquipamento = mysqli_fetch_array($qrTiposEquipamento)) {
                                    $optionsTiposEquipamento .= "<option value='{$dadoTipoEquipamento['id']}'>{$dadoTipoEquipamento['titulo_tipo_equipamento']}</option>";
                                }
                                $optionsTiposEquipamento = str_replace("value='{$dado['id_tipo_equipamento']}'", "value='{$dado['id_tipo_equipamento']}' selected", $optionsTiposEquipamento);

                                echo $optionsTiposEquipamento;
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <label class="form-label" for="slcEmpresa">Empresa*</label>
                            <select id="slcEmpresa" class="form-select selectpicker" name="empresa" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
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
                        <div class="col-12 col-md-6 col-lg">
                            <label class="form-label" for="slcUnidade">Unidade*</label>
                            <select id="slcUnidade" class="form-select selectpicker" name="unidade" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
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
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg">
                            <label class="form-label" for="slcCategoria">Categoria*</label>
                            <?php
                            $temSelecionado = false;
                            ?>

                            <select id="slcCategoria" class="form-select selectpicker" name="categoriaEquipamento" required="required"
                                <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : "" ?>>

                                <option value="" disabled <?= empty($dado['id_categoria_equipamento']) ? "selected" : "" ?>>
                                    Selecione...
                                </option>

                                <?php
                                $qrCategorias = mysqli_query($connect, "SELECT id, titulo_categoria_equipamento FROM categorias_equipamento WHERE id_empresa = '{$dado['id_empresa']}'");

                                while ($dadoCategorias = mysqli_fetch_array($qrCategorias)) {
                                    $selected = "";

                                    if ($dadoCategorias['id'] == $dado['id_categoria_equipamento']) {
                                        $selected = "selected";
                                        $temSelecionado = true;
                                    }

                                    echo "<option value='{$dadoCategorias['id']}' $selected>
                                        {$dadoCategorias['titulo_categoria_equipamento']}
                                    </option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <a id="btnAddCategoria" class="btn btn-falcon-padrao w-100" href="<?= $link ?>p=categoria-equipamento" target="_blank">Adicionar Categoria</a>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <label class="form-label" for="slcLocalInstalacao">Local de instalação*</label>
                            <select id="slcLocalInstalacao" class="form-select selectpicker" name="localInstalacaoEquipamento" required="required" data-options='{"placeholder":"Selecione...", "language": { "noResults": "Nenhum resultado encontrado"} }' <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                                <?php
                                $optionsLocalInstalacao = "<option value='' selected disabled>Selecione...</option>";
                                $qrLocalInstalacao = mysqli_query($connect, "SELECT id, titulo_local_instalacao FROM locais_instalacao WHERE id_empresa = '{$dado['id_empresa']}'");
                                while ($dadoLocalInstalacao = mysqli_fetch_array($qrLocalInstalacao)) {
                                    $optionsLocalInstalacao .= "<option value='{$dadoLocalInstalacao['id']}'>{$dadoLocalInstalacao['titulo_local_instalacao']}</option>";
                                }
                                $optionsLocalInstalacao = str_replace("value='{$dado['id_local_instalacao']}'", "value='{$dado['id_local_instalacao']}' selected", $optionsLocalInstalacao);
                                echo $optionsLocalInstalacao;
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <a id="btnAddLocal" class="btn btn-falcon-padrao w-100" href="<?= $link ?>p=local-instalacao" target="_blank">Adicionar Local</a>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md">
                            <label class="form-label" for="txtFabricante">Fabricante</label>
                            <input type="text" id="txtFabricante" class="form-control" name="fabricante" autocomplete="off" maxlength="250" value="<?= $dado['fabricante']; ?>" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="form-label" for="txtAnoFabricacao">Ano de Fabricação</label>
                            <input type="number" id="txtAnoFabricacao" class="form-control" name="anoFabricacao" min="1901" max="<?= date("Y"); ?>" value="<?= $dado['ano_fabricacao']; ?>" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                        </div>
                        <div class="col-12 col-md">
                            <label class="form-label" for="txtMaterial">Material</label>
                            <input type="text" id="txtMaterial" class="form-control" name="material" autocomplete="off" maxlength="250" value="<?= $dado['material']; ?>" <?= permissaoUsuario("Cliente", $sessaoUsuario["funcao"]) ? "disabled" : ""  ?>>
                        </div>
                    </div>

                    <div id="campos-nao-fixos">
                        <?php
                        echo '<div class="row g-3 mb-3">';

                        $valoresCampos = [];
                        $qrValoresCampos = mysqli_query($connect, "SELECT slug, valor FROM valores_campos_personalizados_equipamento WHERE id_equipamento = '{$dado["id"]}'");

                        while ($dadoValoresCampos = mysqli_fetch_assoc($qrValoresCampos)) {
                            $valoresCampos[$dadoValoresCampos["slug"]] = $dadoValoresCampos["valor"];
                        }

                        $qrCamposPersonalizadosEquipamento = mysqli_query($connect, "SELECT * FROM campos_tipo_equipamento WHERE id_tipo_equipamento = '{$dado["id_tipo_equipamento"]}'");
                        $dadosCamposPersonalizadosEquipamento = mysqli_fetch_all($qrCamposPersonalizadosEquipamento, MYSQLI_ASSOC);


                        foreach ($dadosCamposPersonalizadosEquipamento as $dadosCampoPersonalizadoEquipamento) {

                            $valorCampoAtual = $valoresCampos[$dadosCampoPersonalizadoEquipamento["slug"]];

                            $obrigatorio = $required =  "";
                            if ($dadosCampoPersonalizadoEquipamento["obrigatorio"]) {
                                $obrigatorio = "<span>*</span>";
                                $required = "required";
                            }

                            $campo = "
                            <label class='form-label fw-semibold' for='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                            <input id='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' type='text' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' $required value='{$valorCampoAtual}' {$disabledCliente} >    
                        ";
                            if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Texto longo") {
                                $campo = "
                                <label class='form-label fw-semibold' for='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <textarea id='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' rows='4' $required {$disabledCliente} >{$valorCampoAtual} </textarea>    
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Múltipla escolha") {
                                $opcoes = "";
                                foreach (explode(",", $dadosCampoPersonalizadoEquipamento["opcoes"]) as $key => $value) {
                                    $checked = $valorCampoAtual == $value ? "checked" : "";

                                    $opcoes .= "
                                    <div class='form-check d-flex align-items-center gap-2 p-0 m-0'>
                                        <input class='form-check-input m-0' id='rdo_{$dadosCampoPersonalizadoEquipamento["slug"]}_{$key}' type='radio' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' value='{$value}' $required $checked $disabledCliente />
                                        <label class='form-check-label m-0' for='rdo_{$dadosCampoPersonalizadoEquipamento["slug"]}_{$key}'>{$value}</label>
                                    </div>
                                ";
                                }

                                $campo = "
                                <label class='form-label fw-semibold'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <div class='d-flex flex-wrap gap-3'> 
                                    {$opcoes}
                                </div>   
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Caixa de seleção") {
                                $valoresSelecionados = explode(", ", $valorCampoAtual);

                                $opcoes = "";
                                foreach (explode(",", $dadosCampoPersonalizadoEquipamento["opcoes"]) as $key => $value) {
                                    $checked = in_array($value, $valoresSelecionados) ? "checked" : "";

                                    $opcoes .= "
                                    <div class='form-check m-0'>
                                        <input class='form-check-input' id='rdo_{$dadosCampoPersonalizadoEquipamento["slug"]}_{$key}' type='checkbox' name='{$dadosCampoPersonalizadoEquipamento["slug"]}[]' value='{$value}' $required $checked  $disabledCliente/>
                                        <label class='form-check-label' for='rdo_{$dadosCampoPersonalizadoEquipamento["slug"]}_{$key}'>{$value}</label>
                                    </div>
                                ";
                                }

                                $campo = "
                                <label class='form-label fw-semibold'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <div> 
                                    {$opcoes}
                                </div>   
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Lista suspensa") {
                                $opcoes = "<option value='' disabled selected>Selecione...</option>";
                                $dataOptions = '"minimumResultsForSearch": -1';

                                $multiple = $multiplo = "";
                                if ($dadosCampoPersonalizadoEquipamento["multiplo"]) {
                                    $multiple = "multiple";
                                    $multiplo = "[]";

                                    $opcoes = "";
                                    $dataOptions = '"placeholder": "Selecione..."';
                                }

                                $valoresSelecionados = explode(", ", $valorCampoAtual);

                                foreach (explode(",", $dadosCampoPersonalizadoEquipamento["opcoes"]) as $value) {
                                    $selected = in_array($value, $valoresSelecionados) ? "selected" : "";
                                    $opcoes .= "<option value='{$value}' {$selected}>{$value}</option>";
                                }

                                $campo = "
                                <label class='form-label fw-semibold' for='slc_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <select id='slc_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-select selectpicker' name='{$dadosCampoPersonalizadoEquipamento["slug"]}$multiplo' $required $multiple data-options='{{$dataOptions}}' $disabledCliente> 
                                    {$opcoes}
                                </select>   
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Upload de arquivo") {
                                $required = $obrigatorio = ""; // Remover obrigatoriedade deste campo

                                $multiple = $dadosCampoPersonalizadoEquipamento["multiplo"] ? "multiple" : "";

                                $accept = "";
                                if ($dadosCampoPersonalizadoEquipamento["tipo_arquivo"] != "" && $dadosCampoPersonalizadoEquipamento["tipo_arquivo"] != "Qualquer tipo")
                                    $accept = "accept='{$dadosCampoPersonalizadoEquipamento["tipo_arquivo"]}'";

                                $linksArquivos = linksArquivosEquipamento($dado["id"], $dadosCampoPersonalizadoEquipamento["slug"], $valorCampoAtual, permissaoUsuario("Administrador", $sessaoUsuario["funcao"]));
                                $campo = "<label class='form-label fw-semibold' for='fle_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>{$linksArquivos}";
                                if (!permissaoUsuario("Cliente", $sessaoUsuario["funcao"]))
                                    $campo .= "<input id='fle_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' type='file' name='{$dadosCampoPersonalizadoEquipamento["slug"]}[]' $required $multiple $accept>";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Data e Hora") {
                                $campo = "
                                <label class='form-label fw-semibold' for='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <input id='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' type='datetime-local' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' $required value='{$valorCampoAtual}' $disabledCliente>    
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Data") {
                                $campo = "
                                <label class='form-label fw-semibold' for='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <input id='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' type='date' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' $required value='{$valorCampoAtual}'  $disabledCliente>    
                            ";
                            } else if ($dadosCampoPersonalizadoEquipamento["tipo"] == "Horário") {
                                $campo = "
                                <label class='form-label fw-semibold' for='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}'>{$dadosCampoPersonalizadoEquipamento["titulo_campo"]}$obrigatorio</label>
                                <input id='txt_{$dadosCampoPersonalizadoEquipamento["slug"]}' class='form-control' type='time' name='{$dadosCampoPersonalizadoEquipamento["slug"]}' $required value='{$valorCampoAtual}' $disabledCliente>    
                            ";
                            }

                            echo "<div class='col-12 col-md-6 col-lg-4'>$campo</div>";
                        }

                        echo "
                        </div>
                    <input type='hidden' name='campos' value='" . json_encode($dadosCamposPersonalizadosEquipamento) . "' $disabledCliente>";
                        ?>
                    </div>

                    <?php
                    if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <button class="btn btn-padrao" type="submit">Salvar</button>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                </form>
                <?php if (permissaoUsuario("Administrador", $sessaoUsuario["funcao"])) { ?>
                    <form id="formExcluirArquivoEquipamento" action="./equipamentos/equipamento-acao.php?acao=excluir-arquivo&amp;id=<?= intval($dado["id"]); ?>" method="post">
                        <input type="hidden" name="token_exclusao_arquivo" value="<?= $tokenExclusaoArquivo; ?>">
                    </form>
                <?php } ?>
        </div>

        <script>
            var valoresCampos = <?= json_encode($valoresCampos); ?>;
        </script>
        <script src="./assets/js/equipamentos/equipamentos.js<?= $version; ?>"></script>

    <?php
            }
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
                    <h3 class="text-padrao">Tem certeza de que deseja excluir este equipamento?</h3>
                    <?php
                    $whereEquipamento = "";
                    if (permissaoUsuario("Cliente", $sessaoUsuario["funcao"])) {
                        $whereEquipamento = " AND " . condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $empresaAtual);
                    }
                    $qr = mysqli_query($connect, "SELECT e.*, ce.titulo_categoria_equipamento, li.titulo_local_instalacao, em.nome_empresa, u.titulo_unidade FROM equipamentos e INNER JOIN empresas em ON em.id = e.id_empresa INNER JOIN unidades u ON u.id = e.id_unidade INNER JOIN categorias_equipamento ce ON ce.id = e.id_categoria_equipamento INNER JOIN locais_instalacao li ON li.id = e.id_local_instalacao WHERE e.id = '{$id}'{$whereEquipamento}");
                    $dado = mysqli_fetch_array($qr);

                    if (!$dado) {
                        echo "<p class='lead mb-0'>VocÃª nÃ£o tem acesso a este equipamento.</p>";
                    } else {
                        echo "<form action='./equipamentos/equipamento-acao.php?acao=excluir&id=" . $dado['id'] . "' method='POST'>"
                    ?>
                        <p class="lead">
                            <?php
                            echo "Título: " . $dado['nome_equipamento'] . "<br>Nº pasta: " . $dado['numero_pasta'] . "<br>TAG: " . $dado['tag'] . "<br>Categoria: " . $dado['titulo_categoria_equipamento'] . "<br>Local de instalação: " . $dado['titulo_local_instalacao'] . "<br>Empresa: " . $dado['nome_empresa'] . "<br>Unidade: " . $dado['titulo_unidade'];
                            ?>
                        </p>
                        <button id="btnExcluir" type="submit" class="btn btn-falcon-padrao">Confirmar exclusão</button>

                        </form>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
<?php
        }
    }
?>
