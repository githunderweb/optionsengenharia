<?php
if (isset($_SESSION["alert_success"])) {
    if (!is_array($_SESSION["alert_success"]))
        $_SESSION["alert_success"] = [$_SESSION["alert_success"]];

    foreach ($_SESSION["alert_success"] as $alerta) {
?>
        <div class="alert alert-success border-2 d-flex align-items-center" role="alert">
            <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-3"></span></div>
            <p class="mb-0 flex-1"><?= $alerta; ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
    }
}
unset($_SESSION["alert_success"]);

if (isset($_SESSION["alert_danger"])) {
    if (!is_array($_SESSION["alert_danger"]))
        $_SESSION["alert_danger"] = [$_SESSION["alert_danger"]];

    foreach ($_SESSION["alert_danger"] as $alerta) {
    ?>
        <div class="alert alert-danger border-2 d-flex align-items-center" role="alert">
            <div class="bg-danger me-3 icon-item"><span class="fas fa-times-circle text-white fs-3"></span></div>
            <p class="mb-0 flex-1"><?= $alerta; ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
    }
}
unset($_SESSION["alert_danger"]);

if (isset($_SESSION["alert_warning"])) {
    if (!is_array($_SESSION["alert_warning"]))
        $_SESSION["alert_warning"] = [$_SESSION["alert_warning"]];

    foreach ($_SESSION["alert_warning"] as $alerta) {
    ?>
        <div class="alert alert-warning border-2 d-flex align-items-center" role="alert">
            <div class="bg-warning me-3 icon-item"><span class="fas fa-times-circle text-white fs-3"></span></div>
            <p class="mb-0 flex-1"><?= $alerta; ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
    }
}
unset($_SESSION["alert_warning"]);
