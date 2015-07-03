<?php
//this is an api to set profile picture

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
$image=$_FILES['photo'];
if(!($token && $image)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{


	if($image){
		$randomFileName=randomFileNameGenerator("Img_").".".end(explode(".",$image['name']));
				if(@move_uploaded_file($image['tmp_name'], "../uploads/$randomFileName")){
				$image_path=$randomFileName;}
		}
	else{
		$image_path="";
	}

	$sql="select * from users where verification_code=:token and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('token',$token);
	try{$sth->execute();}
	catch(Exception $e){}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	
	if(count($res)){
	/*
	$sql="select * from photos where image=:image and user_id=:user_id";
	$sth=$conn->prepare($sql);
	$sth->bindValue('image',$image_name);
	$sth->bindValue('user_id',$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	$res1=$sth->fetchAll();*/
	
	//if(count($res1)){
	$sql="update users set photo=:photo where id=:id";
	$sth=$conn->prepare($sql);
	$sth->bindValue('id',$uid);
	$sth->bindValue('photo',$image_path);
	try{$sth->execute();
	$success=1;
	$msg="Profile Picture Updated";
	$img_path=BASE_PATH.BASE_PATH."timthumb.php?src=uploads/".$image_path;
	}
	catch(Exception $e){}
	/*}
	else{
	$success=0;
	$msg="Image not uploaded yet";
	}*/
	}
	else{
	$success=0;
	$msg="Invalid User";
	}
	

}
// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
if($success==1){
echo json_encode(array("success"=>$success,"msg"=>$msg,"image_name"=>$image_path,"image_path"=>$img_path));
}
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>