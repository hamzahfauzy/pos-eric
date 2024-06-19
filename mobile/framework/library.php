<?php
function openConnection() {
   try {
		$hostname = "localhost";
		$port = 10060;
		$dbname = "POS";
		$username = "sa";
		$pw = "P4ssw0rd";
		//$dbh = new PDO ("dblib:host=$hostname:$port;dbname=$dbname","$username","$pw");
		$con = new PDO("sqlsrv:Server=$hostname;Database=$dbname;TrustServerCertificate=true", "$username", "$pw");
		$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      throw new Exception("0: " . $e->getMessage());
   }
   return $con;
}

function logHeader() {
	$header = var_export(getallheaders(), true);
	file_put_contents("log/log.txt", TimeStampMicro() . "[" . getClientIPAddress() . "] " . "\n-- HEADER: " . $header  . "\n\n", FILE_APPEND | LOCK_EX);
}

function logRequest() {
	$get = var_export($_GET, true);
	$post = var_export($_POST, true);	
	$request = var_export($_REQUEST, true);	
	file_put_contents("log/log.txt", TimeStampMicro() . "[" . getClientIPAddress() . "] " . "\n-- GET: " . $get  ."\n-- POST:" . $post . "\n-- REQUEST: " . $request . "\n\n", FILE_APPEND | LOCK_EX);
}

function logSession() {
	if (isset($_SESSION)) {
		$session = var_export($_SESSION, true);
		file_put_contents("log/log.txt", TimeStampMicro() . "[" . getClientIPAddress() . "] " . "\n-- SESSION: " . $session . "\n\n", FILE_APPEND | LOCK_EX);
	} else {
		file_put_contents("log/log.txt", TimeStampMicro() . "[" . getClientIPAddress() . "] " . "\n-- SESSION: No SESSION RECORDED Yet!\n\n", FILE_APPEND | LOCK_EX);
	}
}

function logDebug($text) {
	file_put_contents("log/log.txt", TimeStampMicro() . "[" . getClientIPAddress() . "] " . " -- " . $text . "\n\n", FILE_APPEND | LOCK_EX);
}

function getEndPoint() {
	$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .  
                $_SERVER['REQUEST_URI'];
	return $link;
}

function getClientIPAddress() {
	$ipaddress="";
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress= $_SERVER['HTTP_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress= $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress= $_SERVER['HTTP_X_FORWARDED'];	
	else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress= $_SERVER['HTTP_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress= $_SERVER['HTTP_FORWARDED'];	
	else if (isset($_SERVER['REMOTE_ADDR']))
		$ipaddress= $_SERVER['REMOTE_ADDR'];	
	return $ipaddress;
}

function isMobile($useragent) {
	return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
}

function param($name, $mandatory=1, $default="") {
	$value = $default;
	if (isset($_POST[$name])) {
		$value = $_POST[$name];
	} else if (isset($_GET[$name])) {
		$value = $_GET[$name];
	} else if ($mandatory==1) {
		throw new Exception("ERROR:parameter $name tidak ditemukan!");
	}
	return $value;
}

function filterObj($obj, $arr) {
	$newObj = new stdClass();
	foreach ($obj as $key=>$value)
		if (in_array($key, $arr))
			$newObj->{$key} = $value;
	return $newObj;
}

function assertCompleteParam($request, $arr) {
	for ($i=0; $i<sizeof($arr); $i++) {
		$param = $arr[$i];
		if (!array_key_exists($param, $request))
			throw new Exception("ERROR:Expected param $param not found");
	}	
}

function assertCompleteJSON($obj, $arr) {
	//$obj2 = json_decode($obj);
	//var_dump(is_object($obj2));
	//var_dump($obj);
	//var_dump($arr);
	//echo $obj->tgl_order;
	for ($i=0; $i<sizeof($arr); $i++) {
		$key = $arr[$i];
		//echo $key;
		//var_dump(property_exists($obj, $key));
		if (!property_exists($obj, $key))
			throw new Exception("ERROR:Expected key $key not found");
	}	
}

function session($name, $mandatory=0, $default=null) {
	$value = $default;
	if (isset($_SESSION[$name])) {
		$value = $_SESSION[$name];
	} else if ($mandatory==1) {
		throw new Exception("ERROR:session $name tidak ditemukan!");
	}
	return $value;
}

function getRomanMonth($m) {
   $month=array('','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
   return $month[$m];
}

//Select
function querySingleValue($con, $sSql, $values) {
//return single Value
	 $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
               return $row[0];
            } else {
               return null;
            }
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function queryArrayValue($con, $sSql, $values) {
//return array values
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
               return $row;
            } else {
               return null;
            }
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function escquote($str, $mustQuote=true, $clean=true) {
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

function queryJsonPatternedValue($con, $sSql, $values, $patterned) {
//return array values
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$retStr = $patterned;
				for ($i=0; $i<sizeof($row);$i++) {			
					if (strpos($patterned,"{" . $i  . "}")>-1) 
						$retStr = str_replace("{" . $i . "}",escquote($row[$i], false), $retStr);
				}
               return $retStr;
            } else {
               return null;
            }
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function queryArrayRowsValue($con, $sSql, $values) {
//return array values
	  $arr = array();	
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				do {
					$arr[] = $row[0];
				
				} while ($row = $stmt->fetch(PDO::FETCH_NUM));		
            }
			
			return $arr;
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function queryArrayRowsValues($con, $sSql, $values) {
//return array values
	  $arr = array();	
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");
		
      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
			
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				do {

					$arr[] = $row;
				
				} while ($row = $stmt->fetch(PDO::FETCH_NUM));		
            }
						
			return $arr;
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function queryArrayJsonPatternedValue($con, $sSql, $values, $patterned) {
//return array values
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"select"))
            throw new Exception("0: Invalid Select statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$retStr = "'";
				$recCount=0;
				do {
					if ($recCount < 1)
						$retStr .= "";
					else
						$retStr .= ",";
					
					$newObj = $patterned;
					
					for ($i=0; $i<sizeof($row);$i++) {			
						if (strpos($patterned,"{" . $i  . "}")>-1) 
							$newObj = str_replace("{" . $i . "}",escquote($row[$i], false), $newObj);
					}
								
					$retStr .= $newObj;
					
				} while ($row = $stmt->fetch(PDO::FETCH_NUM));		
				
				return $retStr;
            } else {
				return null;
			}
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

//CRUD
function createRow($con, $sSql, $values) {
//return row Affected
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"insert"))
            throw new Exception("0: Invalid Insert statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
            $stmt->execute();
            return $stmt->rowCount();
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function updateRow($con, $sSql, $values) {
//return row Affected
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"update"))
            throw new Exception("0: Invalid Update statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            return $stmt->rowCount();
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function deleteRow($con, $sSql, $values) {
//return row Affected
	  $temp = explode(";",$sSql); $sSql0 = $temp[0];
      if (!startsWith(strtolower(trim($sSql0)),"delete"))
            throw new Exception("0: Invalid Delete statement!");

      try {
         if (!($stmt = $con->prepare($sSql0))) {
            throw new Exception("0:  (" . $con->errno . ") " . $con->error);
         } else {
            $paramValues = $values;
			if (strpos($sSql0, "?")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam($paramCount, $paramValues[$i]);
				}			
			} else if (strpos($sSql0, ":1")) {
				for ($i=0; $i<sizeof($values);$i++) {   
					$paramCount = $i+1;
					$paramValues[$i] = descapeCSV($values[$i]);
					$stmt->bindParam(":" . $paramCount, $paramValues[$i]);
				}				
			} else {
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);				
			}
			
            $stmt->execute();
            return $stmt->rowCount();
         }
      } catch (PDOException $e) {
         throw new Exception("0: " . $e->getMessage());
      }
}

function checkPKExists($con, $table, $key, $value, $filter="", $sufix="") {
	if (querySingleValue($con, "select count(*) from $table where $key=:value $filter", array("value"=>$value))>0)
		throw new Exception($sufix . "Primary key $value telah ada!");
}

function checkFKExists($con, $table, $key, $value, $filter="", $sufix="") {
	if (querySingleValue($con, "select count(*) from $table where $key=:value $filter", array("value"=>$value))<1)
		throw new Exception($sufix . "Foreign key $value tidak ditemukan!");
}

function escapeCSV($str) {
//escape karakter CR, Lf, dan ;
   return str_replace(":","&colon",str_replace("\n","&linefeed",str_replace("\r","&carriagereturn",str_replace(";", "&semicolon",$str))));
}

function descapeCSV($str) {
//descape karakter CR, Lf, dan ;
   return str_replace("&colon",":",str_replace("&linefeed","\n",str_replace("&carriagereturn","\r",str_replace("&semicolon",";",$str))));
}

function csvLinesToArray($text) {
   $arr = array();
   $lines = explode("\n",$text);
   foreach($lines as $line) {
      $arrline = preg_split('#;#',$line);
      $arr[] = $arrline;
   }
   return $arr;
}

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function fmoney($number, $decimal) {
	return number_format($number, $decimal, ',', '.');
}

function percent($deviden, $divisor) {
	if ($divisor==0)
		return "~";
	else 
		return number_format((float)($deviden*100/$divisor), 2, '.', '')."%";
}

function getPeriode($delta=0) {
	if ($delta==0)
		return date( "Ym");
	else
		return date( "Ym", strtotime(date("Y-m-d") . " $delta month" ) );
}

function periodFirstDay($str) {
	return substr($str,0,4) . "-".substr($str,4) . "-01";
}

function periodLastDay($str) {
	return date("Y-m-t", strtotime(periodFirstDay($str)));
}

function strFirstDayofMonth() {	
	return date('Y-m-01');
}

function strLastDayofMonth() {
	return date('Y-m-t');
}

function reFormatDate($str, $fromFmt, $toFmt) {
	/*
	if (strpos($str,"/"))
		list($day,$month,$year) = explode("/",$str);
	else
		list($day,$month,$year) = explode("-",$str);
	return date("Y-m-d", mktime(0,0,0,$month,$day,$year));
	*/
	return date($toFmt, toDateTime($str, $fromFmt));
}

function reFormatDMYToYMD($str) {
	/*
	if (strpos($str,"/"))
		list($day,$month,$year) = explode("/",$str);
	else
		list($day,$month,$year) = explode("-",$str);
	return date("Y-m-d", mktime(0,0,0,$month,$day,$year));
	*/
	return reFormatDate($str, "d/m/Y", "Y-m-d");
}

function reFormatYMDToDMY($str) {
	/*
	if (strpos($str,"/"))
		list($year,$month,$day) = explode("/",$str);
	else
		list($year,$month,$day) = explode("-",$str);
	return date("d/m/Y", mktime(0,0,0,$month,$day,$year));
	*/
	return reFormatDate($str, "Y-m-d", "d/m/Y");
}

function reFormatDMYHisToYMDHis($str) {
	/*
	list($date, $time) = explode(" ",$str);
	if (strpos($str,"/"))
		list($day,$month,$year) = explode("/",$date);
	else
		list($day,$month,$year) = explode("-",$date);
	list($hour,$minute,$second) = explode(":",$time);
	return date("Y-m-d H:i:s", mktime($hour,$minute,$second,$month,$day,$year));
	*/
	return reFormatDate($str, "d/m/Y H:i:s", "Y-m-d H:i:s");
}

function reFormatYMDHisToDMYHis($str) {
	/*
	list($date, $time) = explode(" ",$str);
	if (strpos($str,"/"))
		list($year,$month,$day) = explode("/",$date);
	else
		list($year,$month,$day) = explode("-",$date);
	list($hour,$minute,$second) = explode(":",$time);
	return date("d/m/Y H:i:s", mktime($hour,$minute,$second,$month,$day,$year));
	*/
	return reformatDate($str, "Y-m-d H:i:s", "d/m/Y H:i:s");
}

function convertTo($str, $arrPair) {
	return $arrPair[$str];
}

function toNumber($str) {
	return floatval(preg_replace('/[^\d.-]/', '', $str));
}

function toDateTime($str, $fstr) {
	$astr = explode(" ",$str);
	$fdate = str_replace("-","",str_replace("/","", $fstr));
	if (strpos($str,"/")) {
		$adate = explode("/",$astr[0]);		
	} else {
		$adate = explode("-",$astr[0]);
	}
	if (sizeof($astr)>1)
		list($hour,$minute,$second) = explode(":",$astr[1]);
	else {
		$hour=0; $minute=0; $second=0;
	}
	$y = strpos($fdate,"y");
	$m = strpos($fdate,"m");
	$d = strpos($fdate,"d");	
	return mktime(intval($hour),intval($minute),intval($second),intval($adate[$m]),intval($adate[$d]),intval($adate[$y]));
}

function validateType($obj, $pair, &$error, $sufix="") {
	foreach ($pair as $key=>$type) {
		
		if (is_object($obj))
			$value = $obj->{$key};
		else
			$value = $obj[$key];
		
		if ($type=="boolean") {
			if ($value!=true && $value !=false)
				$error[$sufix . $key] =  "bukan true atau false!";
		}
		else if ($type=="email") {
			if (!filter_var($value, FILTER_VALIDATE_EMAIL))
				$error[$sufix . $key] =  "bukan alamat email !";
		}
		else if (($type=="number") && !isNumber($value))
			$error[$sufix . $key] =  "bukan numerik !";
		else if (($type==">0") && floatval(toNumber($value))<=0)
			$error[$sufix . $key] = "harus lebih besar dari nol!";
		else if (($type==">=0") && floatval(toNumber($value))<0)
			$error[$sufix . $key] = "tidak boleh negatif!";		
		else if (startsWith($type, "=")) {
			$strRange=substr($type,1);
			$range=explode("-", $strRange);
			if (sizeof($range)==1) {
				$val = floatval($range[0]);
				if (floatval(toNumber($value))!=$val)
					$error[$sufix . $key] = "harus bernilai $val!";
			} else {
				$val1=floatval($range[0]);
				$val2=floatval($range[1]);
				if (floatval(toNumber($value))<$val1 || floatval(toNumber($value))>$val2) {
					$error[$sufix . $key] = "harus bernilai antara $val1 sampai $val2!";
				}				
			}			
		}
		else if (($type=="~!0") && strlen($value)==0)
			$error[$sufix . $key] =  "tidak boleh kosong!";
		else if (startsWith($type,"~=")) {
			$arr=explode("=", $type);
			$range=explode("-", $arr[1]);
			if (sizeof($range)==1) {
				$len=$range[0];
				if (strlen($value)!=intval($len))
					$error[$sufix . $key] = "harus $len karakter!";
			} else {
				$len1=intval($range[0]);
				$len2=intval($range[1]);
				if (strlen($value)<$len1 || strlen($value)>$len2) {
					$error[$sufix . $key] = "harus antara $len1 sampai $len2 karakter!";
				}
			}
		}
		else if (startsWith($type,"in")){
			$strList = substr($type,2);
			$list = explode(",", $strList);
			if (!in_array($value, $list))
				$error[$sufix . $key] = "harus salah satu dari $strList !";
		}
		/*
		else if ($type=="date" && !isValidDate($value))
			$error[$sufix . $key] = "harus tanggal Y-m-d!";
		else if ($type=="datetime"  && !isValidDateTime($value))
			$error[$sufix . $key] = "harus waktu Y-m-d H:i:s!";
		*/
		else if (startsWith($type,"date")) {
			if ($type=="datetime")
			{
				if(!isValidDateTime($value))
					$error[$sufix . $key] = "harus waktu Y-m-d H:i:s atau tanggal tidak valid!";
			}			
			else 
			{
				if (!isValidDate($value))
					$error[$sufix . $key] = "harus tanggal Y-m-d atau tanggal tidak valid!";
				else {
					$arr=explode("date", $type);
					$range=explode("~", $arr[1]);
					if (sizeof($range)==1) {
						$lowerLimit=$range[0];
						
						if ($value<$lowerLimit)
							$error[$sufix . $key] = "harus lebih besar dari $lowerLimit !";
					} else {
						$lowerLimit=$range[0];
						$upperLimit=$range[1];
						if ($value<$lowerLimit || $value>$upperLimit) {
							$error[$sufix . $key] = "harus antara $lowerLimit sampai $upperLimit !";
						}
					}
				}
			}
		}
	}
}

function pairArrayToString($pair, $delimiter="</br>") {
	$retStr = "";
	foreach($pair as $key=>$value) {
		if ($retStr=="")
			$retStr .= "$key -> $value";
		else
			$retStr .= $delimiter . "$key -> $value";
	}
	return $retStr;
}

function isValidLength($str, $len) {
	return strlen($str)==$len;
}

function isValidMinLength($str, $len) {
	return strlen($str)>=$len;
}

function isValidMaxLength($str, $len) {
	return strlen($str)<=$len;
}

function isValidMinMaxLength($str, $minLen, $maxLen) {
	return (strlen($str)>=$minLen && strlen($str)<=$maxLen);
}

function isInRange($str, $min, $max) {
	$val = floatVal(toNumber($str));
	return ($val >= $min && $val <= $max);
}

function isNumber($str) {
  return is_numeric(str_replace(",","",$str));
}

//mengecek apakah string sesuai dengan format tanggal
function isValidDate($str, $format = 'Y-m-d')
{
	/*
	$newFormat = strtolower(str_replace("/","",str_replace("-","",$format)));
	
	$y = strpos($newFormat,"y");
	$m = strpos($newFormat,"m");
	$d = strpos($newFormat,"d");
	
	if (strpos($str,"/")) {
		$part = explode("/",$str);
	} else {
		$part = explode("-",$str);
	}
	
	return ($str==date($format, mktime(0,0,0,$part[$m],$part[$d],$part[$y])));
	*/
	if ($str < 10)
		return false;
	else
		return ($str== reFormatDate($str, $format, $format));
}

//mengecek apakah string sesuai dengan format tanggal
function isValidTime($str) {
	return preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])(:[0-5][0-9])?$/', $str);
}

//mengecek apakah string sesuai dengan format tanggal
function isValidDateTime($str, $format = 'Y-m-d H:i:s')
{
	/*
	list($date, $time) = explode(" ",$str);
	list($fdate, $ftime) = explode(" ",$format);
	
	$newFdate = strtolower(str_replace("/","",str_replace("-","",$fdate)));
	$newFtime = strtolower(str_replace(":","",$ftime));
	
	$y = strpos($newFdate,"y");
	$m = strpos($newFdate,"m");
	$d = strpos($newFdate,"d");
	
	$h = strpos($newFtime,"h");
	$i = strpos($newFtime,"i");
	$s = strpos($newFtime,"s");
	
	if (strpos($date,"/")) {
		$dpart = explode("/",$date);
	} else {
		$dpart = explode("-",$date);
	}
	
	$tpart = explode(":",$time);
	
	return ($str==date($format, mktime($tpart[$h],$tpart[$i],$tpart[$s],$dpart[$m],$dpart[$d],$dpart[$y])));
	*/
	if ($str<19)
		return false;
	else
		return ($str== reFormatDate($str, $format, $format));
}

function TimeStampSecond() {
	return date('Y-m-d H:i:s');
}

function TimeStampMicro() {
	$t = microtime(true);
	$micro = sprintf("%03d",($t - floor($t)) * 1000);
	return date('Y-m-d H:i:s') . '.' . $micro;
}

function percentToDB($str) {
	return str_replace(",",".", $str);
}

function dBToPercent($str) {
	return str_replace(".",",", $str);
}

function sign($n) {
	return ($n > 0) - ($n < 0); 
}

/*
for json post as object
*/
function mandatoryParam($property, $obj) {
	if(property_exists($obj, $property)) {
		return $obj->{$property};
	} else {
		throw new Exception("0:Incomplete Mandatory Param " . $property . "!");
	}
}

function optionalParam($property, $obj, $default="") {
	if(property_exists($obj, $property)) {
		return $obj->{$property};
	} else {
		return $default;
	}
}

function existFilter($obj, $property) {
	return (property_exists($obj, $property));
}

/*
for json post
*/

function addFilter($filter, $value, $exp) {
	if ($value =="")
		return $filter;
	else if ($filter!="")
		return "$filter and $exp";
	return $exp;
}

function tanggal($date, $format = 'd-m-Y')
{
    //$d = createDateFromFormat($format, $date);
    $d = reFormatDate($date, $format, $format);
    return $d;
}

//////////////////////////////////////////////////////////////////////
//PARA: Date Should In YYYY-MM-DD Format
//RESULT FORMAT:
// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
// '%m Month %d Day'                                            =>  3 Month 14 Day
// '%d Day %h Hours'                                            =>  14 Day 11 Hours
// '%d Day'                                                        =>  14 Days
// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
// '%h Hours                                                    =>  11 Hours
// '%a Days                                                        =>  468 Days
//////////////////////////////////////////////////////////////////////
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
	$seconds = strtotime($date_1) - strtotime($date_2);
	return $seconds / (24*60*60);
	//$interval = date_diff($date_1, $date_2);  
    //return $interval->format($differenceFormat);
}

//setting timezone
date_default_timezone_set('Asia/Jakarta');
//unit test
/*
$error=[];
$obj = new Stdclass;
$obj->nik="01234";
$obj->gaji="ada";
$obj->tgllahir="a1973-06-19";
$obj->kelamin="PRIA";
validateType($obj, array("nik"=>"~=3-4","gaji"=>"=1000-2000","tgllahir"=>"date","kelamin"=>"inPRIA,WANITA"), $error);
print_r($error);
$con = openConnection();
$pattern = <<<EOD
{
	"name":"{0}",
	"position": "{1}",
	"branch": {
		"office": "{2}",
		"extn": "{3}"
	},
	"start_date": "{4}",
	"salary": "{5}",
	"id": "{6}",
	"checked": "{7}",
	"status": "{8}"
}
EOD;
$queryBase = queryJsonPatternedValue($con, "select name, position, office, extn, start_date, salary, id, checked, status from employee where id=:id;", array("id"=>7), $pattern);
print_r($queryBase);

echo "test" . isValidDate("2020-01-32", "Y-m-d");

echo isNumber("123");

echo isValidTime("24:01:01");

echo "test" . isValidDateTime("2020-01-19 06:28:01", "Y-m-d H:i:s");

echo "batas";

echo reFormatDate("2020-01-19 06:28:01", "Y-m-d h:i:s", "d/m/Y H:i:s");

echo reFormatYMDHisToDMYHis("2020-01-19 06:28:01");

echo reFormatDMYHisToYMDHis("19/01/2020 06:28:01");

echo reFormatYMDToDMY("2020-01-19");

echo reFormatDMYToYMD("19/01/2020");
*/
?>