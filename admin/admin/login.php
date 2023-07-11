<?php
require_once('./include/admin-load.php');
if(isset($_REQUEST['action'])){
    if($_REQUEST['action'] == 'logout'){ $auth->logout(); }
}
if(isset($_REQUEST['login'])){
    if(empty($_REQUEST['email']) || empty($_REQUEST['pwd'])){
        $error = 'Please enter Email and Password';
    }else{
        $email=trim($_REQUEST['email']);
        $password=trim($_REQUEST['pwd']);
        if(!$auth->login($email,$password)){
            $error = $auth->error;
        }
    }
}
if($auth->is_loggedin()) { header("location:index.php"); }
if(!empty($auth->msg)){ $success = $auth->msg; }
if(!empty($auth->error)){ $error = $auth->error; } ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GB Barber | Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <meta name="theme-color" content="#078d5d">
    <link rel="stylesheet" type="text/css" href="css/parl.css">
</head>
<body class="gray-bg" style="overflow: hidden; background-color: #222222;display:flex;">
        <div class="flex-center">
            <div style="position: relative;z-index: 999999">
                <div class="col-lg-12">
                        <div class="text-center loginscreen animated fadeInDown col-lg-12" style="">
                            <div style="padding: 20px;background-color: #313131;border-radius: 10px;">
                                <div>
                                    <h1 class="logo-name"><img src="../images/barbers/<?=$setting['businessLogo']?>" style="width: 300px;padding-bottom: 20px;padding-top: 20px;"></h1>
                                </div>
                                <?php if(isset($error)){ echo "<div class='alert alert-danger'>$error</div>"; }
                                if(isset($success)){ echo "<div class='alert alert-success'>$success</div>"; } ?>
                                <form class="m-t" role="form" action="login" method="post">
                                    <div class="form-group">
                                        <input type="email"  style="font-size: 15px;" class="form-control" name="email" id="email" placeholder="Username" required="">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" style="font-size: 15px;" class="form-control" id="pwd" name="pwd" placeholder="Password" required="">
                                    </div>
                                    <button type="submit" name="login" class="btn btn-primary block full-width m-b">Login</button>

                                </form>
                            </div>
                        </div>
                </div>
            </div>
        </div>

    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
