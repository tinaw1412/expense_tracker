<?php 
include_once 'DemoConn.php';
include_once 'Mail.php';
$db=new DemoConn();
session_start();
function userDeletable($db,$userId) {
    $data=$db->select("Select count(*) as cnt from daybook where user_id=?",[$userId]);
    return intval($data[0]["cnt"]) == 0;

}

if (isset($_GET['page'])){
    $page=$_GET['page'] ?? 1;
    $size=5;

    $offset= ($page-1) * $size;
    $limit= sprintf("limit %s, %s", $offset, $size);

   
    $users=$db->select("Select * from users $limit");

    $dataNum=$db->select('Select count(*) as cnt from users');

    $pageNum=ceil($dataNum[0]['cnt']/$size);

    $data= [
        'users' => $users,
        'pagination' => [
            'page'=>intval($page),
            'pageNum'=> $pageNum,
            'total'=>intval($dataNum[0]['cnt']),
            'size'=>$size,
        ]
    ];
    sleep(0.5);
    echo json_encode($data);
}else if(isset($_GET['action']) && $_GET['action']=='isLogged') {
    if (isset($_SESSION['user_id'])){
        $loggedEmail=$db->select("select email from users where user_id=?",[$_SESSION['user_id']]);
        $loggedProjects=$db->select("select p.project_id,p.project_name from project_user_maps as map
        join projects as p on map.project_id=p.project_id
        where map.user_id=?",[$_SESSION['user_id']]);

        $data=[
            'loggedEmail'=>$loggedEmail[0]['email'],
            'loggedProjects'=>$loggedProjects,
            "status"=>true,
        ];
        echo json_encode($data);
    }else{
        echo json_encode([
            'status'=>false,
        ]);

    };
    
    
}else if (isset($_GET['action']) && $_GET['action']=='confirm'){
    $token=$_GET['token'];
    $users=$db->select("select user_id from users where confirmEmailToken=?",[$token]);
    if (count($users) == 1){
        $db->exec("update users set status=1 where confirmEmailToken=?",[$token]);
        echo "Your email has been confirmed, you can login in from now on.";
    }else{
        echo "Something went wrong, please try again.";
    }

}else{
    //post
    $action=$_POST['action'];
    if ($action== 'delete'){
        $userId=$_POST['id'];
        if (userDeletable($db,$userId)) {
            $result = [
                'status'=> $db->exec("Delete from users where user_id=?", [$userId]),
            ]; 
            echo json_encode($result);

        }else {
            echo json_encode([
                "status"=> false,
                "msg"=> "Cannot delete this user since it is already in use",
            ]);
        }


    }else if ($action=='update'){
        $sql="Update users set email=?, full_name=?, status=?, admin=? where user_id=?";
        $params=[
            $_POST['email'],
            $_POST['fullname'],
            $_POST['status'],
            $_POST['admin'],
            $_POST['id'],
        ];

        try {
            $db->exec($sql,$params);
            echo json_encode([
                'status'=> true,
            ]);
        }
        catch (Exception $e) {
            echo json_encode([
                'status'=> false,
                'msg'=> $e->getMessage(),
            ]);
        }

    }else if ($action=='new'){
        if (isset($_POST['status'],$_POST['admin'])){
            //new user action (by admin)
            $signup=false;
            $sql="insert into users (email,full_name,password,status,admin) values(?,?,?,?,?);";
            $password=$_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $params=[
                $_POST['email'],
                $_POST['fullname'],
                $hashed_password,
                $_POST['status'],
                $_POST['admin'],
            ];
        }else{
            //sign up action
            $signup=true;
            $sql="insert into users (email,full_name,password,status,confirmEmailToken) values(?,?,?,0,?);";
            $password=$_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $token=uniqid();

            $params=[
                $_POST['email'],
                $_POST['fullname'],
                $hashed_password,
                $token,
            ];
            
        }

        try {
            $db->exec($sql,$params);
            if ($signup){
                $url=$_ENV['web_url'];
                $body="<a href='$url/users.php?action=confirm&token=$token'>Confirm your email</a>";
                $resultSendMail=Mail::send($_POST['email'],"Confirm your email",$body);
            }else{
                $resultSendMail=null;
            }

            echo json_encode([
                'status'=> true,
                'resultSendMail'=>$resultSendMail,
            ]);
        }
        catch (Exception $e) {
            echo json_encode([
                'status'=> false,
                'msg'=> $e->getMessage(),
            ]);
        }
    }else if ($action=='login'){
        $sql="select * from users where email=?;";
        $email=$_POST["email"];
        $password=$_POST["password"];

        $data=$db->select($sql,[$email]);

        if (count($data)==1){
            $auth = password_verify($password, $data[0]["password"]);
            if ($auth){
                $_SESSION["user_id"] = $data[0]["user_id"];
            };
            echo json_encode([
                "status"=> $auth,
                
            ]);
        }else{
            echo json_encode([
                "status"=> false,
                "msg"=> "Email is not signed up",
            ]);
        }
    }else if ($action=='logout'){
        unset($_SESSION["user_id"]);
        echo json_encode([
            "status"=> true,
        ]);
    }else if ($action=='selectProject'){
        $projectId = $_POST['projectId'];
        $sql = "select 1 from project_user_maps where project_id = ? and user_id =  ?;";
        $data = $db->select($sql,[$projectId,$_SESSION["user_id"]]);
        if (count($data)==1){
            $_SESSION['projectId'] = $projectId;
            
            $status=true;
            $msg='Project set successfully';
        }else if ($projectId!=0){
            $status=false;
            $msg="Failed to select project";
        }else{
            $status=true;
            $msg= "";
        }

        echo json_encode([
            'status' => $status, 
            'msg' => $msg
        ]);
        

    }
}
