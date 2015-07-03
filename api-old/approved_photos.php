<?php
//this is an api to request photos approval

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$user1=$_REQUEST['user1'];   //owner id
$user2=$_REQUEST['user2'];  //requestor id

if(!($user1 && $user2)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{

	$sql="select * from users where id=:user1";
	$sth=$conn->prepare($sql);
	$sth->bindValue('user1',$user1);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res=$sth->fetchAll();

	$sql="select photo_approved.*,photos.id as pid,photos.*,photos.date as photo_date,Year(UTC_TIMESTAMP())-Year(photos.date) as time_elapsed from photo_approved join photos on photos.id=photo_approved.photo_id where photo_approved.user_id=:user2 and photos.user_id=:user1 group by pid";
	$sth=$conn->prepare($sql);
	$sth->bindValue('user2',$user2);
	$sth->bindValue('user1',$user1);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res1=$sth->fetchAll();
	if(count($res1)){
	
	$success='1';
	$msg="Approved Photos Found";
		foreach($res as $key=>$value){
		
			$data['profile']=array(
			'owner_id'=>$value['id'],
			'username'=>$value['username']?$value['username']:"",
			'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
			);
		
		}
		
		if($res1){
		foreach($res1 as $key=>$value){
		$data['pictures'][$key]= array(
			"image_id"=>$value['pid']?$value['pid']:"",
			"image_url"=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
			"time_elapsed"=>$value['time_elapsed']?$value['time_elapsed']:0,
			"photo_date"=>$value['photo_date']?$value['photo_date']:""
			);
		}
		}
		
			
		
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