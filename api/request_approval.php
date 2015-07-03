<?php
//this is an api to request photos approval

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
require_once ('../easyapns/apns.php');
require_once('../easyapns/classes/class_DbConnect.php');
$db = new DbConnect('localhost', 'root', 'core2duo', 'codebrew_gander');
$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$token=$_REQUEST['token'];
$user2=$_REQUEST['user2'];//requestor
$photo_ids=$_REQUEST['photo_id'];// for approved photos

$img=$_REQUEST['image_id'];// for disapproved photos

$images=json_decode($img);
$pics=json_decode($photo_ids);

if(!($token && $user2 && $photo_ids)){
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
	echo $e->getMessage();
	}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	$uname=$res[0]['username'];
	
	
	if(count($res)){
	
	//$sql="delete from date_request where user_id_requestor=:user2 and user_id_owner=:user1 and date_request.`date`=date_format(UTC_TIMESTAMP(),'%Y-%m-%d')";
	$sql="delete from date_request where user_id_requestor=:user2 and user_id_owner=:user1 ";
	$sth=$conn->prepare($sql);
	$sth->bindValue('user2',$user2);
	$sth->bindValue('user1',$uid);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	
	foreach($pics as $value){
	$success=1;
	$msg="Photos Approved";
	$sql="update `codebrew_gander`.`photo_approved` set status=1 where photo_approved.user_id=:user_id and photo_approved.owner_id=:owner_id and photo_id=:photo_id";
		$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$user2);
		$sth->bindValue("owner_id",$uid);
		$sth->bindValue("photo_id",$value);
		try{$sth->execute();}
		catch(Exception $e){
		//echo $e->getMessage();
		}
	}
	
		if($images){
		foreach($images as $v){
	
		$sql="update `codebrew_gander`.`photo_approved` set status=2 where photo_approved.user_id=:user_id and photo_approved.owner_id=:owner_id and photo_id=:photo_id";
		$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$user2);
		$sth->bindValue("owner_id",$uid);
		$sth->bindValue("photo_id",$v);
		try{$sth->execute();}
		catch(Exception $e){
		//echo $e->getMessage();
		}
	}
	}
	
	$sql="select apn_id from users where id=:uid";
		$sth=$conn->prepare($sql);
		$sth->bindValue('uid',$user2);
		try{$sth->execute();}
		catch(Exception $e){
		//echo $e->getMessage();
		}
		$r34=$sth->fetchAll();
		$type='6';//approval push
		$message['msg']=$uname." shared some photos with you. Check them out!";
		$apnid=$r34[0]['apn_id'];
		if(!empty($apnid)){
		try{
		$apns->newMessage($apnid);
		$apns->addMessageAlert($message['msg']);
		$apns->addMessageSound('Siren.mp3');
		$apns->addMessageCustom('u1', $uid);//owner id
		$apns->addMessageCustom('t', $type);
		$apns->addMessageCustom('name', $uname);
		$apns->addMessageCustom('u2', $user2);//requestor id
		$apns->queueMessage();
		$apns->processQueue();
		}
		catch(Exception $e){}
		
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