<?php 
//this page is to handle all the admin events occured at client side
 require_once("php_include/db_connection.php"); 
require_once('PHPMailer_5.2.4/class.phpmailer.php');
function randomFileNameGenerator($prefix){
	$r=substr(str_replace(".","",uniqid($prefix,true)),0,20);
	if(file_exists("../uploads/$r")) randomFileNameGenerator($prefix);
	else return $r;
}
      
      
	$success=0;
	$msg="";
	session_start();
	//switch case to handle different events
	switch($_REQUEST['event']){
	case "reset-password":
		$token=$_REQUEST["token"];
		$password=$_REQUEST["password"];
		$confirm=$_REQUEST["confirm"];
		$base=BASE_PATH."/reset-password.php";
		if($password==$confirm){
				$sth=$conn->prepare("update users set password=:password where verification_code=:token");
				$sth->bindValue("token",$token);
				$sth->bindValue("password",md5($password));
				$count=0;
				try{$count=$sth->execute();}
				catch(Exception $e){}
				if($count){
					$success=1;
					$msg="Password changed successfully";
					$path=$base.'?success='.$success.'&msg='.$msg;
				}
			}else{
				$success=0;
				$msg="Passwords didn't match";
				$path=$base.'?token='.$token.'&success='.$success.'&msg='.$msg;
			}
	header("Location: $path");
	break;
	
	
	
}	
?>