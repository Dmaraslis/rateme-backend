<?php require_once('./include/admin-load.php'); ?>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pagename; ?> | GM Barber</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <script src="js/jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="js/symbiotic.js"></script>
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css?ver=<?=time()?>" rel="stylesheet">
    <script src="js/dateTime.js"></script>
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="manifest">
    <meta name="theme-color" content="#1d1d1d">
    <link href="css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="css/plugins/c3/c3.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="img/apple-touch-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="img/apple-touch-icon.png">
    <link href="css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="css/parl.css?ver=<?=time()?>" rel="stylesheet">
    <link href="css/plugins/chosen/bootstrap-chosen.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="mask-icon" href="img/safari-pinned-tab.svg" color="#222222">
    <meta name="msapplication-TileColor" content="#222222">
    <meta name="theme-color" content="#222222">
    <link href="css/tenant.php" rel="stylesheet">
    </head>

    <div id="modals"></div>

<?php
if(isset($error)){
    echo "<div class='alert alert-danger'>$error</div>";
}
if(isset($success)){
    echo "<div class='alert alert-success'>$success</div>";
}
    ?>