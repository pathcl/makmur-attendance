<?php
include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

set_time_limit(1800);

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);
$status = @$_GET['normal'];
$filter = @$_GET['filter'];

$d = new DateTime();
foreach($_POST['att'] as $emp_id => $dates){
	$sql = 'update infer_attendance set updated_by = ?, updated_at = ?, actual_in = ?, actual_out = ? where employee_id = ? and [date] = ?';
	
	foreach($dates as $date => $data){
		if (substr($data['in'], 0, 1) == '+'){
			$in = new DateTime($date . substr($data['in'], 2) . ':00');
			$in->modify('+1 day');
			$in = $in->format('Y-m-d H:i:s');
		} else if (!$data['in']) {
			$in = null;
		} else {
			$in = $date . ' ' . $data['in'] . ':00';
		}
		if (substr($data['out'], 0, 1) == '+'){
			$out = new DateTime($date . substr($data['out'], 2) . ':00');
			$out->modify('+1 day');
			$out = $out->format('Y-m-d H:i:s');
		} else if (!$data['out']) {
			$out = null;
		} else {
			$out = $date . ' ' . $data['out'] . ':00';
		}
		$param = array($_SESSION['user'], $d->format('Y-m-d'), $in, $out, $emp_id, $date);
		$q = $conn->prepare($sql);
		$q->execute($param);
	}
}
header('content-type: application/json');
echo json_encode(array('success' => true));
//header('location: attendance.php?from=' . $from->format('Y-m-d') . '&to=' . $to->format('Y-m-d') . ($status ? '&normal=' . $status : ''));
?>