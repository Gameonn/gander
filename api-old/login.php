<?php
//this is an api to login users

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$phone=$_REQUEST['phone_number'];
$password=$_REQUEST['password'];
$apn_id=$_REQUEST['apn_id']?$_REQUEST['apn_id']:0;

if(!($phone && $password)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{

	$sql="select * from users where phone_number=:phone and password=:password and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('phone',$phone);
	$sth->bindValue('password',md5($password));
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	if(count($res)){
	
	if($apn_id){
	$sql="update users set apn_id='' where apn_id=:apn_id";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue('apn_id',$apn_id);
	try{$sth->execute();}
	catch(Exception $e){}
	
	$sql="update users set apn_id=:apn_id where id=:uid";
	$sth=$conn->prepare($sql);
	$sth->bindValue('uid',$uid);
	$sth->bindValue('apn_id',$apn_id);
	$count=0;
	try{$count=$sth->execute();}
	catch(Exception $e){}
	} 
	
	$tnt=DataClass::get_profile($phone);
	$data['profile']=$tnt?$tnt:[];
	
	//$tnt1=DataClass::get_photos($phone);
	//$data['photos']=$tnt1?$tnt1:[];
	if($data){
	$success=1;
	$msg="Profile Active";
	}
	else{
	$success=0;
	$msg="No Records Found";
	}
	}
	else{
	$success=0;
	$msg="Invalid Phone Number or Password";
	}
	

}
// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
if($success==1){
echo json_encode(array("success"=>$success,"msg"=>$msg,"data"=>$data));
}
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>