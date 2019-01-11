<?php
	include './lib/db.php';
	if(isset($_POST['regis'])) {
		if(strlen($_POST['password']) < 10) {
			echo "For security reason password must be 10 or more!";
		} else {
			if($_POST['password'] !== $_POST['confirm_password']) {
				echo "you drunk, buddy ?";
			} else {
				if(mysqli_num_rows(mysqli_query($link, "SELECT username FROM users WHERE username = '".mysqli_real_escape_string($link, $_POST['username'])."'")) > 0) {
					echo "that fuckin username already exitsts";
				} else {
					$query = "INSERT INTO users (username,password) VALUES ('".mysqli_real_escape_string($link, $_POST['username'])."','".md5($_POST['password'])."')";
					if(mysqli_query($link, $query)) {
						echo 'registration success. login <a href="index.php">here</a>';
					} else {
						echo 'registration failed';
					}
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Register</title>
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
			<td>Confirm Password</td>
			<td></td>
			<td><input type="password" name="confirm_password" required></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><button type="submit" name="regis">Register</button></td>
		</tr>
		</table>
	</form>
</body>
</html>