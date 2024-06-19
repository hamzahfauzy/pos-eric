<?php
include_once "library.php";
include_once "base.php";

class QueryBasePatterned extends Base{
	private $con;
	private $sql;
	private $patterned;
	private $recCount;	

	public function __construct($con, $sql, $patterned) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);		
		#clean pattern
		$temp = str_replace("\t","",str_replace("\r\n","\n", $patterned));
		$lines = explode("\n",$temp);
		for ($i=0;$i<sizeof($lines);$i++)
			$lines[$i] = trim($lines[$i]);
		$this->patterned = implode(" ", $lines);
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
		$retStr = "";
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$this->recCount = 0;
			do {
				
				if ($this->recCount < 1)
					$retStr .= "";
				else
					$retStr .= ",";
				
				$newRow = $this->reconvert($row);
				
				$newObj = $this->patterned;
				
				for ($i=0; $i<sizeof($newRow);$i++) {			
					if (strpos($newObj,"{" . $i  . "}")>-1) 
						$newObj = str_replace("{" . $i . "}",$this->escquote($newRow[$i], false), $newObj);
				}
							
				$retStr .= $newObj;
				
				$this->recCount++;
			} while ($row = $stmt->fetch(PDO::FETCH_NUM));		
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
$queryBase = new QueryBasePatterned($con, "select name, position, office, extn, start_date, salary, id, checked, status from employee where id=:id;", $pattern);
try {	
	$retJSON = $queryBase->query(array("id"=>0), true);
	echo $queryBase->responseSucceedPrepared(array("succeess"), $retJSON);
}
catch (Exception $e) {
	echo $queryBase->responseFailed(array("Failed", $e->getMessage()));	
}
*/
?>
