<?php

$experiment = "YouTube";
$date = date('r');
$userId = md5($date);

require_once("util.php");
require_once("survey_display.php");
require_once("data_handler.php");
session_start();

$config = getConfig();
$ytConfig = $config[$experiment];
$_SESSION['experiment'] = $experiment;
$surveyKeys = getSurveyQuestions($ytConfig);

$_CLEAN = clean($_REQUEST);
$debug = false;

if (array_key_exists("test",$_CLEAN)){
	$debug = true;
}
$errors = array();
if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Submit survey") {
	//Error check form
	$errors = validateForm($_CLEAN, $surveyKeys);
	
	if (empty($errors)){
		
		//Check for user errors. Right now just checks age, but could check login info and such.
		$userValidationErrors = userValidationErrors($_CLEAN);
		if ($userValidationErrors){
			$_SESSION['errors'] = implode("%BREAK%", $userValidationErrors);
			header("Location: thanks.php");
		} else {
		
			//Update session data
			//Store survey data
			$_CLEAN['admin'] = $ytConfig;
			foreach ($_CLEAN as $key => $value) {
				$key = urldecode($key);
				if ($key == "pic_condition" || $key == "gender_condition" || $key == "age_condition" || $key == "summary_condition") {
					$_SESSION['condition'][$key] = $value;
				} else if ($key == "age" || $key == "gender" || in_array($key, $surveyKeys)) {
					$_SESSION['survey'][$key] = $value;
				}
			}
			$data = array();
			$data['age'] = $_CLEAN['age'];
			$data['gender'] = $_CLEAN['gender'];
			$data['date'] = $date;
			$data['user_id'] = $userId;
			foreach ($surveyKeys as $key) {
				$data[urlencode($key)] = $_CLEAN[urlencode($key)];
			}
			//Store our survey data and go
			storeDb($data, 'survey');
			//storeDataJson($_SESSION, $_SESSION['experiment']);
			header("Location: experiment.php?uid=" . $userId);
		}
	}
}

	//Set up arrays of likert scale questions
	$likert3 = array();
	$likert5 = array();
	$likert7 = array();
	foreach ($ytConfig as $key => $value) {
		if (!strncasecmp("Likert:", $key, 7)) {
			if (intval($value) == 3) {
				$likert3[] = trim(substr($key, 7));
			} elseif (intval($value) == 5) {
				$likert5[] = trim(substr($key, 7));
			} elseif (intval($value) == 7) {
				$likert7[] = trim(substr($key, 7));
			}
		}
	}
	
	
//Set up condition assignments
if (array_key_exists('Percent assigned to display profile pictures condition', $ytConfig) &&  $ytConfig['Percent assigned to display profile pictures condition'] != "") {
	$percentPicsCondition = intval($ytConfig['Percent assigned to display profile pictures condition']);
} else {
	$percentPicsCondition = 50;
}
if (array_key_exists('Percent assigned to same gender condition', $ytConfig) &&  $ytConfig['Percent assigned to same gender condition'] != "") {
	$percentSameAgeCondition = intval($ytConfig['Percent assigned to same gender condition']);
} else {
	$percentSameAgeCondition = 50;
}
if (array_key_exists('Percent assigned to same age condition', $ytConfig) &&  $ytConfig['Percent assigned to same age condition'] != "") {
	$percentSameGenderCondition = intval($ytConfig['Percent assigned to same age condition']);
} else {
	$percentSameGenderCondition = 50;
}
if (array_key_exists('Percent assigned to display summary condition', $ytConfig) &&  $ytConfig['Percent assigned to display summary condition'] != "") {
	$percentSummaryCondition = intval($ytConfig['Percent assigned to display summary condition']);
} else {
	$percentSummaryCondition = 50;
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Survey</title>
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

<script type="text/javascript">
<?php printRandomizer($debug, $percentPicsCondition, $percentSameAgeCondition, $percentSameGenderCondition, $percentSummaryCondition); ?>
</script>

</head>

<body class="oneColFixCtrHdr">

<div id="container">

  <div id="header">
    <h1>Survey</h1>
  <!-- end #header --></div>

  <div id="mainContent">
    <form action="survey.php" method="post" name="survey">
    	<p>
            <label for="age">Enter your age:</label>
            <input type="text" name="age" id="age" maxlength="3" size="3" 
            	value="<?php if (array_key_exists('age', $_CLEAN)) { print $_CLEAN['age']; } ?>" />
            <?php if (array_key_exists('age', $errors)){
            	print "<span class=\"error\">" . $errors['age'] . "</span>";
            } ?>
        </p>
        <p>
            <label for="gender">How do you identify yourself:</label>
            <select name="gender" id="gender">
            	<option value=""></option>
                <option value="f"<?php if (array_key_exists('age', $_CLEAN) && $_CLEAN['gender'] == "f") { print " selected=\"selected\""; } ?>>Female</option>
                <option value="m"<?php if (array_key_exists('age', $_CLEAN) && $_CLEAN['gender'] == "m") { print " selected=\"selected\""; } ?>>Male</option>
            </select>
            <?php if (array_key_exists('gender', $errors)) {
            	print "<span class=\"error\">" . $errors['gender'] . "</span>";
            } ?>
    	</p>
        <p>Here are a number of personality traits that may or may not apply to you. Please select the button next to each statement to indicate the extent to which you agree or disagree with that statement. You should rate the extent to which the pair of traits applies to you, even if one characteristic applies more strongly than the other.</p>
<?php
if (!empty($likert3)) {
	drawLikert($likert3, 3, $_CLEAN, $errors);
}
if (!empty($likert5)) {
	drawLikert($likert5, 5, $_CLEAN, $errors);
}
if (!empty($likert7)) {
	drawLikert($likert7, 7, $_CLEAN, $errors);
}
?>

<?php setConditions($debug, $percentPicsCondition, $percentSameAgeCondition, $percentSameGenderCondition, $percentSummaryCondition); ?>
        <p>
        	<input type="submit" name="submit" id="submit" value="Submit survey" /> 
    	</p>
    </form>
	<!-- end #mainContent --></div>

  <div id="footer">
    <p>A <a href="http://sm.rutgers.edu/">SMIL</a> production</p>
  <!-- end #footer --></div>
  
<!-- end #container --></div>
</body>
</html>