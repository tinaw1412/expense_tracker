<?php
include_once 'DemoConn.php';
$db = new DemoConn();
session_start();
function projectDeletable($db,$projectId) {
    $data=$db->select("Select count(*) as cnt from daybook where project_id=?",[$projectId]);
    return intval($data[0]["cnt"]) == 0;
}

if (isset($_GET['page'])) {
    $page = $_GET['page'] ?? 1;
    $size = 5;

    $offset = ($page - 1) * $size;
    $limit = sprintf("limit %s, %s", $offset, $size);

    $projects = $db->select("Select p.project_id,p.project_name,p.created_by_user_id,u.email
     from projects as p 
     join users as u on p.created_by_user_id=u.user_id 
     join project_user_maps as map on p.project_id=map.project_id
     where map.user_id=? $limit",[$_SESSION['user_id']]);

    $dataNum = $db->select('Select count(*) as cnt from projects as p
    join project_user_maps as map on p.project_id=map.project_id
    where map.user_id=?',[$_SESSION['user_id']]);

    $pageNum = ceil($dataNum[0]['cnt'] / $size);

    $data = [
        'projects' => $projects,
        'pagination' => [
            'page' => intval($page),
            'pageNum' => $pageNum,
            'total' => intval($dataNum[0]['cnt']),
            'size' => $size,
        ]
    ];
    sleep(0.5);
    echo json_encode($data);
}else if(isset($_GET['action']) && ($_GET['action'])=="getProjects"){
    $projects= $db->select("select * from projects where created_by_user_id=?",[$_SESSION['user_id']]);
    
    echo json_encode($projects);

}else {
    //post
    $action = $_POST['action'];
    if ($action == 'delete') {
        $projectId = $_POST['id'];
        if (projectDeletable($db, $projectId)) {
            $result = [
                'status' => $db->exec("Delete from projects 
                where project_id=? and created_by_user_id=?", [$projectId, $_SESSION["user_id"]]),
            ];
            echo json_encode($result);

        } else {
            echo json_encode([
                "status" => false,
                "msg" => "Cannot delete this project since it is already in use or it is not created by current user",
            ]);
        }
    } else if ($action == 'update') {
        $sql = "Update projects set project_name=? where project_id=?";
        $params = [
            $_POST['projectname'],
            $_POST['id'],
        ];

        try {
            $db->exec($sql, $params);
            echo json_encode([
                'status' => true,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'msg' => $e->getMessage(),
            ]);
        }

    } else if ($action == 'new') {

        if (isset($_SESSION['user_id'])){
            $sql = "insert into projects (project_name,created_by_user_id) values(?,?);";
            $sql2 = "insert into project_user_maps (project_id,user_id) values(?,?)";


            $params = [
                $_POST['projectname'],
                $_SESSION['user_id'],
            ];
    
            try {
                $db->exec($sql, $params);
                $projectId = $db->lastId();
                $db->exec($sql2,[$projectId,$_SESSION['user_id']]);

                echo json_encode([
                    'status' => true,
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'msg' => $e->getMessage(),
                ]);
            }
    
        }else{
            echo json_encode([
                'status'=> false,
                'code'=>401,
            ]);
        }

    } 
}