<?php
session_start();
session_destroy();
// echo '<script>navigator.serviceWorker.getRegistrations().then(function (registrations) { for (let registration of registrations) { registration.unregister(); window.location.href = "./"; } })</script>';
header('Location: ./');
exit();
