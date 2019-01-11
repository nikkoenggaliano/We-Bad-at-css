<?php 
session_start();
if(!isset($_SESSION['username'])){
  if($_SESSION['username'] != True){
     session_destroy(); 
  header("location: index.php");
  exit;
}
}

if (!isset($_SESSION['nay'])) {
 	$_SESSION['nay'] = md5('nepska'.time().'nayeon'); 
}
if(isset($_POST['input']) && $_POST['csrf']){
	if($_POST['csrf'] !== $_SESSION['nay']){
		die("Fuck this hacker!");
	}
    libxml_disable_entity_loader (false);
    $xmlfile = $_POST['input'];
    $dom = new DOMDocument();
    $dom->loadXML($xmlfile, LIBXML_NOENT | LIBXML_DTDLOAD);
    $creds = simplexml_import_dom($dom);
    $user = $creds->user;
    $pass = $creds->pass;

}
?> 
<!DOCTYPE html>
<html>
<head>
	<title>Nepska</title>
</head>
<body>

<center>
<h1>XML Parse</h1>
<form method="POST">
	<textarea name="input" rows="10" cols="50">
		<?php echo "<creds>
    <user>Nepska Username</user>
    <pass>Your Password</pass>
		</creds>"; ?></textarea>
	<br>
	<input type="hidden" name="csrf" value="<?=$_SESSION['nay']?>">
	<input type="submit" name="go">
	</form>
</center>
<br>
<center>
<textarea rows="10" cols="50"><?php if(isset($user) && isset($pass)){echo $user;}?></textarea>
</center>
</body>
</html>



