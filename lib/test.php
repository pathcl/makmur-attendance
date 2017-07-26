<?php

include 'am05zk/am05zk.php';

//$zk = new am05zk('10.14.1.8');
//$zk = new am05zk('10.11.1.3');
$zk = new am05zk('10.12.1.3');

	
if ($zk->connect()){
	$ver = $zk->get_version();
	$ver = substr($ver, 4, 4);
	
	//var_dump($zk->get_users($ver < '6.40' ? true : false));
	var_dump($zk->get_attendance($ver < '6.40' ? true : false));
	$zk->disconnect();
}

/*
$uid = 1000;
$p = pack('C3', $uid % 256, $uid >> 8, 14);
echo bin2hex($p);
/**/