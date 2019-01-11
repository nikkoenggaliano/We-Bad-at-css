<?php 
session_start();
if(!isset($_SESSION['username'])){
	header("location: index.php");
	//exit;
}
include './lib/phpmarkdown.php';
$main = new Parsedown();
if (!isset($_SESSION['nay'])) {
 	$_SESSION['nay'] = md5('nepska'.time().'nayeon'); 
}
if(isset($_POST['input']) && $_POST['csrf']){
	if($_POST['csrf'] !== $_SESSION['nay']){
		die("Fuck this hacker!");
	}
	$data = $_POST['input'];
	$user = $main->text($data);

}
?> 
<!DOCTYPE html>
<html>
<head>
	<title>Nepska</title>
</head>
<body>


<h1>Markdown Parse</h1>

<form method="POST">
<textarea name="input" rows="10" cols="50"><?php echo "# Nepska _Markdown_"; ?></textarea>
	<br>
	<input type="hidden" name="csrf" value="<?=$_SESSION['nay']?>">
	<input type="submit" name="go">
	</form>

<br>
	<?php
	if(isset($user)){
		echo $user;
	}
	?>

</body>
</html>



