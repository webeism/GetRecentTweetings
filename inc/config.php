<?php
// Put your twitter credentials here in place of the xxx's... 

// "Consumer key"
if(getenv("CONSUMERKEY")===false){
	define("CONSUMERKEY","xxx");
}else{
	define("CONSUMERKEY",getenv("CONSUMERKEY"));
}
// "Consumer secret"
if(getenv("CONSUMERKEYSECRET")===false){
	define("CONSUMERKEYSECRET","xxx");
}else{
	define("CONSUMERKEY",getenv("CONSUMERKEYSECRET"));	
}
?>