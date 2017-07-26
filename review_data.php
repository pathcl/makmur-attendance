<?php
include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);
$status = @$_GET['normal'];
$filter = @$_GET['filter'];

if ($status == 1){
	$sql = 'select ia.*, w.WorkgroupName, s.ShiftName from infer_attendance ia
			left join [10.14.1.10].AS_MBP.dbo.HRM_Workgroups w on ia.workgroup_id = w.WorkgroupID
			left join [10.14.1.10].AS_MBP.dbo.HRM_Shifts s on ia.shift_id = s.ShiftID
			where leave_only = 0 and (ia.confidence >= 90 or ia.updated_by is not null) and ia.[date] between ? and ? ' . 
			($filter ? 'and (w.WorkgroupName like ? or ia.fullname like ? or employee_code like ? or finger_id like ?) ' : '') . 
			'order by w.WorkgroupName, ia.fullname, ia.[date]';
}
else {
	$sql = 'select ia.*, w.WorkgroupName, s.ShiftName from infer_attendance ia
			left join [10.14.1.10].AS_MBP.dbo.HRM_Workgroups w on ia.workgroup_id = w.WorkgroupID
			left join [10.14.1.10].AS_MBP.dbo.HRM_Shifts s on ia.shift_id = s.ShiftID
			where leave_only = 0 and ia.confidence < 90 and ia.updated_by is null and ia.[date] between ? and ? ' . 
			($filter ? 'and (w.WorkgroupName like ? or ia.fullname like ? or employee_code like ? or finger_id like ?) ' : '') . 
			'order by w.WorkgroupName, ia.fullname, ia.[date]';
}
if ($filter){
	$param = array($from->format('Y-m-d'), $to->format('Y-m-d'), '%' . $filter . '%', '%' . $filter . '%', $filter . '%', '%' . $filter . '%');
} else {
	$param = array($from->format('Y-m-d'), $to->format('Y-m-d'));
}
$q = $conn->prepare($sql);
$q->execute($param);
?>
<div class="fixed-header hidden">
<table>
<thead>
<tr>
	<th rowspan="2">Workgroup</th>
	<th colspan="3">Employee</th>
	<th rowspan="2">Date</th>
	<th rowspan="2">Shift</th>
	<th colspan="2" width="15%">Time</th>
</tr>
<tr>
	<th>Name</th>
	<th>Code</th>
	<th>Finger ID</th>
	<th>In</th>
	<th>Out</th>
</tr>
</thead>
</table>
</div>
<form id="post-form" method="post" action="save.php?from=<?php echo $from->format('Y-m-d') ?>&to=<?php echo $to->format('Y-m-d') ?><?php echo $status ? '&normal=' . $status : '' ?><?php echo $filter ? '&filter=' . $filter : '' ?>">
<table>
<thead>
<tr>
	<th rowspan="2">Workgroup</th>
	<th colspan="3">Employee</th>
	<th rowspan="2">Date</th>
	<th rowspan="2">Shift</th>
	<th colspan="2" width="15%">Time</th>
	<th rowspan="2" width="10%">Action</th>
</tr>
<tr>
	<th>Name</th>
	<th>Code</th>
	<th>Finger ID</th>
	<th>In</th>
	<th>Out</th>
</tr>
</thead>
<tbody>
<?php
while($row = $q->fetch(PDO::FETCH_ASSOC)){
	$d = new DateTime($row['date']);
?><tr data-times="<?php 
	$times = explode(',', $row['logs']);
	$_times = array();
	foreach($times as $t){
		$tmp = new DateTime($t);
		$_times[($d->format('d') != $tmp->format('d') ? '+1 ' : '') . $tmp->format('H:i')] = 1;
	}
	echo implode(', ', array_keys($_times));
	?>">
	<td><?php echo $row['WorkgroupName'] ?></td>
	<td><?php echo $row['fullname'] ?></td>
	<td><?php echo $row['employee_code'] ?></td>
	<td><?php echo $row['finger_id'] ?></td>
	<td><?php echo $d->format('Y-m-d') ?></td>
	<td><?php echo $row['ShiftName'] ?></td>
	<td class="time"><input type="text" name="att[<?php echo $row['employee_id'] ?>][<?php echo $d->format('Y-m-d') ?>][in]" value="<?php if($row['actual_in']){$in = new DateTime($row['actual_in']); echo $in->format('H:i');} ?>"/><button type="button">...</button></td>
	<td class="time"><input type="text" name="att[<?php echo $row['employee_id'] ?>][<?php echo $d->format('Y-m-d') ?>][out]" value="<?php if($row['actual_out']){$out = new DateTime($row['actual_out']); echo ($d->format('d') != $out->format('d') ? '+1 ' : '') . $out->format('H:i');} ?>"/><button type="button">...</button></td>
	<td class="btns"><button type="button">V</button><button type="button">X</button></td>
</tr><?php
}
?>
</tbody>
</table>
<div class="btn-save">
<button type="submit">Save</button>
</div>
</form>