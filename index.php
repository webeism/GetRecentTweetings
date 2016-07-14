<?php
//Get config params
require_once("inc/config.php");
//Get functions
require_once("inc/functions.php");
//Get class
require_once("inc/clsTweetings.php");
//Initiate instance
$oTweetings = new clsTweetings();
//Do something
echo $oTweetings -> fnDisplay();
?>