<style>
    body{
        background-color: #272727;
        color:white;
    }
</style>
<?php
/** Execution Every 1 min */
require_once('../../include/client-load.php');
date_default_timezone_set('Europe/Athens');

if ($_GET['SSID'] == 'TAKIS123!@#') {
    $dateD = date('D');
    $dateH = date('H');
    $dateI = date('i');
    $dateFull = date('H:i');


    //crons that run every minute
    //$settings->gather_stats_for_graph_ZTD(0);


    if(!is_float($dateI / 2)){ //run every 2 minutes

    }
    if(!is_float($dateI / 5)){//run every 5 minutes

    }
    if(!is_float($dateI / 30)){ //run every 30 minutes

    }
    if($dateI > 59){  //run every 1 hour

    }
    if(!is_float($dateH / 2) && $dateI >= 59){ //run every 2 hours

    }
    if($dateH > 23 && $dateI > 59){     //run every day

    }

    if(!is_float($dateD / 2) && $dateH == 23 && $dateI == 59){  //run every 2 days

    }
}else{
    echo 'ACCESS DENIED';
}
?>
