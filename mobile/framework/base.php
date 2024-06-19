<?php
include_once "library.php";

class Base {
	protected $error = array();
	
	public function pushError($key, $message) {
		$this->error[$key] = $message;
	}
	
	public function getError() {
		return json_encode($this->error);
	}
	
//Response
	protected function escquote($str, $mustQuote=true, $clean=true) {
		if ($clean) {
			if ($mustQuote)
				return "\"" . str_replace("\t","",str_replace("\r", "", str_replace("\n","", str_replace('"', '\"', str_replace('\\', '\\\\',$str))))) . "\"";
			else
				return str_replace("\t","",str_replace("\r", "", str_replace("\n","", str_replace('"', '\"', str_replace('\\', '\\\\',$str)))));			
		}
		else {
			if ($mustQuote)
				return "\"" . str_replace("\t","\\t",str_replace("\r", "\\r", str_replace("\n","\\n", str_replace('"', '\"', str_replace('\\', '\\\\',$str))))) . "\"";
			else
				return str_replace("\t","\\t",str_replace("\r", "\\r", str_replace("\n","\\n", str_replace('"', '\"', str_replace('\\', '\\\\',$str)))));
		}
	}
	
	public static function responseSucceed($arrMessages, $arrNameValues) {
/*
{
"status": 1,
"message": ["message1","message2", ...],
"data": [{
"variable1": 1,
"variable2": "value2"
...
}] }
*/
        if (sizeof($arrNameValues)==0)
            $strResponse = "{\"status\": 1,\"message\":" . json_encode($arrMessages) . ",\"data\":null}";
        else
            $strResponse = "{\"status\": 1,\"message\":" . json_encode($arrMessages) . ",\"data\":" . json_encode($arrNameValues) . "}";
        
        return $strResponse;
    }
    
    
    public static function responseSucceedPrepared($arrMessages, $strJSON) {
        if ($strJSON=="")
            $strResponse = "{\"status\": 1,\"message\":" . json_encode($arrMessages) . "}";
        //else if ($strJSON=="[]" || $strJSON=="{}")
		else if ($strJSON=="{}" || $strJSON=="[]")
            $strResponse = "{\"status\": 1,\"message\":" . json_encode($arrMessages) . ",\"data\":null}";        
        else
            $strResponse = "{\"status\": 1,\"message\":" . json_encode($arrMessages) . ",\"data\":" . $strJSON . "}";
        return $strResponse;        
    }
    
    public static function responseFailed($arrMessages) {
/*
{
"status": 0,
"message": ["message1", "message2",...]
},
"data": null
}
*/    
        $strResponse = "{\"status\": 0,\"message\":" .  json_encode($arrMessages) . ",\"data\": null}";
        return $strResponse;
    }
}
?>