# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Equipment-inspection management system for Options Engenharia (NR-13 work: pressure vessels, boilers, safety valves). It is written in plain procedural PHP using `mysqli`, with jQuery and Bootstrap 5 on the front end. There is no framework, no Composer or npm, no build step and no automated test suite. Identifiers, table and column names and UI text are in Brazilian Portuguese; keep new code consistent with that.

## Running locally

- **Web server.** XAMPP lives at `C:\xampp`. Apache's document root is `C:\xampp\htdocs`, so the app is served at:
  - admin/client panel: `http://localhost/public_html/`
  - inspector app: `http://localhost/public_html/app/`

  Start Apache and MySQL from the XAMPP Control Panel, or with `C:\xampp\apache_start.bat` and `C:\xampp\mysql_start.bat`.
- **Configuration.** DB credentials are hard-coded in `config.php`: user `root`, no password, database `optionsengenharia` on localhost. The same file also sets UTF-8 and the `America/Sao_Paulo` timezone.
- **Database.** `banco/optionsengenharia.sql` is a phpMyAdmin dump of production. It has no `CREATE DATABASE` or `USE` statement, so create the database first and import into it. Run both commands from the repo root:

  ```
  C:/xampp/mysql/bin/mysql.exe -u root -e "CREATE DATABASE optionsengenharia"
  C:/xampp/mysql/bin/mysql.exe -u root optionsengenharia -e "source banco/optionsengenharia.sql"
  ```

  The dump was made on MariaDB 10.6, and XAMPP ships 10.4. If the import fails with `Unknown collation: 'utf8mb3_general_ci'`, replace it with `utf8_general_ci` in a copy of the dump.
- **PHP versions.** Local XAMPP runs **PHP 7.4.33**, while production runs **PHP 8.3** (per the dump header), and code must work on both. That rules out:
  - PHP 8-only syntax and functions (`match`, `?->`, named arguments, `str_contains`, …);
  - anything that PHP 8 turns into a fatal `TypeError`, such as `count()` on `null`.

## Checking changes

The only automated check is PHP's syntax lint. After linting, exercise the affected page in the browser.

- One file: `C:/xampp/php/php.exe -l path/to/file.php`
- All project PHP (Git Bash), skipping vendored libraries and upload directories:

```bash
find . \( -path ./.git -o -path ./vendors -o -path ./app/vendors -o -path ./classes -o -path ./arquivos -o -path ./equipamentos/arquivos -o -path ./inspecoes/arquivos \) -prune -o -name "*.php" -print0 | xargs -0 -n1 /c/xampp/php/php.exe -l | grep -v "^No syntax errors"
```

When debugging, note that every request runs `error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED)`, both in `verifica-login.php` and at the top of action scripts. The logical `&&` makes this `error_reporting(1)`, which shows fatal errors only and hides every warning and notice. Override it temporarily while you debug. Correcting it globally would surface a flood of notices from existing code.

## Architecture

### Two front ends, one database

- **Admin/client panel (repo root).** `index.php` is the only layout and router:
  - `?p=<page>` is looked up in the `$paginas` array, which maps each page to a file and a required role. The file is `include`d. Unknown or forbidden pages render `404.php`.
  - `?acao=` picks the sub-view inside a page file: `""` shows the list, and `editar`, `excluir`, `ver` and so on show the others.
  - To add a page, create the file, then add a `$paginas` entry and a sidebar link in `index.php`.
- **Inspector app (`app/`).** A mobile-first UI for field inspectors. It has its own:
  - router, `app/index.php`, with no roles;
  - layout, with Bootstrap and jQuery loaded from a CDN;
  - assets and vendors;
  - login, `app/sigin.php`, which checks the `inspetores` table.

  It shares `config.php` with the panel. Inspection submissions go to `app/controllers/inspecao.php`.
- **Shared session key.** Both sides store their user in `$_SESSION['sessao_usuario']`, but with different row shapes (`usuarios` vs `inspetores`). Each side's `verifica-login.php` re-validates the session against its own table on every request and logs out on a mismatch. As a result, logging into one side in a browser logs you out of the other. Changing a user's credentials or role also ends their session.

### Admin request pattern

- **Page files** run inside `index.php`'s global scope. Each module has `<module>/<plural>.php` for the list, edit and delete views and `<module>/<singular>.php` for the create form. Page files use these layout globals:

  | Global | What it holds |
  |---|---|
  | `$connect` | the `mysqli` connection |
  | `$sessaoUsuario` | the full `usuarios` row |
  | `$link` | `./index.php?`, plus `empresa=…&` when a company is selected; append `p=...` |
  | `$linkHome` | the link to the home page |
  | `$empresaAtual` | the company currently selected |
  | `$acao` | the sub-view from `?acao=` |
  | `$version` | the cache-busting query string for JS/CSS |

  Page files declare helper functions at the top level, so every helper name must be unique across the app. By convention the page name is added as a suffix, as in `textoDashboard` or `textoFiltroEquipamentos`.
- **Action scripts** (`<module>/<singular>-acao.php?acao=...`) receive the form POSTs as standalone scripts. Each one runs these steps:
  1. include `../config.php`, `../verifica-login.php` and `../functions.php`;
  2. do the database work;
  3. set a flash message in `$_SESSION["alert_success"|"alert_danger"|"alert_warning"]`, either a string or an array; `alertas.php` renders and clears it;
  4. redirect with `header("Location: .{$link}p=...")`. Here `$link` comes from `$_SESSION["empresa_atual_link"]`, which `index.php` saves. The leading `.` turns `./index.php?` into `../index.php?`, because action scripts live one directory down.
- **JavaScript.** `assets/js/main.js` calls a global `inicio()` function on document ready, if one is defined.
  - Module scripts in `assets/js/<module>/` each define `inicio()`. They are loaded by a `<script>` tag at the bottom of the page file, cache-busted with `<?= $version ?>`.
  - Bump `$version` in `index.php` (or in `app/index.php`) whenever you change JS or CSS.
  - Every `table.dataTable` is initialized automatically by `assets/js/dataTable.js`. Its options come from a `data-table='{...}'` attribute.
- **AJAX.** `get-json.php?select=<SQL>` and `app/utils/get-json.php` run a raw SQL string built in the browser and return the rows as JSON. The only protection is the login check plus a Referer-host check. `assets/js/equipamentos/`, `assets/js/ordens-servico/` and the `app/` scripts all depend on these endpoints. Dedicated endpoints such as `unidades/busca-unidades.php` are the safer pattern.

### Roles and data scoping

- **Roles.** `usuarios.funcao` is either `Administrador` or `Cliente`. Check it with `permissaoUsuario($required, $sessaoUsuario["funcao"])`, where `$required` is one role, an array of roles, or `"Todos"`. Inspectors are not a role. They are stored separately in `inspetores`.
- **Administrators** can limit any page to one company with the selector in the top bar (`?empresa=<id>`; an empty value means all companies).
- **Clientes** are read-only. They only see the companies listed for them in `usuario_empresas` and the equipment types listed in `usuario_tipos_equipamento`. `verifica-login.php` works out the current company from `?empresa=<id>|todas` (kept in `$_SESSION["empresa_atual"]`) and overwrites `$sessaoUsuario["id_empresa"]` with it.
- **Scoping queries.** Any query that shows equipment or inspection data to a Cliente must add `condicaoEquipamentosPermitidosUsuario($connect, $sessaoUsuario["id"], "e", $sessaoUsuario["id_empresa"])`. This returns `1 = 0` when the user has no permissions.
- **Blocking writes.** Action scripts must reject Cliente writes themselves, as `equipamentos/equipamento-acao.php` does. Disabling inputs on the page only changes how it looks.

### Domain model and dynamic fields

- **Hierarchy.** `empresas` → `unidades` → `equipamentos`. An equipment item is identified by its `tag` within one empresa + unidade pair. It is also classified by `tipos_equipamento`, `categorias_equipamento` and `locais_instalacao`.
- **Custom fields are the core abstraction.** Fields are defined in two tables:
  - `campos_tipo_equipamento` holds the fields of an equipment type;
  - `campos_modelo_relatorio` holds the fields of a report template (`modelos_relatorio`), and each template belongs to one equipment type.

  Both tables use the same type enum: `Texto`, `Texto longo`, `Múltipla escolha`, `Caixa de seleção`, `Lista suspensa`, `Upload de arquivo`, `Data`, `Horário` and `Data e Hora`. Choice options are stored comma-separated in the `opcoes` column.
- **Values are snapshots.** Each stored value copies the field's slug, type and title, so it stays as it was even if the field definition changes later. Values live in two tables:
  - `valores_campos_personalizados_equipamento` holds one row per field per equipment;
  - `valores_inspecao` holds one row per field per inspection. Its `origem` column records whether the value came from `campos_tipo_equipamento` or `campos_modelo_relatorio`.

  Answers with several values are joined with `", "`. Upload fields store `|`-separated filenames.
- **Form processing is duplicated.** Forms are rendered on the server from the field definitions. A hidden JSON input lists the fields: `dados` for inspections, `campos` for equipment. The action script loops over that list and reads `$_POST[slug]` and `$_FILES[slug]` for each field. The code that handles each type is copy-pasted across three files: `app/controllers/inspecao.php`, `inspecoes/inspecao-acao.php` and `equipamentos/equipamento-acao.php`. Adding or changing a field type therefore means updating:
  - all three of those files;
  - the form renderers;
  - the `enum` column in all four field and value tables;
  - `inspecoes/gerar-pdf.php`.
- **Slugs are unique per table, not per type or template.** Slugs come from `slugfy()` in `functions.php`. It checks for duplicates across the whole table; its `$addWhere` scope argument is built but never used. When a field title repeats on another type or template, its slug gets a suffix such as `-2`. Code that looks for a special field must therefore match a slug prefix or the title, not the exact slug. Two groups of special fields depend on this:
  - **Expiry fields** (slugs starting `vencimento-interno`, `vencimento-externo`, `proxima-inspecao`, `proxima-calibracao`, or `vencimento[-N]`) set the equipment "semáforo" colour (red, orange, green or grey) in `equipamentos/equipamentos.php` and `dashboard.php`.
  - **`STATUS DO EQUIPAMENTO`** (values `EQUIPAMENTO APTO` and `EQUIPAMENTO INAPTO`) drives the Cliente filters and charts.

### Inspection lifecycle

1. **An administrator opens a service order.** The admin creates an `ordens_servico` row (OS) for one empresa, unidade and inspector, then picks report templates and a quantity for each. This creates one `modelos_relatorio_os` row per report to fill. The OS starts with status `Em aberto`.
2. **The inspector fills in the reports.** In `app/`, the inspector sees their open orders (`Em aberto`) and fills in each report.
   - Each report becomes an `inspecoes` row with `status_inspecao = 'Pendente'`, plus its `valores_inspecao` rows.
   - Uploaded files go to `inspecoes/arquivos/<id>/<slug>/`.
   - Editing a report sets it back to `Pendente`.
3. **An administrator reviews the report** at `?p=inspecoes&acao=ver`. `inspecoes/inspecao-acao.php` handles approving and rejecting. Approving an inspection does three things:
   - once all of the OS's reports are approved, it moves the OS to `Pendente`;
   - it finds the equipment by tag + empresa + unidade and updates it, or creates it if none exists;
   - it copies the inspection's values with `origem = 'campos_tipo_equipamento'` into the equipment's custom-field values.
4. **The approved inspection can be exported as a PDF.** `inspecoes/gerar-pdf.php` builds it with the custom `PDF` class in `classes/TCPDF/pdf-padrao.php`, which adds the watermark and footer. Signature images come from `assets/img/{usuarios,inspetores}/assinaturas/`.

### Files, vendored code, conventions

- **Uploaded files are runtime data, not source code.** Together they hold about 1.5 GB of production files:
  - `equipamentos/arquivos/<id>/<slug>/` holds equipment attachments, served through `equipamentos/visualizar-arquivo.php`, which checks permissions.
  - `inspecoes/arquivos/<id>/<slug>/` holds inspection uploads.
  - `arquivos/<nome_empresa>/` is the GED document store, backed by the tables `pastas`, `arquivos` and `downloads_arquivo`. `empresas/empresa-acao.php` creates, renames and deletes each company's folder along with the company. The `?p=ged` route still works, but its menu link is commented out.
  - `assets/img/usuarios/` and `assets/img/inspetores/` hold avatars and signature images.

  Keep all of these, and `banco/*.sql`, out of commits. `.gitignore` excludes them and keeps each directory with a `.gitkeep`, because the avatar and signature upload code does not create its directories.
- **Third-party code: don't edit `vendors/`, `app/vendors/`, `classes/TCPDF/` (6.6.2) or `classes/PHPMailer-master/`.** The one exception is `classes/TCPDF/pdf-padrao.php`, which is project code.
  - PHPMailer is unused. Email goes through `enviarEmail()` in `functions.php`, which calls PHP's `mail()`.
  - `assets/css/theme*.css`, `assets/css/user*.css` and `assets/js/theme.js` come from the Falcon Bootstrap template. Project styles live in `assets/css/main.css` and `app/assets/css/main.css`.
- **Two coding styles coexist; follow the recent one.**
  - Legacy code interpolates request values into SQL. It escapes them with `mysqli_real_escape_string` at best, and usually leaves `$_GET` ids unescaped. It also echoes database values without HTML-escaping them.
  - Recent code uses `intval`, prepared statements and `htmlspecialchars`. It checks file paths with `realpath` containment and compares session tokens with `hash_equals`. For examples, see `equipamentos/visualizar-arquivo.php` and the `excluir-arquivo` action in `equipamentos/equipamento-acao.php`.
- **Passwords are unsalted MD5** for both `usuarios` and `inspetores`, because login compares against `md5(...)` in SQL. Any code that writes a password must hash it the same way.
