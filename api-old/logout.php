<?php
//this is an api to logout users
// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");

$success=$msg="0";$data=array();

// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
$token=$_REQUEST['token'];

if(!($token)){
	$success="0";
	$msg="Incomplete Parameters";
}
else{
	$sql="select id from users where verification_code=:token ";
	$sth=$conn->prepare($sql);
	$sth->bindValue("token",$token);
	try{$sth->execute();}
	catch(Exception $e){}
	$result=$sth->fetchAll();
	$uid=$result[0]['id'];

	if(count($result)){
	$sql="update users set apn_id='' where id=:id";
	$sth=$conn->prepare($sql);
	$sth->bindValue("id",$uid);
	
	try{$sth->execute();
	$success=1;
	$msg="Logout successful";
	}
	catch(Exception $e){}
	
}
else{
	$success="0";
	$msg="Invalid User";
}
}
// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+

echo json_encode(array("success"=>$success,"msg"=>$msg));
?>