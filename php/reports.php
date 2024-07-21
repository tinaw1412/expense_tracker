<?php
include_once 'DemoConn.php';
$db = new DemoConn();
session_start();
$projectId=$_SESSION['projectId'];

function getExpenses($db,$projectId){
    $expenses = $db->select("Select d.daybook_id,d.project_id,u.email,p.project_name,d.date,d.amount,d.note
     from daybook as d 
     join projects as p on d.project_id=p.project_id 
     join users as u on d.user_id=u.user_id
     where d.project_id=?", [$projectId]);
    return $expenses;
}

function getAllData($db,$projectId){
    $expenses = getExpenses($db, $projectId);

    $sum = $db->select("select sum(d.amount) from daybook as d 
    join projects as p on p.project_id=d.project_id
    where d.project_id=?",[$projectId]);
    $sum = round($sum[0]['sum(d.amount)'],2);

    $numUsers = $db->select("select count(user_id) from project_user_maps where project_id=?",[$projectId]);
    $numUsers = $numUsers[0]['count(user_id)'];
    $avg=$numUsers==0 ? 0 : round($sum/$numUsers,2);

    $amounts = $db->select("select u.email,ifnull(sum(d.amount),0) as amount
    from users as u
	join project_user_maps as map on u.user_id=map.user_id 
	left join daybook as d on u.user_id=d.user_id and d.project_id=map.project_id
    where map.project_id=?
    group by u.email
    order by amount desc", [$projectId]);

    foreach ($amounts as &$amount) {
        $paid = (float)$amount['amount'];
        if ($paid>$avg){
            $out=0;
            $in=$paid-$avg;
        }else if($paid==$avg){
            $out=0;
            $in=0;
        }else{
            $out=$avg-$paid;
            $in=0;
        }
        $amount['in']=$in;
        $amount['out']=$out;
    };


    $data = [
        'expenses' => $expenses,
        'sum' => $sum,
        'avg' => $avg,
        'amounts' => $amounts,
    ];
    return $data;
}


if (isset($_GET['action']) && ($_GET['action'])=="getExpenses"){
    echo json_encode(getAllData($db,$projectId));
}else if  (isset($_GET['action']) && ($_GET['action'])=="csv"){
    $data = getAllData($db,$projectId);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=report.csv');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    $headerExpenses=[
        ['Expenses List'],
        ['Daybook ID', 'User email', 'Project name', 'Date', 'Amount', 'Note'],
    ];

    $dataSummary=[
        ['Total expenses', 'Average expense'],
        [$data['sum'],$data['avg']]
    ];
    
    $headerAmounts=[
        ['Amounts List'],
        ['User email', 'Paid amount', 'To have', 'To pay'],
        
    ];
    foreach ($headerExpenses as $row){
        fputcsv($output, $row);
    };
    foreach ($data['expenses'] as $row) {
        unset($row['project_id']);
        fputcsv($output, $row);
    }
    foreach ($dataSummary as $row) {
        fputcsv($output, $row);
    }
    foreach ($headerAmounts as $row){
        fputcsv($output, $row);
    };
    foreach ($data['amounts'] as $row) {
        fputcsv($output, $row);
    }

    // Close the file pointer
    fclose($output);
    exit;
}
