<?php
//this is an api to update users images
// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
require_once('s3upload/image_check.php');
require_once('s3upload/s3_config.php');
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
	
		//upload script	
		$name = $_FILES['photo']['name'];
		$size = $_FILES['photo']['size'];
		$tmp = $_FILES['photo']['tmp_name'];
		$ext = getExtension($name);

		$actual_image_name = $image_name;
		if($s3->putObjectFile($tmp, $bucket , $actual_image_name, S3::ACL_PUBLIC_READ) )
		{
		$success='1';
		$msg = "Upload Successful.";	
		//$s3file='http://'.$bucket.'.s3.amazonaws.com/'.$actual_image_name;
		
		//updating image_click time as per image
		$sql="Update `codebrew_gander`.`photos` set image_click=:image_click where image=:image";
		$sth=$conn->prepare($sql);
		$sth->bindValue("image",$image_name);
		$sth->bindValue("image_click",$image_click);
		try{$sth->execute();}
		catch(Exception $e){}
		}
		else{
		$success='0';
		$msg = "Upload Fail.";
		}
		//removing images with nil image click time	
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