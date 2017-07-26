<?php

include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

set_time_limit(1800);

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);
$test = @$_GET['test'];

$sql = 'exec infer_attendance_log_newdb ?, ?, ?';
$param = array('MBP-ASCEND', $from->format('Y-m-d'), $to->format('Y-m-d'));
$q = $conn->prepare($sql);
$q->execute($param);

if ($test){
	$sql = 'exec post_attendance_test ?, ?';
} else {
	$sql = 'exec post_attendance_newdb ?, ?';
}
$param = array($from->format('Y-m-d'), $to->format('Y-m-d'));
$q = $conn->prepare($sql);
$q->execute($param);

header('Content-Type: application/json');
echo json_encode(array('done' => true));
