<?php
$ctx = stream_context_create(array('http'=>
    array(
        'timeout' => 1800
    )
));
$test = file_get_contents('http://10.14.1.10/attendance/pull.php?mch=KIM3-001', false, $ctx);
var_dump($test);
?>