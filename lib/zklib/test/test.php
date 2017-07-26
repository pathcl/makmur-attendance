<html>
    <head>
        <title>ZK Test</title>
    </head>
    
    <body>
<?php
    include("../zklib.php");
    
    $zk = new ZKLib("10.11.1.3", 4370);
    
    $ret = $zk->connect();
	
	if ($ret){
		echo $zk->version() . '<br>';
		echo $zk->osversion() . '<br>';
		echo $zk->platform() . '<br>';
		echo $zk->fmVersion() . '<br>';
		echo $zk->workCode() . '<br>';
		echo $zk->ssr() . '<br>';
		echo $zk->pinWidth() . '<br>';
		echo $zk->faceFunctionOn() . '<br>';
		echo $zk->serialNumber() . '<br>';
		echo $zk->deviceName() . '<br>';
		$attendance = $zk->getAttendance();
		$zk->disconnect();
		
		var_dump($attendance);
	}
	
?>
    </body>
</html>
