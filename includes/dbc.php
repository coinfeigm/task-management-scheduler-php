<?php 
DEFINE("DB_HOST", "192.168.1.107");
DEFINE("DB_USER", "root");
DEFINE("DB_PASS", "password");
DEFINE("DB_NAME", "polaris");

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
$opt = array(
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => true,
	PDO::ATTR_PERSISTENT 		 => true
);
$dbc = new PDO($dsn, DB_USER, DB_PASS, $opt);
$dbc->query('SET NAMES utf8');
?>