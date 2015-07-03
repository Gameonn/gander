<?php
//this is an api to get friends 

// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once("../php_include/db_connection.php");
require_once("DataClass.php");
$success=$msg="0";$data=array();
// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+

$cont=$_REQUEST['contacts'];
$token=$_REQUEST['token'];
$contacts=json_decode($cont);


if(!($contacts && $token)){
	$success="0";
	/*if(!($contacts))
	$msg="Contacts List Empty";
	else*/
	$msg="Incomplete Parameters";
	$data=array();
}
else{

	$sql="select * from users where verification_code=:token and is_deleted=0";
	$sth=$conn->prepare($sql);
	$sth->bindValue('token',$token);
	try{$sth->execute();}
	catch(Exception $e){
	//echo $e->getMessage();
	}
	$res=$sth->fetchAll();
	$uid=$res[0]['id'];
	
	if(count($res)){
	
	$tnt1=DataClass::get_all_contacts();
	
	foreach($tnt1 as $r){
	foreach($contacts as $row){
	
	$a= substr_compare($r['phone_number'],$row,-10,10);
	if($a==0){
	$dict[]=$r['phone_number'];
	}
	}
	}
	
	if($dict){
	foreach($dict as $key=>$val){
		$m=$val;
		$post_imgs.=$m.',';
		}
		$num= rtrim($post_imgs, ', ');
	
	$tnt=DataClass::get_friends($num,$uid);
	
	$data['profile']=$tnt?$tnt:[];
	
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
echo json_encode(array("success"=>$success,"msg"=>$msg,"data"=>$data));
}
else
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>