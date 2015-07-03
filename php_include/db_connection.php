<?php
require_once('config.php');
try {
	$dsn = 'mysql:host='.$DB_HOST.';dbname='.$DB_DATABASE;
	$conn = new PDO($dsn, $DB_USER, $DB_PASSWORD);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$conn->exec("SET NAMES 'utf8'");
}
catch(PDOException $e) {
	echo 'ERROR: ' . $e->getMessage();
}

?>