<?php
$datefrom = @$_GET['dfrom'];
$dateto = @$_GET['dto'];
$c = @$_GET['c'];

include('../report/inc/session.php');

//var_dump($_SESSION);
//exit;


include('../report/inc/conn.php');
include('../report/inc/auth.php');

$tsql = 'select * from finger_scanner';
//$stmt = $conn->prepare($tsql);
//$stmt->execute();

$read_xml = '<GetAttLog>
<ArgComKey xsi:type="xsd:integer">0</ArgComKey>
<Arg>
<PIN xsi:type="xsd:integer">All</PIN>
</Arg>
</GetAttLog>';

//while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$row = array('status' => 1, 'ip' => '10.14.1.5', 'url' => '/iWsService');
	if ($row['status'] == 1){
		$url = $row['ip'].$row['url'];

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
		$xml = simplexml_load_string($data);
		$json = json_encode($xml);
		$data = json_decode($json);
		
		
		echo '<pre>';
		var_dump($data);
		/**/
		$arr = array();
		$vals = array();
		$i = 0;
		$now = new DateTime();
		foreach($data->Row as $d){
			$arr[] = $d->DateTime; 
			$arr[] = $d->PIN;
			$arr[] = '';
			$arr[] = 1;
			$arr[] = 'Auto';
			$arr[] = $now->format('Y-m-d H:i:s');
			$vals[] = '(?, ?, ?, ?, ?, ?)';
			$i++;
			if ($i == 300){
				$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
				$ins = $conn->prepare($isql);
				$ins->execute($arr);
				
//				var_dump(count($arr));
				
				$arr = array();
				$vals = array();
				$i = 0;
			}
		}
		
//		var_dump(count($arr));
		/*if (! empty($arr)){
			$isql = 'insert into attendance_log([datetime], finger_id, terminal_code, success, action, insert_date) values ' . implode(',', $vals);
			$ins = $conn->prepare($isql);
			$ins->execute($arr);
		}
		/**/
		//echo '</pre>';
	}

//}

?>
