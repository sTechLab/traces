<?php

function printInstructions($hasInstructions, $instructions = "") {
	if ($hasInstructions) {
		print "<div id=\"instructions\" class=\"popup\">\n";
		print "<div id=\"close_instructions\" style=\"width:100%; text-align:right;\"><a href=\"#\"class=\"close\"/>X</a></div>\n";
		print "<p id=\"instruction_text\">" . $instructions . "</p>\n";
		print "</div>\n";
	}
}

function printSummary($withSummary, $malePercent = 0, $femalePercent = 0, $meanAge = 0) {
	if ($withSummary) {
		print "<div id=\"no_pics_summary\">\n";
		print "<h3 id=\"audience_label\">Audience:</h3>";
        print "<div id=\"male_female\" class=\"fltlft\">\n";
        print "<div class=\"bar fltlft\" style=\"height: " . strval($malePercent / 2) . "px; margin-top: " . strval($femalePercent / 2) . "px;\">";
        print "<div class=\"bar_label\">" . $malePercent . "%</div></div>\n";
        print "<div class=\"bar fltlft\" style=\"height: " . strval($femalePercent / 2) . "px; margin-top: " . strval($malePercent / 2) . "px;\">";
		print "<div class=\"bar_label\">" . $femalePercent . "%</div></div>\n";
        print "</div>\n";
        print "<div id=\"average_age\" class=\"fltlft\">\n";
        print "<div class=\"pad_right\">" . $meanAge . "</div>\n";
		print "</div>\n";
		print "</div>\n";
	} else {
		print "<br class=\"clearfloat\">";
	}
}

function printComments($numPics, $commenters = array(), $comments = array(), $withPics = false, $withCommentLike = false) {
	$numCommenters = count($commenters);
	$numComments = count($comments);
	for ($i = 0; $i < $numPics; $i++) {
		if ($i < $numCommenters && $i < $numComments && 
		array_key_exists($i, $commenters) && !empty($commenters[$i]) &&
		array_key_exists($i, $comments) && !empty($comments[$i])){
		    print "<div class=\"comment_area\">\n";
			if ($withPics) {
				//print "<img src=\"img/faces/" . substr($commenters[$i]['gender'], 0, 1) . substr(strval($commenters[$i]['age']), 0, 1) . "0.jpg\" width=\"50\" height=\"50\" class=\"fltlft\" />\n";
				print "\t<img src=\"img/faces/" . $commenters[$i]['image_url'] . "\" width=\"50\" height=\"50\" class=\"fltlft\" />\n";
			} else {
				print "\t<img src=\"img/youtube/blank.jpg\" width=\"50\" height=\"50\" class=\"fltlft\" />\n";
			}
			print "<div class=\"comment_text\">\n";
			print "<h4>" . $commenters[$i]['user_name'] . " (" . $commenters[$i]['first_name'] . " " . $commenters[$i]['last_name'] . ")</h4>\n";
			print "<p>" . $comments[$i] . "</p>\n";
			print "<div>\n";
		    print "<img class=\"button reply\" src=\"img/youtube/comment-reply.jpg\" onclick=\"toggleComment(this, " . $i . ");\" />\n";
		    if ($withCommentLike) {
		    	print "<img class=\"button like\" name=\"buttonCommentLike-" . $i . "\" id=\"buttonCommentLike-" .  $i ."\" src=\"img/youtube/comment-like.jpg\" onclick=\"toggleLike(this, 'comment', " . $i . ");\" />\n";
		    	print "<img class=\"button like\" name=\"buttonCommentDislike-" . $i . "\" id=\"buttonCommentDislike-" . $i . "\" src=\"img/youtube/comment-dislike.jpg\" onclick=\"toggleDislike(this, 'comment', " . $i . ");\" />\n";
			}
		    print "</div>\n";
			print "<div class=\"comment_commit\" style=\"display: none;\"></div>";
		    print "<textarea class=\"comment\" style=\"display: none;\" rows=\"3\" cols=\"50\" name=\"commentComment-" . $i . "\" id=\"commentComment-" . $i . "\">Reply to this comment...</textarea>\n";
		    print "<img class=\"button post\" style=\"display: none;\" name=\"post-" . $i . "\" id=\"post-" . $i . "\" src=\"img/youtube/post.gif\" width=\"46\" height=\"28\" />\n";
		    print "<input type=\"hidden\" class=\"likeValue\" name=\"commentLike-" . $i . "\" id=\"commentLike-" . $i . "\" value=\"0\" />\n";
		    print "<input type=\"hidden\" name=\"commentAge-" . $i . "\" id=\"commentAge-" . $i . "\" value=\"" . $commenters[$i]['age'] . "\" />\n";
		    print "<input type=\"hidden\" name=\"commentGender-" . $i . "\" id=\"commentGender-" . $i . "\" value=\"" . substr($commenters[$i]['gender'], 0, 1) . "\" />\n";
		    print "<input type=\"hidden\" name=\"comment-" . $i . "\" id=\"comment-" . $i . "\" value=\"\" />\n";
			print "</div>\n";
			print "</div>\n";
		}
	}
}

function printUploader($uploader = array()){
	if ($uploader){
        if (array_key_exists('type', $uploader) && $uploader['type'] == "anon") {
			print "<img src=\"img/youtube/blank.jpg\" class=\"fltlft\" />";
			print "<h2 class=\"fltlft\">" . $uploader['name'] . "</h2>";
		} else if (array_key_exists('type', $uploader) && $uploader['type'] == "none") {
			print "";
		} else {
        	print "<img src=\"" . $uploader['src'] . "\" class=\"fltlft\" />";
			print "<h2 class=\"fltlft\">" . $uploader['name'] . "</h2>";
		}
	}
}
?>