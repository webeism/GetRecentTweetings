<?php

/*
 * @fn GetJSONFromURL Given a url and parameters, attept to retrieve JSON
 * @param string strURL endpoint URL
 * @param array arrParams valid array for creating stream context
 * @return array with success indicator, any error messages and the actual JSON object as an array
*/

	function fnGetJSONFromURL($strURL = "", $arrParams = array()){
		$arrResults = "";
		$arrErrs = array();
		$arrResponse = array();
		$boolSuccess = false;
		if($strURL!=""){
			if($arrParams!==array()){
				if($context = @stream_context_create( $arrParams )){
					if($strVerifyResponse = @file_get_contents($strURL,false,$context)){
						if($arrResults = @json_decode($strVerifyResponse,true)){
								$boolSuccess = true;
						}else{
							$arrErrs[] = "Unable to decode JSON from: " . var_dump($strVerifyResponse); 
						}
					}else{
						$arrErrs[] = "Unable to get file: " . $strURL; 
					}
				}else{
					$arrErrs[] = "Unable to create stream_context from: <pre>" . print_r($arrParams,true) . "</pre>";
				}
			}else{
				$arrErrs[] = "Empty parameters array detected";
			}
		}else{
			$arrErrs[] = "Blank URL detected";
		}
		$arrResponse["arrResults"] = $arrResults;
		$arrResponse["arrErrs"] = $arrErrs;
		$arrResponse["boolSuccess"] = $boolSuccess;
		return $arrResponse;
	}

/*
 * @fn GetInput Get input and check against valid values
 * @param string $strVarname String of name to collect
 * @param mixed $mxdDefault Default value if checks fail
 * @param mixed $mxdAllowedType Checks to perform
 * 		string A = Check if is array
 * 		string S = Check if is string
 * 		string SN = Check if is string (not empty string)
 * 		string N = Check if is number
 * 		string DT = Check if is format yyyy-mm-dd hh-ii-ss
 * 		array    = Check if returned value is in array supplied
 * @param string $strCheckOrder What to check 1 char up to all in order of check
 * 		string S = _SESSION
 * 		string P = _POST
 * 		string G = _GET
 * 		string C = _COOKIE
 * @return mixed result of check or default
*/

function fnGetInput($strVarname, $mxdDefault, $mxdAllowedType, $strCheckOrder="PG") {
	for ($intI=0;$intI<strlen($strCheckOrder) && !isset($strOutput);$intI++) {
		switch (substr($strCheckOrder,$intI,1)) {
			case "S": if (isset($_SESSION[$strVarname])) $strOutput = $_SESSION[$strVarname]; break;
			case "P": if (isset($_POST[$strVarname])) $strOutput = $_POST[$strVarname]; break;
			case "G": if (isset($_GET[$strVarname])) $strOutput = $_GET[$strVarname]; break;
			case "C": if (isset($_COOKIE[$strVarname])) $strOutput = $_COOKIE[$strVarname]; break;
		}
	}
	if (!isset($strOutput)) {
		return $mxdDefault;
	} else if (is_array($mxdAllowedType)) {
		return (array_search($strOutput,$mxdAllowedType)===false ? $mxdDefault : $strOutput);
	} else {
		switch ($mxdAllowedType) {
			case "A" : $strOutput = (is_array($strOutput) ? $strOutput : $mxdDefault); break;
			case "S" : $strOutput = (is_string($strOutput) ? $strOutput : $mxdDefault); break;
			case "SN": $strOutput = (is_string($strOutput) && $strOutput!="" ? $strOutput : $mxdDefault); break;
			case "N" : $strOutput = (is_numeric($strOutput) ? $strOutput : $mxdDefault); break;
			case "DT" : $strOutput = (date("Y-m-d H:i:s",strtotime($strOutput))==$strOutput ? $strOutput : $mxdDefault); break;
			default: $strOutput = $mxdDefault;
		}
		return $strOutput;
	}
}
?>