<?php
include './lib/db.php';
	if(isset($_POST['login'])) {
		$username = mysqli_real_escape_string($link, $_POST['username']);
		$password = md5($_POST['password']);
		$q = "SELECT username,password FROM users WHERE username = '{$username}' AND  password = '{$password}'";
		$login = mysqli_query($link, $q);
		if(mysqli_num_rows($login) > 0) {
			session_start();
			$_SESSION['username'] = $_POST['username'];
			header("location: dashboard.php");
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login</title>
</head>
<body>
	<form action="" method="post">
		<table>
		<tr>
			<td>Username</td>
			<td></td>
			<td><input type="text" name="username" maxlength="20" required></td>
		</tr>
		<tr>
			<td>Password</td>
			<td></td>
			<td><input type="password" name="password" required></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><button type="submit" name="login">Login</button></td>
		</tr>
		</table>
	</form>
	<a href="regis.php">register here</a>
</body>
</html>