<?php
$ehAdministrador = permissaoUsuario("Administrador", $sessaoUsuario["funcao"]);
$coresGraficos = ["#2c7be5", "#00d27a", "#f5803e", "#e63757", "#6f42c1", "#27bcfd", "#f9c74f", "#577590", "#43aa8b", "#f94144"];

function valorContagem($connect, $sql)
{
    $resultado = mysqli_query($connect, $sql);
    $dado = $resultado ? mysqli_fetch_row($resultado) : [0];
    return intval($dado[0] ?? 0);
}

function textoDashboard($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function chaveSemAcentosDashboard($valor)
{
    $valor = mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) $valor)), "UTF-8");
    return strtr($valor, [
        "Á" => "A", "À" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A",
        "É" => "E", "È" => "E", "Ê" => "E", "Ë" => "E",
        "Í" => "I", "Ì" => "I", "Î" => "I", "Ï" => "I",
        "Ó" => "O", "Ò" => "O", "Ô" => "O", "Õ" => "O", "Ö" => "O",
        "Ú" => "U", "Ù" => "U", "Û" => "U", "Ü" => "U", "Ç" => "C"
    ]);
}

function montarCategoriasGraficoDashboard($connect, $condicaoEquipamentos)
{
    $categorias = [];
    $qrCategorias = mysqli_query($connect, "SELECT COALESCE(NULLIF(TRIM(ce.titulo_categoria_equipamento), ''), 'Sem categoria') AS categoria FROM equipamentos e LEFT JOIN categorias_equipamento ce ON ce.id = e.id_categoria_equipamento WHERE {$condicaoEquipamentos}");

    while ($qrCategorias && $dadoCategoria = mysqli_fetch_assoc($qrCategorias)) {
        $categoria = mb_strtoupper(preg_replace('/\s+/u', ' ', trim($dadoCategoria["categoria"])), "UTF-8");
        $chaveCategoria = chaveSemAcentosDashboard($categoria);
        $categoriasEquivalentes = [
            "VASO DE PRESSAO" => "VASO DE PRESSÃO",
            "VASOS DE PRESSAO" => "VASO DE PRESSÃO",
            "MANOMETRO" => "MANÔMETRO",
            "MANOMETROS" => "MANÔMETRO",
            "VALVULA DE SEGURANCA" => "VÁLVULA DE SEGURANÇA",
            "VALVULAS DE SEGURANCA" => "VÁLVULA DE SEGURANÇA"
        ];
        $categoria = $categoriasEquivalentes[$chaveCategoria] ?? $categoria;
        $categorias[$categoria] = ($categorias[$categoria] ?? 0) + 1;
    }

    arsort($categorias);
    $dadosGrafico = [];
    foreach ($categorias as $categoria => $total)
        $dadosGrafico[] = ["name" => $categoria, "value" => $total];

    return $dadosGrafico;
}

function tokenFiltroPersonalizadoDashboard($idsCampos, $valor)
{
    $json = json_encode([array_values($idsCampos), $valor], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return rtrim(strtr(base64_encode($json), "+/", "-_"), "=");
}

function dadosTokenFiltroPersonalizadoDashboard($token)
{
    $token = strtr((string) $token, "-_", "+/");
    $token .= str_repeat("=", (4 - strlen($token) % 4) % 4);
    $dados = json_decode(base64_decode($token, true), true);

    if (!is_array($dados) || count($dados) !== 2)
        return null;

    $tituloLegado = is_string($dados[0]) ? trim($dados[0]) : "";
    $idsCampos = array_values(array_unique(array_filter(array_map("intval", is_array($dados[0]) ? $dados[0] : []))));
    $valor = trim((string) $dados[1]);
    sort($idsCampos);
    return (count($idsCampos) > 0 || $tituloLegado !== "") && $valor !== "" ? ["ids" => $idsCampos, "titulo_legado" => $tituloLegado, "valor" => $valor] : null;
}

function condicaoFiltrosDashboard($connect, $condicaoBase, $filtros, $alias = "e")
{
    $condicoes = ["({$condicaoBase})"];
    $camposNumericos = [
        "tipo" => "id_tipo_equipamento",
        "categoria" => "id_categoria_equipamento",
        "local" => "id_local_instalacao"
    ];

    foreach ($camposNumericos as $filtro => $coluna) {
        $valor = intval($filtros[$filtro] ?? 0);
        if ($valor > 0)
            $condicoes[] = "{$alias}.{$coluna} = '{$valor}'";
    }

    if (($filtros["situacao"] ?? "") === "valido")
        $condicoes[] = "EXISTS (SELECT 1 FROM inspecoes fd_i WHERE fd_i.id = (SELECT MAX(fd_i2.id) FROM inspecoes fd_i2 WHERE fd_i2.id_equipamento = {$alias}.id) AND fd_i.status_inspecao = 'Aprovado')";
    elseif (($filtros["situacao"] ?? "") === "pendente")
        $condicoes[] = "NOT EXISTS (SELECT 1 FROM inspecoes fd_i WHERE fd_i.id = (SELECT MAX(fd_i2.id) FROM inspecoes fd_i2 WHERE fd_i2.id_equipamento = {$alias}.id) AND fd_i.status_inspecao = 'Aprovado')";
    elseif (($filtros["situacao"] ?? "") === "apto")
        $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fd_status_v INNER JOIN campos_tipo_equipamento fd_status_c ON fd_status_c.id = fd_status_v.id_campo_tipo_equipamento WHERE fd_status_v.id_equipamento = {$alias}.id AND UPPER(TRIM(fd_status_c.titulo_campo)) = 'STATUS DO EQUIPAMENTO' AND UPPER(TRIM(fd_status_v.valor)) = 'EQUIPAMENTO APTO')";
    elseif (($filtros["situacao"] ?? "") === "inapto")
        $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fd_status_v INNER JOIN campos_tipo_equipamento fd_status_c ON fd_status_c.id = fd_status_v.id_campo_tipo_equipamento WHERE fd_status_v.id_equipamento = {$alias}.id AND UPPER(TRIM(fd_status_c.titulo_campo)) = 'STATUS DO EQUIPAMENTO' AND UPPER(TRIM(fd_status_v.valor)) = 'EQUIPAMENTO INAPTO')";

    foreach (($filtros["campos_personalizados"] ?? []) as $filtroPersonalizado) {
        $idsCampos = array_values(array_filter(array_map("intval", $filtroPersonalizado["ids"] ?? [])));
        $valorCampo = mysqli_real_escape_string($connect, trim($filtroPersonalizado["valor"] ?? ""));
        if (count($idsCampos) > 0 && $valorCampo !== "")
            $condicoes[] = "EXISTS (SELECT 1 FROM valores_campos_personalizados_equipamento fd_v WHERE fd_v.id_equipamento = {$alias}.id AND fd_v.id_campo_tipo_equipamento IN (" . implode(",", $idsCampos) . ") AND (fd_v.valor = '{$valorCampo}' OR FIND_IN_SET('{$valorCampo}', REPLACE(fd_v.valor, ', ', ',')) > 0))";
    }

    return implode(" AND ", $condicoes);
}

function montarGraficosCamposEquipamento($connect, $condicaoEmpresas, $limite = 0)
{
    $camposAgrupados = [];
    $qrCampos = mysqli_query($connect, "SELECT v.id AS id_valor, e.id AS id_equipamento, c.titulo_campo AS titulo, c.opcoes, c.tipo, v.valor FROM valores_campos_personalizados_equipamento v INNER JOIN campos_tipo_equipamento c ON c.id = v.id_campo_tipo_equipamento INNER JOIN equipamentos e ON e.id = v.id_equipamento WHERE {$condicaoEmpresas} AND v.valor IS NOT NULL AND TRIM(v.valor) <> '' AND c.tipo IN ('Múltipla escolha', 'Caixa de seleção')");

    while ($qrCampos && $dadoCampo = mysqli_fetch_assoc($qrCampos)) {
        $tituloCampo = trim($dadoCampo["titulo"]);
        $tituloNormalizado = mb_strtoupper($tituloCampo, "UTF-8");

        if (in_array($tituloNormalizado, ["TIPO DE INSPEÇÃO EXECUTADA", "TIPOS DE INSPEÇÃO EXECUTADA"], true)) {
            $tituloCampo = "TIPO DE INSPEÇÃO EXECUTADA";
            $tituloNormalizado = $tituloCampo;
        }

        $opcoesCampo = trim($dadoCampo["opcoes"] ?? "");
        $chaveCampo = $tituloNormalizado . "|" . mb_strtoupper(preg_replace('/\s+/u', '', $opcoesCampo), "UTF-8");

        if (!isset($camposAgrupados[$chaveCampo])) {
            $camposAgrupados[$chaveCampo] = [
                "titulo" => $tituloCampo,
                "titulo_normalizado" => $tituloNormalizado,
                "opcoes" => $opcoesCampo,
                "multipla" => stripos($dadoCampo["tipo"], "Caixa") === 0,
                "equipamentos" => [],
                "valores" => [],
                "respostas_unicas" => []
            ];
        }

        $idEquipamento = intval($dadoCampo["id_equipamento"]);
        $camposAgrupados[$chaveCampo]["equipamentos"][$idEquipamento] = true;
        $campoMultiplo = stripos($dadoCampo["tipo"], "Caixa") === 0;
        $valores = $campoMultiplo ? preg_split('/,\s*/u', $dadoCampo["valor"]) : [$dadoCampo["valor"]];

        foreach ($valores as $valor) {
            $valor = trim($valor);
            if ($valor == "")
                continue;

            if (!$campoMultiplo) {
                $respostaAnterior = $camposAgrupados[$chaveCampo]["respostas_unicas"][$idEquipamento] ?? null;
                if ($respostaAnterior !== null && intval($respostaAnterior["id"]) >= intval($dadoCampo["id_valor"]))
                    continue;
                if ($respostaAnterior !== null)
                    unset($camposAgrupados[$chaveCampo]["valores"][$respostaAnterior["valor"]][$idEquipamento]);
                $camposAgrupados[$chaveCampo]["respostas_unicas"][$idEquipamento] = ["id" => intval($dadoCampo["id_valor"]), "valor" => $valor];
            }

            if (!isset($camposAgrupados[$chaveCampo]["valores"][$valor]))
                $camposAgrupados[$chaveCampo]["valores"][$valor] = [];
            $camposAgrupados[$chaveCampo]["valores"][$valor][$idEquipamento] = true;
        }
    }

    $quantidadePorTitulo = [];
    foreach ($camposAgrupados as $campo)
        $quantidadePorTitulo[$campo["titulo_normalizado"]] = ($quantidadePorTitulo[$campo["titulo_normalizado"]] ?? 0) + 1;

    $graficos = [];
    foreach ($camposAgrupados as $campo) {
        $campo["valores"] = array_filter($campo["valores"], function ($equipamentos) {
            return count($equipamentos) > 0;
        });
        if (count($campo["valores"]) === 0)
            continue;

        $dados = [];
        foreach ($campo["valores"] as $valor => $equipamentos)
            $dados[] = ["name" => $valor, "value" => count($equipamentos)];

        usort($dados, function ($a, $b) {
            return $b["value"] <=> $a["value"];
        });
        $totalRespostas = array_sum(array_column($dados, "value"));

        $tituloGrafico = $campo["titulo"];
        if ($quantidadePorTitulo[$campo["titulo_normalizado"]] > 1 && $campo["opcoes"] !== "")
            $tituloGrafico .= " — " . str_replace(",", " / ", $campo["opcoes"]);

        $graficos[] = [
            "titulo" => $tituloGrafico,
            "cobertura" => count($campo["equipamentos"]),
            "total_respostas" => $totalRespostas,
            "multipla" => $campo["multipla"],
            "barra" => $campo["multipla"] || count($dados) > 8,
            "prioridade" => strpos($campo["titulo_normalizado"], "INSPEÇÃO EXECUTADA") !== false ? 0 : 1,
            "dados" => $dados
        ];
    }

    usort($graficos, function ($a, $b) {
        if ($a["prioridade"] != $b["prioridade"])
            return $a["prioridade"] <=> $b["prioridade"];
        return $b["cobertura"] <=> $a["cobertura"];
    });

    return $limite > 0 ? array_slice($graficos, 0, $limite) : $graficos;
}

$filtrosPersonalizadosDashboard = [];
foreach ((array) ($_GET["f_personalizado"] ?? []) as $tokenFiltroPersonalizado) {
    $dadosFiltroPersonalizado = dadosTokenFiltroPersonalizadoDashboard($tokenFiltroPersonalizado);
    if ($dadosFiltroPersonalizado !== null)
        $filtrosPersonalizadosDashboard[] = $dadosFiltroPersonalizado;
}

$situacoesPermitidasDashboard = $ehAdministrador ? ["valido", "pendente"] : ["apto", "inapto"];
$filtrosDashboard = [
    "tipo" => intval($_GET["f_tipo"] ?? 0),
    "categoria" => intval($_GET["f_categoria"] ?? 0),
    "local" => intval($_GET["f_local"] ?? 0),
    "situacao" => in_array($_GET["f_situacao"] ?? "", $situacoesPermitidasDashboard, true) ? $_GET["f_situacao"] : "",
    "campos_personalizados" => $filtrosPersonalizadosDashboard
];

if ($ehAdministrador) {
    $condicaoBaseDashboard = intval($empresaAtual) > 0 ? "e.id_empresa = '" . intval($empresaAtual) . "'" : "1 = 1";
} else {
    $condicaoBaseDashboard = condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $empresaAtual);
}

foreach ($filtrosDashboard["campos_personalizados"] as &$filtroPersonalizadoDashboard) {
    if (count($filtroPersonalizadoDashboard["ids"]) > 0 || $filtroPersonalizadoDashboard["titulo_legado"] === "")
        continue;

    $tituloLegado = mysqli_real_escape_string($connect, $filtroPersonalizadoDashboard["titulo_legado"]);
    $condicaoTituloLegado = in_array(mb_strtoupper($filtroPersonalizadoDashboard["titulo_legado"], "UTF-8"), ["TIPO DE INSPEÇÃO EXECUTADA", "TIPOS DE INSPEÇÃO EXECUTADA"], true)
        ? "c.titulo_campo IN ('TIPO DE INSPEÇÃO EXECUTADA', 'TIPOS DE INSPEÇÃO EXECUTADA')"
        : "c.titulo_campo = '{$tituloLegado}'";
    $qrIdsCamposLegados = mysqli_query($connect, "SELECT DISTINCT c.id FROM campos_tipo_equipamento c INNER JOIN valores_campos_personalizados_equipamento v ON v.id_campo_tipo_equipamento = c.id INNER JOIN equipamentos e ON e.id = v.id_equipamento WHERE {$condicaoBaseDashboard} AND {$condicaoTituloLegado}");
    while ($qrIdsCamposLegados && $dadoIdCampoLegado = mysqli_fetch_assoc($qrIdsCamposLegados))
        $filtroPersonalizadoDashboard["ids"][] = intval($dadoIdCampoLegado["id"]);
    $filtroPersonalizadoDashboard["ids"] = array_values(array_unique($filtroPersonalizadoDashboard["ids"]));
    sort($filtroPersonalizadoDashboard["ids"]);
}
unset($filtroPersonalizadoDashboard);
$filtrosDashboard["campos_personalizados"] = array_values(array_filter($filtrosDashboard["campos_personalizados"], function ($filtro) {
    return count($filtro["ids"]) > 0;
}));

$condicaoEquipamentosDashboard = condicaoFiltrosDashboard($connect, $condicaoBaseDashboard, $filtrosDashboard);
$quantidadeFiltrosDashboard = 0;
foreach (["tipo", "categoria", "local", "situacao"] as $nomeFiltroDashboard) {
    $valorFiltroDashboard = $filtrosDashboard[$nomeFiltroDashboard];
    if ($valorFiltroDashboard !== "" && $valorFiltroDashboard !== 0)
        $quantidadeFiltrosDashboard++;
}
$quantidadeFiltrosDashboard += count($filtrosDashboard["campos_personalizados"]);
$linkLimparFiltrosDashboard = intval($empresaAtual) > 0
    ? "./index.php?empresa=" . intval($empresaAtual) . "&p=home"
    : ($ehAdministrador ? "./index.php?p=home" : "./index.php?empresa=todas&p=home");

$tiposFiltroDashboard = [];
$qrTiposFiltro = mysqli_query($connect, "SELECT DISTINCT t.id, t.titulo_tipo_equipamento AS titulo FROM equipamentos e INNER JOIN tipos_equipamento t ON t.id = e.id_tipo_equipamento WHERE {$condicaoBaseDashboard} ORDER BY titulo");
while ($qrTiposFiltro && $dadoFiltro = mysqli_fetch_assoc($qrTiposFiltro))
    $tiposFiltroDashboard[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

$categoriasFiltroDashboard = [];
$qrCategoriasFiltro = mysqli_query($connect, "SELECT DISTINCT c.id, c.titulo_categoria_equipamento AS titulo FROM equipamentos e INNER JOIN categorias_equipamento c ON c.id = e.id_categoria_equipamento WHERE {$condicaoBaseDashboard} ORDER BY titulo");
while ($qrCategoriasFiltro && $dadoFiltro = mysqli_fetch_assoc($qrCategoriasFiltro))
    $categoriasFiltroDashboard[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

$locaisFiltroDashboard = [];
$qrLocaisFiltro = mysqli_query($connect, "SELECT DISTINCT l.id, l.titulo_local_instalacao AS titulo FROM equipamentos e INNER JOIN locais_instalacao l ON l.id = e.id_local_instalacao WHERE {$condicaoBaseDashboard} ORDER BY titulo");
while ($qrLocaisFiltro && $dadoFiltro = mysqli_fetch_assoc($qrLocaisFiltro))
    $locaisFiltroDashboard[] = ["id" => intval($dadoFiltro["id"]), "titulo" => $dadoFiltro["titulo"]];

$camposFiltroDashboard = [];
$qrCamposFiltro = mysqli_query($connect, "SELECT c.id AS id_campo, c.titulo_campo AS titulo, c.opcoes, c.tipo, v.valor FROM valores_campos_personalizados_equipamento v INNER JOIN campos_tipo_equipamento c ON c.id = v.id_campo_tipo_equipamento INNER JOIN equipamentos e ON e.id = v.id_equipamento WHERE {$condicaoBaseDashboard} AND v.valor IS NOT NULL AND TRIM(v.valor) <> '' AND c.tipo IN ('Múltipla escolha', 'Caixa de seleção') ORDER BY c.titulo_campo, c.opcoes, v.valor");
while ($qrCamposFiltro && $dadoFiltro = mysqli_fetch_assoc($qrCamposFiltro)) {
    $tituloFiltro = trim($dadoFiltro["titulo"]);
    $tituloNormalizadoFiltro = mb_strtoupper($tituloFiltro, "UTF-8");
    if (!$ehAdministrador && $tituloNormalizadoFiltro === "STATUS DO EQUIPAMENTO")
        continue;
    if (in_array($tituloNormalizadoFiltro, ["TIPO DE INSPEÇÃO EXECUTADA", "TIPOS DE INSPEÇÃO EXECUTADA"], true)) {
        $tituloFiltro = "TIPO DE INSPEÇÃO EXECUTADA";
        $tituloNormalizadoFiltro = $tituloFiltro;
    }
    $opcoesFiltro = trim($dadoFiltro["opcoes"] ?? "");
    $chaveFiltro = $tituloNormalizadoFiltro . "|" . mb_strtoupper(preg_replace('/\s+/u', '', $opcoesFiltro), "UTF-8");
    if (!isset($camposFiltroDashboard[$chaveFiltro]))
        $camposFiltroDashboard[$chaveFiltro] = ["titulo" => $tituloFiltro, "titulo_normalizado" => $tituloNormalizadoFiltro, "opcoes" => $opcoesFiltro, "ids" => [], "valores" => []];
    $camposFiltroDashboard[$chaveFiltro]["ids"][intval($dadoFiltro["id_campo"])] = intval($dadoFiltro["id_campo"]);
    $valoresFiltro = stripos($dadoFiltro["tipo"], "Caixa") === 0 ? preg_split('/,\s*/u', $dadoFiltro["valor"]) : [$dadoFiltro["valor"]];
    foreach ($valoresFiltro as $valorFiltro) {
        $valorFiltro = trim($valorFiltro);
        if ($tituloFiltro != "" && $valorFiltro != "")
            $camposFiltroDashboard[$chaveFiltro]["valores"][$valorFiltro] = $valorFiltro;
    }
}
uasort($camposFiltroDashboard, function ($a, $b) {
    return strnatcasecmp($a["titulo"] . $a["opcoes"], $b["titulo"] . $b["opcoes"]);
});
$quantidadeCamposFiltroPorTitulo = [];
foreach ($camposFiltroDashboard as $campoFiltroDashboard)
    $quantidadeCamposFiltroPorTitulo[$campoFiltroDashboard["titulo_normalizado"]] = ($quantidadeCamposFiltroPorTitulo[$campoFiltroDashboard["titulo_normalizado"]] ?? 0) + 1;

$valoresFiltrosPersonalizadosSelecionados = [];
foreach ($filtrosDashboard["campos_personalizados"] as $filtroPersonalizadoSelecionado)
    $valoresFiltrosPersonalizadosSelecionados[implode(",", $filtroPersonalizadoSelecionado["ids"])] = $filtroPersonalizadoSelecionado["valor"];

if ($ehAdministrador) {
    $idEmpresaDashboard = intval($empresaAtual);
    $whereEmpresa = $idEmpresaDashboard > 0 ? " WHERE id_empresa = '{$idEmpresaDashboard}'" : "";
    $whereEmpresaEquipamento = " WHERE {$condicaoEquipamentosDashboard}";
    $andEmpresaUnidade = $idEmpresaDashboard > 0 ? " AND u.id_empresa = '{$idEmpresaDashboard}'" : "";

    $totalEmpresas = $idEmpresaDashboard > 0 ? 1 : valorContagem($connect, "SELECT COUNT(*) FROM empresas");
    $totalUnidades = valorContagem($connect, "SELECT COUNT(*) FROM unidades{$whereEmpresa}");
    $totalEquipamentos = valorContagem($connect, "SELECT COUNT(*) FROM equipamentos e{$whereEmpresaEquipamento}");
    $totalInspecoesPendentes = valorContagem($connect, "SELECT COUNT(*) FROM inspecoes i INNER JOIN equipamentos e ON e.id = i.id_equipamento WHERE i.status_inspecao = 'Pendente' AND {$condicaoEquipamentosDashboard}");

    $categoriasGrafico = montarCategoriasGraficoDashboard($connect, $condicaoEquipamentosDashboard);

    $situacoesGrafico = [["name" => "Válidos", "value" => 0], ["name" => "Pendentes", "value" => 0]];
    $qrSituacoes = mysqli_query($connect, "SELECT CASE WHEN i.status_inspecao = 'Aprovado' THEN 'Válidos' ELSE 'Pendentes' END AS situacao, COUNT(e.id) AS total FROM equipamentos e LEFT JOIN inspecoes i ON i.id = (SELECT MAX(i2.id) FROM inspecoes i2 WHERE i2.id_equipamento = e.id){$whereEmpresaEquipamento} GROUP BY situacao");
    while ($dadoSituacao = mysqli_fetch_assoc($qrSituacoes)) {
        foreach ($situacoesGrafico as &$situacaoGrafico) {
            if ($situacaoGrafico["name"] == $dadoSituacao["situacao"])
                $situacaoGrafico["value"] = intval($dadoSituacao["total"]);
        }
        unset($situacaoGrafico);
    }

    $condicaoBaseUnidades = $idEmpresaDashboard > 0 ? "eq.id_empresa = '{$idEmpresaDashboard}'" : "1 = 1";
    $condicaoEquipamentosUnidades = condicaoFiltrosDashboard($connect, $condicaoBaseUnidades, $filtrosDashboard, "eq");
    $sqlUnidades = "SELECT u.id, u.titulo_unidade, e.nome_empresa, COUNT(eq.id) AS total FROM unidades u INNER JOIN empresas e ON e.id = u.id_empresa LEFT JOIN equipamentos eq ON eq.id_unidade = u.id AND {$condicaoEquipamentosUnidades} WHERE 1=1{$andEmpresaUnidade} GROUP BY u.id, u.titulo_unidade, e.nome_empresa";
    $qrMaiorUnidade = mysqli_query($connect, "{$sqlUnidades} ORDER BY total DESC, u.titulo_unidade LIMIT 1");
    $maiorUnidade = mysqli_fetch_assoc($qrMaiorUnidade);
    $qrMenorUnidade = mysqli_query($connect, "{$sqlUnidades} ORDER BY total ASC, u.titulo_unidade LIMIT 1");
    $menorUnidade = mysqli_fetch_assoc($qrMenorUnidade);

    $condicaoEmpresaCampos = $condicaoEquipamentosDashboard;
    $graficosCamposEquipamento = montarGraficosCamposEquipamento($connect, $condicaoEmpresaCampos);
?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Visão geral</h4>
            <p class="text-600 mb-0"><?= $idEmpresaDashboard > 0 ? "Resumo da empresa selecionada" : "Resumo de todas as empresas"; ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($quantidadeFiltrosDashboard > 0) { ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?= $linkLimparFiltrosDashboard; ?>"><span class="fas fa-times me-1"></span>Limpar filtros</a>
            <?php } ?>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltrosDashboard" aria-controls="offcanvasFiltrosDashboard"><span class="fas fa-filter me-1"></span>Filtros<?php if ($quantidadeFiltrosDashboard > 0) { ?><span class="badge bg-primary ms-1"><?= $quantidadeFiltrosDashboard; ?></span><?php } ?></button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <?php
        $cards = [
            ["Empresas", $totalEmpresas, "fa-building", "primary"],
            ["Unidades", $totalUnidades, "fa-map-marker-alt", "info"],
            ["Equipamentos", $totalEquipamentos, "fa-tools", "success"],
            ["Inspeções pendentes", $totalInspecoesPendentes, "fa-clipboard-check", "warning"],
        ];
        foreach ($cards as $card) { ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                    <div><p class="text-600 fs--1 mb-1"><?= textoDashboard($card[0]); ?></p><h3 class="mb-0"><?= number_format($card[1], 0, ",", "."); ?></h3></div>
                    <div class="rounded-circle bg-<?= $card[3]; ?> bg-opacity-10 text-<?= $card[3]; ?> d-flex align-items-center justify-content-center" style="width:48px;height:48px"><span class="fas <?= $card[2]; ?>"></span></div>
                </div></div>
            </div>
        <?php } ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-7"><div class="card h-100"><div class="card-header bg-light"><h6 class="mb-0">Equipamentos por categoria</h6></div><div class="card-body"><div id="graficoCategorias" style="height:360px"></div></div></div></div>
        <div class="col-12 col-xl-5"><div class="card h-100"><div class="card-header bg-light"><h6 class="mb-0">Situação dos equipamentos</h6></div><div class="card-body"><div id="graficoSituacoes" style="height:360px"></div></div></div></div>
    </div>

    <div class="row g-3">
        <?php foreach ([["Unidade com mais equipamentos", $maiorUnidade, "fa-arrow-up", "success"], ["Unidade com menos equipamentos", $menorUnidade, "fa-arrow-down", "danger"]] as $resumoUnidade) { ?>
            <div class="col-12 col-lg-6"><div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-<?= $resumoUnidade[3]; ?> bg-opacity-10 text-<?= $resumoUnidade[3]; ?> d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px"><span class="fas <?= $resumoUnidade[2]; ?>"></span></div>
                <div class="min-w-0"><p class="text-600 fs--1 mb-1"><?= $resumoUnidade[0]; ?></p><h6 class="mb-1"><?= textoDashboard($resumoUnidade[1]["titulo_unidade"] ?? "Nenhuma unidade"); ?></h6><?php if ($resumoUnidade[1]) { ?><p class="mb-0 fs--1 text-600"><?= textoDashboard($resumoUnidade[1]["nome_empresa"]); ?> · <?= intval($resumoUnidade[1]["total"]); ?> equipamento(s)</p><?php } ?></div>
            </div></div></div>
        <?php } ?>
    </div>

    <div class="mt-4 mb-3">
        <h5 class="mb-1">Indicadores dos campos dos equipamentos</h5>
        <p class="text-600 mb-0">Distribuição dos campos de escolha com maior quantidade de equipamentos preenchidos.</p>
    </div>

    <?php if (count($graficosCamposEquipamento) > 0) { ?>
        <div class="row g-3">
            <?php foreach ($graficosCamposEquipamento as $indiceGrafico => $graficoCampo) { ?>
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between gap-1">
                            <h6 class="mb-0"><?= textoDashboard($graficoCampo["titulo"]); ?></h6>
                            <span class="text-600 fs--1 text-nowrap"><?= number_format($graficoCampo["cobertura"], 0, ",", "."); ?> equipamento(s)<?= $graficoCampo["multipla"] ? " · " . number_format($graficoCampo["total_respostas"], 0, ",", ".") . " seleções" : ""; ?></span>
                        </div>
                        <div class="card-body"><div id="graficoCampoEquipamento<?= $indiceGrafico; ?>" style="height:<?= $graficoCampo["barra"] ? min(720, max(340, count($graficoCampo["dados"]) * 42)) : 340; ?>px"></div></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="card"><div class="card-body text-center text-600">Nenhum campo de escolha preenchido para a empresa selecionada.</div></div>
    <?php } ?>

    <script>
        window.addEventListener('load', function () {
            criarGraficoPizzaDashboard('graficoCategorias', <?= json_encode($categoriasGrafico, JSON_UNESCAPED_UNICODE); ?>, <?= json_encode($coresGraficos); ?>, 'categoria');
            <?php foreach ($graficosCamposEquipamento as $indiceGrafico => $graficoCampo) { ?>
                <?= $graficoCampo["barra"] ? "criarGraficoBarrasDashboard" : "criarGraficoPizzaDashboard"; ?>('graficoCampoEquipamento<?= $indiceGrafico; ?>', <?= json_encode($graficoCampo["dados"], JSON_UNESCAPED_UNICODE); ?>, <?= json_encode($coresGraficos); ?>, <?= json_encode($graficoCampo["titulo"], JSON_UNESCAPED_UNICODE); ?>, <?= intval($graficoCampo["cobertura"]); ?>);
            <?php } ?>
            criarGraficoPizzaDashboard('graficoSituacoes', <?= json_encode($situacoesGrafico, JSON_UNESCAPED_UNICODE); ?>, ['#00d27a', '#f5803e'], 'situação');
        });
    </script>
<?php
} else {
    $idEmpresaCliente = intval($empresaAtual);
    $condicaoEmpresasCliente = $condicaoEquipamentosDashboard;
    $graficosCamposEquipamento = montarGraficosCamposEquipamento($connect, $condicaoEmpresasCliente);
    $baseCliente = " FROM equipamentos e LEFT JOIN categorias_equipamento ce ON ce.id = e.id_categoria_equipamento LEFT JOIN inspecoes i ON i.id = (SELECT MAX(i2.id) FROM inspecoes i2 WHERE i2.id_equipamento = e.id)";
    $whereCliente = " WHERE {$condicaoEmpresasCliente}";
    $totalEquipamentos = valorContagem($connect, "SELECT COUNT(e.id){$baseCliente}{$whereCliente}");
    $condicaoStatusEquipamentoCliente = "SELECT 1 FROM valores_campos_personalizados_equipamento status_v INNER JOIN campos_tipo_equipamento status_c ON status_c.id = status_v.id_campo_tipo_equipamento WHERE status_v.id_equipamento = e.id AND UPPER(TRIM(status_c.titulo_campo)) = 'STATUS DO EQUIPAMENTO'";
    $totalAptos = valorContagem($connect, "SELECT COUNT(e.id){$baseCliente}{$whereCliente} AND EXISTS ({$condicaoStatusEquipamentoCliente} AND UPPER(TRIM(status_v.valor)) = 'EQUIPAMENTO APTO')");
    $totalInaptos = valorContagem($connect, "SELECT COUNT(e.id){$baseCliente}{$whereCliente} AND EXISTS ({$condicaoStatusEquipamentoCliente} AND UPPER(TRIM(status_v.valor)) = 'EQUIPAMENTO INAPTO')");
    $totalPendentes = valorContagem($connect, "SELECT COUNT(e.id){$baseCliente}{$whereCliente} AND NOT EXISTS ({$condicaoStatusEquipamentoCliente} AND UPPER(TRIM(status_v.valor)) IN ('EQUIPAMENTO APTO', 'EQUIPAMENTO INAPTO'))");

    $nomeEmpresaCliente = "Todas as empresas";
    if ($idEmpresaCliente > 0) {
        $qrNomeEmpresa = mysqli_query($connect, "SELECT nome_empresa FROM empresas WHERE id = '{$idEmpresaCliente}' LIMIT 1");
        if ($dadoNomeEmpresa = mysqli_fetch_assoc($qrNomeEmpresa))
            $nomeEmpresaCliente = $dadoNomeEmpresa["nome_empresa"];
    }

    $categoriasClienteGrafico = montarCategoriasGraficoDashboard($connect, $condicaoEmpresasCliente);
    $situacoesClienteGrafico = [["name" => "Aptos", "value" => $totalAptos], ["name" => "Inaptos", "value" => $totalInaptos], ["name" => "Itens de Segurança", "value" => $totalPendentes]];
    $itensSegurancaPorTipoGrafico = [];
    $qrItensSegurancaPorTipo = mysqli_query($connect, "SELECT COALESCE(NULLIF(TRIM(te.titulo_tipo_equipamento), ''), 'Outros') AS tipo, COUNT(DISTINCT e.id) AS total FROM equipamentos e LEFT JOIN tipos_equipamento te ON te.id = e.id_tipo_equipamento WHERE {$condicaoEmpresasCliente} AND NOT EXISTS ({$condicaoStatusEquipamentoCliente} AND UPPER(TRIM(status_v.valor)) IN ('EQUIPAMENTO APTO', 'EQUIPAMENTO INAPTO')) GROUP BY te.id, te.titulo_tipo_equipamento HAVING total > 0 ORDER BY total DESC, tipo");
    while ($qrItensSegurancaPorTipo && $dadoItemSeguranca = mysqli_fetch_assoc($qrItensSegurancaPorTipo)) {
        $tipoItemSeguranca = chaveSemAcentosDashboard($dadoItemSeguranca["tipo"]);
        if (strpos($tipoItemSeguranca, "MANOMETRO") !== false)
            $tipoItemSeguranca = "Manômetros";
        elseif (strpos($tipoItemSeguranca, "VALVULA") !== false)
            $tipoItemSeguranca = "Válvulas de segurança";
        else
            $tipoItemSeguranca = $dadoItemSeguranca["tipo"];

        if (!isset($itensSegurancaPorTipoGrafico[$tipoItemSeguranca]))
            $itensSegurancaPorTipoGrafico[$tipoItemSeguranca] = 0;
        $itensSegurancaPorTipoGrafico[$tipoItemSeguranca] += intval($dadoItemSeguranca["total"]);
    }
    arsort($itensSegurancaPorTipoGrafico);
    $itensSegurancaPorTipoGrafico = array_map(function ($nome, $total) {
        return ["name" => $nome, "value" => $total];
    }, array_keys($itensSegurancaPorTipoGrafico), array_values($itensSegurancaPorTipoGrafico));
    $inaptosPorEmpresaGrafico = [];
    $qrInaptosPorEmpresa = mysqli_query($connect, "SELECT em.nome_empresa AS empresa, COUNT(DISTINCT e.id) AS total FROM equipamentos e INNER JOIN empresas em ON em.id = e.id_empresa WHERE {$condicaoEmpresasCliente} AND EXISTS ({$condicaoStatusEquipamentoCliente} AND UPPER(TRIM(status_v.valor)) = 'EQUIPAMENTO INAPTO') GROUP BY em.id, em.nome_empresa HAVING total > 0 ORDER BY total DESC, empresa");
    while ($qrInaptosPorEmpresa && $dadoInaptosEmpresa = mysqli_fetch_assoc($qrInaptosPorEmpresa))
        $inaptosPorEmpresaGrafico[] = ["name" => $dadoInaptosEmpresa["empresa"], "value" => intval($dadoInaptosEmpresa["total"])];
?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h4 class="mb-1">Meus equipamentos</h4><p class="text-600 mb-0"><?= textoDashboard($nomeEmpresaCliente); ?> &middot; todas as unidades</p></div>
        <div class="d-flex gap-2">
            <?php if ($quantidadeFiltrosDashboard > 0) { ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?= $linkLimparFiltrosDashboard; ?>"><span class="fas fa-times me-1"></span>Limpar filtros</a>
            <?php } ?>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltrosDashboard" aria-controls="offcanvasFiltrosDashboard"><span class="fas fa-filter me-1"></span>Filtros<?php if ($quantidadeFiltrosDashboard > 0) { ?><span class="badge bg-primary ms-1"><?= $quantidadeFiltrosDashboard; ?></span><?php } ?></button>
            <a href="<?= $link; ?>p=equipamentos" class="btn btn-padrao btn-sm"><span class="fas fa-list me-1"></span>Ver todos</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <?php foreach ([["Equipamentos", $totalEquipamentos, "fa-tools", "primary"], ["Aptos", $totalAptos, "fa-check-circle", "success"], ["Inaptos", $totalInaptos, "fa-times-circle", "danger"], ["Itens de Segurança", $totalPendentes, "fa-shield-alt", "warning"]] as $card) { ?>
            <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><p class="text-600 fs--1 mb-1"><?= $card[0]; ?></p><h3 class="mb-0"><?= number_format($card[1], 0, ",", "."); ?></h3></div><span class="fas <?= $card[2]; ?> text-<?= $card[3]; ?> fs-2"></span></div></div></div></div>
        <?php } ?>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6"><div class="card h-100"><div class="card-header bg-light"><h6 class="mb-0">Equipamentos por categoria</h6></div><div class="card-body"><div id="graficoCategoriasCliente" style="height:340px"></div></div></div></div>
        <div class="col-12 col-xl-6"><div class="card h-100"><div class="card-header bg-light"><h6 class="mb-0">Situação dos equipamentos</h6></div><div class="card-body"><div id="graficoSituacoesCliente" style="height:340px"></div></div></div></div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center gap-2">
            <h6 class="mb-0">Inaptos por empresa</h6>
            <span class="text-600 fs--1 text-nowrap"><?= number_format($totalInaptos, 0, ",", "."); ?> equipamento(s)</span>
        </div>
        <div class="card-body">
            <?php if (count($inaptosPorEmpresaGrafico) > 0) { ?>
                <div id="graficoInaptosPorEmpresaCliente" style="height:<?= min(720, max(300, count($inaptosPorEmpresaGrafico) * 58)); ?>px"></div>
            <?php } else { ?>
                <p class="text-600 text-center mb-0 py-5">Nenhum equipamento inapto encontrado.</p>
            <?php } ?>
        </div>
    </div>

    <div class="mt-4 mb-3">
        <h5 class="mb-1">Indicadores dos campos dos equipamentos</h5>
        <p class="text-600 mb-0">Distribuição dos campos de escolha dos equipamentos da empresa selecionada.</p>
    </div>

    <?php if (count($graficosCamposEquipamento) > 0) { ?>
        <div class="row g-3">
            <?php foreach ($graficosCamposEquipamento as $indiceGrafico => $graficoCampo) { ?>
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between gap-1">
                            <h6 class="mb-0"><?= textoDashboard($graficoCampo["titulo"]); ?></h6>
                            <span class="text-600 fs--1 text-nowrap"><?= number_format($graficoCampo["cobertura"], 0, ",", "."); ?> equipamento(s)<?= $graficoCampo["multipla"] ? " · " . number_format($graficoCampo["total_respostas"], 0, ",", ".") . " seleções" : ""; ?></span>
                        </div>
                        <div class="card-body"><div id="graficoCampoCliente<?= $indiceGrafico; ?>" style="height:<?= $graficoCampo["barra"] ? min(720, max(340, count($graficoCampo["dados"]) * 42)) : 340; ?>px"></div></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="card"><div class="card-body text-center text-600">Nenhum campo de escolha preenchido para a empresa selecionada.</div></div>
    <?php } ?>

    <script>
        window.addEventListener('load', function () {
            criarGraficoPizzaDashboard('graficoCategoriasCliente', <?= json_encode($categoriasClienteGrafico, JSON_UNESCAPED_UNICODE); ?>, <?= json_encode($coresGraficos); ?>, 'categoria');
            criarGraficoSituacoesCliente('graficoSituacoesCliente', <?= json_encode($situacoesClienteGrafico, JSON_UNESCAPED_UNICODE); ?>, <?= json_encode($itensSegurancaPorTipoGrafico, JSON_UNESCAPED_UNICODE); ?>);
            criarGraficoBarrasDashboard('graficoInaptosPorEmpresaCliente', <?= json_encode($inaptosPorEmpresaGrafico, JSON_UNESCAPED_UNICODE); ?>, ['#e63757'], 'Inaptos', <?= intval($totalInaptos); ?>, 320);
            <?php foreach ($graficosCamposEquipamento as $indiceGrafico => $graficoCampo) { ?>
                <?= $graficoCampo["barra"] ? "criarGraficoBarrasDashboard" : "criarGraficoPizzaDashboard"; ?>('graficoCampoCliente<?= $indiceGrafico; ?>', <?= json_encode($graficoCampo["dados"], JSON_UNESCAPED_UNICODE); ?>, <?= json_encode($coresGraficos); ?>, <?= json_encode($graficoCampo["titulo"], JSON_UNESCAPED_UNICODE); ?>, <?= intval($graficoCampo["cobertura"]); ?>);
            <?php } ?>
        });
    </script>
<?php } ?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltrosDashboard" aria-labelledby="tituloFiltrosDashboard">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title" id="tituloFiltrosDashboard">Filtros do dashboard</h5>
            <p class="text-600 fs--1 mb-0">Os cards e gráficos serão atualizados.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="./index.php" id="formFiltrosDashboard">
            <input type="hidden" name="p" value="home">
            <input type="hidden" name="empresa" value="<?= intval($empresaAtual) > 0 ? intval($empresaAtual) : ($ehAdministrador ? "" : "todas"); ?>">

            <?php
            $selectsFiltroDashboard = [
                ["f_tipo", "Tipo de equipamento", $tiposFiltroDashboard, $filtrosDashboard["tipo"]],
                ["f_categoria", "Categoria", $categoriasFiltroDashboard, $filtrosDashboard["categoria"]],
                ["f_local", "Local de instalação", $locaisFiltroDashboard, $filtrosDashboard["local"]]
            ];
            foreach ($selectsFiltroDashboard as $selectFiltro) { ?>
                <div class="mb-3">
                    <label class="form-label" for="<?= $selectFiltro[0]; ?>"><?= $selectFiltro[1]; ?></label>
                    <select class="form-select" id="<?= $selectFiltro[0]; ?>" name="<?= $selectFiltro[0]; ?>">
                        <option value="">Todos</option>
                        <?php foreach ($selectFiltro[2] as $opcaoFiltro) { ?>
                            <option value="<?= $opcaoFiltro["id"]; ?>"<?= intval($selectFiltro[3]) === $opcaoFiltro["id"] ? " selected" : ""; ?>><?= textoDashboard($opcaoFiltro["titulo"]); ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="mb-3">
                <label class="form-label" for="f_situacao">Situação</label>
                <select class="form-select" id="f_situacao" name="f_situacao">
                    <option value="">Todas</option>
                    <?php if ($ehAdministrador) { ?>
                        <option value="valido"<?= $filtrosDashboard["situacao"] === "valido" ? " selected" : ""; ?>>Válidos</option>
                        <option value="pendente"<?= $filtrosDashboard["situacao"] === "pendente" ? " selected" : ""; ?>>Pendentes</option>
                    <?php } else { ?>
                        <option value="apto"<?= $filtrosDashboard["situacao"] === "apto" ? " selected" : ""; ?>>Aptos</option>
                        <option value="inapto"<?= $filtrosDashboard["situacao"] === "inapto" ? " selected" : ""; ?>>Inaptos</option>
                    <?php } ?>
                </select>
            </div>

            <hr>
            <h6>Campos do equipamento</h6>
            <p class="text-600 fs--1">Selecione uma resposta em cada campo que deseja filtrar.</p>

            <?php foreach ($camposFiltroDashboard as $chaveCampoFiltro => $campoFiltroDashboard) {
                $idsCampoFiltro = array_values($campoFiltroDashboard["ids"]);
                sort($idsCampoFiltro);
                $assinaturaCampoFiltro = implode(",", $idsCampoFiltro);
                $idCampoFiltro = "f_personalizado_" . substr(sha1($chaveCampoFiltro), 0, 10);
                $valorSelecionadoCampo = $valoresFiltrosPersonalizadosSelecionados[$assinaturaCampoFiltro] ?? "";
                $tituloCampoFiltro = $campoFiltroDashboard["titulo"];
                if ($quantidadeCamposFiltroPorTitulo[$campoFiltroDashboard["titulo_normalizado"]] > 1 && $campoFiltroDashboard["opcoes"] !== "")
                    $tituloCampoFiltro .= " — " . str_replace(",", " / ", $campoFiltroDashboard["opcoes"]);
            ?>
                <div class="mb-3">
                    <label class="form-label" for="<?= $idCampoFiltro; ?>"><?= textoDashboard($tituloCampoFiltro); ?></label>
                    <select class="form-select filtro-personalizado-dashboard" id="<?= $idCampoFiltro; ?>" name="f_personalizado[]">
                        <option value="">Todos</option>
                        <?php foreach ($campoFiltroDashboard["valores"] as $valorCampoFiltro) {
                            $tokenCampoFiltro = tokenFiltroPersonalizadoDashboard($idsCampoFiltro, $valorCampoFiltro);
                        ?>
                            <option value="<?= textoDashboard($tokenCampoFiltro); ?>"<?= $valorSelecionadoCampo === $valorCampoFiltro ? " selected" : ""; ?>><?= textoDashboard($valorCampoFiltro); ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="d-flex gap-2 sticky-bottom bg-white py-2">
                <a class="btn btn-outline-secondary flex-fill" href="<?= $linkLimparFiltrosDashboard; ?>">Limpar</a>
                <button class="btn btn-padrao flex-fill" type="submit"><span class="fas fa-filter me-1"></span>Aplicar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('formFiltrosDashboard').addEventListener('submit', function () {
        this.querySelectorAll('.filtro-personalizado-dashboard').forEach(function (campo) {
            campo.disabled = campo.value === '';
        });
    });

    function criarGraficoBarrasDashboard(elementoId, dados, cores, nomeSerie, totalEquipamentos, larguraRotulo) {
        var elemento = document.getElementById(elementoId);
        if (!elemento || typeof echarts === 'undefined') return;
        var grafico = echarts.init(elemento);
        larguraRotulo = larguraRotulo || 210;
        var dadosOrdenados = dados.slice().sort(function (a, b) { return a.value - b.value; });
        var textoPercentual = function (valor) {
            var percentual = totalEquipamentos > 0 ? (valor / totalEquipamentos) * 100 : 0;
            return percentual.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 1 }) + '%';
        };
        grafico.setOption({
            color: cores,
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: function (itens) { return itens[0].name + ': ' + itens[0].value + ' equipamento(s) (' + textoPercentual(itens[0].value) + ')'; } },
            grid: { top: 10, right: 85, bottom: 25, left: 15, containLabel: true },
            xAxis: { type: 'value', minInterval: 1 },
            yAxis: { type: 'category', data: dadosOrdenados.map(function (item) { return item.name; }), axisLabel: { width: larguraRotulo, overflow: 'truncate' } },
            series: [{ name: nomeSerie, type: 'bar', data: dadosOrdenados.map(function (item, indice) { return { value: item.value, itemStyle: { color: cores[indice % cores.length], borderRadius: [0, 4, 4, 0] } }; }), label: { show: true, position: 'right', formatter: function (item) { return item.value + ' (' + textoPercentual(item.value) + ')'; } } }]
        });
        window.addEventListener('resize', function () { grafico.resize(); });
    }

    function criarGraficoPizzaDashboard(elementoId, dados, cores, nomeSerie) {
        var elemento = document.getElementById(elementoId);
        if (!elemento || typeof echarts === 'undefined') return;
        var grafico = echarts.init(elemento);
        grafico.setOption({
            color: cores,
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            legend: { type: 'scroll', bottom: 0, left: 'center' },
            series: [{ name: nomeSerie, type: 'pie', radius: ['42%', '68%'], center: ['50%', '44%'], avoidLabelOverlap: true, itemStyle: { borderRadius: 5, borderColor: '#fff', borderWidth: 2 }, label: { formatter: '{b}\n{c} ({d}%)' }, data: dados }]
        });
        window.addEventListener('resize', function () { grafico.resize(); });
    }

    function criarGraficoSituacoesCliente(elementoId, situacoes, itensSeguranca) {
        var elemento = document.getElementById(elementoId);
        if (!elemento || typeof echarts === 'undefined') return;
        var grafico = echarts.init(elemento);
        var totalItensSeguranca = itensSeguranca.reduce(function (total, item) { return total + Number(item.value || 0); }, 0);
        var valorSituacao = function (nome) {
            var item = situacoes.find(function (situacao) { return situacao.name === nome; });
            return item ? Number(item.value || 0) : 0;
        };
        var coresItens = ['#f5803e', '#ffad73', '#d96524', '#ffc59e', '#b94e16'];
        var dadosExternos = [
            { name: '__aptos', value: valorSituacao('Aptos'), itemStyle: { color: 'transparent' }, label: { show: false }, labelLine: { show: false }, tooltip: { show: false }, emphasis: { disabled: true } },
            { name: '__inaptos', value: valorSituacao('Inaptos'), itemStyle: { color: 'transparent' }, label: { show: false }, labelLine: { show: false }, tooltip: { show: false }, emphasis: { disabled: true } }
        ].concat(itensSeguranca.map(function (item, indice) {
            return { name: item.name, value: item.value, itemStyle: { color: coresItens[indice % coresItens.length] } };
        }));

        grafico.setOption({
            color: ['#00d27a', '#e63757', '#f5803e'],
            tooltip: {
                trigger: 'item',
                formatter: function (item) {
                    if (String(item.name).indexOf('__') === 0) return '';
                    if (item.seriesIndex === 1) {
                        var percentualItens = totalItensSeguranca > 0 ? (item.value / totalItensSeguranca) * 100 : 0;
                        return item.name + ': ' + item.value + ' equipamento(s) (' + percentualItens.toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + '% dos itens de segurança)';
                    }
                    return item.name + ': ' + item.value + ' (' + item.percent + '%)';
                }
            },
            legend: { type: 'scroll', bottom: 0, left: 'center', data: situacoes.map(function (item) { return item.name; }).concat(itensSeguranca.map(function (item) { return item.name; })) },
            series: [
                {
                    name: 'Situação', type: 'pie', radius: ['28%', '50%'], center: ['50%', '43%'],
                    itemStyle: { borderRadius: 5, borderColor: '#fff', borderWidth: 2 },
                    label: { formatter: '{b}\n{c} ({d}%)' }, data: situacoes
                },
                {
                    name: 'Itens de Segurança', type: 'pie', radius: ['58%', '73%'], center: ['50%', '43%'],
                    avoidLabelOverlap: true, itemStyle: { borderColor: '#fff', borderWidth: 2 },
                    label: {
                        formatter: function (item) {
                            if (String(item.name).indexOf('__') === 0) return '';
                            var percentualItens = totalItensSeguranca > 0 ? (item.value / totalItensSeguranca) * 100 : 0;
                            return item.name + '\n' + item.value + ' (' + percentualItens.toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + '%)';
                        }
                    },
                    data: dadosExternos
                }
            ]
        });
        window.addEventListener('resize', function () { grafico.resize(); });
    }
</script>
