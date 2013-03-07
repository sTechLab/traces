<?php
//Some helper functions that do rendering on the survey page

function userValidationErrors($_CLEAN){
	$validationErrors = array();
	$age = intval($_CLEAN['age']);
	if ($age < 18){
		$validationErrors[] = "Persons under 18 cannot participate in this study.";
	}
	return $validationErrors;
}

function validateForm($_CLEAN, $surveyKeys){
	$errors = array();
	$age = intval($_CLEAN['age']);
	$gender = $_CLEAN['gender'];
	if ($age < 1 || $age > 120) {
		$errors['age'] = "Please enter a valid age";
	}
	if ($gender === "") {
		$errors['gender'] = "Please select a gender";
	}
	foreach ($surveyKeys as $key) {
		if (!array_key_exists(urlencode($key), $_CLEAN) || $_CLEAN[urlencode($key)] === ""){
			$errors[urlencode($key)] = "Please make a selection";
		}
	}
	return $errors;
}

function drawLikert($items, $scale, $values = array(), $errors = array()) {
	$headers = array("Disagree", "Disagree strongly", "Disagree moderately", "Disagree a little", "Neither agree nor disagree", "Agree a little", "Agree moderately", "Agree strongly", "Agree");
	
	print "<table class=\"likert\">\n<tr>\n<th class=\"top_left\">I see myself as:</th>\n";
	
	if ($scale == 3) {
		$scaleHeaders = array($headers[0], $headers[4], $headers[8]);
		drawLikertHeaders($scaleHeaders);
	} elseif ($scale == 5) {
		$scaleHeaders = array($headers[1], $headers[2], $headers[4], $headers[6], $headers[7]);
		drawLikertHeaders($scaleHeaders);
	} elseif ($scale == 7) {
		$scaleHeaders = array($headers[1], $headers[2], $headers[3], $headers[4], $headers[5], $headers[6], $headers[7]);
		drawLikertHeaders($scaleHeaders);
	}
	
	print "</tr>\n";

	$row = 0;
	foreach ($items as $item) {
		$urlItem = urlencode($item);
		$row++;
		$style = ($row % 2) ? "row_shaded"  : "";
		print "<tr class=\"response_row " . $style . "\">\n";
		print "<td class=\"item_label\">" . $item . "</td>\n";
		for ($i = 1; $i <= $scale; $i++) {
			print "<td class=\"response\"><input type=\"radio\" name=\"". $urlItem . "\" value=\"" . $i . "\" "; 
			if (array_key_exists($urlItem, $values) && intval($values[$urlItem]) == $i) {
				print "checked=\"checked\" ";
			}
			print "/></td>\n";
		}
		print "</tr>\n";
		if (array_key_exists($urlItem, $errors)) {
            print "<tr class=\"" . $style . "\"><td colspan=\"" . strval($scale + 1) . "\"><span class=\"error\">" . $errors[$urlItem] . "</span></td></tr>";
        }
	}
	print "</table>\n";
}

function drawLikertHeaders($headers) {
	foreach ($headers as $header) {
		print "<th class=\"response_header\">" . $header . "</th>\n";
	}
}

function setConditions($debug, $percentPicsCondition, $percentSameAgeCondition, $percentSameGenderCondition, $percentSummaryCondition){
	if ($debug){
		$output = <<<EOD
	    <h3>[Items below for testing purposes; not to appear on deployed survey]</h3>
        <p>
        	Current condition assignment percentages are:
            <ul>
            	<li>With pictures condition: <?php print $percentPicsCondition; ?>%</li>
            	<li>With summary statistics condition: <?php print $percentSummaryCondition; ?>%</li>
                <li>Same gender condition: <?php print $percentSameGenderCondition; ?>%</li>
                <li>Same age condition: <?php print $percentSameAgeCondition; ?>%</li>
            </ul>
        	<input type="button" value="Randomize Conditions" name="randomize" id="randomize" onClick="randomizeConditions();" />
        </p>
        <p>
            <label for="pic_condition">Select the picture condition you'd like to be assigned to:</label>
            <select name="pic_condition" id="pic_condition">
            	<option value=""></option>
                <option value="p">With profile pictures</option>
                <option value="n">Without profile pictures</option>
            </select>
    	</p>
    	<p>
            <label for="summary_condition">Select the summary condition you'd like to be assigned to:</label>
            <select name="summary_condition" id="summary_condition">
            	<option value=""></option>
                <option value="s">With summary statistics</option>
                <option value="n">Without summary statistics</option>
            </select>
    	</p>
        <p>
            <label for="gender_condition">Select the gender condition you'd like to be assigned to:</label>
            <select name="gender_condition" id="gender_condition">
            	<option value=""></option>
                <option value="s">Same gender</option>
                <option value="d">Different gender</option>
            </select>
    	</p>
        <p>
            <label for="age_condition">Select the age condition you'd like to be assigned to:</label>
            <select name="age_condition" id="age_condition">
            	<option value=""></option>
                <option value="s">Same age</option>
                <option value="d">Different age</option>
            </select>
    	</p>
EOD;
	} else {
		$rand = rand(1, 100);
		$picCondition = ($rand <= $percentPicsCondition) ? "p" : "n";
		$rand = rand(1, 100);
		$summaryCondition = ($rand <= $percentSummaryCondition) ? "s" : "n";
		$rand = rand(1, 100);
		$ageCondition = ($rand <= $percentSameAgeCondition) ? "s" : "d";
		$rand = rand(1, 100);
		$genderCondition = ($rand <= $percentSameGenderCondition) ? "s" : "d";
		
		$output = "";
		$output .= "<input type=\"hidden\" name=\"pic_condition\" id=\"pic_condition\" value=\"" . $picCondition . "\" />\n";
		$output .= "<input type=\"hidden\" name=\"summary_condition\" id=\"summary_condition\" value=\"" . $summaryCondition . "\" />\n";
		$output .= "<input type=\"hidden\" name=\"age_condition\" id=\"age_condition\" value=\"" . $ageCondition . "\" />\n";
		$output .= "<input type=\"hidden\" name=\"gender_condition\" id=\"gender_condition\" value=\"" . $genderCondition . "\" />\n";
	}
	print $output;
}

function printRandomizer($debug, $percentPicsCondition, $percentSameAgeCondition, $percentSameGenderCondition, $percentSummaryCondition){
	$output = "";
	if ($debug){
		$output .= <<<EOD
function randomizeConditions() {
	random100 = Math.floor(Math.random() * 100);
	if (random100 < $percentPicsCondition) {
		document.getElementById('pic_condition').value = "p";
		document.getElementById('pic_condition').selectedIndex = 1;
	} else {
		document.getElementById('pic_condition').value = "n";
		document.getElementById('pic_condition').selectedIndex = 2;
	}
	random100 = Math.floor(Math.random() * 100);
	if (random100 < $percentSameGenderCondition) {
		document.getElementById('gender_condition').value = "s";
		document.getElementById('gender_condition').selectedIndex = 1;
	} else {
		document.getElementById('gender_condition').value = "d";
		document.getElementById('gender_condition').selectedIndex = 2;
	}
	random100 = Math.floor(Math.random() * 100);
	if (random100 < $percentSameAgeCondition) {
		document.getElementById('age_condition').value = "s";
		document.getElementById('age_condition').selectedIndex = 1;
	} else {
		document.getElementById('age_condition').value = "d";
		document.getElementById('age_condition').selectedIndex = 2;
	}
	random100 = Math.floor(Math.random() * 100);
	if (random100 < $percentSummaryCondition) {
		document.getElementById('summary_condition').value = "s";
		document.getElementById('summary_condition').selectedIndex = 1;
	} else {
		document.getElementById('summary_condition').value = "n";
		document.getElementById('summary_condition').selectedIndex = 2;
	}
}
EOD;
	}
	print $output;
}

?>