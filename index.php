<?php
//Get config params
require_once("inc/config.php");
//Process config params
require_once("inc/configProcess.php");
//Get class
require_once("inc/clsTweetings.php");
//Initiate instance
$oTweetings = new clsTweetings();
//Display the results
echo $oTweetings -> fnDisplay();
?>