<?php
include_once "library.php";
include_once "base.php";

class QueryBaseHD2 extends Base{
	public $con;
	private $arrKey;
	private $dtlKey1;
	private $dtlKey2;	
	private $DetailFlag;
	private $patternedHD;
	private $patternedDTL1;
	private $patternedDTL2;
	private $recCount;

	public function __construct($con, $sql, $patternedHD, $patternedDTL1, $patternedDTL2) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);
		#clean pattern
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patternedHD));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patternedHD = implode(" ", $lines);
		
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patternedDTL1));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patternedDTL1 = implode(" ", $lines);
		
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patternedDTL2));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patternedDTL2 = implode(" ", $lines);
		$this->dtlKey1=null;
		$this->dtlKey2=null;
	}
	
	//menentukan key Header, (Dtl1, Dtl2) adalah non array
	public function setKey($arrKey, $dtlKey1=null, $dtlKey2=null) {
		$this->arrKey = $arrKey;
		$this->dtlKey1 = $dtlKey1;
		$this->dtlKey2 = $dtlKey2;
	}
	
	//menentukan posisi kolom yang menunjukan angka 1, 2, dan 3
	public function setDetailFlag($detailFlag) {
		$this->detailFlag=$detailFlag;
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
	
	private function generateJSONHDR($newRow, $valueDTL1, $valueDTL2) {
		$newObj = $this->patternedHD;

		for ($i=0; $i<sizeof($newRow);$i++) {
			if (strpos($newObj,"{". $i . "}")>-1) 
				$newObj = str_replace("{" . $i . "}",$this->escquote($newRow[$i], false), $newObj);
		}
		
		$newObj = str_replace("{details1}", $valueDTL1, $newObj);
		$newObj = str_replace("{details2}", $valueDTL2, $newObj);
				
		return $newObj;		
	}
	
	private function generateJSONDTL($newRow, $patternedDTL) {
		
		$newObj = $patternedDTL;
				
		for ($i=0; $i<sizeof($newRow);$i++) {
			if (strpos($newObj,"{". $i . "}")>-1) 
				$newObj = str_replace("{" . $i . "}",$this->escquote($newRow[$i], false), $newObj);
		}
				
		return $newObj; 
	}

	private function generateJSON($stmt) {
		//$retStr = "[";
		$retStr = "";
		
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$this->recCount = 0;
			$dtlJSON1 = "";
			$dtlJSON2 = "";
	
			do {
				$oldRow = $this->reconvert($row);
							
				if ($oldRow[$this->detailFlag]==1) {
					
					if ($this->dtlKey1==null) {				
						if ($dtlJSON1 == "")
							$dtlJSON1 = $this->generateJSONDTL($oldRow, $this->patternedDTL1);
						else
							$dtlJSON1 .=  "," . $this->generateJSONDTL($oldRow, $this->patternedDTL1);
					}
					else if ($oldRow[$this->dtlKey1]!=null) {
						if ($dtlJSON1 == "")
							$dtlJSON1 = $this->generateJSONDTL($oldRow, $this->patternedDTL1);
						else
							$dtlJSON1 .=  "," . $this->generateJSONDTL($oldRow, $this->patternedDTL1);						
					}
				} else {
					if ($this->dtlKey2==null) {
						if ($dtlJSON2 == "")
							$dtlJSON2 = $this->generateJSONDTL($oldRow, $this->patternedDTL2);
						else
							$dtlJSON2 .=  "," . $this->generateJSONDTL($oldRow, $this->patternedDTL2);
					}
					else if ($oldRow[$this->dtlKey2]!=null) {
						if ($dtlJSON2 == "")
							$dtlJSON2 = $this->generateJSONDTL($oldRow, $this->patternedDTL2);
						else
							$dtlJSON2 .=  "," . $this->generateJSONDTL($oldRow, $this->patternedDTL2);						
					}
				}
				
				//generate key values
				$arrPrevious = $this->genValueKey($oldRow, $this->arrKey);
				
				if (!$row = $stmt->fetch(PDO::FETCH_NUM)) {
					if ($this->recCount < 1) {
						$retStr .= $this->generateJSONHDR($oldRow, $dtlJSON1, $dtlJSON2);
					}
					else {
						$retStr .= "," . $this->generateJSONHDR($oldRow, $dtlJSON1, $dtlJSON2);
					}
					break;					
				} else {
					
					$newRow = $this->reconvert($row);
										
					$arrNow = $this->genValueKey($newRow,  $this->arrKey); //generate header key
					
					if (!$this->compareValueKey($arrNow, $arrPrevious)) {
						
						if ($this->recCount < 1) {
							$retStr .= $this->generateJSONHDR($oldRow, $dtlJSON1, $dtlJSON2);
						}
						else {
							$retStr .= "," . $this->generateJSONHDR($oldRow, $dtlJSON1, $dtlJSON2);
						}
						
						$this->recCount++;
						$dtlJSON1 = "";
						$dtlJSON2 = "";
					}
				}
			} while (true);			
		}
		//$retStr = $retStr . "]";
		$retStr = $retStr . "";
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
	"parts": [{details1}],
	"kk": [{details2}]
}
EOD;

$patternDTL1 = <<<EOD
{
	"kode":"{6}",
	"keterangan": "{7}",
	"qty": {8},
	"harga": {9},
	"jumlah":  {10}
}
EOD;

$patternDTL2 = <<<EOD
{
	"nik":"{11}",
	"nama": "{12}",
	"kelamin": "{13}",
	"tgllahir": "{14}",
	"pekerjaan":  "{15}"
}
EOD;


$sql = <<<EOD
select * from (

select a.nopesanan, a.tanggal, a.keterangan, a.total, a.creatime,
	1 as jenis, b.kodeitem as dt11, b.keterangan as dt12, b.qty as dt13, b.harga as dt14, b.jumlah as dt15,
				null as dt21, null as dt22, null as dt23, null as dt24, null as dt25
	from pesanan a inner join pesanandtl b on a.nopesanan = b.nopesanan
	
	union
	
select a.nopesanan, a.tanggal, a.keterangan, a.total, a.creatime,
	2 as jenis, null as dt11, null as dt12, null as dt13, null as dt14, null as dt15,
				c.nik as dt21, c.nama as dt22, c.kelamin as dt23, c.tgllahir as dt24, c.pekerjaan as dt25
	from pesanan a inner join pesanankk c on a.nopesanan = c.nopesanan
) x order by x.nopesanan, x.jenis
EOD;

$queryBaseHD2 = new QueryBaseHD2($con, $sql, $patternHD, $patternDTL1, $patternDTL2);
$queryBaseHD2->setKey(array(0), 6, 11); //kolom PK pada header, DTL1, DTL2 (6=kode, 11=NIK)
$queryBaseHD2->setDetailFlag(5); //5=kolom jenis

try {	
	$retJSON = $queryBaseHD2->query(array());
	echo $queryBase->responseSucceedPrepared(array("succeess"), $retJSON);
}
catch (Exception $e) {
	echo $queryBase->responseFailed(array("Failed", $e->getMessage()));	
}
*/
?>
