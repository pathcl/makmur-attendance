<?php

include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

set_time_limit(1800);

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);
$test = @$_GET['test'];

$hris_host = 'localhost';
$hris_db = 'db_hris';
$hris_user = 'root';
$hris_pass = 'rasengan';

try {
	$myconn = new PDO('mysql:host='.$hris_host.';dbname='.$hris_db, $hris_user, $hris_pass);
} catch (Exception $e){
	die('MySQL connect failed');
}
$myconn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$sql = 'truncate table hris_employees';
$q = $conn->query($sql);

$msql = 'select p.FingerId finger_id, p.EmployeeCode as employee_code, p.EmployeeType as employee_type, p.FullName as fullname from db_pegawai p join (select FingerId, max(EmployeeCode) as EmployeeCode from db_pegawai group by FingerId) tmp on p.FingerId = tmp.FingerId and p.EmployeeCode = tmp.EmployeeCode';
$mq = $myconn->query($msql);

while ($data = $mq->fetch(PDO::FETCH_ASSOC)){
	$sql = 'insert into hris_employees(finger_id, employee_code, employee_type, fullname) values(?, ?, ?, ?)';
	$param = [$data['finger_id'], $data['employee_code'], $data['employee_type'], $data['fullname']];
	$q = $conn->prepare($sql);
	$q->execute($param);
}

/**/
$sql = 'exec infer_attendance_hris_log ?, ?, ?';
$param = array('MBP-ASCEND', $from->format('Y-m-d'), $to->format('Y-m-d'));
$q = $conn->prepare($sql);
$q->execute($param);
/**/

$sql = 'select * from infer_attendance_hris where leave_only = 0 and employee_code is not null and [date] between ? and ?';
$param = [$from->format('Y-m-d'), $to->format('Y-m-d')];
$q = $conn->prepare($sql);
$q->execute($param);

$msql = 'select tgl from db_holiday where tgl between ? and ?';
$mparam = [$from->format('Y-m-d'), $to->format('Y-m-d')];
$mq = $myconn->prepare($msql);
$mq->execute($mparam);

$holidays = [];
while($h = $mq->fetch(PDO::FETCH_ASSOC)){
	$d = new DateTime($h['tgl']);
	$holidays[] = $d->format('Y-m-d');
}

while($data = $q->fetch(PDO::FETCH_ASSOC)){
	$msql = 'insert into db_absensi(EmployeeCode, Fullname, Tanggal, Shift, JamMulai, JamSelesai, isHoliday) values(?, ?, ?, ?, ?, ?, ?)';
	$shift = 1;
	if ($data['actual_in']){
		$in = new DateTime($data['actual_in']);
		$in = $in->format('H:i:s');
	} else {
		$in = '?';
	}
	if ($data['actual_out']){
		$out = new DateTime($data['actual_out']);
		$out = $out->format('H:i:s');
	} else {
		$out = '?';
	}
	if ($in != '?'){
		if ($in >= '15:30:00' and $in <= '16:45:00'){
			$shift = 2;
		}
		if ($in >= '23:30:00' and $in <= '00:45:00'){
			$shift = 3;
		}
	} else {
		if ($out >= '23:30:00' and $out <= '00:45:00'){
			$shift = 2;
		}
		if ($out >= '07:30:00' and $out <= '08:45:00'){
			$shift = 3;
		}
	}
	$d = new DateTime($data['date']);
	$isholiday = in_array($d->format('Y-m-d'), $holidays) ? 1 : 0;
	$mparam = [
			$data['employee_code'], $data['fullname'], $data['date'], $shift, $data['actual_in'], $data['actual_out'], $isholiday
		];
	$mq = $myconn->prepare($msql);
	$mq->execute($mparam);
}

header('Content-Type: application/json');
echo json_encode(array('done' => true, 'holidays' => $holidays));
