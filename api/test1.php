<?php 


require_once("../php_include/db_connection.php");


$uname=$_REQUEST['username'];
global $conn;
$sql="select apn_id from users where username=:uname";
$sth=$conn->prepare($sql);
$sth->bindValue('uname',$uname);
try{$sth->execute();}
catch(Exception $e){}
$res=$sth->fetchAll();

print_r($res);die;


?>