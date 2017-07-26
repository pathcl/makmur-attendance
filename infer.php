<?php
include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);

set_time_limit(1800);
$sql = 'exec infer_attendance_log ?, ?, ?';
$param = array('MBP-ASCEND', $from->format('Y-m-d'), $to->format('Y-m-d'));
$q = $conn->prepare($sql);
$q->execute($param);
