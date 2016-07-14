<?php
/**
 * Tweetings class
 * Class to retrieve tweetages from selected user accounts
*/

class clsTweetings{

/**
 * @var public bool boolAuthSet Has the user set up the account keys
 * @var private array arrHTMLs array of HTML strings to inject into template
 * @var private string strBearerToken Bearer token (which should be cached) for oauth2/token
 * @var private string strOutput Final output after template replacements
 * @var private array arrErrs Array of any errors encountered
 * @var private string strURLBaseOauth Base URL for Oauth authentication
 * @var private string strURLBase Base URL for API endpoint
 * @var private array arrFinalResults JSON results as an array
*/
	public $boolAuthSet = false;
	private $arrHTMLs = array();
	private $strBearerToken = "";
	private $strOutput = "";
	private $arrErrs = array();
	private	$strURLBaseOauth = "https://api.twitter.com/oauth2/token";
	private	$strURLBase = "https://api.twitter.com/1.1/statuses/user_timeline.json";
	private	$arrFinalResults = array();

/**
 * @fn __construct Constructor
 * @param none
 * @access public
 * @return void
*/
	public function __construct(){
		//Initiate session
		//The "bearer token" will be stored in the session as repeated attempts to create it result in the same value
		if(!isset($_SESSION)){session_start();}
		//Check basic auth credentials
		$this->fnCheckAuth();
		//Check for an existing bearer token (create if none is set)
		$this->fnCheckBearerToken();
		//Get the twitter feed results
		$this->fnCreateResults();
	}

/*
 * @fn CheckAuth Check if authorisation details have been set in the session. If so, set object variables
 * @param none
 * @access private
 * @return void
*/

	private function fnCheckAuth(){
		//Retrieve defined auth vars
		$strAPIKey = (defined("CONSUMERKEY")?CONSUMERKEY:"");
		$strAPIKeySecret = (defined("CONSUMERKEYSECRET")?CONSUMERKEYSECRET:"");
		$this->boolAuthSet = 
			$strAPIKey!="" &&
			$strAPIKeySecret!="" &&
			$strAPIKey!="xxx" &&
			$strAPIKeySecret!="xxx";
	}

/*
 * @fn CheckBearerToken Check if a bearer token is already in the session using the defined CONSUMER keys, if not, create
 * @param none
 * @access private
 * @return void
*/

	private function fnCheckBearerToken(){
		if($this->boolAuthSet){
			$boolCreateNewBearerToken = false;
			$this->strBearerToken = fnGetInput("strBearerToken","","S","S");
			$strBearerTokenUser = fnGetInput("strBearerTokenUser","","S","S");
			if(!($this->strBearerToken!="" && md5(CONSUMERKEY.CONSUMERKEYSECRET)==$strBearerTokenUser)){
					$this->strBearerToken = "";
					$boolCreateNewBearerToken = true;
			}
			if($boolCreateNewBearerToken){
				//Attempt to generate
				//Step 1: Encode consumer key and secret
				
				//URL encode
				$strAPIKeyEncoded = urlencode(CONSUMERKEY);
				$strAPIKeySecretEncoded = urlencode(CONSUMERKEYSECRET);
				
				//Concatenate with :
				$strEncodedKey = $strAPIKeyEncoded . ":" . $strAPIKeySecretEncoded;
				
				//base64 to create the "bearer token credentials"
				$strEncodedKey = base64_encode($strEncodedKey);

				//Step 2: post to oauth2/token (application only authentication)
				$strURL = $this->strURLBaseOauth;
				$strHeaders = "Authorization: Basic ".$strEncodedKey."\r\n";
				$strHeaders .= "Content-Type: application/x-www-form-urlencoded;charset=UTF-8";

				$arrParams = array(
					'http' => array(
	          'method' => 'POST',
	          'header' => $strHeaders,
	          'content' => 'grant_type=client_credentials',
	         )
	      );

				$arrJSON = fnGetJSONFromURL($strURL,$arrParams);
				//Update any encountered errors
				foreach($arrJSON["arrErrs"] as $str){
					$this->arrErrs[] = $str;
				}
				//Retrieve the actual JSON
				if($arrJSON["boolSuccess"]){
					$arrResults = $arrJSON["arrResults"];
					//Check for the bearer token
					if(isset($arrResults["access_token"]) && isset($arrResults["token_type"]) && $arrResults["token_type"]=="bearer"){
						$this->strBearerToken = $arrResults["access_token"];
						//Set in the session as future requests respond with the same token
						$_SESSION["strBearerToken"] = $this->strBearerToken;
						//Also set in the session a hash of the current credentials.
						$_SESSION["strBearerTokenUser"] = md5(CONSUMERKEY.CONSUMERKEYSECRET);
					}else{
						$this->arrErrs[] = "Unable to retrieve bearer token from: " . var_dump($arrResults); 
					}
				}
			}
		}
	}


/*
 * @fn CreateResults Go ahead and query twitter
 * @param none
 * @access private
 * @return void
*/

	private function fnCreateResults(){
		if($this->boolAuthSet && $this->strBearerToken!=""){
			//Check if twitteruser has been posted
			$strTwitterUser = trim(fnGetInput("twitteruser","","S","P"));
			$this->arrHTMLs["strTwitterUser"] = htmlspecialchars($strTwitterUser);
			if(preg_match('/^[A-Za-z0-9_]{1,15}$/', $strTwitterUser)){
				//EndPoint
				$strURL = $this->strURLBase;
				$strURL .= "?screen_name=".$strTwitterUser;
				$strURL .= "&count=10";
				$strURL .= "&trim_user=true";
				$strURL .= "&exclude_replies=true";
				$strURL .= "&include_rts=false";

				//Headers
				$strHeaders = "Authorization: Bearer ". $this->strBearerToken ."\r\n";
				$strHeaders .= "Content-Type: application/x-www-form-urlencoded;charset=UTF-8";
				$arrParams = array(
					'http' => array(
	          'method' => 'GET',
	          'header' => $strHeaders
	         ),
	      );
				$arrJSON = fnGetJSONFromURL($strURL,$arrParams);
				//Update any encountered errors
				foreach($arrJSON["arrErrs"] as $str){
					$this->arrErrs[] = $str;
				}
				//Retrieve the actual JSON
				if($arrJSON["boolSuccess"]){
					$this->arrFinalResults = $arrJSON["arrResults"];
				}
			}else{
				$this->arrErrs[] = "Invalid username: " . $strTwitterUser;
			}
		}
	}


/*
 * @fn Display Display the interface
 * @param none
 * @access public
 * @return str
*/

	public function fnDisplay(){
		//Form action
		$this->arrHTMLs["strFormAction"] = htmlspecialchars($_SERVER["PHP_SELF"]);
		
		//Generate the credentials section of the form
		$this->fnGetHTML("Credentials");
		
		//Generate the options section of the form
		$this->fnGetHTML("Options");
		
		//Generate the results section of the form
		$this->fnGetHTML("Results");
		
		//Process any template variables to be replaced
		$this->fnProcessReplacements();
		
		//Return the resulting output
		return $this->strOutput;
	}

/*
 * @fn GetHTML Get HTML for defined sections and add to the template replacements array
 * @param str strType Type of HTML to get
 * @access private
 * @return void
*/
	private function fnGetHTML($strType = ""){
		//Template variable
		$strTemplateVar = ($strType != "" ? "str" . $strType . "HTML" : "");
		//Buffer Output
		ob_start();
		switch($strType){
			case "Credentials":
				if($this->boolAuthSet){
					echo '<p class="succ">The details set in the inc/config.php will be used to communicate with the twitter</p>';
				}else{
					echo '<p class="err">Make sure your twitter credentials are set in inc/config.php</p>';
				}
			break;
			case "Results":
				foreach($this->arrErrs as $strErr){
					echo '<p class="err">'.$strErr.'</p>';
				}
				foreach($this->arrFinalResults as $int=>$arrTweet){
					echo '<p class="tweetp">'. ($int+1) . ": ".$arrTweet["created_at"].'<br />'.$arrTweet["text"].'</p>';
				}
			break;
			default;
		}
		//Get the buffer
		$strOutput = ob_get_contents();
		//Close the buffer
		ob_end_clean();
		//Add the output to the replacements array
		if($strTemplateVar!="" && $strOutput!=""){
			$this->arrHTMLs[$strTemplateVar] = $strOutput;
		}
	}


/*
 * @fn ProcessReplacements Replace template vars with data
 * @param none
 * @access public
 * @return str
*/
	private function fnProcessReplacements(){
		//Get the template
		$str = file_get_contents("template.html");

		//Get any variable names to replace
		$strPattern = "/{.*}/";
		preg_match_all($strPattern, $str, $arrMatches);
		if(isset($arrMatches[0]) && is_array($arrMatches[0])){
			foreach($arrMatches[0] as $strMatch){
				//remove template variable delimeters
				$strVar = substr($strMatch, 1, -1);
				$strVal = (isset($this->arrHTMLs[$strVar])?$this->arrHTMLs[$strVar]:"");
				$str = str_replace($strMatch, $strVal, $str);
			}
		}
		$this->strOutput = $str;
	}
}//cls