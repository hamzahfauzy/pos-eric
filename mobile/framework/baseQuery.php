<?php
include_once "library.php";
include_once "base.php";

class QueryBase extends Base{
	private $con;
	private $sql;
	private $arrPosition;
	private $arrMask;
	private $arrQuote;
	private $recCount;	

	public function __construct($con, $sql) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);		
	}

	public function setPosition($arrPosition) {
		$this->arrPosition = $arrPosition;
	}
	
	public function setMask($arrMask) {
		$this->arrMask = $arrMask;
	}
	
	public function setQuote($arrQuote) {
		$this->arrQuote	= $arrQuote;
	}
	
	public function getDataCount() {
		return $this->recCount;
	}

//User customization
	public function reconvert($row) {
		return $row;
	}
	
//Response
	private function generateJSON($stmt) {
		$retStr = "[";
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$this->recCount = 0;
			do {
				
				if ($this->recCount < 1)
					$retStr .= "{";
				else
					$retStr .= ",{";
				
				$newRow = $this->reconvert($row);
				
				for ($i=0; $i<sizeof($this->arrPosition);$i++) {
					$element = $this->escquote($this->arrMask[$i], true) . ":" . $this->escquote($newRow[$this->arrPosition[$i]], $this->arrQuote[$i]);
					if ($i < 1)		
						$retStr .= $element;
					else
						$retStr .= "," . $element;
				}				
							
				$retStr .= "}";
				
				$this->recCount++;
			} while ($row = $stmt->fetch(PDO::FETCH_NUM));		
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
$queryBase = new QueryBase($con, "select kode, tanggal, jumlah, creatime from testdata where kode=?;");
$queryBase->setPosition(array(0, 1, 2, 3));
$queryBase->setMask(array("kode1","tanggal1","jumlah1","creatime1"));
$queryBase->setQuote(array(true, true, false, true));
try {	
	$retJSON = $queryBase->query(array('001'));
	echo $queryBase->responseSucceed($retJSON);
}
catch (Exception $e) {
	echo $queryBase->responseFailed("Failed", $e->getMessage());	
}
*/
/*
class QueryBaseEnch extends QueryBase {

//Overide user customization
	public function reconvert($row) {
		return array($row[0], $row[1], $row[2]/3, $row[3]);
	}	
}

$con = openConnection();
$queryBase = new QueryBaseEnch($con, "select kode, tanggal, jumlah, creatime from testdata where kode=?;");
$queryBase->setPosition(array(0, 1, 2, 3));
$queryBase->setMask(array("kode2","tanggal2","jumlah2","creatime2"));
$queryBase->setQuote(array(true, true, false, true));
try {	
	$retJSON = $queryBase->query(array('001'));
	echo $queryBase->responseSucceed($retJSON);
}
catch (Exception $e) {
	echo $queryBase->responseFailed("Failed", $e->getMessage());	
}
*/
?>
