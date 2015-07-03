<?php
//error_reporting(0);
$servername = $_SERVER['HTTP_HOST'];
$pathimg=$servername."/";
define("ROOT_PATH",$_SERVER['DOCUMENT_ROOT']);
define("UPLOAD_PATH","http://code-brew.com/projects/gander/");
define("BASE_PATH","http://code-brew.com/projects/gander/");

define("SERVER_OFFSET","0");
$DB_HOST = 'localhost';
$DB_DATABASE = 'codebrew_gander';
$DB_USER = 'codebrew_super';
$DB_PASSWORD = 'core2duo';

//GCM
define("AUTH_KEY","AIzaSyCAi-kxIDNTPxdUS6UJTEOrA1dZ6cAEx2c");

define('SMTP_USER','pargat@code-brew.com');
define('SMTP_EMAIL','pargat@code-brew.com');
define('SMTP_PASSWORD','core2duo');
define('SMTP_NAME','Gander');
define('SMTP_HOST','mail.code-brew.com');
define('SMTP_PORT','25');
