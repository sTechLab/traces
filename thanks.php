<?php
//Says thanks
require_once("util.php");
require_once("data_handler.php");
session_start();

$_CLEAN = clean($_REQUEST);
$formSubmit = false;
$errors = false;

if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "All done!") {
	$data = array();
	$data['experiment_name'] = $_CLEAN['experiment_name'];
	$data['url'] = $_CLEAN['url'];
	$data['user_id'] = $_CLEAN['uid'];
	$data['type'] = "submit";
	$data['date'] = date('r');
	$data['key'] = "submit";
	$data['value'] = "submit";
	storeDb($data, 'interaction');
	
	$payment = array();
	$payment['experiment_name'] = $data['experiment_name'];
	$payment['user_id'] = $data['user_id'];
	$payment['date_code_delivered'] = $data['date'];
	$words = getRandomCodeWords("admin/4+_letter_words.txt", 2);
	$payment['payment_code'] = $words[0] . strval(rand(1, 99)) . $words[1];
	storeDb($payment, 'payment');
	
	$formSubmit = true;
}
if (array_key_exists("errors", $_SESSION)){
	$errors = explode("%BREAK%", $_SESSION['errors']);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Thanks</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<!--[if IE 5]>
<style type="text/css"> 
/* place css box model fixes for IE 5* in this conditional comment */
.twoColFixLtHdr #sidebar1 { width: 230px; }
</style>
<![endif]--><!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.twoColFixLtHdr #sidebar1 { padding-top: 30px; }
.twoColFixLtHdr #mainContent { zoom: 1; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->

</head>

<body class="oneColFixCtrHdr">

<div id="container">

  <div id="header">
    <h1>Thanks</h1>
  <!-- end #header --></div>

  <div id="mainContent">
<?php
if ($formSubmit && !$errors){
	print "<p>Here's the code you can use to get paid:</p>\n";
	print "<p>" . $payment['payment_code'] . "<p>\n";
} else if ($errors) {
	print "<p>We're sorry, the following errors were encountered:</p>\n<ol>\n";
	foreach ($errors as $error){
		print "<li>" . $error . "</li>\n";
	}
	print "</ol>\n";
} else {
	print "<p>It doesn't look like you've submitted a survey. <a href=\"survey.php\">Would you like to?</a></p>\n";
}
?>
  <!-- end #mainContent --></div>

  <div id="footer">
    <p>A <a href="http://sm.rutgers.edu/">SMIL</a> production</p>
  <!-- end #footer --></div>
  
<!-- end #container --></div>
</body>
</html>
<?php

end_session();

?>