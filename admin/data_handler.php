<?php

function clean($elem) 
{ 
    if(!is_array($elem)) 
        $elem = htmlentities(trim($elem),ENT_QUOTES,"UTF-8"); 
    else 
        foreach ($elem as $key => $value) 
            $elem[clean($key)] = clean($value); 
    return $elem; 
} 

function getConfig() {
	return parse_ini_file("settings.ini", true);
}

function getBlocks($arrIn) {
	return array_keys($arrIn);
}

function printExperimentLinks($arrConfig) {
	foreach($arrConfig as $strBlock => $arrVariables) {
		echo "      <li><a href=\"admin.php?page=" . urlencode($strBlock) . "\">" . $strBlock . "</a></li>\n";
	}
}

function printTitle($strIn) {
	echo "<input onchange=\"setPageValue();\" id=\"blockName\" class=\"title\" name=\"blockName\" type=\"text\" value=\"" . $strIn . "\" />\n";
	if ($strIn) {
		echo '<input type="submit" name="submit" value="Delete Experiment" />';
	}
}

function printForm($arrIn, $strIndex, $boolBlank) {
	$intCount = 1;
	if (array_key_exists($strIndex, $arrIn)) {
		foreach ($arrIn[$strIndex] as $strKey => $strValue) {
			echo "  <tr>\n";
			echo "    <td><input class=\"variableKey\" name=\"variableKey-" . strval($intCount) . "\" type=\"text\" value=\"" . $strKey . "\" /></td>\n";
			echo "    <td>&nbsp;=&nbsp;</td>\n";
			echo "    <td><input class=\"variableValue\" name=\"variableValue-" . strval($intCount) . "\" type=\"text\" value=\"" . $strValue . "\" /></td>\n";
			echo "    <td><input type=\"submit\" name=\"submit\" value=\"Delete Variable\" onClick=\"setDeleteVariableValue('variableKey-" . $intCount . "')\" />\n";
			echo "  </tr>\n";
			$intCount++;
		}
	}
	if ($boolBlank) {
		echo "  <tr>\n";
		echo "    <td><input class=\"variableKey\" name=\"variableKey-" . strval($intCount) . "\" type=\"text\" value=\"\" /></td>\n";
		echo "    <td>&nbsp;=&nbsp;</td>\n";
		echo "    <td><input class=\"variableValue\" name=\"variableValue-" . strval($intCount) . "\" type=\"text\" value=\"\" /></td>\n";
		echo "  </tr>\n";
	}
}

function printAddButton($boolAddButton) {
	if ($boolAddButton) {
    	echo "<input type=\"submit\" name=\"submit\" value=\"Add Variable\" /><br />\n";
	}
}

function updateIniArrayFromSingleRequestArray(&$arrIni, $arrUpdate) {
	if (array_key_exists($arrUpdate['previousBlockName'], $arrIni)) {
		unset($arrIni[$arrUpdate['previousBlockName']]);
	}
	$arrTemp = array();
	foreach ($arrUpdate as $strKey => $strValue) {
		if (substr($strKey, 0, 12) == 'variableKey-' && $strValue) {
			$arrTemp[$strValue] = $arrUpdate['variableValue-' . substr($strKey, 12)];
		}
	}
	$arrIni[$arrUpdate['blockName']] = $arrTemp;
	
	/*
	if (!array_key_exists($arrUpdate['blockName'], $arrIni)) {
		$arrIni[$arrUpdate['blockName']] = array();
	}
	foreach ($arrUpdate as $strKey => $strValue) {
		if (substr($strKey, 0, 12) == 'variableKey-' && $strValue) {
			$arrIni[$arrUpdate['blockName']][$strValue] = $arrUpdate['variableValue-' . substr($strKey, 12)];
		}
	}
	*/
}

function removeVariableFromIniArrayBlock($strVariableName, $strBlockName, &$arrIni) {
	if (array_key_exists($strVariableName, $arrIni[$strBlockName])) {
		unset($arrIni[$strBlockName][$strVariableName]);
	}
}

function removeBlockfromIniArray($strBlockName, &$arrIni) {
	if (array_key_exists($strBlockName, $arrIni)) {
		unset($arrIni[$strBlockName]);
	}
}

function writeIni($arrIn, $fileOut) {
    $res = array();
    foreach($arrIn as $key => $val) {
        if(is_array($val)) {
            $res[] = "[" . $key . "]";
            foreach($val as $skey => $sval) {
				$res[] = $skey . " = " . (is_numeric($sval) ? $sval : '"' . $sval . '"');
			}
		}
        else {
			$res[] = "$key = ".(is_numeric($val) ? $val : '"' . $val . '"');
		}
    }
    file_put_contents($fileOut, implode("\r\n", $res));
}



?>