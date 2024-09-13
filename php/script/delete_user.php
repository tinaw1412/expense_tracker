<?php
include_once '../DemoConn.php';
$db=new DemoConn();

if (count($argv)==2){
    $email=$argv[1];
    $sql="delete from users where email=?";
    $result=$db->exec($sql,[$email]);

    echo "User deleted: $result".PHP_EOL;

}else{
    echo "Please enter email to be deleted only".PHP_EOL;
}