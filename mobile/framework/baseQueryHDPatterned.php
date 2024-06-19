<?php
include_once "library.php";
include_once "base.php";

class QueryBaseHDPatterned extends Base{
	public $con;
	private $arrKey;
	private $dtlKey;	
	private $patternedHD;
	private $patternedDTL;
	private $recCount;

	public function __construct($con, $sql, $patternedHD, $patternedDTL) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);
		#clean pattern
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patternedHD));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patternedHD = implode(" ", $lines);
		
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patternedDTL));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patternedDTL = implode(" ", $lines);
		$this->dtlKey=null;
	}
	
	public function setKey($arrKey, $dtlKey=null) {
		$this->arrKey = $arrKey;
		$this->dtlKey=$dtlKey;
	}
	
	public function getDataCount() {
		return $this->recCount;
	}
	
	
//User customization
	public function reconvert($row) {
		return $row;
	}
	
//Response
	//generate key values
	
	public function genValueKey($newRow, $arrKey) {
		$retVal = array();
		foreach ($arrKey as $col) {
			array_push($retVal, $newRow[$col]);
		}
		return $retVal;	   
	}
   
	//compare key values
	public function compareValueKey($arrNow, $arrPrevious) {
		$diff = array_diff($arrNow, $arrPrevious);	
		return (sizeof($diff)<1);	   
	}
	
	private function generateJSONHDR($newRow, $valueDTL) {
		$newObj = $this->patternedHD;

		for ($i=0; $i<sizeof($newRow);$i++) {
			if (strpos($newObj,"{". $i . "}")>-1) 
				$newObj = str_replace("{" . $i . "}",$this->escquote($newRow[$i], false), $newObj);
		}
		
		$newObj = str_replace("{details}", $valueDTL, $newObj);
				
		return $newObj;
	}
	
	private function generateJSONDTL($newRow) {
		$newObj = $this->patternedDTL;
				
		for ($i=0; $i<sizeof($newRow);$i++) {
			if (strpos($newObj,"{". $i . "}")>-1) 
				$newObj = str_replace("{" . $i . "}",$this->escquote($newRow[$i], false), $newObj);
		}
				
		return $newObj; 
	}

	private function generateJSON($stmt) {
		$retStr = "";
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$this->recCount = 0;
			$dtlJSON = "";			
			do {
				$oldRow = $this->reconvert($row);
				
				if ($this->dtlKey==null) {
							
					if ($dtlJSON == "")
						$dtlJSON = $this->generateJSONDTL($oldRow);
					else
						$dtlJSON .=  "," . $this->generateJSONDTL($oldRow);
				}
				else if ($oldRow[$this->dtlKey]!=null) {
					
					if ($dtlJSON == "")
						$dtlJSON = $this->generateJSONDTL($oldRow);
					else
						$dtlJSON .=  "," . $this->generateJSONDTL($oldRow);					
				}
						
				//generate key values
				$arrPrevious = $this->genValueKey($oldRow, $this->arrKey);
				
				if (!$row = $stmt->fetch(PDO::FETCH_NUM)) {
					if ($this->recCount < 1) {
						$retStr .= $this->generateJSONHDR($oldRow, $dtlJSON);
					}
					else {
						$retStr .= "," . $this->generateJSONHDR($oldRow, $dtlJSON);
					}
					break;					
				} else {
					
					$newRow = $this->reconvert($row);
					
					$arrNow = $this->genValueKey($newRow, $this->arrKey); //generate header key
					
					if (!$this->compareValueKey($arrNow, $arrPrevious)) {
						
						if ($this->recCount < 1) {
							$retStr .= $this->generateJSONHDR($oldRow, $dtlJSON);
						}
						else {
							$retStr .= "," . $this->generateJSONHDR($oldRow, $dtlJSON);
						}
						
						$this->recCount++;
						$dtlJSON = "";
					}
				}
			} while (true);
		}
		$retStr = $retStr;
		return $retStr; 
	}

//Query
   public function query($values, $single=false, $singleNullReplace="null") {
	   try {
         if (startsWith(strtolower($this->sql),"select")) {
             if (!($stmt = $this->con->prepare($this->sql))) {
                throw new Exception("0:(" . $con->errno . ") " . $con->error);
             } else {
				 
				$paramValues = $values;
				
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);
 
                $stmt->execute();
				
				if ($single) {
					$strJSON = $this->generateJSON($stmt);
					if ($strJSON=="")
						$strJSON = $singleNullReplace;
				}
				else
					$strJSON = "[" . $this->generateJSON($stmt) . "]";
				
				return $strJSON;
				
             }
         } else {
            throw new Exception("0:Exception!: Invalid Query " . $strSQL);
         }
      } catch (PDOException $e) {
         throw new Exception("0:Exception!: " . $e->getMessage());
      }
   }
}
 //unit test
 /*
$con = openConnection();
$patternHD = <<<EOD
{
	"nodoc":"{0}",
	"tanggal": "{1}",
	"keterangan": "{2}",
	"total": "{3}",
	"parts": [{details}]
}
EOD;
/*
$patternDTL = <<<EOD
{
	"kode":"{5}",
	"keterangan": "{6}",
	"qty": {7},
	"harga": {8},
	"jumlah":  {9}
}
EOD;
*/
/*
$patternDTL = <<<EOD
"{5}"
EOD;

$queryBaseHD = new QueryBaseHDPatterned($con, "select a.nopesanan, a.tanggal, a.keterangan, a.total, a.creatime, b.kodeitem, b.keterangan, b.qty, b.harga, b.jumlah from pesanan a inner join pesanandtl b on a.nopesanan=b.nopesanan;", $patternHD, $patternDTL);
$queryBaseHD->setKey(array(0));

try {	
	$retJSON = $queryBaseHD->query(array());
	echo $queryBaseHD->responseSucceedPrepared(array("succeess"), $retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD->responseFailed(array("Failed", $e->getMessage()));	
}
*/
/*
$con = openConnection();
$queryBaseHD = new QueryBaseHD($con, "select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime, b.kode, b.keterangan, b.qty, b.harga, b.jumlah, b.id from testheader a inner join testdetails b on a.nodoc=b.nodoc where a.nodoc=?;");
$queryBaseHD->setKey(array(0));
$queryBaseHD->setMaskHD(array("nodoc","tanggal","keterangan","total","creatime"));
$queryBaseHD->setPositionHD(array(0,1,2,3,4));
$queryBaseHD->setQuoteHD(array(true, true, true, false, true));
$queryBaseHD->setAttributeDTL("parts");
$queryBaseHD->setMaskDTL(array("kode","keterangan","qty","harga","jumlah","id"));
$queryBaseHD->setPositionDTL(array(5,6,7,8,9,10));
$queryBaseHD->setQuoteDTL(array(true, true, false, false, false, false));
try {	
	$retJSON = $queryBaseHD->query(array("001"));
	echo $queryBaseHD->responseSucceedPrepared(array("succeess"), $retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD->responseFailed(array("Failed", $e->getMessage()));	
}
*/
/*
class QueryBaseHDEnch extends QueryBaseHD {

//Overide user customization
	public function reconvert($row) {
		return array($row[0], $row[1], $row[2], $row[3]*3,$row[4], $row[5], $row[6], $row[7]*3,$row[8], $row[9], $row[10]);
	}	
}

$con = openConnection();
$queryBaseHD = new QueryBaseHDEnch($con, "select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime, b.kode, b.keterangan, b.qty, b.harga, b.jumlah, b.id from testheader a inner join testdetails b on a.nodoc=b.nodoc where a.nodoc=?;");
$queryBaseHD->setKey(array(0));
$queryBaseHD->setMaskHD(array("nodoc","tanggal","keterangan","total","creatime"));
$queryBaseHD->setPositionHD(array(0,1,2,3,4));
$queryBaseHD->setQuoteHD(array(true, true, true, false, true));
$queryBaseHD->setAttributeDTL("parts");
$queryBaseHD->setMaskDTL(array("kode","keterangan","qty","harga","jumlah","id"));
$queryBaseHD->setPositionDTL(array(5,6,7,8,9,10));
$queryBaseHD->setQuoteDTL(array(true, true, false, false, false, false));
try {	
	$retJSON = $queryBaseHD->query(array("001"));
	echo $queryBaseHD->responseSucceedPrepared(array("succeess"), $retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD->responseFailed(array("Failed", $e->getMessage()));	
}
*/
?>
