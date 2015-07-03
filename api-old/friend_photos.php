<?php
//this is an api to get friends photos

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
$zone=$_REQUEST['zone']?$_REQUEST['zone']:19800;
$user_date=$_REQUEST['user_date']?$_REQUEST['user_date']:$date;
$cont=$_REQUEST['contacts'];//external contacts from device

$contacts=json_decode($cont);

if(!($contacts && $token)){
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
	
	$current_date=DataClass::get_current_date($date,$zone);
	$tnt1=DataClass::get_all_contacts();// contacts fetched from database
	
	foreach($tnt1 as $r){
	foreach($contacts as $row){
	
	$a= substr_compare($r['phone_number'],$row,-10,10);
	if($a==0){
	$dict[]=$row;
	}
	}
	}
	
	//matched contacts b/w device and database
	if($dict){
	foreach($dict as $key=>$val){
	$m=$val;
	$post_nums.=$m.',';
	}
	$num= rtrim($post_nums, ', ');
	
	$tnt=DataClass::get_friends_photos($num,$uid,$zone,$user_date);
	$data=$tnt?$tnt:[];
	
	if($data){
	$success=1;
	$msg="Profile Active";
	}
	else{
	$success=0;
	$msg="No Records Found";
	}
	}
	else{
	$success=0;
	$msg="No Records Found";
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