<?php
//this is an api to update users images
// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

//random file name generator for image
function randomFileNameGenerator($prefix){
	$r=substr(str_replace(".","",uniqid($prefix,true)),0,20);
	if(file_exists("../uploads/$r")) randomFileNameGenerator($prefix);
	else return $r;
}

$token=$_REQUEST['token'];
$image_name=$_REQUEST['image_name'];
if(!($token && $image_name)){
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
	if(count($res)){
		
	$sql="select * from photos where image=:image and user_id=:user_id";
	$sth=$conn->prepare($sql);
	$sth->bindValue('image',$image_name);
	$sth->bindValue('user_id',$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	$res1=$sth->fetchAll();
	if(count($res1)){
	$app_status=$res1[0]['auto_approved'];
	if($app_status){
	$success='0';
	$msg="Already Approved";
	}
	else{
	$sql="update photos set auto_approved=1 where image=:image and user_id=:user_id";
	$sth=$conn->prepare($sql);
	$sth->bindValue('image',$image_name);
	$sth->bindValue('user_id',$uid);
	try{$sth->execute();
	$success='1';
	$msg="Image Approved";
	}
	catch(Exception $e){}
	}
	}
	else{
	$success=0;
	$msg="Image not uploaded yet";
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