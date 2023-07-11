<?php
if(isset($_GET['key'])){
    require_once('../../include/client-load.php');
    $decryptedMessage = $encryption->safeDecrypt($_GET['key'],'BarbreonProd');
    if($decryptedMessage){
        $notifications->appointmentUpdate($decryptedMessage);
    }
}