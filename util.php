<?php

/*
Cleans GET and POST inputs.
Input an array with strings in the keys a values.
Returns an array with clean key and value strings.
*/
function clean($elem) 
{ 
    if(!is_array($elem)) 
        $elem = htmlentities(trim($elem),ENT_QUOTES,"UTF-8"); 
    else 
        foreach ($elem as $key => $value) 
            $elem[clean($key)] = clean($value); 
    return $elem; 
} 

function getSurveyQuestions($config){
	$surveyKeys = array();
	foreach ($config as $key => $valye) {
		if (!strncasecmp("Likert:", $key, 7)) {
			$surveyKeys[] = trim(substr($key, 7));
		}
	}
	return $surveyKeys;
}

function isHumanBoolean($str) {
	$test = strtolower(substr(trim($str), 0, 1));
	return ($test == "1" or $test == "y" or $test == "t") ? True : False;
}

function end_session() {
	// Unset all of the session variables.
	$_SESSION = array();
	
	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	
	// Finally, destroy the session.
	session_destroy();
}

function glob_recursive($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	}
	return $files;
}

?>