<?php
//this is an api to edit profile 

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
$success=$msg="0";$data=array();

//random file name generator for image
function randomFileNameGenerator($prefix){
	$r=substr(str_replace(".","",uniqid($prefix,true)),0,20);
	if(file_exists("../uploads/$r")) randomFileNameGenerator($prefix);
	else return $r;
}
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$token=$_REQUEST['token'];
$username=$_REQUEST['name'];
$image=$_FILES['photo'];

if(!($token && $username)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{

	if($image){
		$randomFileName=randomFileNameGenerator("Img_").".".end(explode(".",$image['name']));
				if(@move_uploaded_file($image['tmp_name'], "../uploads/$randomFileName")){
				$image_path=$randomFileName;
				$img_path=BASE_PATH."timthumb.php?src=uploads/".$image_path;
				}else{
				$image_path="";
				$img_path="";
				}
		}
	else{
		$image_path="";
		$img_path="";
	}	

	$sql="select * from users where verification_code=:token and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('token',$token);
	try{$sth->execute();}
	catch(Exception $e){}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	
	if(count($res)){
	if($image_path)
	$sql="update users set username=:name,photo=:photo where id=:id";
	else
	$sql="update users set username=:name where id=:id";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue('id',$uid);
	$sth->bindValue('name',$username);
	if($image_path) $sth->bindValue('photo',$image_path);
	try{$sth->execute();
	$success=1;
	$msg="Profile Updated";
	}
	catch(Exception $e){
	echo $e->getMessage();
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
if($success==1){
echo json_encode(array("success"=>$success,"msg"=>$msg,"image_name"=>$image_path,"image_path"=>$img_path));
}
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>