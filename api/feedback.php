<?php
//this is an api to recover password
// +-----------------------------------+
// + STEP 1: include required files    +
// +-----------------------------------+
require_once "../php_include/db_connection.php"; 
require_once('../PHPMailer_5.2.4/class.phpmailer.php');


function sendEmail($email,$subjectMail,$bodyMail,$email_back){

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
	  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automaticall//y
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




$success=$msg="0";$data=array();

// +-----------------------------------+
// + STEP 2: get data				   +
// +-----------------------------------+
$uid=$_REQUEST['user_id'];
$username=$_REQUEST['username'];
$phone=$_REQUEST['phone_number'];
$subject=$_REQUEST['subject_message'];

$email='jindal.ankit89@gmail.com';

if(!($uid && $subject)){
	$success="0";
	$msg="Incomplete Parameters";
}
else{


	
		$success="1";
		$msg="Mail sent";
		sendEmail($email,"Gander- Feedback Message",
						"<div style='font-size:16px;line-height:1.6;'>
							
							<p>This is a feedback message from ".$username  ." with user id ".$uid." and phone number as ". $phone. "</p>
							<br>
							<p>".$subject."</p>
							<p>Thank You </p>
						</div>"
					,SMTP_EMAIL);
	
}

// +-----------------------------------+
// + STEP 4: send json data			   +
// +-----------------------------------+
echo json_encode(array("success"=>$success,"msg"=>$msg));
?>