<?php
//this is an api to verify users on the server

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
require_once('s3upload/image_check.php');
require_once('s3upload/s3_config.php');

//random file name generator for image
function randomFileNameGenerator($prefix){
	$r=substr(str_replace(".","",uniqid($prefix,true)),0,20);
	if(file_exists("../uploads/$r")) randomFileNameGenerator($prefix);
	else return $r;
}

$success=$msg="0";$data=array();$phn_status=0;$v_code=0;
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
//Recieved parameters from device
$username=$_REQUEST['name'];
$image=$_FILES['photo'];
$password=isset($_REQUEST['password']) && $_REQUEST['password'] ? $_REQUEST['password'] : null;
$phone=$_REQUEST['phone_number'];
$token=$_REQUEST['token'];
$apn_id=$_REQUEST['apn_id']?$_REQUEST['apn_id']:"";

global $conn;

if(!($phone && $token)){
	$success="0";
	$msg="Incomplete Parameters";
	$data=array();
}

else{ 

	if($image){
		$randomFileName=randomFileNameGenerator("Img_").".".end(explode(".",$image['name']));
				$image_path=$randomFileName;
				$img=BASE_PATH.$image_path;
				
		}
	else{
		$image_path="";
		$img="";
	}		
	
	//upload script	
		if($image_path){
		$name = $_FILES['photo']['name'];
		$size = $_FILES['photo']['size'];
		$tmp = $_FILES['photo']['tmp_name'];
		$ext = getExtension($name);
		
		$actual_image_name = $image_path;
		if($s3->putObjectFile($tmp, $bucket , $actual_image_name, S3::ACL_PUBLIC_READ) )
		{
		$status=1;
		//$success='1';
		//$msg = "Upload Successful.";	
		//$s3file='http://'.$bucket.'.s3.amazonaws.com/'.$actual_image_name;
		}
		else
		$status=0;
		}

	$sql="select * from users where phone_number=:phone";
	$sth=$conn->prepare($sql);
	$sth->bindValue("phone",$phone);
	try{$sth->execute();}
	catch(Exception $e){echo $e->getMessage();}
	$result=$sth->fetchAll(PDO::FETCH_ASSOC);
	$uid=$result[0]['id'];
	
	if(count($result)){
		$v_code=DataClass::generateRandomString();		
		$sql="update users set username=:username,password=:password,photo=:photo,verification_code=:vcode where phone_number=:phone";
		$sth=$conn->prepare($sql);
		$sth->bindValue("username",$username);
		$sth->bindValue("password",md5($password));
		$sth->bindValue("phone",$phone);
		$sth->bindValue("vcode",$v_code);
		$sth->bindValue("photo",$image_path);
		$count=0;
		try{
		$count=$sth->execute();
		$success=1;
		$msg="User Successfully registered";
		}
		catch(Exception $e){
		//echo $e->getMessage();
		}
		
		/*if($image){
	$sql="INSERT INTO `codebrew_gander`.`photos` (`id`, `user_id`, `image`, `date`, `auto_approved`, `created_on`) VALUES (DEFAULT, :user_id, :image,NOW(), 0, NOW())";
	
		$sth=$conn->prepare($sql);
		$sth->bindValue("user_id",$uid);
		$sth->bindValue("image",$image_path);
		try{$sth->execute();}
		catch(Exception $e){}
	}*/
		
		
		}
	
	}	

// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
if($success==1)
echo json_encode(array("success"=>$success,"msg"=>$msg,"token"=>$v_code,"image_path"=>$img,"phone_number"=>$phone,"user_id"=>$uid));
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>