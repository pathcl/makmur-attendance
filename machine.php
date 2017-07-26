<?php
include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

?>
<html>
<head>
<title>Employee Attendance</title>
<link rel="stylesheet" type="text/css" href="inc/styles.css"/>
<?php include('inc/scripts.php'); ?>
<script src="inc/scripts.js"></script>
</head>
<body>
<?php
include('inc/menu.php');
?>
<div class="stretch">
<h1>Fingerprint Scanners</h1>
<?php
$cname = 'MAKMURGROUP';

$sql = 'select * from finger_scanner where status = 1 and company_code = \'MBP-ASCEND\'';
$q = $conn->query($sql);

?>
<table class="fixed">
<thead>
<tr>
	<th class="checkbox"><input type="checkbox" id="all"/></th><th>Machine Name</th><th>Machine IP</th><th>Last Data Download</th>
</tr>
</thead>
<tbody>
<?php
while($row = $q->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr>
		<td class="checkbox"><input type="checkbox" id="<?php echo $row['machine_code'] ?>"/></td>
		<td><?php echo $row['machine_code'] ?></td>
		<td><?php echo $row['ip'] ?></td>
		<td id="st-<?php echo $row['machine_code'] ?>"><?php if ($row['last_download']) { $d = new DateTime($row['last_download']); echo $d->format('Y-m-d H:i:s'); } ?></td>
	</tr>
	<?php
}
?>
<tfoot>
<tr>
	<th colspan="4" class="buttons">
		<button type="button" id="btn-download">Download</button>
	<?php /** / ?>
		<button type="button" id="btn-clear">Clear Attendance Data</button>
		<button type="button" id="btn-cal-prc">Process Attendance</button><button type="button" id="btn-cal-rvw">Review Attendance</button>
	<?php /**/ ?>
	</th>
</tr>
</tfoot>
</tbody>
</table>
</div>
</body>
</html>