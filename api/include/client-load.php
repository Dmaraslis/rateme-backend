<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function sanitize_output($buffer)
{
    $search = array(
        '/\>[^\S ]+/s', //strip whitespaces after tags, except space
        '/[^\S ]+\</s', //strip whitespaces before tags, except space
        '/(\s)+/s'  // shorten multiple whitespace sequences
        );
    $replace = array(
        '>',
        '<',
        '\\1'
        );
    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

ob_start("sanitize_output");

session_name('SYMBIOTIC');
session_start();

require_once('config.php');
require_once('functions.php');
require_once('classes/load-classes.php');
require_once('mySql-injection-vaccine.php');


define('DBTYPE', 'mysql');
// Check if user is logged in else redirect to login page
$actual_link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$explodedLink = explode("/",$actual_link);
?>