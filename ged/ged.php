<?php
$pasta = @$_GET['pasta'];

// Usado para saber se a empresa e pasta atual existem
$dirValid = true;

if ($empresaAtual != "") {
    $qrEmpresaAtual = " WHERE id_empresa = '{$empresaAtual}'";

    $idPasta = "";
    if ($pasta != "") {
        $idPasta = "&pasta={$pasta}";
        if (intval(mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(id) FROM pastas{$qrEmpresaAtual} AND id = '{$pasta}'"))[0]) < 1)
            $dirValid = false;
    } else if (intval(mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(id) FROM empresas WHERE id = '{$empresaAtual}'"))[0]) < 1)
        $dirValid = false;
}
?>

<link rel="stylesheet" href="./assets/css/ged/ged.css<?= $version; ?>">

<div class="card">
    <div class="card-body pe-md-3">

        <div class="d-md-flex justify-content-between align-items-center pe-md-1 mb-2">
            <h5 class="mb-2 mb-md-0">GED</h5>
            <div class="d-flex gap-2">
                <?php
                if ($dirValid && $empresaAtual != "" && permissaoUsuario('Administrador', $sessaoUsuario['funcao'])) {
                ?>
                    <button class="btn btn-padrao btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalCriarPasta" type="button">
                        <span class="fas fa-plus me-1" data-fa-transform="shrink-3"></span>Criar pasta
                    </button>
                    <button class="btn btn-padrao btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalUploadArquivo" type="button">
                        <span class="fas fa-upload me-1" data-fa-transform="shrink-3"></span>Upload
                    </button>
                <?php
                }
                ?>
                <button class="btn btn-padrao btn-sm flex-fill" type="button" onclick="window.history.back()">
                    <span class="fas fa-arrow-left me-1" data-fa-transform="shrink-3"></span>Voltar
                </button>
            </div>
        </div>

        <div class="px-3 pt-3 pb-5 mt-4 rounded arquivos-area d-flex flex-wrap align-content-start gap-3 justify-content-center justify-content-lg-start position-relative" style="min-height: 65vh;">
            <?php
            $dirBase = "./arquivos/";

            // Se existir empresa lista sub-pastas e arquivos. Se não, lista pastas de empresa
            if ($empresaAtual != "") {

                //Busca nome empresa atual
                $qr = mysqli_query($connect, "SELECT * FROM empresas WHERE id = '{$empresaAtual}'");
                $dado = mysqli_fetch_array($qr);
                $nomeEmpresa = $dado['nome_empresa'];
                $dir = "{$dirBase}{$nomeEmpresa}/";
                $crumb = "<li class='breadcrumb-item active' aria-current='page'>{$nomeEmpresa}</li>";

                // Definindo o diretório atual em $dir e remontando $crumb caso não esteja na raiz
                $qrPastaAtual = " AND id_pasta_ascendente IS NULL";
                if ($pasta != "") {
                    $qrPastaAtual = " AND id_pasta_ascendente = '{$pasta}'";
                    $qrPasta = mysqli_query($connect, "SELECT * FROM pastas{$qrEmpresaAtual} AND id = '{$pasta}'");
                    $dadoPasta = mysqli_fetch_array($qrPasta);
                    $nomePastaAtual = $dadoPasta['nome_pasta'];
                    $idAscendenteAtual = $dadoPasta['id_pasta_ascendente'];
                    $caminho = "";
                    $crumb = "<li class='breadcrumb-item active' aria-current='page'>{$nomePastaAtual}</li>";

                    $i = 0;
                    while (true) {
                        $qr = mysqli_query($connect, "SELECT id, id_pasta_ascendente, nome_pasta, id_unidade FROM pastas WHERE id_empresa = '{$empresaAtual}' AND  id = '{$idAscendenteAtual}'");
                        $dado = mysqli_fetch_array($qr);


                        if ($dado['nome_pasta'] != "") {
                            $caminho = $dado['nome_pasta'] . '/' . $caminho;
                            $crumb = "<li class='breadcrumb-item'><a href='./index.php?empresa={$empresaAtual}&p=ged&pasta={$dado['id']}'>{$dado['nome_pasta']}</a></li>{$crumb}";
                        }

                        if ($dado['id_pasta_ascendente'] == "")
                            break;
                        else
                            $idAscendenteAtual = $dado['id_pasta_ascendente'];

                        $i++;
                    }

                    $dir .= $caminho . $nomePastaAtual . '/';
                    if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                        $crumb = "<li class='breadcrumb-item'><a href='./index.php?empresa={$empresaAtual}&p=ged'>{$nomeEmpresa}</a></li>{$crumb}";
                }


                echo "<script>var dirAtual = '{$dir}';</script>";
                if (is_dir($dir) && $dirValid) {

                    $breadcrumb = '
                        <nav class="w-100" style="--falcon-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'8\' height=\'8\'%3E%3Cpath d=\'M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z\' fill=\'%23748194\'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="./index.php?p=ged">
                    ';

                    if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                        $breadcrumb .= 'Empresas';
                    else
                        $breadcrumb .= 'Início';

                    $breadcrumb .= '
                                </a></li>
                                ' . $crumb . '
                            </ol>
                        </nav>
                    ';

                    if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']) || @$_GET['pasta'] != "")
                        echo $breadcrumb;



                    // Exibe unidades na pasta raíz da empresa
                    if (!$pasta != "") {
                        $qrPastasUnidades = mysqli_query($connect, "SELECT * FROM pastas{$qrEmpresaAtual}{$qrPastaAtual} AND id_unidade IS NOT NULL");
                        while ($dadoPastasUnidades = mysqli_fetch_array($qrPastasUnidades)) {
                            $btnsAdmDropdown = "";
                            if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                                $btnsAdmDropdown = "
                                    <li><a href='{$link}p=unidades&acao=editar&id={$dadoPastasUnidades['id_unidade']}' class='dropdown-item'>
                                        <i class='bi bi-pencil me-2'></i>Editar
                                    </a></li>
                                ";

                            if (is_dir($dir . $dadoPastasUnidades['nome_pasta'])) {
                                $dataSubPasta = '{ "id": "' . $dadoPastasUnidades['id'] . '", "dir": "' . $dir . $dadoPastasUnidades['nome_pasta'] . '", "nome": "' . $dadoPastasUnidades['nome_pasta'] . '" }';

                                echo "
                                    <div class='dropdown-center'>  
                                        <div class='card-item pt-3 mb-0 sub-pasta' data-item='{$dataSubPasta}'>
                                            <i class='bi bi-folder text-padrao fs-7 d-flex justify-content-center'></i>
                                            <p class='text-limitado text-center text-padrao overflow-hidden px-2 mb-0'>{$dadoPastasUnidades['nome_pasta']}</p>
                                        </div>
                                        <ul class='dropdown-menu'>
                                            <li><a class='dropdown-item' href='{$link}p=ged&pasta={$dadoPastasUnidades['id']}'>
                                                <i class='bi bi-folder2-open me-2'></i>Abrir
                                            </a></li>
                                            {$btnsAdmDropdown}
                                        </ul>
                                    </div>
                                ";
                            }
                        }
                    }


                    // Exibe sub-pastas da pasta atual
                    $qrPastas = mysqli_query($connect, "SELECT * FROM pastas{$qrEmpresaAtual}{$qrPastaAtual} AND id_unidade IS NULL");
                    while ($dadoPastas = mysqli_fetch_array($qrPastas)) {
                        $btnsAdmDropdown = "";
                        if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                            $btnsAdmDropdown = "
                                <li><button class='dropdown-item btn-renomear'>
                                    <i class='bi bi-pencil me-2'></i>Renomear
                                </button></li>
                                <li><button class='dropdown-item btn-excluir'>
                                    <i class='bi bi-trash3 me-2'></i>Excluir
                                </button></li>
                            ";

                        if (is_dir($dir . $dadoPastas['nome_pasta'])) {
                            $dataSubPasta = '{ "id": "' . $dadoPastas['id'] . '", "dir": "' . $dir . $dadoPastas['nome_pasta'] . '", "nome": "' . $dadoPastas['nome_pasta'] . '" }';

                            echo "
                                <div class='dropdown-center'>  
                                    <div class='card-item pt-3 mb-0 sub-pasta' data-item='{$dataSubPasta}'>
                                        <i class='bi bi-folder fs-7 d-flex justify-content-center'></i>
                                        <p class='text-limitado text-center overflow-hidden px-2 mb-0'>{$dadoPastas['nome_pasta']}</p>
                                    </div>
                                    <ul class='dropdown-menu'>
                                        <li><a class='dropdown-item' href='{$link}p=ged&pasta={$dadoPastas['id']}'>
                                            <i class='bi bi-folder2-open me-2'></i>Abrir
                                        </a></li>
                                        {$btnsAdmDropdown}
                                    </ul>
                                </div>
                            ";
                        }
                    }

                    // Exibe arquivos da pasta atual
                    $qrArquivo = mysqli_query($connect, "SELECT * FROM arquivos{$qrEmpresaAtual}{$qrPastaAtual}");
                    while ($dadoArquivo = mysqli_fetch_array($qrArquivo)) {
                        if (is_file($dir . $dadoArquivo['arquivo'])) {

                            $arquivo = explode('.', $dadoArquivo['arquivo']);
                            $extensao = strtolower(end($arquivo));
                            array_pop($arquivo);
                            $nomeItem = join('.', $arquivo);

                            $btnsAdmDropdown = "";
                            if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                                $btnsAdmDropdown = "
                                    <li> <button class='dropdown-item btn-copiar'><i class='far fa-copy me-2'></i>Copiar</button> </li>
                                    <li> <button class='dropdown-item btn-renomear'><i class='bi bi-pencil me-2'></i>Renomear</button> </li>
                                    <li> <button class='dropdown-item btn-excluir'><i class='bi bi-trash3 me-2'></i>Excluir</button> </li>
                                    <li> <button class='dropdown-item btn-informacoes'><i class='bi bi-info-circle me-2'></i>Informações</button> </li>
                                ";

                            $iconAtual = 'bi bi-file-earmark';
                            if (infoTipoArquivo($extensao))
                                $iconAtual = infoTipoArquivo($extensao, "icon");

                            $dataArquivo = '{ "id": "' . $dadoArquivo['id'] . '", "dir": "' . $dir . $dadoArquivo['arquivo'] . '", "nome": "' . $nomeItem . '", "extensao": "' . $extensao . '" }';

                            echo " 
                                <div class='dropdown-center'>     
                                    <div class='card-item arquivo pt-3 mb-0' data-item='{$dataArquivo}'>
                                        <i class='{$iconAtual} fs-7 d-flex justify-content-center ' ></i>
                                        <p class='text-limitado text-center overflow-hidden px-2 mb-0'>{$nomeItem}</p>
                                    </div>
                                    <ul class='dropdown-menu'>
                                        <li> <a class='dropdown-item btn-download' download href='" . $dir . $dadoArquivo['arquivo'] . "' ><i class='bi bi-download me-2'></i>Download</a> </li>
                                        {$btnsAdmDropdown}
                                    </ul>
                                </div>                
                            ";
                        }
                    }


                    // Exibe se a pasta atual estiver vazia
                    $rowsPastas = mysqli_num_rows($qrPastas);
                    $rowsArquivos = mysqli_num_rows($qrArquivo);
                    if ($rowsPastas < 1 && $rowsArquivos < 1)
                        echo '<div class="box position-absolute top-50 start-50 translate-middle opacity-75" style="pointer-events: none;"> <div class="conteudo-pasta text-center"> <i class="bi bi-folder-fill text-padrao fs-6"></i> <h3>Nada encontrado por aqui</h3> <p>Envie um arquivo ou crie uma pasta</p> </div> </div>';
            ?>
                    <div class="modal fade" id="modalCriarPasta" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                            <div class="modal-content position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="./ged/ged-acao.php?acao=criar-pasta&empresa=<?= "{$empresaAtual}{$idPasta}&dir={$dir}"; ?>" method='POST' class='needs-validation' novalidate='novalidate'>
                                    <div class="modal-body p-0">
                                        <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                            <h4 class="mb-0" id="modalExampleDemoLabel">Criar pasta</h4>
                                        </div>
                                        <div class="p-3">
                                            <label class="form-label" for="txtNomePasta">Nome da pasta</label>
                                            <input class="form-control" type="text" name="nomePasta" required="required" id="txtNomePasta" autocomplete="off" maxlength="250">
                                        </div>
                                    </div>
                                    <div class="modal-footer flex-nowrap">
                                        <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cancelar</button>
                                        <button class="btn btn-padrao w-100" type="submit">Criar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalUploadArquivo" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px">
                            <div class="modal-content position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="./ged/ged-acao.php?acao=upload-arquivo&empresa=<?= "{$empresaAtual}{$idPasta}&dir={$dir}"; ?>" method="POST" enctype="multipart/form-data" class='needs-validation' novalidate='novalidate'>
                                    <div class="modal-body p-0">
                                        <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                            <h4 class="mb-0" id="modalExampleDemoLabel">Upload de arquivos</h4>
                                        </div>
                                        <div class="p-4">
                                            <div class="bg-light file-arrasta-solta">
                                                <input class="form-control" name="arquivos[]" type="file" multiple="multiple" required="required" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                                        <button class="btn btn-padrao" type="submit">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalRenomear" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                            <div class="modal-content position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method='POST' class='needs-validation' novalidate='novalidate'>
                                    <div class="modal-body p-0">
                                        <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                            <h5 class="mb-0" id="modalRenomearLabel">Renomear</h5>
                                        </div>
                                        <div class="p-3">
                                            <label class="form-label" for="txtNome">Nome</label>
                                            <input class="form-control" type="text" name="nome" required="required" id="txtNome" autocomplete="off" maxlength="250">
                                        </div>
                                    </div>
                                    <div class="modal-footer flex-nowrap">
                                        <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cancelar</button>
                                        <button class="btn btn-padrao w-100" type="submit">Renomear</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalExcluir" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                            <div class="modal-content position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                        <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill text-danger fw-bold me-2"></i>Atenção!</h4>
                                    </div>
                                    <div class="py-3 px-4">
                                        <p id="modalExcluirLabel" class="m-0 text-center">Tem certeza que deseja excluir?</p>
                                    </div>
                                </div>
                                <div class="modal-footer flex-nowrap">
                                    <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cancelar</button>
                                    <a class="btn btn-danger btn-excluir w-100">Excluir</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="modal fade show" id="modalInformacoes" tabindex="-1" role="dialog" aria-hidden="true" style="display: block"> -->
                    <div class="modal fade" id="modalInformacoes" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 365px">
                            <div class="modal-content position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                        <h4 class="mb-0">Informações</h4>
                                    </div>
                                    <div class="py-2 px-3 overflow-hidden modal-conteudo position-relative">
                                        <div class="offcanvas offcanvas-start position-absolute rounded-bottom-lg" id="offcanvasDownloadsUsuarios" tabindex="-1" aria-labelledby="offcanvasDownloadsUsuariosLabel">
                                            <div class="offcanvas-header">
                                                <h5 id="offcanvasDownloadsUsuariosLabel" class="m-0">Downloads</h5><button class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body scrollbar">
                                                <table class="table table-sm fw-medium m-0">
                                                    <tbody class="overflow-hidden">
                                                        <tr>
                                                            <td class="p-2">Nome</td>
                                                            <td class="p-2"><input type="text" readonly="" value="teste"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-2">Tipo</td>
                                                            <td class="p-2"><input type="text" readonly="" value="Arquivo compactado (zip)"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-2">Tamanho</td>
                                                            <td class="p-2"><input type="text" readonly="" value="6.14 KB"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-2">Data upload</td>
                                                            <td class="p-2"><input type="text" readonly="" value="27/04/2023 11:23"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <table class="table table-sm fw-semi-bold m-0">
                                            <tbody class="overflow-hidden">
                                                <tr>
                                                    <td class="p-2">Nome</td>
                                                    <td class="p-2"><input type="text" readonly="" value="teste"></td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2">Tipo</td>
                                                    <td class="p-2"><input type="text" readonly="" value="Arquivo compactado (zip)"></td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2">Tamanho</td>
                                                    <td class="p-2"><input type="text" readonly="" value="6.14 KB"></td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2">Data upload</td>
                                                    <td class="p-2"><input type="text" readonly="" value="27/04/2023 11:23"></td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2">Downloads</td>
                                                    <td class="p-2">
                                                        <div class="text-padrao w-auto cursor-pointer w-fit-content text-underline-hover">10</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!-- <div class="d-flex justify-content-center">
                                            <div class="spinner-border text-padrao" role="status"> <span class="visually-hidden">Loading...</span> </div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                } else {
                    $erroDirInvalido = '<div class="box position-absolute top-50 start-50 translate-middle opacity-75" style="pointer-events: none;"> <div class="conteudo-pasta text-center"> <i class="bi bi-folder-x text-danger fs-6"></i> <h3>Houve algum problema</h3> <p>';
                    if (permissaoUsuario('Administrador', $sessaoUsuario['funcao']))
                        $erroDirInvalido .= 'Verifique se a empresa ou pasta atual está correta';
                    else
                        $erroDirInvalido .= 'Verifique se a pasta atual está correta ou contate um administrador!';
                    $erroDirInvalido .= '</p> </div> </div>';
                    echo $erroDirInvalido;
                }
            } else {
                // Listagem pastas empresas
                $whereEmpresasGed = "";
                if ($sessaoUsuario['funcao'] == 'Cliente')
                    $whereEmpresasGed = " WHERE " . condicaoEmpresasPermitidasUsuario($connect, $sessaoUsuario['id'], "id", 0);
                $qrEmpresas = mysqli_query($connect, "SELECT * FROM empresas{$whereEmpresasGed}");
                while ($dadoEmpresa = mysqli_fetch_array($qrEmpresas)) {
                    if (is_dir($dirBase . $dadoEmpresa['nome_empresa'])) {
                        $dataPasta = '{ "id": "' . $dadoEmpresa['id'] . '", "nome": "' . $dadoEmpresa['nome_empresa'] . '" }';

                        echo "
                            <div class='dropdown-center'>     
                                <div class='card-item pt-3 mb-0 pasta' data-item='{$dataPasta}'>
                                    <i class='bi bi-folder fs-7 d-flex justify-content-center'></i>
                                    <p class='text-limitado text-center overflow-hidden px-2 mb-0'>{$dadoEmpresa['nome_empresa']}</p>
                                </div>
                                <ul class='dropdown-menu'>
                                    <li><a class='dropdown-item' href='{$link}empresa={$dadoEmpresa['id']}&p=ged' >
                                        <i class='bi bi-folder2-open me-2'></i>Abrir
                                    </a></li>
                                    <li><a class='dropdown-item' href='{$link}p=empresas&acao=editar&id={$dadoEmpresa['id']}' >
                                        <i class='bi bi-pencil me-2'></i>Editar
                                    </a></li>
                                </ul>
                            </div>
                        ";
                    }
                }

                $rowsEmpresas = mysqli_num_rows($qrEmpresas);
                if ($rowsEmpresas < 1)
                    echo '<div class="box position-absolute top-50 start-50 translate-middle"> <div class="conteudo-pasta text-center"> <i class="bi bi-building text-padrao fs-6 opacity-75"></i> <h3 class="opacity-75">Nada encontrado por aqui</h3> <p>Comece <a href="./index.php?p=empresa">cadastrando</a> uma empresa</p> </div> </div>';
            }
            ?>

        </div>
    </div>
</div>
<script>
    var funcaoUsuarioAtual = '<?= $sessaoUsuario["funcao"]; ?>';
</script>
<script src="./assets/js/ged/ged.js<?= $version; ?>"></script>
