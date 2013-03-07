<?php
//lots of helper functions to handle storing and retrieving data to/from disk.
//REMEMBER YOUR FILE PERMISSIONS.
//on cactuar PHP is running under an account that is in the research-sm group
//so you will need to be sure any writing is done to a directory or file
//that has the group write permission bit set.

function getConfig() {
	return parse_ini_file("admin/settings.ini", true);
}

function storeDb($data, $table){
	require("credentials.php");
	$db = mysql_connect($database, $user, $password);
	mysql_select_db("social_traces", $db);
	mysql_set_charset('utf8', $db);
	$query = "INSERT INTO `" . $table . "` SET ";
	$inserts = array();
	foreach ($data as $key => $value) {
		array_push($inserts, "`" . $key . "` = '" . mysql_real_escape_string($value) . "'");
	}
	$query .= implode(", ", $inserts);
	//need the below at some point.
	//$query .= " ON DUPLICATE KEY UPDATE ...";
	mysql_query($query);
	mysql_close($db);
}

function getSqlData($table, $limit=50, $offset=0){
	require("credentials.php");
	$db = mysql_connect($database, $user, $password);
	mysql_select_db("social_traces", $db);
	mysql_set_charset('utf8', $db);
	$query = "SELECT * FROM " . $table . " LIMIT " . strval($limit) . " OFFSET " . strval($offset);
	$sqlResult = mysql_query($query);
	$arrResult = array();
	while ($row = mysql_fetch_assoc($sqlResult)){
		array_push($arrResult, $row);
	}
	mysql_close($db);
	return $arrResult;
}

function getJsonComments($count = 10){
	$json = getJsonData('admin/comments.json');
	return $json['comments'];
}

function getJsonData($filename){
	return json_decode(file_get_contents($filename), true);
}

function storeDataJson($data, $name) {
	$name = strtolower($name);
	$filename = strval(time());
	$path = "data/" . $name . "/" . $filename . ".json";
	$i = 1;
	while (file_exists($path)) {
		$path = "data/" . $name . "/" . $filename . "-" . strval($i) . ".json";
		$i++;
	}
	writeJson($data, $path);
}

function writeJson($data, $path) {
	$file = fopen($path, 'wb');
	$jsonData = json_encode($data);
	fwrite($file, $jsonData);
	fclose($file);
}

function addDataToCsv($data, $name) {
	$name = strtolower($name);
	$oldPath = "data/" . $name . "/" . $name . ".csv";
	$newPath = "data/" . $name . "/" . $name . ".tmp";
	$oldFile = fopen($oldPath, 'rb+');
	$newFile = fopen($newPath, 'wb+');
	$headers = fgetcsv($oldFile);
	$orderedData = array();
	foreach ($headers as $header) {
		if (array_key_exists($header, $data)) {
			$orderedData[] = $data[$header];
		} else {
			$orderedData[] = "";
		}
	}
	$newHeaders = array_diff(array_keys($data), $headers);
	foreach ($newHeaders as $newHeader) {
		if ($newHeader != "") {
			$orderedData[] = $data[$newHeader];
			$headers[] = $newHeader;
		}
	}
	fputcsv($newFile, $headers);
	fwrite($newFile, fread($oldFile, filesize($oldPath)));
	fputcsv($newFile, $orderedData);
	fclose($oldFile);
	fclose($newFile);
	rename($newPath, $oldPath);
	
}

function getBlocks($array) {
	return array_keys($array);
}

function getVariables($array){
	$temp = array();
	foreach ($array as $key => $value) {
		if (substr($key, 0, 12) == 'variableKey-' && $value) {
			$temp[$value] = $array['variableValue-' . substr($key, 12)];
		}
	}
	return $temp;
}

function getVariableValues($array){
	$temp = array();
	foreach($array as $key => $value){
		if (substr($key, 0, 14) == 'variableValue-' && $value){
			array_push($temp, $value); 
		}
	}
	return $temp;
}

function updateIniArrayFromSingleRequestArray(&$ini, $update) {
	if (array_key_exists($update['previousBlockName'], $ini)) {
		unset($ini[$update['previousBlockName']]);
	}
	$ini[$update['blockName']] = getVariables($update);
}

function removeVariableFromIniArrayBlock($var, $block, &$ini) {
	if (array_key_exists($var, $ini[$block])) {
		unset($ini[$block][$var]);
	}
}

function removeBlockfromIniArray($block, &$ini) {
	if (array_key_exists($block, $ini)) {
		unset($ini[$block]);
	}
}

function writeIni($data, $file) {
    $res = array();
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $res[] = "[" . $key . "]";
            foreach ($val as $skey => $sval) {
				$res[] = $skey . " = " . (is_numeric($sval) ? $sval : '"' . $sval . '"');
			}
		}
        else {
			$res[] = "$key = ".(is_numeric($val) ? $val : '"' . $val . '"');
		}
    }
    file_put_contents($file, implode("\r\n", $res));
}

function validateId($id, $table='survey'){
	require("credentials.php");
	$db = mysql_connect($database, $user, $password);
	mysql_select_db("social_traces", $db);
	mysql_set_charset('utf8', $db);
	$query = "SELECT `user_id` FROM `" . $table . "` WHERE `user_id` = " . $id . ";";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	mysql_close($db);
	$ret = ($row) ? $row['user_id'] : false;
	return $ret;
}

function getYoutubeComments($videoId, $stop = -1) {
	$count = 0;
	$jsonRaw = file_get_contents("http://gdata.youtube.com/feeds/api/videos/" . $videoId . "/comments?v=2&alt=json");
	$jsonData = json_decode($jsonRaw, true); //'true' forces json_decode to return an associative array
	if ($stop < 1) { //note: each api call will only get min(openSearch$totalResults, openSearch$itemsPerPage)
		$total = intval($jsonData['feed']['openSearch$totalResults']['$t']);
		$resultsPerPage = intval($jsonData['feed']['openSearch$itemsPerPage']['$t']);
		$stop = min(array($total, $resultsPerPage));
	}
	//print json_format($jsonRaw);
	$comments = array();
	foreach($jsonData['feed']['entry'] as $comment){
		foreach ($comment as $commentKey => $commentValue){
			$isReply = false;
			if ($commentKey == "link"){
				foreach ($commentValue as $linkValue){
					foreach($linkValue as $varKey => $varValue){
						if (strpos($varValue, "#in-reply-to") !== false) {
							$isReply = true;
						}
					}
				}
			}
		}
		if (!$isReply && $count < $stop){
			array_push($comments, $comment['content']['$t']);
			$count++;
		}
	}
	return $comments;
}

/** 
 * Based on an example by ramdac at ramdac dot org 
 * Returns a multi-dimensional array from a CSV file optionally using the 
 * first row as a header to create the underlying data as associative arrays. 
 * @param string $file Filepath including filename 
 * @param bool $head Use first row as header. 
 * @param string $delim Specify a delimiter other than a comma. 
 * @param int $len Line length to be passed to fgetcsv 
 * @return array or false on failure to retrieve any rows. 
 */ 

function importCsvAssoc($file,$head=false,$delim=",",$len=1000) { 
    $return = array(); 
	//ini_set('auto_detect_line_endings', true); //Funda prepared the data file on a Mac, which uses \r as EOL.
    $handle = fopen($file, "r"); 
    if ($head) { 
        $header = fgetcsv($handle, $len, $delim);
    } 
    while (($data = fgetcsv($handle,$len,$delim)) !== FALSE) { 
        if ($head && isset($header)) { 
            foreach ($header as $key=>$heading) { 
                $row[$heading]=(isset($data[$key])) ? $data[$key] : ''; 
            } 
            $return[]=$row; 
        } else { 
            $return[]=$data; 
        } 
    } 
    fclose($handle); 
	//ini_set('auto_detect_line_endings', false);
    return $return; 
} 
/***
 * takes an array of assoc arrays, returns an array of all values of a single column,
 * preserving indices but not keys
 */
function searchAssocArrayColumnValues($needle, $haystack, $key){
	$return = array();
	for ($i = 0; $i < count($haystack); $i++){
		if (in_array($haystack[$i][$key], $needle)){
			$return[$i] = $haystack[$i];	
		}
	}
	return $return;
}

/*
 * Preserves keys
 */
function searchProfiles($profiles,$gender,$ageRange, $availableImages){
	$return = array();
	for($i = 0; $i < count($profiles); $i++){
		if (array_key_exists($i, $profiles) && 
		$profiles[$i]['gender'] == $gender && 
		in_array($profiles[$i]['age'], $ageRange) &&
		in_array($profiles[$i]['image_url'], $availableImages)){
			$return[$i] = $profiles[$i];
		}
	}
	return $return;
}
/*
 * Genders are only indicated by the url string for the image.
 * Adds a column for that.
 */
function addGenders(&$profiles){
	foreach ($profiles as &$profile){
		$url = $profile['image_url'];
		$regex = "/(fe)?male/";
		$matches = array();
		if (preg_match($regex, $url, $matches)){
			$profile['gender'] = $matches[0];
		}
	}
	unset($profile);
}

function getRandomCodeWords($filename, $num = 1){
	$file = fopen($filename, 'rb');
	$words = array();
	$ret = array();
	while (($line = fgets($file)) !== false){
		array_push($words, trim($line));
	}
	fclose($file);
	for ($x = 0; $x < $num; $x++){
		array_push($ret, $words[array_rand($words)]);
	}
	return $ret;
}

function json_format($json) 
{ 
    $tab = "  "; 
    $new_json = ""; 
    $indent_level = 0; 
    $in_string = false; 

    $json_obj = json_decode($json); 

    if($json_obj === false) 
        return false; 

    $json = json_encode($json_obj); 
    $len = strlen($json); 

    for($c = 0; $c < $len; $c++) 
    { 
        $char = $json[$c]; 
        switch($char) 
        { 
            case '{': 
            case '[': 
                if(!$in_string) 
                { 
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
                    $indent_level++; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '}': 
            case ']': 
                if(!$in_string) 
                { 
                    $indent_level--; 
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ',': 
                if(!$in_string) 
                { 
                    $new_json .= ",\n" . str_repeat($tab, $indent_level); 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ':': 
                if(!$in_string) 
                { 
                    $new_json .= ": "; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '"': 
                if($c > 0 && $json[$c-1] != '\\') 
                { 
                    $in_string = !$in_string; 
                } 
            default: 
                $new_json .= $char; 
                break;                    
        } 
    } 

    return $new_json; 
} 

?>