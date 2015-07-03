<?php
 
class DataClass{
	
	
public static function get_lg_data($phone){

global $conn;

$sql="SELECT users.*,users.id as uid,photos.id as pid,photos.* from users left join photos on photos.user_id=users.id where users.phone_number=:phone and Date_format(photos.date,'%d-%m')=Date_format(UTC_TIMESTAMP(),'%d-%m')";
$sth=$conn->prepare($sql);
$sth->bindValue('phone',$phone);
try{$sth->execute();}
catch(Exception $e){}
$res2=$sth->fetchAll();

		if($res2){
			foreach($res2 as $key=>$value){
				$data['profile']=array('user_id'=>$value['uid'],
				'name'=>$value['username']?$value['username']:"",
				'phone_number'=>$value['phone_number']?$value['phone_number']:"",
				'profile_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:""
				);
				
				$data['photos'][$value['pid']]=array(
				'photo_id'=>$value['pid'],
				'photo'=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
				'date'=>$value['date'],
				'auto_approved'=>$value['auto_approved']
				);
				}
			}
			if($data['photos']){
			foreach($data['photos'] as $key=>$value){
		
			$data[]=$value;
		
			}
			}	
			
return $data;
}	

public static function get_pictures($uid,$owner_id,$zone,$user_date){

global $conn;
$path=BASE_PATH."timthumb.php?src=uploads/";
/*$sql="select count(photo_approved.id) as photo_approved_status from photo_approved where photo_approved.owner_id='$owner_id' and photo_approved.user_id='$uid' and DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone}) ))= DATE(FROM_UNIXTIME(UNIX_TIMESTAMP('23-06-2015 06:06:20') +".SERVER_OFFSET."+ ({$zone})))";

$sth=$conn->prepare($sql);
//$sth->bindValue('owner_id',$owner_id);
//$sth->bindValue('user_id',$uid);
try{$sth->execute();}
catch(Exception $e){}

$res=$sth->fetchAll();
 $app_status=$res[0]['photo_approved_status'];*/



$sql="select Year(NOW())-Year(photos.date) as time_elapsed,photos.date as photo_date,photos.id as image_id,concat('$path',photos.image) as image_url from date_request join users on user_id_requestor=users.id join photos on photos.user_id=date_request.user_id_owner and photos.auto_approved=0 and (date_format(photos.date,'%m-%d')=date_format(('$user_date'),'%m-%d') or (DATE(photos.date) BETWEEN (('$user_date')- INTERVAL 6 DAY) AND ('$user_date'))) where user_id_owner=:owner_id and (date_format(FROM_UNIXTIME(UNIX_TIMESTAMP(date_request.date)  +".SERVER_OFFSET."+ ({$zone})   ),'%m-%d') = date_format(FROM_UNIXTIME(UNIX_TIMESTAMP('$user_date')  +".SERVER_OFFSET."+ ({$zone}) ),'%m-%d')) and date_request.status=0 and photos.id NOT IN (SELECT photo_approved.photo_id from photo_approved where photo_approved.user_id=:user_id and photo_approved.owner_id=:owner_id and photo_approved.status IN (1,2)) group by photos.id";

	$sth=$conn->prepare($sql);
	$sth->bindValue("owner_id",$owner_id);
	$sth->bindValue('user_id',$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	
	$res1=$sth->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($res1 as $k=>$v){

	$sql="select * from photo_approved where user_id=:user_id and owner_id=:owner_id and photo_id=:photo_id";
	$sth=$conn->prepare($sql);
	$sth->bindValue('user_id',$uid);
	$sth->bindValue('owner_id',$owner_id);
	$sth->bindValue('photo_id',$v['image_id']);
	try{$sth->execute();}
	catch(Exception $e){}
	$p[$k]=$sth->fetchAll();
	
	if(!count($p[$k])){
	$sql="Insert into photo_approved(id,user_id,owner_id,photo_id,status,created_on) values(DEFAULT,:user_id,:owner_id,:photo_id,0,UTC_TIMESTAMP)";
	$sth=$conn->prepare($sql);
	$sth->bindValue('user_id',$uid);
	$sth->bindValue('owner_id',$owner_id);
	$sth->bindValue('photo_id',$v['image_id']);
	try{$sth->execute();}
	catch(Exception $e){}
	
	}
	
	}
	
	return $res1;
}


public static function get_approved_photos($contacts,$uid){

global $conn;
$sql="select users.*,photos.date as photo_date,photos.image,photos.id as pid,Year(NOW())-Year(photos.date) as time_elapsed from users join photos on photos.user_id=users.id and  (date_format(photos.created_on,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d') or (DATE(photos.created_on) BETWEEN (UTC_DATE()- INTERVAL 6 DAY) AND (UTC_DATE()))) join photo_approved on photo_approved.photo_id=photos.id and photo_approved.user_id=:uid and date_format(photo_approved.created_on,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d')
 where users.phone_number IN ($contacts) and users.id!=:uid";

$sth=$conn->prepare($sql);
$sth->bindValue('uid',$uid);
try{$sth->execute();}
catch(Exception $e){}
$res2=$sth->fetchAll();

		if($res2){
		foreach($res2 as $key=>$value){
	
		if(!ISSET($final[$value['id']])){
			$final[$value['id']]=array(
			'user_id'=>$value['id'],
			'username'=>$value['username']?$value['username']:"",
			'phone_number'=>$value['phone_number']?$value['phone_number']:"",
			'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
			'date_request_status'=>$value['date_request_status']?1:0,
			'photos_count'=>$value['photos_count']?$value['photos_count']:0,
			'pictures'=>array()
			);
		}
		
		if(!ISSET($final[$value['id']]['pictures'][$value['pid']])){
		
		if($value['pid']){
		$final[$value['id']]['pictures'][$value['pid']]=array(
			"image_id"=>$value['pid']?$value['pid']:"",
			"image_url"=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
			"time_elapsed"=>$value['time_elapsed']?$value['time_elapsed']:0,
			"photo_date"=>$value['photo_date']?$value['photo_date']:""
			);
		}}
		}
		}
			
		if($final){
		foreach($final as $key=>$value){
		$data2=array();
		foreach($value['pictures'] as $value2){
			$data2[]=$value2;
		}
		$value['pictures']=$data2;
		$result[]=$value;
		}
		}
			

return $result;
}

public static function get_friends_photos_1($contacts,$uid){

global $conn;

$sql="select users.*,(select count(photos.id) from photos where photos.user_id=users.id and photos.auto_approved=1 and (date_format(photos.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d')))as poll,(select count(photo_approved.id) from photo_approved join photos on photos.id=photo_approved.photo_id where photo_approved.user_id={$uid} and photos.user_id=users.id and DATE_FORMAT(photo_approved.created_on,'%Y-%m-%d')=DATE_FORMAT(UTC_TIMESTAMP(),'%Y-%m-%d')) as photo_approved_status,(select date_request.id from date_request where user_id_requestor={$uid} and user_id_owner=users.id and date_format(date_request.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d')) as date_request_status,(select count(photos.id) from photos where photos.user_id=users.id and photos.auto_approved=0 and (date_format(photos.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d') or (DATE(photos.date) BETWEEN (UTC_DATE()-INTERVAL 6 DAY ) AND (UTC_DATE()))))as photos_count,photos.date as photo_date, photos.image, photos.id as pid, Year(NOW())-Year(photos.date) as time_elapsed from users left join photos on photos.user_id=users.id and photos.auto_approved=1 and (date_format(photos.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d'))
 where users.phone_number IN ($contacts) and users.id!={$uid}
 
 UNION
 select users.*,(select count(photo_approved.id) from photo_approved join photos on photos.id=photo_approved.photo_id where photo_approved.user_id=6 and photo_approved.owner_id=users.id and (DATE_FORMAT(photo_approved.created_on,'%Y-%m-%d')=DATE_FORMAT(UTC_TIMESTAMP(),'%Y-%m-%d' )))as poll,(select count(photo_approved.id) from photo_approved join photos on photos.id=photo_approved.photo_id where photo_approved.user_id={$uid} and photos.user_id=users.id and DATE_FORMAT(photo_approved.created_on,'%Y-%m-%d')=DATE_FORMAT(UTC_TIMESTAMP(),'%Y-%m-%d')) as photo_approved_status,(select date_request.id from date_request where user_id_requestor={$uid} and user_id_owner=users.id and date_format(date_request.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d')) as date_request_status,(select count(photos.id) from photos where photos.user_id=users.id and photos.auto_approved=0 and (date_format(photos.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d') or (DATE(photos.date) BETWEEN (UTC_DATE()- INTERVAL 6 DAY) AND (UTC_DATE()))))as photos_count,photos.date as photo_date,photos.image,photos.id as pid,Year(NOW())-Year(photos.date) as time_elapsed from users join photos on photos.user_id=users.id and  (date_format(photos.date,'%m-%d')=date_format(UTC_TIMESTAMP(),'%m-%d') or (DATE(photos.date) BETWEEN (UTC_DATE()- INTERVAL 6 DAY) AND (UTC_DATE()))) join photo_approved on photo_approved.photo_id=photos.id and photo_approved.user_id={$uid} and (DATE_FORMAT(photo_approved.created_on,'%Y-%m-%d')=DATE_FORMAT(UTC_TIMESTAMP(),'%Y-%m-%d')) where users.phone_number IN ($contacts) and users.id!={$uid}";
//echo $sql;die;
$sth=$conn->prepare($sql);
//$sth->bindValue('uid',$uid);
try{$sth->execute();}
catch(Exception $e){
//echo $e->getMessage();
}
$res2=$sth->fetchAll();
//print_r($res2);die;
			if($res2){
			foreach($res2 as $key=>$value){
			if($value['poll']){
			if(!ISSET($final[$value['pid']])){
				$final[$value['pid']]=array(
				'user_id'=>$value['id'],
				'username'=>$value['username']?$value['username']:"",
				'phone_number'=>$value['phone_number']?$value['phone_number']:"",
				'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
				'date_request_status'=>$value['date_request_status']?1:0,
				//'photo_approved_status'=>$value['photo_approved_status']?1:0,
				'photo_approved_status'=>0,
				'photos_count'=>self::get_photos_count($value['photo_approved_status'],$uid,$value['id']),
				//'photos_count'=>$value['photos_count']?$value['photos_count']:0,
				"image_id"=>$value['pid']?$value['pid']:"",
				"image_url"=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
				"time_elapsed"=>$value['time_elapsed']?$value['time_elapsed']:0,
				"photo_date"=>$value['photo_date']?$value['photo_date']:""
				);
			}
			}
			else{
				$final2[]=array(
				'user_id'=>$value['id'],
				'username'=>$value['username']?$value['username']:"",
				'phone_number'=>$value['phone_number']?$value['phone_number']:"",
				'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
				'date_request_status'=>$value['date_request_status']?1:0,
				//'photo_approved_status'=>$value['photo_approved_status']?1:0,
				'photo_approved_status'=>0,
				'photos_count'=>self::get_photos_count($value['photo_approved_status'],$uid,$value['id']),
				//'photos_count'=>$value['photos_count']?$value['photos_count']:0,
				"image_id"=>$value['pid']?$value['pid']:"",
				"image_url"=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
				"time_elapsed"=>$value['time_elapsed']?$value['time_elapsed']:0,
				"photo_date"=>$value['photo_date']?$value['photo_date']:""
				);
			
			}
			}
			}
			$data=array_merge($final,$final2);
			
			
			
		/*	if($final){
		foreach($final as $key=>$value){
		$data2=array();
		/*foreach($value['pictures'] as $value2){
			$data2[]=$value2;
		}
		$value['pictures']=$data2;
		$result[]=$value;
		}
		}*/
			

return $data;
}	


public static function get_photos_count($app_status,$user_id,$owner_id,$zone,$user_date){

global $conn;
$count=0;

/*$sql="select count(photo_approved.id) as photo_approved_status from photo_approved where photo_approved.owner_id='$owner_id' and photo_approved.user_id='$user_id' and DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone}) ))= DATE(FROM_UNIXTIME(UNIX_TIMESTAMP('$user_date') +".SERVER_OFFSET."+ ({$zone})))";
$sth=$conn->prepare($sql);
//$sth->bindValue('owner_id',$owner_id);
//$sth->bindValue('user_id',$uid);
try{$sth->execute();}
catch(Exception $e){}

$res=$sth->fetchAll();
$app_status=$res[0]['photo_approved_status'];*/

 if($app_status){
 $sql="select count(photos.id) as photos_count from photos where photos.user_id=:owner_id and photos.auto_approved=0 and photos.id NOT IN (SELECT photo_approved.photo_id from photo_approved where photo_approved.user_id=:user_id and photo_approved.owner_id=:owner_id and photo_approved.status IN (1,2)) and (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d') or (DATE(photos.date) BETWEEN ('$user_date'- INTERVAL 6 DAY) AND ('$user_date')))";
//echo $sql;die;
  $sth=$conn->prepare($sql);
 $sth->bindValue('owner_id',$owner_id);
 $sth->bindValue('user_id',$user_id);
 try{$sth->execute();}
 catch(Exception $e){}
 $res1=$sth->fetchAll();
 $count=$res1[0]['photos_count'];

 return $count;
 }
 
 elseif($app_status==0){
 $sql="select count(photos.id) as photos_count from photos where photos.user_id=:owner_id and photos.auto_approved=0 and photos.id NOT IN (SELECT photo_approved.photo_id from photo_approved where photo_approved.user_id=:user_id and photo_approved.owner_id=:owner_id and photo_approved.status IN (1,2)) and (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d') or (DATE(photos.date) BETWEEN ('$user_date'- INTERVAL 6 DAY) AND ('$user_date')))";
//echo $sql;die;
  $sth=$conn->prepare($sql);
 $sth->bindValue('owner_id',$owner_id);
 $sth->bindValue('user_id',$user_id);
 try{$sth->execute();}
 catch(Exception $e){}
 $res1=$sth->fetchAll();
 $count=$res1[0]['photos_count'];
 
 return $count;
 }
 else{
 return 0;
 }
 
//return $count;
}


public static function get_friend_pictures($uid,$owner_id,$zone,$user_date){

global $conn;
$path=BASE_PATH."timthumb.php?src=uploads/";
$sql="select users.id as user_id,users.username,CONCAT('$path',users.photo) as user_pic,photos.id as pid, Year(NOW())-Year(photos.date) as time_elapsed,photos.date as photo_date,photos.id as image_id,concat('$path',photos.image) as image_url from  users 
join photos on photos.user_id=users.id
where users.id=:owner_id AND users.id!={$uid} and photos.auto_approved=1 and (date_format(photos.date,'%m-%d')=date_format(('$user_date'),'%m-%d') or (DATE(photos.date) BETWEEN (('$user_date')- INTERVAL 6 DAY) AND ('$user_date'))) 
UNION

select users.id,users.username,CONCAT('$path',users.photo) as user_pic,photos.id as pid, Year(NOW())-Year(photos.date) as time_elapsed,photos.date as photo_date,photos.id as image_id,concat('$path',photos.image) as image_url FROM users 
JOIN photo_approved ON photo_approved.user_id={$uid} AND photo_approved.owner_id=users.id
JOIN photos ON photos.id=photo_approved.photo_id
WHERE users.id=:owner_id AND photo_approved.status=1
  AND users.id!={$uid} AND (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d') 
  OR (DATE(photos.date) BETWEEN ('$user_date' - INTERVAL 6 DAY) AND ('$user_date'))) AND (DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone}) ))=DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(UTC_TIMESTAMP) +".SERVER_OFFSET."+ ({$zone})   )))";
//
	$sth=$conn->prepare($sql);
	$sth->bindValue("owner_id",$owner_id);
	$sth->bindValue('user_id',$uid);
	try{$sth->execute();}
	catch(Exception $e){}
	
	$res1=$sth->fetchAll(PDO::FETCH_ASSOC);
	
	return $res1;

}

public static function get_current_date($date,$zone){

global $conn;
$sth=$conn->prepare("SELECT DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(UTC_TIMESTAMP) +".SERVER_OFFSET."+ ({$zone}))) as today_date");

try{$sth->execute();}
catch(Exception $e){}
$cur_date1=$sth->fetchAll();
$cur_date=$cur_date1[0]['today_date'];
return $cur_date;
}


public static function get_friends_photos($contacts,$uid,$zone,$user_date){

global $conn;

$sql="SELECT users.*,

  (SELECT count(photo_approved.id)
   FROM photo_approved
   JOIN photos ON photos.id=photo_approved.photo_id
   WHERE photo_approved.user_id={$uid}
     AND photos.user_id=users.id AND photo_approved.status=1
     AND DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone})   ))=DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(UTC_TIMESTAMP) +".SERVER_OFFSET."+ ({$zone})   ))) AS photo_approved_status,

  (SELECT date_request.id
   FROM date_request
   WHERE user_id_requestor={$uid}
     AND user_id_owner=users.id
     AND date_format(FROM_UNIXTIME(UNIX_TIMESTAMP(date_request.date) +".SERVER_OFFSET."+ ({$zone}) ),'%m-%d')=date_format(FROM_UNIXTIME(UNIX_TIMESTAMP('$user_date') +".SERVER_OFFSET."+ ({$zone}) ),'%m-%d')) AS date_request_status,

  (SELECT count(photos.id)
   FROM photos
   WHERE photos.user_id=users.id
     AND photos.auto_approved=0 
     AND (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d')
          OR (DATE(photos.date) BETWEEN ('$user_date'-INTERVAL 6 DAY) AND ('$user_date'))))AS photos_count,
       photos.date AS photo_date,
       photos.image,
       photos.id AS pid,
       Year(NOW())-Year(photos.date) AS time_elapsed
FROM users
LEFT JOIN photos ON photos.user_id=users.id
AND photos.auto_approved=1
AND (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d'))
WHERE users.phone_number IN ($contacts)
  AND users.id!={$uid}
UNION
SELECT users.*,

  (SELECT count(photo_approved.id)
   FROM photo_approved
   JOIN photos ON photos.id=photo_approved.photo_id
   WHERE photo_approved.user_id={$uid}
     AND photos.user_id=users.id AND photo_approved.status=1
     AND DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone}) ))=DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(UTC_TIMESTAMP) +".SERVER_OFFSET."+ ({$zone}) ))) AS photo_approved_status,

  (SELECT date_request.id
   FROM date_request
   WHERE user_id_requestor={$uid}
     AND user_id_owner=users.id
     AND date_format(DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(date_request.date) +".SERVER_OFFSET."+ ({$zone}) )),'%m-%d')=date_format(DATE(FROM_UNIXTIME(UNIX_TIMESTAMP('$user_date') +".SERVER_OFFSET."+ ({$zone})  )),'%m-%d')) AS date_request_status,

  (SELECT count(photos.id)
   FROM photos
   WHERE photos.user_id=users.id
     AND photos.auto_approved=0
     AND (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d')
          OR (DATE(photos.date) BETWEEN ('$user_date'- INTERVAL 6 DAY) AND ('$user_date'))))AS photos_count,
       photos.date AS photo_date,
       photos.image,
       photos.id AS pid,
       Year(NOW())-Year(photos.date) AS time_elapsed
FROM users 
JOIN photo_approved ON photo_approved.user_id={$uid} AND photo_approved.owner_id=users.id
JOIN photos ON photos.id=photo_approved.photo_id
WHERE users.phone_number IN ($contacts)
  AND users.id!={$uid} AND (date_format(photos.date,'%m-%d')=date_format('$user_date','%m-%d')
     OR (DATE(photos.date) BETWEEN ('$user_date' - INTERVAL 6 DAY) AND ('$user_date'))) AND (DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(photo_approved.created_on) +".SERVER_OFFSET."+ ({$zone})   ))=DATE(FROM_UNIXTIME(UNIX_TIMESTAMP(UTC_TIMESTAMP) +".SERVER_OFFSET."+ ({$zone})   )))";
//echo $sql;die;
$sth=$conn->prepare($sql);
//$sth->bindValue('uid',$uid);
try{$sth->execute();}
catch(Exception $e){}
$res2=$sth->fetchAll();

			if($res2){
			foreach($res2 as $key=>$value){
			
			if(!ISSET($final[$value['id']])){
				$final[$value['id']]=array(
				'user_id'=>$value['id'],
				'username'=>$value['username']?$value['username']:"",
				'phone_number'=>$value['phone_number']?$value['phone_number']:"",
				'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
				'date_request_status'=>$value['date_request_status']?1:0,
				//'photo_approved_status'=>$value['photo_approved_status']?1:0,
				'photo_approved_status'=>0,
				'photos_count'=>$value['photo_approved_status']?0:self::get_photos_count($value['photo_approved_status'],$uid,$value['id'],$zone,$user_date),
				//'photos_count'=>$value['photo_approved_status']?0:$value['photos_count'],
				'pictures'=>self::get_friend_pictures($uid,$value['id'],$zone,$user_date),
				);
			}
			
			/*if(!ISSET($final[$value['id']]['pictures'][$value['pid']])){
			
			if($value['pid']){
			$final[$value['id']]['pictures'][$value['pid']]=array(
				
				'user_id'=>$value['id'],
				'username'=>$value['username']?$value['username']:"",
				'user_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:"",
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
		$result[]=$value;
		}
		}
			

return $result;
}		

public static function generateRandomString($length = 6){
    //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


public static function get_profile($phone){

global $conn;

$sql="SELECT users.*,users.id as uid from users where users.phone_number=:phone";
$sth=$conn->prepare($sql);
$sth->bindValue('phone',$phone);
try{$sth->execute();}
catch(Exception $e){}
$res2=$sth->fetchAll();

	if($res2){
		$data=array(
		'user_id'=>$res2[0]['uid'],
		'name'=>$res2[0]['username']?$res2[0]['username']:"",
		'phone_number'=>$res2[0]['phone_number']?$res2[0]['phone_number']:"",
		'profile_pic'=>$res2[0]['photo']?BASE_PATH."timthumb.php?src=uploads/".$res2[0]['photo']:"",
		'token'=>$res2[0]['verification_code']
		);
		}
	return $data;
}

public static function get_all_contacts(){

global $conn;

$sql="select phone_number from users";
$sth=$conn->prepare($sql);
try{$sth->execute();}
catch(Exception $e){}

$result=$sth->fetchAll();

return $result;

}


public static function get_friends($contacts,$uid){

global $conn;

$sql="select users.id,users.username,users.phone_number,users.photo from users where users.phone_number IN ($contacts) and users.id !=$uid";
$sth=$conn->prepare($sql);
//$sth->bindValue('contacts',$contacts);
$sth->bindValue('uid',$uid);
try{$sth->execute();}
catch(Exception $e){echo $e->getMessage();}
$res2=$sth->fetchAll();

	
		if($res2){
			foreach($res2 as $key=>$value){
		$data[$value['id']]=array(
		'user_id'=>$value['id'],
		'name'=>$value['username']?$value['username']:"",
		'phone_number'=>$value['phone_number']?$value['phone_number']:"",
		'profile_pic'=>$value['photo']?BASE_PATH."timthumb.php?src=uploads/".$value['photo']:""
		);
		}}
	
	if($data){
	foreach($data as $key=>$value){
	$result[]=$value;
	
		}}	
		
	return $result;
}


public static function get_photos($phone){

global $conn;

$sql="SELECT photos.id as pid,photos.*,Year(NOW())-Year(photos.date) as time_elapsed from users left join photos on photos.user_id=users.id where users.phone_number=:phone and Date_format(photos.date,'%d-%m')=Date_format('$user_date','%d-%m')";
$sth=$conn->prepare($sql);
$sth->bindValue('phone',$phone);
try{$sth->execute();}
catch(Exception $e){}
$res2=$sth->fetchAll();

		if($res2){
			foreach($res2 as $key=>$value){
				$data[$value['pid']]=array(
				'photo_id'=>$value['pid'],
				'photo'=>$value['image']?BASE_PATH."timthumb.php?src=uploads/".$value['image']:"",
				'date'=>$value['date'],
				'time_elapsed'=>$value[time_elapsed]?$value['time_elapsed']:0,
				'auto_approved'=>$value['auto_approved']
				);
				}
			}
			if($data){
			foreach($data as $key=>$value){
		
		
			$result[]=$value;
		
		}}	
			

return $result;
}

public static function sendSMS($to,$body){
  $AccountSid = "AC0d3815a3f85544b960275d287c2f8dc3";
    $AuthToken = "f3b67cd258c0371d1c7205e128042520";

	// Instantiate a new Twilio Rest Client
	$client = new Services_Twilio($AccountSid, $AuthToken);

	/* Your Twilio Number or Outgoing Caller ID */
	$from = '+1 415-319-6793';

		// Send a new outgoing SMS */
		
		$client->account->sms_messages->create($from, $to, $body);
		
}

public static function sendEmail($email,$subjectMail,$bodyMail,$email_back){

	$mail = new PHPMailer(true); 
	$mail->IsSMTP(); // telling the class to use SMTP
	try {
	  //$mail->Host       = SMTP_HOST; // SMTP server
	  $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
	  $mail->SMTPAuth   = true;                  // enable SMTP authentication
	  $mail->Host       = SMTP_HOST; // sets the SMTP server
	  $mail->Port       = SMTP_PORT;                    // set the SMTP port for the GMAIL server
	  $mail->Username   = SMTP_USER; // SMTP account username
	  $mail->Password   = SMTP_PASSWORD;        // SMTP account password
	  $mail->AddAddress($email, '');     // SMTP account password
	  $mail->SetFrom(SMTP_EMAIL, SMTP_NAME);
	  $mail->AddReplyTo($email_back, SMTP_NAME);
	  $mail->Subject = $subjectMail;
	  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';  // optional - MsgHTML will create an alternate automaticall//y
	  $mail->MsgHTML($bodyMail) ;
	  if(!$mail->Send()){
			$success='0';
			$msg="Error in sending mail";
	  }else{
			$success='1';
	  }
	} catch (phpmailerException $e) {
	  $msg=$e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
	  $msg=$e->getMessage(); //Boring error messages from anything else!
	}
	//echo $msg;
}
}	
	
?>	