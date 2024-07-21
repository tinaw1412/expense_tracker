<?php
include_once 'DemoConn.php';
$db = new DemoConn();
session_start();

if (isset($_GET['page'])) {
    $page = $_GET['page'] ?? 1;
    $size = 10;

    $offset = ($page - 1) * $size;
    $limit = sprintf("limit %s, %s", $offset, $size);
    
    $projectId=$_SESSION['projectId'];

    $expenses = $db->select("Select d.daybook_id,d.project_id,u.email,p.project_name,d.date,d.amount,d.note
     from daybook as d 
     join projects as p on d.project_id=p.project_id 
     join users as u on d.user_id=u.user_id 
     where d.project_id=?
     $limit", [$projectId]);

    $dataNum = $db->select('Select count(*) as cnt from daybook where project_id = ?', [$projectId]);

    $pageNum = ceil($dataNum[0]['cnt'] / $size);

    $data = [
        'expenses' => $expenses,
        'pagination' => [
            'page' => intval($page),
            'pageNum' => $pageNum,
            'total' => intval($dataNum[0]['cnt']),
            'size' => $size,
        ]
    ];
    sleep(0.5);
    echo json_encode($data);
}else {
    //post
    $action = $_POST['action'];
    if ($action == 'delete') {
        $expenseId = $_POST['id'];
        $result = [
            'status' => $db->exec("Delete from daybook where daybook_id=?", [$expenseId]),
        ];
        echo json_encode($result);

    } else if ($action == 'update') {
        $sql = "Update daybook set project_id=?,date=?,amount=?,note=? where daybook_id=?";
        $params = [
            $_POST['project_id'],
            $_POST['date'],
            $_POST['amount'],
            $_POST['note'],
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
            $sql = "insert into daybook (user_id,project_id,date,amount,note) values(?,?,?,?,?);";

            $params = [
                $_SESSION['user_id'],
                $_POST['project_id'],
                $_POST['date'],
                $_POST['amount'],
                $_POST['note'],
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
    
        }else{
            echo json_encode([
                'status'=> false,
                'code'=>401,
            ]);
        }

    } 
}