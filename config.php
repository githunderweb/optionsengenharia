<?php

$currency = 'R$ '; // Moeda 'R$ '

$db_username = 'root';
$db_password = '';
$db_name = 'optionsengenharia';
$db_host = 'localhost';

//$db_username = 'root';
//$db_password = '';
//$db_name = 'options';
//$db_host = 'localhost';

//Conectar no MySql
$connect = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($connect->connect_error) {
    die('Error : (' . $connect->connect_error . ') ' . $connect->connect_error);
}

$connect->set_charset("utf8");

// trata caracteres especiais
$query = $connect->query("SET NAMES 'utf8'");
$query = $connect->query('SET character_set_connection=utf8');
$query = $connect->query('SET character_set_client=utf8');
$query = $connect->query('SET character_set_results=utf8');
$query = $connect->query("SET NAMES 'utf8'");


date_default_timezone_set('America/Sao_Paulo');
