<?php
session_start();

if (isset($_SESSION['user'])){
	if (@$_GET['logout']) {
		unset($_SESSION['user']);
		header('location: index.php');
		exit();
	}
	header('location: machine.php');
	/** /
	?>
	<html class="blue">
	<head>
	<title>Attendance</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="theme-color" content="#123d8c" />
	<link rel="stylesheet" type="text/css" href="http://report.makmurgroup.id/inc/style.css">
	<link rel="stylesheet" media="(max-width: 800px)" href="http://report.makmurgroup.id/inc/mstyle.css" />
	<link rel="stylesheet" type="text/css" href="http://report.makmurgroup.id/inc/jquery-ui.css">
	<script src="http://report.makmurgroup.id/inc/jquery.min.js"></script>
	<script src="http://report.makmurgroup.id/inc/jquery-ui.min.js"></script>
	<script src="http://report.makmurgroup.id/inc/filter.js"></script>
	<script src="http://report.makmurgroup.id/inc/modernizr.date.js"></script>
	<script src="http://report.makmurgroup.id/inc/cal.js"></script>
	</head>
	<body class="blue">
	<div class="right">
		<a class='logo' href='/index.php?logout=1'>Logout</a>
	</div>
	<div class="panel">
	<img src="http://helpdesk.makmurgroup.id/images/logo.png">
	<form action="q.php" method="GET" autocomplete="off">
	<label>Profile</label><select name="c">
		<option value="MBP">Makmur Bintang Plastindo</option>
		<option value="MBR">Makmur Bintang Rollindo</option>
	<?php /** / ?>
		<option value="MBI">Makmur Bintang Indonesia</option>
		<option value="TEST">Testing</option>
	<?php /** / ?>
	</select>
	<input type="hidden" name="dfrom" value="<?=$awal?>"/>
	<input type="hidden" name="dto" value="<?=$akhir?>"/>
	<br/>
	<br/>
	<button type="submit">Select</button>
	</form>
	</div>
	<?php include "../report/inc/footer.php"; ?>
	</body>
	</html>
	<?php
	/**/
} else {
	$auth = false;
	if ($_POST){
		$username = "username";
		$pw = "password";

		$c = @$_GET['c'];
		if (!$c)
			$c = 'MBP';

		$hostname = 'db1.makmurgroup.id';
		$dbname = 'RPT';

		$conn = new PDO("sqlsrv:server=$hostname;database=$dbname","$username","$pw");
		$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$user = trim($_POST['user']);
		$pwd = trim($_POST['pwd']);
		
		$param = array($user);
		$tsql = "SELECT u.us_id, u.us_salt, u.us_password, u.us_firstname, u.us_lastname FROM view_users u where us_username = ?";
		$stmt = $conn->prepare($tsql);
		$stmt->execute($param);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($row['us_password']){
			$inputpwd = $pwd.$row['us_salt'];
			$inputpwd = strtoupper(md5($inputpwd));
			
			if ($inputpwd == $row['us_password']){
				$_SESSION['user'] = $user;
				$_SESSION['user_id'] = $row['us_id'];
				$_SESSION['name'] = $row['us_firstname'].' '.$row['us_lastname'];
				$_SESSION['role'] = $row['role'];
				$_SESSION['role_id'] = $row['role_id'];
				$auth = true;
			}
		} else {
			//echo 'ga ketemu';
		}
	}
	if (!$auth){
		?>
		<html class="blue">
		<head>
		<title>Employee Attendance</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="theme-color" content="#123d8c" />
		<link rel="stylesheet" type="text/css" href="http://report.makmurgroup.id/inc/style.css"/>
		<link rel="stylesheet" media="(max-width: 800px)" href="http://report.makmurgroup.id/inc/mstyle.css" />
		</head>
		<body class="blue">
			<div class="panel">
				<img src="http://helpdesk.mbplast.co.id/images/logo.png">
				<form method="post">
					<label>Username</label><input type="text" name="user"/>
					<label>Password</label><input type="password" name="pwd"/>
					<button type="submit">Login</button>
				</form>
				<?php if ($_POST): ?>
					<div class="error">
						Invalid Username or Password
					</div>
				<?php endif ?>
			</div>
		<?php include "../report/inc/footer.php"; ?>
		</body>
		</html>
		<?php
	} else {
		header('location: machine.php');
	}
}
?>