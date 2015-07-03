<?php
//this is an api to request photos

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
require_once ('../easyapns/apns.php');
require_once('../easyapns/classes/class_DbConnect.php');
$db = new DbConnect('localhost', 'codebrew_super', 'core2duo', 'codebrew_gander');

$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$date=date('Y-m-d H:i:s');
$token=$_REQUEST['token'];
$user_date=$_REQUEST['user_date']?$_REQUEST['user_date']:$date;
$user2=$_REQUEST['user2'];

if(!($token && $user2)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{
	$sql="select * from users where verification_code=:token and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('token',$token);
	try{$sth->execute();}
	catch(Exception $e){}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	$uname=$res[0]['username'];
	
	if(count($res)){
	$sql="select * from date_request where user_id_owner=:user2 and user_id_requestor=:user_id and date_request.date=date_format(UTC_TIMESTAMP(),'%Y-%m-%d')";
	$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$uid);
		$sth->bindValue("user2",$user2);
		try{$sth->execute();}
		catch(Exception $e){}
		$res1=$sth->fetchAll();
		
	if(!count($res1)){
	$sql="INSERT INTO `codebrew_gander`.`date_request` (`id`, `user_id_requestor`, `user_id_owner`, `date`, `created_on`,`status`) VALUES (DEFAULT, :user_id, :user2,:user_date,UTC_TIMESTAMP(),0)";
	
		$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$uid);
		$sth->bindValue("user2",$user2);
		$sth->bindValue("user_date",$user_date);
		$count=0;
		try{$count=$sth->execute();
		$success=1;
		$msg="Request Sent";
		}
		catch(Exception $e){}
		
		if($count){
		$sql="select apn_id from users where id=:uid";
		$sth=$conn->prepare($sql);
		$sth->bindValue('uid',$user2);
		try{$sth->execute();}
		catch(Exception $e){
		//echo $e->getMessage();
		}
		$r34=$sth->fetchAll();
		
		$message['type']='5';//request push
		$apnid=$r34[0]['apn_id'];
		$message['msg']=$uname." requested one of your photos from today!";
		if(!empty($apnid)){
			try{
			$apns->newMessage($apnid);
			$apns->addMessageAlert($message['msg']);
			$apns->addMessageSound('Siren.mp3');
			$apns->addMessageCustom('uid', $uid);
			$apns->addMessageCustom('name', $uname);
			$apns->addMessageCustom('t', $message['type']);
			$apns->queueMessage();
			$apns->processQueue();
			}
			catch(Exception $e){
			//echo $e->getMessage();
			}
			
			}
				
		}
	
	}
	else{
	$success=1;
	$msg="Request Already Sent";
	}}
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