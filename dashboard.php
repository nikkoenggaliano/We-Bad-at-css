<?php
	session_start();
	if(!isset($_SESSION['username'])) {
		header("location: index.php");
		exit();
	}
	if(isset($_GET['act'])) {
		if($_GET['act'] == 'out') {
			session_destroy();
			header("location: index.php");
			exit();
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dashboard</title>
</head>
<body>
	<h1>Currently Maintenance</h1>
	<code>
		Hello, 
		<?php
		include './lib/db.php';
			$query = "SELECT * from users WHERE username = '{$_SESSION['username']}'";
			$exec = mysqli_query($link, $query) or die(mysqli_error($link));
			while($data = mysqli_fetch_array($exec)){
				echo $data['username']."<br>";	
			}
			
		?>
	</code>
	<br>
	<a href="?act=out">logout</a>
</body>
</html>