<?php
//this is an api to recover password
// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once "../php_include/db_connection.php"; 

//require_once('../PHPMailer_5.2.4/class.phpmailer.php');
require_once("DataClass.php");
require_once('../twilio/Services/Twilio.php');


$success=$msg="0";$data=array();

// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
$phone=$_REQUEST['phone_number'];

if(!($phone)){
	$success="0";
	$msg="Incomplete Parameters";
}
else{
	$sql="select * from users where phone_number=:phone_number and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue("phone_number",$phone);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res=$sth->fetchAll();

	if(count($res)){
	$token=$res[0]['verification_code'];
		$success="1";
		$msg="An reset link sent to you";
		$path=BASE_PATH.'reset_password.php?token='.$token;
		$body="Reset your Password using $path";
		
		try{
		DataClass::sendSMS($phone,$body);
		}
		catch(Exception $e){}
}
else{
		$success="0";
		$msg="The phone number seems invalid";
	}
	}
// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>