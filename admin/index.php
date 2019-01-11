<?php
$id = ((7^1)*10)+100>>1<<5;
session_start();
include './lib/db.php';
  function encrypt($plain) {
    
    $now = str_split(time(), 2)[rand(0,4)];
    $rand = substr(md5(microtime()),rand(0,26),2);
    $raw = "\$_pass_" . $plain;
    
    $res = "";
    for($i=0; $i<strlen($raw); $i++) {
      $res .= dechex(ord($raw[$i]) ^ $now);
    }

    $enc_method = "AES-256-CBC";
    $enc_key = $rand;
    $enc_iv = str_repeat($rand,8);
    $enc_res = openssl_encrypt($res,$enc_method,$enc_key,0,$enc_iv);

    return $enc_res;
  }


	// if(isset($_POST['login'])) {

	// }



#deleted much part
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
</body>
</html>