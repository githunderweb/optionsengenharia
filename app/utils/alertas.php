<?php
if (isset($_SESSION['alert_success'])) :
?>
    <div class="toast fade align-items-center text-bg-success border-0 mb-3 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1039;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle fs-5 lh-1 me-2"></i><?= $_SESSION['alert_success']; ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
<?php
endif;
unset($_SESSION['alert_success']);

if (isset($_SESSION['alert_danger'])) :
?>
    <div class="toast fade align-items-center text-bg-danger border-0 mb-3 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1039;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-x-circle fs-5 lh-1 me-2"></i><?= $_SESSION['alert_danger']; ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
<?php
endif;
unset($_SESSION['alert_danger']);

if (isset($_SESSION['alert_warning'])) :
?>
    <div class="toast fade align-items-center text-bg-warning border-0 mb-3 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1039;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle fs-5 lh-1 me-2"></i><?= $_SESSION['alert_warning']; ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
<?php
endif;
unset($_SESSION['alert_warning']);
?>