<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once '../DemoConn.php';
$db=new DemoConn();

$handle = fopen("../sql/0__db_structure", "r");

if ($handle) {
    $sql="";

    while (($line = fgets($handle)) !== false) {
        // process the line read.
        $sql .= $line;  
    }
    $result=$db->exec($sql);
    var_dump($result);


    fclose($handle);
}