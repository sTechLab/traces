<?php
//This is a generic page that will get data from the survey, admin panel, etc.
//do some calculations, store stuff into the DB, and call the file that will render the
//experiment page.

require_once("util.php");
require_once("data_handler.php");
session_start();

//$ageThreshold = 35;

$_CLEAN = clean($_REQUEST);
$_CLEAN = array_merge($_CLEAN, $_SESSION);
$config = getConfig();
$ytConfig = $config[$_CLEAN['experiment']];
$surveyKeys = getSurveyQuestions($ytConfig);

$experimentUrl = strtolower($_CLEAN['experiment']) . ".php";
$experimentPage = "rGEqWzw8A9g"; //youtube ID in this case
$userId = $_CLEAN['uid'];

//Get instructions for page
if (array_key_exists('Instructions', $ytConfig) &&  $ytConfig['Instructions'] != "") {
	$instructions = $ytConfig['Instructions'];
	$hasInstructions = True;
} else {
	$hasInstructions = False;
}
$withCommentLike = false;

//Get data from survey needed to render this page
$age = $_CLEAN['survey']['age'];
$isMale = ($_CLEAN['survey']['gender'] == "m") ? True : False;
$withPics = ($_CLEAN['condition']['pic_condition'] == "p") ? True : False;
$withSummary = ($_CLEAN['condition']['summary_condition'] == "s") ? True : False;
$sameGender = ($_CLEAN['condition']['gender_condition'] == "s") ? True : False;
$sameAge = ($_CLEAN['condition']['age_condition'] == "s") ? True : False;
$numPics = (array_key_exists('Number of pictures', $ytConfig)) ? intval($ytConfig['Number of pictures']) : 10;
$percentHeteroGenders = (array_key_exists('Percent heterogeneous genders', $ytConfig)) ? floatval($ytConfig['Percent heterogeneous genders']) : 0.0;
$percentHeteroAges = (array_key_exists('Percent heterogeneous ages', $ytConfig)) ? floatval($ytConfig['Percent heterogeneous ages']) : 0.0;
$similarRange = (array_key_exists('Similar age range inside', $ytConfig)) ? intval($ytConfig['Similar age range inside']) : 5;
$dissimilarRange = (array_key_exists('Dissimilar age range outside', $ytConfig)) ? intval($ytConfig['Dissimilar age range outside']) : 10;

//Calculate some parameters for this page based on settings in admin panel
$malePicRatio = (($isMale and $sameGender) or (!$isMale and !$sameGender)) ? 1.0 : 0.0;
$malePicRatio = abs($malePicRatio - ($percentHeteroGenders * .01));
$numMalePics = round($numPics * $malePicRatio);
$numFemalePics = $numPics - $numMalePics;
$malePercent = round($malePicRatio * 100);
$femalePercent = 100 - $malePercent;

$numHeteroAges = round($numPics * ($percentHeteroAges * .01));



//Create a matrix of random ages of our fictional commenters
$allowedAges = range(18, 79);
$ageLowerBound = ($sameAge) ? $age - $similarRange : $age - $dissimilarRange;
$ageUpperBound = ($sameAge) ? $age + $similarRange : $age + $dissimilarRange;
if ($sameAge) {
	$homoAges = array_intersect($allowedAges, range($ageLowerBound, $ageUpperBound));
	$heteroAges = array_diff($allowedAges, range($ageLowerBound, $ageUpperBound));
} else {
	$homoAges = array_diff($allowedAges, range($ageLowerBound, $ageUpperBound));
	$heteroAges = array_intersect($allowedAges, range($ageLowerBound, $ageUpperBound));
}

//Get profile pic images
//For all folders use:
$availableImages = glob_recursive("img/faces/*/[!-]*.jp*");

//For just one folder use:
//$availableImages = glob_recursive("img/faces/1/*/[!-]*.jp*"); //some end in .jpg others in .jpeg

//remove 'img/faces' to match text in csv.
foreach ($availableImages as &$availableImage){
	$availableImage = substr($availableImage, 10);
}
unset($availableImage);

//Create an array of our fictional commenters
$commenters = array();
$totalAge = 0;

//Import profile data we have
$profiles = importCsvAssoc("admin/People.csv", true);
addGenders($profiles);

for ($i = 0; $i < $numPics; $i++) {
	//$ages[] = ($i < $numHeteroAges) ? $heteroAges[array_rand($heteroAges)] : $homoAges[array_rand($homoAges)];
	$availableAgeTypes[] = ($i < $numHeteroAges) ? 'hetero' : 'homo';
	$availableGenders[] = ($i < $numMalePics) ? "male" : "female";
}


for ($i = 0; $i < $numPics; $i++) {
	//determine age type
	$ageTypeIndex = array_rand($availableAgeTypes);
	$ageType = $availableAgeTypes[$ageTypeIndex];
	unset($availableAgeTypes[$ageTypeIndex]);
	$targetAges = ($ageType == "homo") ? $homoAges : $heteroAges;
	
	//determine gender
	$genderIndex = array_rand($availableGenders);
	$gender = $availableGenders[$genderIndex];
	unset($availableGenders[$genderIndex]);
	
	$matches = searchProfiles($profiles, $gender, $targetAges, $availableImages);

	if ($matches){
		$matchIndex = array_rand($matches);
		$match = $matches[$matchIndex];
		unset($profiles[$matchIndex]);
		
		$commenters[$i] = $match;
		$totalAge += $match['age'];
	}
	
	/*
	$commenterAge = $ages[array_rand($ages)];
	$commenterAgeType = ($commenterAge < $ageThreshold) ? "young" : "old";
	$commenterGender = ($i < $numMalePics) ? "male" : "female";
	$regex = "/^.*\/". $commenterGender . "\/" . $commenterAgeType . "\/(\d)+\.jpe?g$/";
	$possibleImages = preg_grep($regex, $unusedImages);
	if ($possibleImages){
		$commenterImageIndex = array_rand($possibleImages);
		$commenterImage = $possibleImages[$commenterImageIndex];
	} else {
		$commenterImage = "img/spacer.gif";
	}
	$commenters[$i] = array('age' => $commenterAge, 'gender' => $commenterGender, 'image' => $commenterImage);
	unset($unusedImages[$commenterImageIndex]); //note preg_grep will return array with indices intact, allowing this.
	$totalAge += $commenterAge;
	 */
}


$meanAge = round(floatval($totalAge) / floatval($numPics));
shuffle($commenters);

$comments = getJsonComments(10);
shuffle($comments);

//store data
$data = array();
$data['user_id'] = $userId;
$data['pic_condition'] = $_CLEAN['condition']['pic_condition'];
$data['summary_condition'] = $_CLEAN['condition']['summary_condition'];
$data['age_condition'] = $_CLEAN['condition']['age_condition'];
$data['gender_condition'] = $_CLEAN['condition']['gender_condition'];
$data['experiment_name'] = $_CLEAN['experiment'];
$data['date'] = date('r');
$thisPage = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$thisDir = substr($thisPage, 0, strrpos($thisPage, '/'));
$data['url'] = $thisDir . "/" . $experimentUrl;
$data['experiment_page'] = $experimentPage;
storeDb($data, 'experiment');

//include file that will render the page
require_once($experimentUrl);


?>
