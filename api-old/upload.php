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

$image=$_FILES['photo'];
$image_click=$_REQUEST['image_clicktime'];
$image_name=$_REQUEST['image_name'];
if(!($image)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}
else{
	
	$sql="Update `codebrew_gander`.`photos` set image_click=:image_click where image=:image";
	
		$sth=$conn->prepare($sql);
		$sth->bindValue("image",$image_name);
		$sth->bindValue("image_click",$image_click);
		try{$sth->execute();}
		catch(Exception $e){}

		//$randomFileName=randomFileNameGenerator("Img_").".".end(explode(".",$image['name']));
				if(@move_uploaded_file($image['tmp_name'], "../uploads/$image_name")){
				$image_path=$randomFileName;
				$img_path=BASE_PATH."timthumb.php?src=uploads/".$image_path;
				$success=1;
				$msg="Image Saved";
				}
				
	$sql="Delete from photos where image_click=''";
	$sth=$conn->prepare($sql);
	try{$sth->execute();}
	catch(Exception $e){}
	
		}

// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+

echo json_encode(array("success"=>$success,"msg"=>$msg));
?>