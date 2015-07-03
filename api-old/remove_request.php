<?php
//this is an api to request photos

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");

$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
$date=date('Y-m-d');
$token=$_REQUEST['token'];
$user2=$_REQUEST['user2'];
$flag=$_REQUEST['flag']?$_REQUEST['flag']:0;
$user_date=$_REQUEST['user_date']?$_REQUEST['user_date']:$date;

if(!($token)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{
	$sql="select * from users where verification_code=:token and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('token',$token);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	$uname=$res[0]['username'];
	
	if(count($res)){
	if($flag)
	$sql="select * from date_request where user_id_owner=:user_id and date_request.date=date_format('$user_date','%Y-%m-%d')";
	else
	$sql="select * from date_request where user_id_owner=:user_id and user_id_requestor={$user2} and date_request.date=date_format('$user_date','%Y-%m-%d')";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue("user_id",$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	$res1=$sth->fetchAll();
		
	if(count($res1)){
	if($flag)
	$sql="Delete from date_request where user_id_owner=:user_id and date_request.date=date_format('$user_date','%Y-%m-%d')";
	else
	$sql="Delete from date_request where user_id_owner=:user_id and user_id_requestor={$user2} and date_request.date=date_format('$user_date','%Y-%m-%d')";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue("user_id",$uid);
	try{$sth->execute();
	$success='1';
	$msg="Request Removed";
	}
	catch(Exception $e){}
	
	if($flag)
	$sql="update photo_approved set status=2 where owner_id=:owner_id and date_format(created_on,'%Y-%m-%d')=date_format(UTC_TIMESTAMP(),'%Y-%m-%d')";
	else
	$sql="update photo_approved set status=2 where owner_id=:owner_id and user_id={$user2} and date_format(created_on,'%Y-%m-%d')= date_format(UTC_TIMESTAMP(),'%Y-%m-%d')";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue("owner_id",$uid);
	try{$sth->execute();
	$success='1';
	$msg="Request Removed";
	}
	catch(Exception $e){}
	
	}
	else{
	$success=0;
	$msg="No request found on current date";
	}
	}
	else{
	$success=0;
	$msg="Invalid User";
	}
	

}
// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+

echo json_encode(array("success"=>$success,"msg"=>$msg));
?>