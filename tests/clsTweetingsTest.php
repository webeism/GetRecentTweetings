<?php
// Thanks to http://www.startutorial.com/articles/view/phpunit-beginner-part-1-get-started

require "inc/clsTweetings.php";

class clsTweetingsTests extends PHPUnit_Framework_TestCase{
	private $oTweetings;

	protected function setUp(){
		$this->oTweetings = new clsTweetings();
	}

	protected function tearDown(){
		$this->oTweetings = NULL;
	}

	public function fnGetInputDataProvider() {
    return array(
    	//Test some GETS
      array("testvar","","S","G","testval"),
      array("testvar","","N","G",1),
      array("testvar","","A","G",array(1,2,3)),
      
    	//Test some POSTS
      array("testvar","","S","P","testval"),
      array("testvar","","N","P",1),
      array("testvar","","A","P",array(1,2,3)),

     	//Test some SESSIONS
      array("testvar","","S","S","testval"),
      array("testvar","","N","S",1),
      array("testvar","","A","S",array(1,2,3)),

     	//Test some COOKIES
      array("testvar","","S","C","testval"),
      array("testvar","","N","C",1),
      array("testvar","","A","C",array(1,2,3)),
    );
  }

  /**
   * @dataProvider fnGetInputDataProvider
  */
  public function testfnGetInput($strVar, $mxdDefault, $mxdAllowedType, $strCheckOrder, $strVal)
  {
  	$boolTestValSet = false;
  	//Simulate setting a get, post, sess or cookie value and test the underlying functionality to retrieve the value
		for ($intI=0 ; $intI < strlen($strCheckOrder) && !$boolTestValSet ; $intI++) {
			switch (substr($strCheckOrder,$intI,1)) {
				case "S": $_SESSION[$strVar] = $strVal; $boolTestValSet = true; break;
				case "P": $_POST[$strVar] = $strVal; $boolTestValSet = true; break;
				case "G": $_GET[$strVar] = $strVal; $boolTestValSet = true; break;
				case "C": $_COOKIE[$strVar] = $strVal; $boolTestValSet = true; break;
				default;
			}
		}

		//Get the actual function result
    $mxResult = $this->oTweetings->fnGetInput($strVar, $mxdDefault, $mxdAllowedType, $strCheckOrder);

  	//Unset the value
  	$boolTestValUnSet = false;
		for ($intI=0 ; $intI < strlen($strCheckOrder) && !$boolTestValUnSet ; $intI++) {
			switch (substr($strCheckOrder,$intI,1)) {
				case "S": $_SESSION[$strVar] = ""; unset($_SESSION[$strVar]); $boolTestValUnSet = true; break;
				case "P": $_POST[$strVar] = ""; unset($_POST[$strVar]); $boolTestValUnSet = true; break;
				case "G": $_GET[$strVar] = ""; unset($_GET[$strVar]); $boolTestValUnSet = true; break;
				case "C": $_COOKIE[$strVar] = ""; unset($_COOKIE[$strVar]); $boolTestValUnSet = true; break;
				default;
			}
		}
    $this->assertEquals($strVal, $mxResult);
  }
}