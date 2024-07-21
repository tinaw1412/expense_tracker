<?php
include_once '../DemoConn.php';
$db=new DemoConn();

$sql="insert into users (email,full_name,password) values(?,?,?);";
$hashed_password = password_hash("demo123", PASSWORD_DEFAULT);
$db->exec($sql,["demo@gmail.com","demo_user",$hashed_password]);
$userId = $db->lastId();

$sql="insert into users (email,full_name,password) values(?,?,?);";
$db->exec($sql,["demo_friend@gmail.com","friend1",$hashed_password]);
$user1Id = $db->lastId();

$sql= "insert into projects (project_name,created_by_user_id) values(?,?)";
$db->exec($sql, ['Outing 2024',$userId]);
$projectId = $db->lastID();

$sql = "insert into project_user_maps (project_id,user_id) values(?,?)";
$db->exec($sql,[$projectId,$userId]);
$db->exec($sql,[$projectId,$user1Id]);

$sql = "insert into daybook (user_id,project_id,amount,note) values(?,?,?,?);";
$db->exec($sql,[$userId,$projectId,245.73,"rent chalet"]);
$db->exec($sql,[$userId,$projectId,92.51,"groceries"]);
$db->exec($sql,[$user1Id,$projectId,12.49,"dollarama"]);

