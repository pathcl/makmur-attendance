<?php
include('../report/inc/session.php');
include('../report/inc/auth.php');

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);
$test = @$_GET['test'];

?>
<html>
<head>
<title>Employee Attendance</title>
<link rel="stylesheet" type="text/css" href="inc/styles.css"/>
<?php include('inc/scripts.php'); ?>
<script src="inc/scripts.js"></script>
<script>
$(window).ready(function(){
	$.ajax({
		url: '/post-hris.php',
		type: 'GET',
		data: {from: '<?php echo $from->format('Y-m-d') ?>', to: '<?php echo $to->format('Y-m-d') ?>', test: <?php echo $test ? '1' : '0' ?>},
		dataType: 'json',
		timeout: 1800000,
		success: function(resp){
			alert('Posting finished');
			location.href = 'machine.php';
		},
		error: function(xhr, stat, msg){
			alert('Unknown error occured. Please check your network.');
		}
	});	/**/
	
/*	$.get('post-hris.php', {from: '<?php echo $from->format('Y-m-d') ?>', to: '<?php echo $to->format('Y-m-d') ?>', test: <?php echo $test ? '1' : '0' ?>}, function(resp){
		alert('Posting finished');
		location.href = 'machine.php';
	});	/**/
})
</script>
</head>
<body>
<?php
include('inc/menu.php');
?>
<div class="stretch">
<h1>Processing Attendance Between <?php echo $from->format('Y-m-d') ?> and <?php echo $to->format('Y-m-d') ?></h1>
<?php
$cname = 'MAKMURGROUP';
?>
<div class="loading">Processing...</div>
<div class="list hidden"></div>
</div>
</body>
</html>