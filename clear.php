<?php
header('Content-Type: application/json');

$machine = @$_GET['mch'];

if (!$machine){
	echo json_encode(array('success' => false, 'error' => 'no machine specified'));
	exit;
}

include('../report/inc/session.php');
include('../report/inc/conn.php');
//include('../report/inc/auth.php');

$param = array($machine);
$tsql = 'select * from finger_scanner where machine_code = ?';
$stmt = $conn->prepare($tsql);
$stmt->execute($param);

$clear_xml = '<ClearData>
<ArgComKey xsi:type="xsd:integer">0</ArgComKey>
<Arg><Value xsi:type="xsd:integer">3</Value></Arg>
</ClearData>';

$read_xml = '<GetAttLog>
<ArgComKey xsi:type="xsd:integer">0</ArgComKey>
<Arg>
<PIN xsi:type="xsd:integer">All</PIN>
</Arg>
</GetAttLog>';

function get_curl($url, $data, $timeout = 10){
	$headers = array(
		"Content-type: text/xml",
		"Content-length: " . strlen($data),
		"Connection: close",
	);

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$data = curl_exec($ch);
	return ['data' => $data, 'curl' => $ch];
}

function process_soap($machine, $data, $now){
	global $conn;
	
	$xml = simplexml_load_string($data);
	$json = json_encode($xml);
	$data = json_decode($json);
	
	$arr = array();
	$vals = array();
	$ctr = 0;
	$i = 0;
	if (property_exists($data, 'Row')){
		foreach($data->Row as $d){
			$arr[] = $d->DateTime; 
			$arr[] = strlen($d->PIN) < 4 ? substr('000' . $d->PIN, -4) : $d->PIN;
			$arr[] = $machine;
			$arr[] = 1;
			$arr[] = ($d->Status == 1 ? 'SignOut' : ($d->Status == 0 ? 'SignIn' : 'Auto'));
			$arr[] = $now->format('Y-m-d H:i:s');
			$vals[] = '(?, ?, ?, ?, ?, ?)';
			$i++;
			if ($i == 300){
				$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
				$ins = $conn->prepare($isql);
				$ins->execute($arr);
				
				$arr = array();
				$vals = array();
				$ctr += $i;
				$i = 0;
			}
		}
		
		if (! empty($arr)){
			$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
			$ins = $conn->prepare($isql);
			$ins->execute($arr);
			
			$ctr += $i;
		}
	}
	
	$uprm = array($now->format('Y-m-d H:i:s'), $machine);
	$usql = 'update finger_scanner set last_download = ? where machine_code = ?';
	$upd = $conn->prepare($usql);
	$upd->execute($uprm);
	
	return $ctr;
}

function process_zk($machine, $data, $now){
	global $conn;

	$arr = array();
	$vals = array();
	$ctr = 0;
	$i = 0;

	foreach($data as $d){
		$arr[] = $d['time']; 
		$arr[] = strlen($d['user']) < 4 ? substr('000' . $d['user'], -4) : $d['user'];
		$arr[] = $machine;
		$arr[] = 1;
		$arr[] = ($d['status'] == 1 ? 'SignOut' : ($d['status'] == 0 ? 'SignIn' : 'Auto'));
		$arr[] = $now->format('Y-m-d H:i:s');
		$vals[] = '(?, ?, ?, ?, ?, ?)';
		$i++;
		if ($i == 300){
			$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
			$ins = $conn->prepare($isql);
			$ins->execute($arr);
			
			$arr = array();
			$vals = array();
			$ctr += $i;
			$i = 0;
		}
	}

	if (! empty($arr)){
		$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
		$ins = $conn->prepare($isql);
		$ins->execute($arr);
		
		$ctr += $i;
	}

	$uprm = array($now->format('Y-m-d H:i:s'), $machine);
	$usql = 'update finger_scanner set last_download = ? where machine_code = ?';
	$upd = $conn->prepare($usql);
	$upd->execute($uprm);
	
	return $ctr;
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
	$now = new DateTime();
	if ($row['status'] == 1){
		set_time_limit(1800);
		
		if ($row['relay']){
			$ctx = stream_context_create(array('http'=>
				array(
					'timeout' => 1800
				)
			));
			$resp = file_get_contents('http://' . $row['relay'] . '/attendance/clear.php?mch=' . $machine, false, $ctx);
			echo trim($resp);
			exit;
		}
		
		$url = $row['ip'].$row['url'];
		
		$curl = get_curl($url, $read_xml);

/*
		$headers = array(
			"Content-type: text/xml",
			"Content-length: " . strlen($read_xml),
			"Connection: close",
		);

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $read_xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$data = curl_exec($ch); 
/**/
		$data = $curl['data'];
		$ch = $curl['curl'];
		
		if($errno = curl_errno($ch)){
			include ('lib/am05zk/am05zk.php');
			$err = array('success' => false, 'error' => curl_error($ch), 'errno' => $errno);

			if ($errno == 7){
				$zk = new am05zk($row['ip']);
				if ($zk->connect()){
					$ver = $zk->get_version();
					$ver = substr($ver, 4, 4);
					$old_sdk = $ver <= '6.40' ? true : false;
					$attendance = $zk->get_attendance($old_sdk);
					$ctr = process_zk($machine, $attendance, $now);
					$zk->clear_attendance();
					$zk->disconnect();
					echo json_encode(array('success' => true));
				} else {
					echo json_encode($err);
				}
			} else {
				echo json_encode($err);
			}
		} else if ($errno == 28) {
			$curl = get_curl($url, $read_xml, 1800);
			
			$data = $curl['data'];
			$ch = $curl['curl'];
				
			if ($errno = curl_errno($ch)) {
				$err = array('success' => false, 'error' => curl_error($ch), 'errno' => $errno);
				echo json_encode($err);
			} else {
				$ctr = process_soap($machine, $data, $now);
				
				$curl = get_curl($url, $clear_xml);
				
				$data = $curl['data']; 
				$ch = $curl['curl'];
				
				if ($errno = curl_errno($ch)){
					$err = array('success' => false, 'error' => curl_error($ch), 'errno' => $errno);
					echo json_encode($err);
				} else {
					echo json_encode(array('success' => true));
				}
			}
				
		} else {
			$ctr = process_soap($machine, $data, $now);
			
			$curl = get_curl($url, $clear_xml);
			
			$data = $curl['data']; 
			$ch = $curl['curl'];
			
			if ($errno = curl_errno($ch)){
				$err = array('success' => false, 'error' => curl_error($ch), 'errno' => $errno);
				echo json_encode($err);
			} else {
				echo json_encode(array('success' => true));
			}

		}
		curl_close($ch);
	}
} else {
	echo json_encode(array('success' => false, 'error' => 'machine not found'));
}

?>
