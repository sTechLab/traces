<?php
//This page actually renders the html for the experiment.
//NOTE: pretty useless unless called from experiment.php!

require_once("data_handler.php");
require_once("youtube_display.php");

$videoId = $experimentPage;
$videoTitle = "&hearts;&hearts; Happiest Penguin Ever &hearts;&hearts;";

$callingPage = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$callingDir = substr($thisPage, 0, strrpos($thisPage, '/'));
$page = $thisDir . "/youtube.php";
$experiment = "YouTube";
$uploader = array('type' => "none", 'name' => "ckb987"); //'type can be "anon" or "none"
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>YouTube Video</title>
<link href="youtube.css" rel="stylesheet" type="text/css" />
<!--[if IE 5]>
<style type="text/css"> 
/* place css box model fixes for IE 5* in this conditional comment */
.thrColFixHdr #sidebar1 { width: 180px; }
.thrColFixHdr #sidebar2 { width: 190px; }
</style>
<![endif]--><!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColFixHdr #sidebar2, .thrColFixHdr #sidebar1 { padding-top: 30px; }
.thrColFixHdr #mainContent { zoom: 1; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
<script src="js/jquery-1.9.0.min.js" type="text/javascript"></script>

<script type="text/javascript">
/**
 * Define and set trim if it's not supported
 */
if (typeof String.prototype.trim != 'function') { // detect native implementation
  String.prototype.trim = function () {
    return this.replace(/^\s+/, '').replace(/\s+$/, '');
  };
}

/***
 * Youtube listeners
 */
function onYouTubePlayerReady(playerId) {
  ytplayer = document.getElementById("youtubeplayer");
  ytplayer.addEventListener("onStateChange", "onytplayerStateChange");
}

function onytplayerStateChange(newState) {
	if (newState == 0 || newState == 1 || newState == 2){ //0 == "ended", 1 == "playing", 2 == "paused"
	   	$.ajax({
			data: {
				type: "player",
				key: newState,
				value: ytplayer.getCurrentTime()
			}
		});
	}
}

/*******
* Some utility functions to handle image url changes, etc.
*
*/
var now = new Date();
var dialogStatus = "closed";

function getExtension(src) {
	return src.slice(-3);
}

function isOn(src) {
	return src.slice(-7, -3) == "-on.";
}

function isOver(src){
	return src.slice(-9, -3) == "-over."
}

function getThisToggleSrc(src) {
	//fucntion to change the src of the button we ARE clicking.
	var extension = getExtension(src);
	if (isOn(src)) {
		src = src.slice(0, -7) + "-over.";
	} else if (isOver(src)) {
		src = src.slice(0, -9) + "-on.";
	} else {
		src = src.slice(0, -4) + "-on.";
	}
	return src + extension;
}

function getTurnOffSrc(src) {
	var extension = getExtension(src);
	if (isOn(src)){
		src = src.slice(0, -7) + "." + extension;
	} else if (isOver(src)){
		src = src.slice(0, -9) + "." + extension;
	}
	return src;
}

function getToggleLikeValue(val) {
	val = parseInt(val);
	if (val === 0) {
		val = 1;
	} else {
		val = -val;
	}
	return val;
}

function getRolloverSrc(src){
	var extension = getExtension(src);
	if (!isOn(src)){
		src = src.slice(0, -4) + "-over." + extension;
	}
	return src
}

function getRolloutSrc(src){
	var extension = getExtension(src);
	if(!isOn(src)){
		src = src.slice(0, -9) + "." + extension;
	}
	return src
}

/*******
* Functions to bind to jQuery actions
*
*/

$.ajaxSetup({
	type: "POST",
	url: "ajax_handler.php",
	data: { user_id: "<?php print $userId; ?>",
		experiment_name: "<?php print $experiment; ?>", 
		url: "<?php print $page; ?>" }
});

toggleLike = function () {
	//Toggle img url. Add or remove "-on" to the end of the file
	$(this).attr( "src", getThisToggleSrc( $(this).attr("src") ) );
	//Toggle img url of sibling button if it is already on
	$(this).siblings(".like").attr( "src", getTurnOffSrc( $(this).siblings(".like").attr("src") ) );
	//Set hidden form attribute
	if ( $(this).attr("name").toLowerCase().indexOf("dislike") !== -1 ) {
		$(this).parent().siblings(".likeValue").val(-1);
	} else {
		$(this).parent().siblings(".likeValue").val(1);
	}
		//getToggleLikeValue( $(this).parent().siblings(".likeValue").val() ) );
	//Send AJAX request
	$.ajax({
		data: {
			type: "like",
			key: $(this).parent().siblings(".likeValue").attr("name"),
			value: $(this).parent().siblings(".likeValue").val()
		}
	});
}

toggleReply = function () {
	//Toggle img url. Add or remove "-on" to the end of the file
	$(this).attr( "src", getThisToggleSrc( $(this).attr("src")));
	//Show reply box
	$(this).parent().siblings(".comment").slideToggle();
	$(this).parent().siblings(".post").slideToggle();
}

clearDefaultComment = function () {
	if ($(this).val() === "Respond to this video..." || $(this).val() === "Reply to this comment...") {
		$(this).val("");
	}
}

restoreDefaultComment = function () {
	if ($(this).val().trim() === "") {
		if ($(this).attr("name") === "videoComment") {
			$(this).val("Respond to this video...");
		} else {
			$(this).val("Reply to this comment...");
		}
	}
}

commitComment = function () {
	//If comment box is empty restore default text
	var comment = $(this).siblings(".comment").val().trim();
	if (comment !== "" && comment != "Respond to this video..." && comment != "Reply to this comment...") {
	//Send AJAX request
		$.ajax({
			data: {
				type: "comment",
				key: $(this).siblings(".comment").attr("name"),
				value: $(this).siblings(".comment").val()
			}
		});
		//Hide!
		$(this).siblings(".comment_commit").html(comment);
		$(this).siblings(".comment_commit").slideToggle();
		$(this).siblings(".comment").slideToggle();
		$(this).slideToggle();
	}
}

rollover = function(){
	$(this).attr("src", getRolloverSrc($(this).attr("src")));
}

rollout = function(){
	$(this).attr("src", getRolloutSrc($(this).attr("src")));
}

showDialog = function (id) {
	var maskHeight = $(document).height();
	var maskWidth = $(window).width();
	$('#mask').css({'width':maskWidth,'height':maskHeight});
	$('#mask').fadeIn(400);
	$('#mask').fadeTo("slow",0.8);
	var winH = $(window).height();
	var winW = $(window).width();
	$(id).css('top',  winH/2-$(id).height()/2);
	$(id).css('left', winW/2-$(id).width()/2);
	$(id).fadeIn(1000);
	dialogStatus = "open";
}

closeDialog = function (){
	$('#mask').fadeOut(400);
	//$('.popup').fadeOut(400);
	
	//First change it to absolute positioning and set those values in CSS.
	//Because animate() cannot change postion:absolute. So we must set this manually.
	$('.popup').css({
		'position':'absolute',
		//'width':$('#instructions').width(),
		//'height':$('#instructions').height(),
		//'left':$('#instructions').position().left,
		//'top':$('#instructions').position().top,
	})
	//Now that it is fixed in one place, we can animate it to the static place on the page we want it.
	$('.popup').animate({
		"top":"0",
		"left":"0",
		"width":"100%",
		"padding-bottom":"0",
		//height:"80px",
	}, 400, "linear",function(){
		$('#container').animate({
			top:$('#instructions').height(),
		}, 400);
	});
	//Remove the "X" button
	$('#close_instructions').css({
		display:"none",
	});
	//Move the page container down
	/*
	$('#container').animate({
		top:$('#instruction_text').height(),
	});
	*/
	dialogStatus = "top";
}

submitForm = function(e){
	document.youtubeForm.submit();
}

$(document).ready( function() {

/*******
 * Some stuff to do our modal windows
 */
	$('a[name=modal]').click(function(e) {
		e.preventDefault();
		var id = $(this).attr('href');
		showDialog(id);
	});
	
	$('.popup .close').click(function (e) {
		e.preventDefault();
		closeDialog();
	});
	
	
	$('#mask').click(function () {
		closeDialog();
	});
	
	$(window).resize(function () {
		if (dialogStatus == "open"){
		 	var box = $('#box .popup');
		 	var maskHeight = $(document).height();
		 	var maskWidth = $(window).width();
		    $('#mask').css({'width':maskWidth,'height':maskHeight});
		    var winH = $(window).height();
		    var winW = $(window).width();
		    box.css('top',  winH/2 - box.height()/2);
		    box.css('left', winW/2 - box.width()/2);
		} else if (dialogStatus == "top"){
			$('#container').css({
				top:$('#instructions').height(),
			});
		}
	});

/*******
* jQuery function bindings
*
*/

	$(".like").click( toggleLike ).mouseover( rollover ).mouseout( rollout );
	$(".reply").click( toggleReply ).mouseover( rollover ).mouseout( rollout );
	$(".comment").focus( clearDefaultComment ).blur( restoreDefaultComment );
	$(".post").click ( commitComment ).mouseover( rollover ).mouseout( rollout );
	$("#submit").click( submitForm ).mouseover( rollover ).mouseout( rollout );

/*
 * Show instructions
 */

<?php if ($hasInstructions) {
	print "showDialog('#instructions');";
} ?>
});


</script>
</head>
<body class="thrColFixHdr">

<!-- modal window -->
<div id="box">
	
	<!-- dialog box -->
	<?php printInstructions($hasInstructions, $instructions) ?>
	
	<!-- Mask to cover the whole screen -->
	<div id="mask"></div>
</div>

<div id="container">
  <div id="header"><img src="img/youtube/header.jpg" />
  <!-- end #header --></div>
  <div id="sidebar1">
  	<img src="img/youtube/guide.jpg" />
    <img src="img/youtube/more_results.jpg" />
  <!-- end #sidebar1 --></div>
  <div id="sidebar2">
  
  <!-- end #sidebar2 --></div>
  <div id="mainContent">
  	<form name="youtubeForm" id="youtubeForm" method="post" action="thanks.php">
 
  	  <script type="text/javascript" src="js/swfobject.js"></script>    
	  <div id="ytapiplayer">
	    You need Flash player 8+ and JavaScript enabled to view this video.
	  </div>
	  <script type="text/javascript">
	    var params = { allowScriptAccess: "always" };
	    var atts = { id: "youtubeplayer" };
	    swfobject.embedSWF("http://www.youtube.com/v/<?php print $videoId; ?>?enablejsapi=1&playerapiid=ytplayer&version=3",
	                       "ytapiplayer", "640", "360", "8", null, null, params, atts);
	</script>
	<!--
    <iframe id="youtubeplayer" width="640" height="360" src="http://www.youtube-nocookie.com/embed/<?php print $videoId; ?>?version=3&enablejsapi=1" frameborder="0" allowfullscreen></iframe>
	-->
	<div id="controls">
    	<h1><?php print $videoTitle; ?></h1>
        <div class="fltlft">
        	<?php printUploader($uploader); ?>
        	<input type="hidden" class="likeValue" name="videoLike" id="videoLike" value="0" />
            <div class="like_buttons">
                <img width="132" height="38" class="button like" name="buttonVideoLike" id="buttonVideoLike" src="img/youtube/video-like.jpg"  />
                <img width="162" height="38" class="button like" name="buttonVideoDislike" id="buttonVideoDislike" src="img/youtube/video-dislike.jpg" />
            </div>
        </div>
<?php printSummary($withSummary, $malePercent, $femalePercent, $meanAge); ?>
    	<div class="clearfloat"></div>
    </div>
    <div id="comments">
        <img src="img/youtube/blank.jpg" class="fltlft" />
        <div class="comment_commit" style="display: none; margin-left:66px;"></div>
        <textarea rows="3" cols="50" class="comment" name="videoComment" id="videoComment">Respond to this video...</textarea>
        <img class="button post" name="videoPost" id="videoPost" src="img/youtube/post.gif" width="46" height="28" />
        <h3>All comments (<?php print strval(min(count($commenters), count($comments))) ?>)</h3>
<?php printComments($numPics, $commenters, $comments, $withPics, $withCommentLike); ?>
	    <input type="hidden" name="experiment_name" value="<?php print $experiment; ?>" />
	    <input type="hidden" name="url" value="<?php print $page; ?>" />
	    <input type="hidden" name="submit" value="All done!" />
	    <input type="hidden" name="uid" id="uid" value="<?php print $userId; ?>" />
	    <input type="image" src="img/youtube/all-done.jpg" id="submit" />
    </div>
    </form>
	<!-- end #mainContent --></div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
    <img src="img/youtube/footer.jpg" />
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
