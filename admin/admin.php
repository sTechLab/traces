<?php
require_once("../data_handler.php");
require_once("admin_display.php");
require_once("../util.php");
require("../credentials.php"); //For safety I usually use require_once() but credentials.php is also needed by data_handler.php
session_start();

//Set up some parameters needed to render the page
$config = getConfig();
$formData = $config;
$_CLEAN = clean($_REQUEST); 
$curPage = (array_key_exists('page', $_CLEAN)) ? $_CLEAN['page'] : key($config);
$offset = (array_key_exists('offset', $_CLEAN)) ? intval($_CLEAN['offset']) : 0;
$limit = (array_key_exists('limit', $_CLEAN)) ? intval($_CLEAN['limit']) : 50;
$showBlankRow = false;
$showAddButton = true;
$showUpdateButton = true;
$valid = false;

if (array_key_exists('pw', $_CLEAN) && $_CLEAN['pw'] == $admin_password){
	$_SESSION['valid'] = 1;
}
if (array_key_exists('valid', $_SESSION) && $_SESSION['valid']){
	$valid = true;
}

//Handle data pages
if (array_key_exists('page', $_CLEAN) && in_array(strtolower($_CLEAN['page']), array("survey", "experiment", "interaction"))) {
	$showAddButton = false;
	$showUpdateButton = false;
} else if (array_key_exists('page', $_CLEAN) && $_CLEAN['page'] == "comments") {
	$formData = getJsonData("comments.json");
}

//Handle our various form submission buttons
if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Update") {
	if (array_key_exists('blockName', $_CLEAN) && $_CLEAN['blockName'] != "") {
		if ($_CLEAN['blockName'] == "comments"){
			$formData = array("comments" => getVariableValues($_CLEAN));
			writeJson($formData, "comments.json");
		} else {
			updateIniArrayFromSingleRequestArray($config, $_CLEAN);
			writeIni($config, "settings.ini");
		}
	}
} else if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Delete Experiment") {
	removeBlockfromIniArray($_CLEAN['blockName'], $config);
	writeIni($config, "settings.ini");
	$curPage = key($config);
} else if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Delete") {
	if (array_key_exists('blockName', $_CLEAN) && $_CLEAN['blockName'] == "comments") {
		unset($_CLEAN[$_CLEAN['deleteVariable']]);
		$formData = array("comments" => getVariableValues($_CLEAN));
		writeJson($formData, "comments.json");
	} else {
		removeVariableFromIniArrayBlock($_CLEAN[$_CLEAN['deleteVariable']], $_CLEAN['blockName'], $config);
		writeIni($config, "settings.ini");
	}
} else if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Add") {
	$showBlankRow = true;
	$showAddButton = false;
} else if (array_key_exists('submit', $_CLEAN) && $_CLEAN['submit'] == "Add Experiment") {
	$curPage = "";
	$showBlankRow = true;
	$showAddButton = false;
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Social Traces Admin</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
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

function setPageValue() {
	document.getElementById('pageValue').value = document.getElementById('blockName').value;
}

function setDeleteVariableValue(variable) {
	document.getElementById('deleteVariable').value = variable;
}

</script>

</head>

<body class="twoColFixLtHdr">
<div id="container">
  <form action="admin.php" method="post" name="update">
  <div id="header">
    <h1>Social Traces Admin Panel</h1>
  <!-- end #header --></div>
<?php if ($valid){ ?>
  <div id="sidebar1">
    <h3>Experiments</h3>
    <ul>   
<?php printExperimentLinks($config); ?>
    </ul>
    <input type="submit" name="submit" value="Add Experiment" />
<?php printDbDataList(); ?>
<?php printCommentList(); ?>
  <!-- end #sidebar1 --></div>

  <div id="mainContent">
<?php printTitle($curPage); ?>
      <table>
<?php printForm($formData, $curPage, $showBlankRow, $limit, $offset); ?>
      </table>
<?php printAddButton($showAddButton); ?>
      <input type="hidden" name="page" id="pageValue" value="<?php echo $curPage; ?>" />
      <input type="hidden" name="deleteVariable" id="deleteVariable" value="" />
      <input type="hidden" name="previousBlockName" id="previousBlockName" value="<?php echo $curPage; ?>" />
<?php printUpdateButton($showUpdateButton); ?>
	<!-- end #mainContent --></div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<?php } else { ?>
	<div id="mainContent">
		<p>
			<label for="pw">Password:</label><input type="text" size="12" name="pw" id="pw" />
		</p>
	</div>
	
<?php } ?>
  <div id="footer">
    <p>A <a href="http://sm.rutgers.edu/">SMIL</a> production</p>
  <!-- end #footer --></div>
  
  </form>
<!-- end #container --></div>
</body>
</html>
