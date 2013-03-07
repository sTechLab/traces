<?php
//Page for the AJAX requests in the jQuery code to ping against. Idea is to store whatever they send in a row in the DB.
//*ideally* this would contain a key that indexes a row on another table or participants, containing the variables
//in their run of the experiment.
require_once("data_handler.php");
require_once("util.php");
session_start();

$data = clean($_POST);
$data['date'] = date('r');
storeDb($data, 'interaction');
//addDataToCsv($_CLEAN, "youtube");

end_session();
?>