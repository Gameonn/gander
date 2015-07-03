<?php
//this is an api to request photos

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");

$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
//date_default_timezone_set("Asia/Kolkata");
$date=date('Y-m-d');
$token=$_REQUEST['token'];
$user_date=$_REQUEST['user_date']?$_REQUEST['user_date']:$date;
$zone=$_REQUEST['zone']?$_REQUEST['zone']:19800;

$current_date=DataClass::get_current_date($date,$zone);
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
	$sql="select users.id as uid,users.username,users.photo,photos.*,photos.id as pid,Year(NOW())-Year(photos.date) as time_elapsed,photos.date as photo_date from date_request join users on user_id_requestor=users.id join photos on photos.user_id=date_request.user_id_owner and (date_format(photos.date,'%m-%d')=date_format(  '$user_date','%m-%d') or (DATE(photos.date) BETWEEN ('$user_date' - INTERVAL 6 DAY) AND ('$user_date'))) where user_id_owner=:uid and (date_format(FROM_UNIXTIME(UNIX_TIMESTAMP(date_request.date) +".SERVER_OFFSET."+ ({$zone})   ),'%m-%d')=date_format(FROM_UNIXTIME(UNIX_TIMESTAMP('$user_date') +".SERVER_OFFSET."+ ({$zone}) ),'%m-%d')) and status=0";

	/*$sql="select users.id as uid,users.username,users.photo,photos.*,photos.id as pid,Year(NOW())-Year(photos.date) as time_elapsed,photos.date as photo_date from date_request join users on user_id_requestor=users.id join photos on photos.user_id=date_request.user_id_owner and (date_format(photos.date,'%m-%d')=date_format(  '$user_date','%m-%d') or (DATE(photos.date) BETWEEN ('$user_date' - INTERVAL 6 DAY) AND ('$user_date'))) where user_id_owner=:uid and status=0";*/
	//echo $sql;die;
	$sth=$conn->prepare($sql);
	$sth->bindValue("uid",$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	$res1=$sth->fetchAll();
		
	if(count($res1)){
	$success='1';
	$msg="Request Found";

		if($res1){
		foreach($res1 as $key=>$value){
		
		if(!ISSET($final[$value['uid']])){
			$final[$value['uid']]=array(
			'requestor_id'=>$value['uid'],
			'username'=>$value['username']?$value['username']:"",
			'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
			'pictures'=>DataClass::get_pictures($value['uid'],$uid,$zone,$user_date)
			//'pictures'=>array()
			);
		
		}
		/*if(!ISSET($final[$value['uid']]['pictures'][$value['pid']])){
		if($value['pid']){
		$final[$value['uid']]['pictures'][$value['pid']]=array(
			"image_id"=>$value['pid']?$value['pid']:"",
			"image_url"=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
			"time_elapsed"=>$value['time_elapsed']?$value['time_elapsed']:0,
			"photo_date"=>$value['photo_date']?$value['photo_date']:""
			);
		}}*/
		}
		}
			
		if($final){
		foreach($final as $key=>$value){
		$data2=array();
		foreach($value['pictures'] as $value2){
			$data2[]=$value2;
		}
		$value['pictures']=$data2;
		$data[]=$value;
		}
		}
	
	}
	else{
	$success=1;
	$msg="No Requests Found";
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
echo json_encode(array("success"=>$success,"msg"=>$msg,"data"=>$data,"current_date"=>$current_date));
}
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>