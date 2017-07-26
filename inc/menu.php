<div class="veil"></div>
<div class="date-popup">
<h2>Select Periode</h2>
	<div class="pop">
	<label>From</label><input type="date" min="0001-01-01" max="9999-12-31" id="opt_from"/>
	<label>To</label><input type="date" min="0001-01-01" max="9999-12-31" id="opt_to"/>
	<input type="checkbox" id="test_only" name="test_only" value="1"/><label for="test_only">Only test</label>
	<br /><br /><button type="button" id="btn-process">Process</button>
	</div>
</div>
<div class="menu">
<ul class="menu">
<li class="home"><a href="machine.php"><img src="http://report.makmurgroup.id/inc/images/home.png" height="20"/></a></li>
<li><a href="#" id="btn-post">Post to Ascend</a></li>
<?php /* delete the space to enable HRIS * / ?>
<li><a href="#" id="btn-post-hris">Post to HRIS</a></li>
<?php /**/ ?>
<li class="right"><a href="index.php?logout=1">Logout</a></li>
	<li class="right welcome"><a>Welcome, <?php echo $_SESSION['name'] ?></a></li>
	<?php /** / ?>
	<li class="right"><a href="#" class="cal"><img src="http://report.makmurgroup.id/inc/images/calendar-icon.png" height="20"/></a></li>
	<?php
	/**/
	if ($_SESSION['role'] == 'admin'){
		?>
		<li class="right"><a href="setting.php"><img src="http://report.makmurgroup.id/inc/images/gear.png" height="20"/></a></li>
		<?php
	}
?>
</ul>
</div>