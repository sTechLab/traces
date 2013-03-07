<?php

function printExperimentLinks($arrConfig) {
	foreach($arrConfig as $strBlock => $arrVariables) {
		echo "      <li><a href=\"admin.php?page=" . urlencode($strBlock) . "\">" . $strBlock . "</a></li>\n";
	}
}

function printTitle($strIn) {
	if (in_array(strtolower($strIn), array("survey", "experiment", "interaction", "comments", "payment"))) {
		print "<h3>" . ucfirst($strIn) . "</h3>\n";
		if ($strIn == "comments"){
			print "<input type=\"hidden\" name=\"blockName\" value=\"comments\" />";
		}
	} else {
		echo "<input onchange=\"setPageValue();\" id=\"blockName\" class=\"title\" name=\"blockName\" type=\"text\" value=\"" . $strIn . "\" />\n";
		if ($strIn) {
			echo '<input type="submit" name="submit" value="Delete Experiment" />';
		}
	}
}

function printForm($arrIn, $page="", $blank=false, $limit=50, $offset=0) {
	if (in_array(strtolower($page), array("survey", "experiment", "interaction", "payment"))) {
		require_once("../data_handler.php");
		$data = getSqlData($page, $limit, $offset);
		print "<tr>\n";
		foreach($data[0] as $key => $value) {
			print "<th class=\"data\">" . urldecode($key) . "</th>\n";
		}
		print "</tr>\n";
		foreach ($data as $row){
			echo "  <tr>\n";
			foreach($row as $key => $value){
				echo "    <td class=\"data\">". $value . "</td>\n";	
			}
		}
		echo "  </tr>\n";
		print "<tr><td><a href=\"admin.php?page=" . $page . "&limit=" . $limit . "&offset=" . strval($offset - 50) . "\">\n";
		print "&larr;</a>\n";
		print "<a href=\"admin.php?page=" . $page . "&limit=" . $limit . "&offset=" . strval($offset + 50) . "\">\n";
		print "&rarr;</a>\n";
		print "</td></tr>\n";
	} else {
		$intCount = 1;
		if (array_key_exists($page, $arrIn)) {
			foreach ($arrIn[$page] as $strKey => $strValue) {
				$delete = ($page == "comments") ? "variableValue-" . $intCount : "variableKey-" . $intCount;
				echo "  <tr>\n";
				if ($page != "comments") {
					echo "    <td><input class=\"variableKey\" name=\"variableKey-" . strval($intCount) . "\" type=\"text\" value=\"" . $strKey . "\" /></td>\n";
					echo "    <td>&nbsp;=&nbsp;</td>\n";
				}
				echo "    <td><input class=\"variableValue\" name=\"variableValue-" . strval($intCount) . "\" type=\"text\" value=\"" . $strValue . "\" /></td>\n";
				echo "    <td><input type=\"submit\" name=\"submit\" value=\"Delete\" onClick=\"setDeleteVariableValue('" . $delete . "')\" />\n";
				echo "  </tr>\n";
				$intCount++;
			}
		}
		if ($blank) {
			echo "  <tr>\n";
			if ($page != "comments") {
				echo "    <td><input class=\"variableKey\" name=\"variableKey-" . strval($intCount) . "\" type=\"text\" value=\"\" /></td>\n";
				echo "    <td>&nbsp;=&nbsp;</td>\n";
			}
			echo "    <td><input class=\"variableValue\" name=\"variableValue-" . strval($intCount) . "\" type=\"text\" value=\"\" /></td>\n";
			echo "  </tr>\n";
		}
	}
}

function printAddButton($boolAddButton) {
	if ($boolAddButton) {
    	echo "<input type=\"submit\" name=\"submit\" value=\"Add\" /><br />\n";
	}
}
function printUpdateButton($boolUpdateButton) {
	if ($boolUpdateButton) {
    	echo "<input type=\"submit\" name=\"submit\" class=\"submit\" value=\"Update\" />\n";
	}
}
function printJsonDataList($curPage){
	if ($curPage) {
	    print "<h3>Data</h3>\n";
	    print "<ul>\n";
		foreach (glob("../data/" . strtolower($curPage) . "/*") as $path) {
			$filename = substr(strrchr($path, "/"), 1);
			print "<li><a href=\"". $path . "\">" . $filename . "</a></li>\n";
		}
	print "</ul>\n";
	}
}
function printDbDataList(){
	print "<h3>Data</h3>\n";
	print "<ul>\n";
	print "<li><a href=\"admin.php?page=survey\">Surveys</a></li>\n";
	print "<li><a href=\"admin.php?page=experiment\">Experiments</a></li>\n";
	print "<li><a href=\"admin.php?page=interaction\">Interactions</a></li>\n";
	print "<li><a href=\"admin.php?page=payment\">Payments</a></li>\n";
	print "</ul>\n";
}
function printCommentList(){
	print "<h3>Comments</h3>\n";
	print "<ul>\n";
	print "<li><a href=\"admin.php?page=comments\">Comments</a></li>\n";
	print "</ul>\n";
}
?>