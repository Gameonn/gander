<?php
//this is an api to register users on the server

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
require_once('../twilio/Services/Twilio.php');

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
$apn_id=$_REQUEST['apn_id']?$_REQUEST['apn_id']:"";
//$date=Date('Y-m-d');
global $conn;

if(!($phone && $password)){
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
	$v_code=DataClass::generateRandomString();
	$sql="select * from users where phone_number=:phone";
	$sth=$conn->prepare($sql);
	$sth->bindValue("phone",$phone);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$result=$sth->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($result)){
	$success="1";
	$msg="Phone Number already taken";
	$phn_status=1;
	
	$body="Verification Code for Gander is ".$v_code;
	try{
	DataClass::sendSMS($phone,$body);
	}
	catch(Exception $e){}
	
	}	
	else{	
	$success=1;
	
	if($apn_id){
	$sql="update users set apn_id='' where apn_id=:apn_id";
	
	$sth=$conn->prepare($sql);
	$sth->bindValue('apn_id',$apn_id);
	try{$sth->execute();}
	catch(Exception $e){}
	}
	
	//$body="Verification Code for Gander is ".$v_code;
	$sql="INSERT INTO `codebrew_gander`.`users` (`id`, `apn_id`, `username`, `password`, `photo`, `phone_number`, `verification_code`,`is_verified`, `is_deleted`, `created_on`) 
	VALUES (DEFAULT, :apn_id, :username, :password,:photo, :phone,:vcode, 0, 0, NOW())";
	
		$sth=$conn->prepare($sql);
		$sth->bindValue("username",$username);
		$sth->bindValue("apn_id",$apn_id);
		$sth->bindValue("password",md5($password));
		$sth->bindValue("phone",$phone);
		$sth->bindValue("vcode",$v_code);
		$sth->bindValue("photo",$image_path);
		$count=0;
		try{
		$count=$sth->execute();
		$uid= $conn->lastInsertId();
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
	
		$tnt=DataClass::get_profile($phone);
		$data['profile']=$tnt?$tnt:[];
	
	/*if($count){
	try{
	DataClass::sendSMS($phone,$body);
	}
	catch(Exception $e){}
	}*/
		}
	
	}	

// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
if($success==1)
echo json_encode(array("success"=>$success,"msg"=>$msg,"verification_code"=>$v_code,"status"=>$phn_status,"data"=>$data));
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>