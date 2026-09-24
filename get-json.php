<?php
session_start();
error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);

if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = parse_url($_SERVER['HTTP_REFERER']);
    if ($referrer["host"] == "localhost" || $referrer["host"] == "192.168.2.2" || ($referrer["host"] == "sistema.optionsengenharia.com.br" || $referrer["host"] == "www.sistema.optionsengenharia.com.br")) {
        include('./config.php');
        include('./verifica-login.php');

        $select = @$_GET['select'];
        $results = [];

        if ($select != "")
            $results = mysqli_fetch_all(mysqli_query($connect, $select), MYSQLI_ASSOC);

        echo json_encode($results);
    }
} else {
    header('Location: ./');
    exit();
}
