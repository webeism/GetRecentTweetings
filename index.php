<?php
//Get config params
require_once("inc/config.php");
//Get class
require_once("inc/clsTweetings.php");
//Initiate instance
$oTweetings = new clsTweetings();
//Display the results
echo $oTweetings -> fnDisplay();
?>