<?php
if(isset($_SERVER['DBNAME']) && isset($_SERVER['DBUSER']) && isset($_SERVER['DBPWD']) && isset($_SERVER['PFX'])){
    DEFINE('DBNAME',$_SERVER['DBNAME']);
    DEFINE('DBUSER',$_SERVER['DBUSER']);
    DEFINE('DBPWD',$_SERVER['DBPWD']);
    DEFINE('DBHOST','localhost:3306');
    DEFINE('PFX',$_SERVER['PFX']);
}else{
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $parsedUrl = parse_url($currentUrl);
    $host = $parsedUrl['host'];
    $subdomain = substr($host, 0, strpos($host, '.'));
    if($subdomain){
        DEFINE('DBNAME',$subdomain.'_barbreon_db');
        DEFINE('DBUSER','barbreonUserTaki');
        DEFINE('DBPWD','KqIqri71c&xI4obcn!82Kpx7sscXogEk');
        DEFINE('DBHOST','localhost:3306');
        DEFINE('PFX','sym_');
    }
}

?>
