<?php
include_once '../DemoConn.php';
$db=new DemoConn();

$handle = fopen("../sql/0__db_structure.sql", "r");
if ($handle) {
    $sql="";
    while (($line = fgets($handle)) !== false) {
        // process the line read.
        $sql .= $line;
        if (str_ends_with($line, ';')){
            echo "executing $sql".PHP_EOL;
            $db->exec($sql);
            $sql="";
        }
    }

    fclose($handle);
}