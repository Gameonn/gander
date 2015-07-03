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

$d=date('Y-m-d');
$token=$_REQUEST['token'];
$image=$_FILES['photo'];
$date=$_REQUEST['img_date']?$_REQUEST['img_date']:$d;
$auto_approval=$_REQUEST['auto_approval']?$_REQUEST['auto_approval']:0;
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
	catch(Exception $e){}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	if(count($res)){
	
	
	$randomFileName=randomFileNameGenerator("Img_").".".end(explode(".",$image['name']));
				if(@move_uploaded_file($image['tmp_name'], "../uploads/$randomFileName")){
				$image_path=$randomFileName;
				$img_path=BASE_PATH."timthumb.php?src=uploads/".$image_path;
				
				}
				else{
				$image_path="";
				$img_path="";
				}
	
	$sql="INSERT INTO `codebrew_gander`.`photos` (`id`, `user_id`, `image`, `date`, `auto_approved`, `created_on`) VALUES (DEFAULT, :user_id, :image,:date, :app, UTC_TIMESTAMP())";
	
		$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$uid);
		$sth->bindValue("image",$image_path);
		$sth->bindValue("app",$auto_approval);
		$sth->bindValue("date",$date);
		try{$sth->execute();
		$success=1;
		$msg="Image Uploaded";
		}
		catch(Exception $e){}
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