<?php
// Bucket Name
$bucket="app.ganderapp.io";
if (!class_exists('S3'))require_once('S3.php');
			
//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJ4WAK7WEPXA7VZ6A');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');
			
//instantiate the class
$s3 = new S3(awsAccessKey, awsSecretKey);

$s3->putBucket($bucket, S3::ACL_PUBLIC_READ);

?>