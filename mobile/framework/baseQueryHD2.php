<?php
include_once "library.php";
include_once "base.php";

class QueryBaseHD2 extends Base{
	public $con;
	private $arrKey;
	private $dtlKey1;
	private $dtlKey2;	
	private $arrPositionHD;
	private $arrMaskHD;
	private $arrQuoteHD;
	private $DetailFlag;
	private $attrDTL1;
	private $arrPositionDTL1;
	private $arrMaskDTL1;
	private $arrQuoteDTL1;
	private $attrDTL2;
	private $arrPositionDTL2;
	private $arrMaskDTL2;
	private $arrQuoteDTL2;
	private $recCount;

	public function __construct($con, $sql) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);
		$this->dtlKey1=null;
		$this->dtlKey2=null;		
	}
	
	//menentukan key Header, Dtl1, Dtl2
	public function setKey($arrKey, $dtlKey1=null, $dtlKey2=null) {
		$this->arrKey = $arrKey;
		$this->dtlKey1 = $dtlKey1;
		$this->dtlKey2 = $dtlKey2;
	}	
	
	public function setPositionHD($arrPosition) {
		$this->arrPositionHD = $arrPosition;
	}
	
	public function setMaskHD($arrMask) {
		$this->arrMaskHD = $arrMask;
	}
	
	public function setQuoteHD($arrQuote) {
		$this->arrQuoteHD = $arrQuote;
	}
	
	public function setDetailFlag($detailFlag) {
		$this->detailFlag=$detailFlag;
	}
	
	public function setAttributeDTL1($attrDTL) {
		$this->attrDTL1 = $attrDTL;
	}
	
	public function setPositionDTL1($arrPosition) {
		$this->arrPositionDTL1 = $arrPosition;
	}
	
	public function setMaskDTL1($arrMask) {
		$this->arrMaskDTL1 = $arrMask;
	}
	
	public function setQuoteDTL1($arrQuote) {
		$this->arrQuoteDTL1 = $arrQuote;
	}			
	
	public function setAttributeDTL2($attrDTL) {
		$this->attrDTL2 = $attrDTL;
	}
	
	public function setPositionDTL2($arrPosition) {
		$this->arrPositionDTL2 = $arrPosition;
	}
	
	public function setMaskDTL2($arrMask) {
		$this->arrMaskDTL2 = $arrMask;
	}
	
	public function setQuoteDTL2($arrQuote) {
		$this->arrQuoteDTL2 = $arrQuote;
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
		$retStr = "{";
				
		for ($i=0; $i<sizeof($this->arrPositionHD);$i++) {
			$element = $this->escquote($this->arrMaskHD[$i], true) . ":" . $this->escquote($newRow[$this->arrPositionHD[$i]], $this->arrQuoteHD[$i]);
			if ($i < 1)		
				$retStr .= $element;
			else
				$retStr .= "," . $element;
		}
		
		$retStr .= "," . $this->escquote($this->attrDTL1, true) . ":[" . $valueDTL1 . "]";
		
		$retStr .= "," . $this->escquote($this->attrDTL2, true) . ":[" . $valueDTL2 . "]";
				
		$retStr .= "}";				
		return $retStr;
	}
	
	private function generateJSONDTL($newRow, $arrMaskDTL, $arrPositionDTL, $arrQuoteDTL) {
		$retStr = "{";
				
		for ($i=0; $i<sizeof($arrPositionDTL);$i++) {
			$element = $this->escquote($arrMaskDTL[$i], true) . ":" . $this->escquote($newRow[$arrPositionDTL[$i]], $arrQuoteDTL[$i]);
			if ($i < 1)		
				$retStr .= $element;
			else
				$retStr .= "," . $element;
		}
				
		$retStr .= "}";
				
		return $retStr; 
	}

	private function generateJSON($stmt) {
		$retStr = "[";
		
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$this->recCount = 0;
			$dtlJSON1 = "";
			$dtlJSON2 = "";
	
			do {
				$oldRow = $this->reconvert($row);
							
				if ($oldRow[$this->detailFlag]==1) {
					
					if ($this->dtlKey1==null) {	
				
						if ($dtlJSON1 == "")
							$dtlJSON1 = $this->generateJSONDTL($oldRow, $this->arrMaskDTL1, $this->arrPositionDTL1, $this->arrQuoteDTL1);
						else
							$dtlJSON1 .=  "," . $this->generateJSONDTL($oldRow, $this->arrMaskDTL1, $this->arrPositionDTL1, $this->arrQuoteDTL1);
						
					}
					else if ($oldRow[$this->dtlKey1]!=null) {

						if ($dtlJSON1 == "")
							$dtlJSON1 = $this->generateJSONDTL($oldRow, $this->arrMaskDTL1, $this->arrPositionDTL1, $this->arrQuoteDTL1);
						else
							$dtlJSON1 .=  "," . $this->generateJSONDTL($oldRow, $this->arrMaskDTL1, $this->arrPositionDTL1, $this->arrQuoteDTL1);						
						
					}
					
				} else {
					
					if ($this->dtlKey2==null) {
					
						if ($dtlJSON2 == "")
							$dtlJSON2 = $this->generateJSONDTL($oldRow, $this->arrMaskDTL2, $this->arrPositionDTL2, $this->arrQuoteDTL2);
						else
							$dtlJSON2 .=  "," . $this->generateJSONDTL($oldRow, $this->arrMaskDTL2, $this->arrPositionDTL2, $this->arrQuoteDTL2);
						
					}					
					else if ($oldRow[$this->dtlKey2]!=null) {
						
						if ($dtlJSON2 == "")
							$dtlJSON2 = $this->generateJSONDTL($oldRow, $this->arrMaskDTL2, $this->arrPositionDTL2, $this->arrQuoteDTL2);
						else
							$dtlJSON2 .=  "," . $this->generateJSONDTL($oldRow, $this->arrMaskDTL2, $this->arrPositionDTL2, $this->arrQuoteDTL2);						
						
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
		$retStr = $retStr . "]";
		return $retStr; 
	}

//Query
   public function query($values) {
	   try {
         if (startsWith(strtolower($this->sql),"select")) {
             if (!($stmt = $this->con->prepare($this->sql))) {
                throw new Exception("0:(" . $con->errno . ") " . $con->error);
             } else {
				 
				$paramValues = $values;
				
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);
 
                $stmt->execute();
				
				$strJSON = $this->generateJSON($stmt);
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
$sql = <<<EOD
select * from (

select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	1 as jenis, b.kode as dt11, b.keterangan as dt12, b.qty as dt13, b.harga as dt14, b.jumlah as dt15, b.id as dt16,
				null as dt21, null as dt22, null as dt23, null as dt33
	from testheader a inner join testdetails b on a.nodoc = b.nodoc
	
	union
	
select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	2 as jenis, null as dt11, null as dt12, null as dt13, null as dt14, null as dt15, null as dt16,
				c.kode as dt21, c.keterangan as dt22, c.harga as dt23, c.dtl2id as dt33
	from testheader a inner join testdetails2 c on a.nodoc = c.nodoc
) x order by x.nodoc, x.jenis
EOD;

$queryBaseHD2 = new QueryBaseHD2($con, $sql);
$queryBaseHD2->setKey(array(0), 6, 11); //Header, DTL1, DTL2
$queryBaseHD2->setMaskHD(array("nodoc","tanggal","keterangan","total","creatime"));
$queryBaseHD2->setPositionHD(array(0,1,2,3,4));
$queryBaseHD2->setQuoteHD(array(true, true, true, false, true));
$queryBaseHD2->setDetailFlag(5); //5=kolom jenis
$queryBaseHD2->setAttributeDTL1("parts");
$queryBaseHD2->setMaskDTL1(array("kode","keterangan","qty","harga","jumlah","id"));
$queryBaseHD2->setPositionDTL1(array(6,7,8,9,10,11));
$queryBaseHD2->setQuoteDTL1(array(true, true, false, false, false, false));
$queryBaseHD2->setAttributeDTL2("jasa");
$queryBaseHD2->setMaskDTL2(array("kode","keterangan","harga","id"));
$queryBaseHD2->setPositionDTL2(array(12,13,14,15));
$queryBaseHD2->setQuoteDTL2(array(true, true, false, false));

try {	
	$retJSON = $queryBaseHD2->query(array());
	echo $queryBaseHD2->responseSucceed($retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD2->responseFailed("Failed", $e->getMessage());	
}
*/
/*
$con = openConnection();
$sql = <<<EOD
select * from (

select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	1 as jenis, b.kode as dt11, b.keterangan as dt12, b.qty as dt13, b.harga as dt14, b.jumlah as dt15, b.id as dt16,
				null as dt21, null as dt22, null as dt23, null as dt33
	from testheader a inner join testdetails b on a.nodoc = b.nodoc where a.nodoc=:1
	
	union
	
select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	2 as jenis, null as dt11, null as dt12, null as dt13, null as dt14, null as dt15, null as dt16,
				c.kode as dt21, c.keterangan as dt22, c.harga as dt23, c.dtl2id as dt33
	from testheader a inner join testdetails2 c on a.nodoc = c.nodoc where a.nodoc=:1
) x order by x.nodoc, x.jenis
EOD;
$queryBaseHD2 = new QueryBaseHD2($con, $sql);
$queryBaseHD2->setKey(array(0), 6, 11); //Header, DTL1, DTL2
$queryBaseHD2->setMaskHD(array("nodoc","tanggal","keterangan","total","creatime"));
$queryBaseHD2->setPositionHD(array(0,1,2,3,4));
$queryBaseHD2->setQuoteHD(array(true, true, true, false, true));
$queryBaseHD2->setDetailFlag(5); //5=kolom jenis
$queryBaseHD2->setAttributeDTL1("parts");
$queryBaseHD2->setMaskDTL1(array("kode","keterangan","qty","harga","jumlah","id"));
$queryBaseHD2->setPositionDTL1(array(6,7,8,9,10,11));
$queryBaseHD2->setQuoteDTL1(array(true, true, false, false, false, false));
$queryBaseHD2->setAttributeDTL2("jasa");
$queryBaseHD2->setMaskDTL2(array("kode","keterangan","harga","id"));
$queryBaseHD2->setPositionDTL2(array(12,13,14,15));
$queryBaseHD2->setQuoteDTL2(array(true, true, false, false));

try {	
	$retJSON = $queryBaseHD2->query(array("001"));
	echo $queryBaseHD2->responseSucceed($retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD2->responseFailed("Failed", $e->getMessage());	
}
*/
/*
class QueryBaseHD2Ench extends QueryBaseHD2 {

//Overide user customization
	public function reconvert($row) {
		return array($row[0], $row[1], $row[2], $row[3]/3, $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13], $row[14], $row[15]);
		//var_dump($row);
		//return $row;
	}	
}

$con = openConnection();

$sql = <<<EOD
select * from (

select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	1 as jenis, b.kode as dt11, b.keterangan as dt12, b.qty as dt13, b.harga as dt14, b.jumlah as dt15, b.id as dt16,
				null as dt21, null as dt22, null as dt23, null as dt33
	from testheader a inner join testdetails b on a.nodoc = b.nodoc
	
	union
	
select a.nodoc, a.tanggal, a.keterangan, a.total, a.creatime,
	2 as jenis, null as dt11, null as dt12, null as dt13, null as dt14, null as dt15, null as dt16,
				c.kode as dt21, c.keterangan as dt22, c.harga as dt23, c.dtl2id as dt33
	from testheader a inner join testdetails2 c on a.nodoc = c.nodoc
) x order by x.nodoc, x.jenis
EOD;

$queryBaseHD2 = new QueryBaseHD2Ench($con, $sql);
$queryBaseHD2->setKey(array(0), 6, 11); //Header, DTL1, DTL2
$queryBaseHD2->setMaskHD(array("nodoc","tanggal","keterangan","total","creatime"));
$queryBaseHD2->setPositionHD(array(0,1,2,3,4));
$queryBaseHD2->setQuoteHD(array(true, true, true, false, true));
$queryBaseHD2->setDetailFlag(5); //5=kolom jenis
$queryBaseHD2->setAttributeDTL1("parts");
$queryBaseHD2->setMaskDTL1(array("kode","keterangan","qty","harga","jumlah","id"));
$queryBaseHD2->setPositionDTL1(array(6,7,8,9,10,11));
$queryBaseHD2->setQuoteDTL1(array(true, true, false, false, false, false));
$queryBaseHD2->setAttributeDTL2("jasa");
$queryBaseHD2->setMaskDTL2(array("kode","keterangan","harga","id"));
$queryBaseHD2->setPositionDTL2(array(12,13,14,15));
$queryBaseHD2->setQuoteDTL2(array(true, true, false, false));

try {	
	$retJSON = $queryBaseHD2->query(array("001"));
	echo $queryBaseHD2->responseSucceed($retJSON);
}
catch (Exception $e) {
	echo $queryBaseHD2->responseFailed("Failed", $e->getMessage());	
}
*/
?>
