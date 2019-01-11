<?php
session_start();
if(!isset($_SESSION['username'])){
  if($_SESSION['username'] != True){
     session_destroy(); 
  header("location: index.php");
  exit;
}
}

if(isset($_GET['act'])){
 session_destroy(); 
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="https//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://d19m59y37dris4.cloudfront.net/universal/2-0-1/vendor/font-awesome/css/font-awesome.min.css">
    <style type="text/css">
      
.top-bar {
    background: #555;
    color: #fff;
    font-size: 0.9rem;
    padding: 10px 0;
}

.top-bar .contact-info {
    margin-right: 20px;
}

.top-bar ul {
    margin-bottom: 0;
}

.top-bar .contact-info a {
    font-size: 0.8rem;
}

.top-bar ul.social-custom {
    margin-left: 20px;
}
.top-bar ul {
    margin-bottom: 0;
}

.top-bar a.login-btn i, .top-bar a.signup-btn i {
    margin-right: 10px;
}

.top-bar ul.social-custom a:hover {
    background: #4fbfa8;
    color: #fff;
}
.top-bar ul.social-custom a {
    text-decoration: none !important;
    font-size: 0.7rem;
    width: 26px;
    height: 26px;
    line-height: 26px;
    color: #999;
    text-align: center;
    border-radius: 50%;
    margin: 0;
}
a:focus, a:hover {
    color: #348e7b;
    text-decoration: underline;
}
.top-bar a.login-btn, .top-bar a.signup-btn {
    color: #eee;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-decoration: none !important;
    font-size: 0.75rem;
    font-weight: 700;
    margin-right: 10px;
}

    </style>
</head>
<body>

<div class="top-bar">
        <div class="container">
          <div class="row d-flex align-items-center">
            <div class="col-md-6 d-md-block d-none">
              <p>Hear Yes or Yes</p>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-md-end justify-content-between">
                <ul class="list-inline contact-info d-block d-md-none">
                  <li class="list-inline-item"><a href="#"><i class="fa fa-phone"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fa fa-envelope"></i></a></li>
                </ul>
                <div class="login"><a href="markdown.php" data-toggle="modal" data-target="#login-modal" class="login-btn"><i class="fa fa-snowflake-o"></i><span class="d-none d-md-inline-block">Markdown Parse</span></a><a href="xml.php" class="signup-btn"><i class="fa fa-superpowers"></i><span class="d-none d-md-inline-block">XML Parse</span></a><a href="?act=logout" class="signup-btn"><i class="fa fa-check"></i><span class="d-none d-md-inline-block">Logout</span></a></div>
              </div>
            </div>
          </div>
        </div>
      </div>
</body>
</html>