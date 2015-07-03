<?php require_once "php_include/db_connection.php"; 
$token=$_REQUEST["token"];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gander | Reset Password</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="assets/css/AdminLTE.css" rel="stylesheet" type="text/css" />
        
         <style>
        .form-box{
        
          width: 500px;
	  margin-left: auto;
	  margin-right: auto;
	  margin-top: auto;
	  margin-bottom: auto;
	  padding: 25px;
	  background: #ccc;
	  margin-top: 20px;
        }
        </style> 
        
  </head>
  <body class="bg-black">

        <div class="form-box" id="login-box">
            <div class="header">RESET PASSWORD</div>
      <form action="eventHandler.php" method="post" enctype="multipart/form-data">
                <div class="body bg-gray">
                  <?php //error div
                  if(isset($_REQUEST['success']) && isset($_REQUEST['msg']) && $_REQUEST['msg']){ ?>
                    <div style="margin:0px 0px 10px 0px;" class="alert alert-<?php if($_REQUEST['success']) echo "success"; else echo "danger"; ?> alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $_REQUEST['msg']; ?>
                  </div>
              <?php } // --./ error -- ?>
              
         <div class="form-group">     
                      <input class="form-control" type="password" name="password" placeholder="Enter new password..">
          </div>
          
                    <div class="form-group">
                     <input class="form-control" type="password" name="confirm" placeholder="Confirm password.."><br>
          </div>
          
                <div class="footer">                                                               
                    <button type="submit" class="btn bg-olive btn-block" onclick="return 0;">Submit</button>
                    <input type="button" class="btn bg-red btn-block" onclick="window.history.back()" value="Cancel">
                </div>
                <!-- hidden -->
                <input type="hidden" name="event" value="reset-password">
				<input type="hidden" name="redirect" value="../reset-password.php">
				<input type="hidden" name="token" value="<?php echo $token; ?>">
            </form>
        </div>
        
        


        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>   
   <script src="assets/js/bootstrap-filestyle.js" type="text/javascript"></script>     

    </body>
</html>