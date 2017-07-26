<?php
$ctx = stream_context_create(array('http'=>
	array(
		'timeout' => 1800
	)
));
$res = file_get_contents('http://attendance.makmurgroup.id/clear.php?mch=' . $argv[1], false, $ctx);

var_dump($res);
?>