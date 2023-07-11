<?php
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($currentUrl);
$host = $parsedUrl['host'];
$subdomain = substr($host, 0, strpos($host, '.'));
if($subdomain){
    DEFINE('DBNAME',$subdomain.'_barbreon_db');
    DEFINE('DBUSER',$_SERVER['DBUSER']);
    DEFINE('DBPWD',$_SERVER['DBPWD']);
    DEFINE('DBHOST','localhost:3306');
    DEFINE('PFX','sym_');
}
?>
