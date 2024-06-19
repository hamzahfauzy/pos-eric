<?php
include_once "library.php";
include_once "base.php";

class QueryBaseFree extends Base{
	private $con;
	private $sql;
	private $recCount;	
	
	private $groupCount;

	public function __construct($con, $sql) {
		$this->con = $con;
		$temp = explode(";",$sql); $this->sql = trim($temp[0]);
		$this->groupCount = 0;
	}
	
	public function setGroupCount($groupCount) {
		$this->groupCount = $groupCount;
	}
   
    //report level filter
	public function onFilter($currentValues) {
		return true;
	}
	
	//User customization
	public function reconvert($values) {
		$newValues = $values;
		return $newValues;
	}
	
   public function evalGroupExpression($index, $values) {
	  return md5("");
   }	
	
	public function isNewGroup($index, $currentValues, $previousValues) {
		return ($this->evalGroupExpression($index,$currentValues)!=$this->evalGroupExpression($index,$previousValues));
	}

	public function onBeforeGroup($index, $currentValues) {
		return "";
	}
   
	public function onDetail($currentValues) {
		return "detail belum ada implementasi";
	}

	public function onAfterGroup($index, $previousValues) {
		return "";
	}
   
	public function getDataCount() {
		return $this->recCount;
	}
	
//Response ini dibuat terkait dengan adanya data Dummy pada Slot View
	private function generateJSONfromPreparedArray($preparedArray) {
		$retStr = "";

			$previousValues = null;
			for ($n=0;$n<sizeof($preparedArray);$n++) {
				
				$newValues = $this->reconvert($preparedArray[$n]);

				if ($this->onFilter($newValues)) {

					for ($i=$this->groupCount-1;$i>=0;$i--) {
						if($this->isNewGroup($i, $previousValues, $newValues)) {
							if ($previousValues!=null)
								$retStr .= $this->onAfterGroup($i, $previousValues);
						}							
					}
														
					for ($i=0;$i<$this->groupCount;$i++) {
						if($previousValues==null) {
								
							$retStr .= $this->onBeforeGroup($i, $newValues);								
						} elseif ($this->isNewGroup($i, $previousValues, $newValues)) {								
									
							$retStr .= $this->onBeforeGroup($i, $newValues);
						}							
					}
																								
					$retStr .= $this->onDetail($newValues);
					
					$this->recCount++;

				}

				$previousValues=$newValues; //save previous values

			}
				
			if ($previousValues!=null)
				for ($i=$this->groupCount-1;$i>=0;$i--)				
					$retStr .= $this->onAfterGroup($i, $previousValues);											

		return $retStr; 
	}	

//Response
	private function generateJSON($stmt) {
		$retStr = "";
		if ($values = $stmt->fetch(PDO::FETCH_NUM)) {
			$previousValues = null;
			do {
				$newValues = $this->reconvert($values);

				if ($this->onFilter($newValues)) {

					for ($i=$this->groupCount-1;$i>=0;$i--) {
						if($this->isNewGroup($i, $previousValues, $newValues)) {
							if ($previousValues!=null)
								$retStr .= $this->onAfterGroup($i, $previousValues);
						}							
					}
														
					for ($i=0;$i<$this->groupCount;$i++) {
						if($previousValues==null) {
								
							$retStr .= $this->onBeforeGroup($i, $newValues);								
						} elseif ($this->isNewGroup($i, $previousValues, $newValues)) {								
									
							$retStr .= $this->onBeforeGroup($i, $newValues);
						}							
					}
																								
					$retStr .= $this->onDetail($newValues);
					
					$this->recCount++;

				}

				$previousValues=$newValues; //save previous values

			} while ($values = $stmt->fetch(PDO::FETCH_NUM));
				
			if ($previousValues!=null)
				for ($i=$this->groupCount-1;$i>=0;$i--)				
					$retStr .= $this->onAfterGroup($i, $previousValues);											
		}
		return $retStr; 
	}

//PreparedArray
   public function preparedArray($preparedArray, $single=false) {		
	   try {
		   if ($single)
				$strJSON = $this->generateJSONfromPreparedArray($preparedArray);
			else
				$strJSON = "[" . $this->generateJSONfromPreparedArray($preparedArray) . "]";
			
			return $strJSON;					
      } catch (PDOException $e) {
         throw new Exception("0:Exception!: " . $e->getMessage());
      }
   }   

//Query
   public function query($values, $single=false) {		
	   try {
         if (startsWith(strtolower($this->sql),"select")) {
             if (!($stmt = $this->con->prepare($this->sql))) {
                throw new Exception("0:(" . $con->errno . ") " . $con->error);
             } else {
				$paramValues = $values;
				
				foreach ($paramValues as $key=>$value)
					$stmt->bindValue(':'.$key,$value);
 
                $stmt->execute();
	
				if ($single)
					$strJSON = $this->generateJSON($stmt);
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
/*
class MyQueryBaseFree extends QueryBaseFree {
	private $row=array(0,0,0);
	
   public function evalGroupExpression($index, $values) {
	 if ($index==0)	
		return md5($values[0]);
	  else	
		return md5($values[0] . $values[1]);   
   }

   public function onBeforeGroup($index, $currentValues) {
   		$this->row[$index]++;
		if ($index==0) {
			if ($this->row[0]==1)
				return "{\"office\": \"" .  $this->escquote($currentValues[0],false) . "\", \"group\":[";
			else
				return ",{\"office\": \"" .  $this->escquote($currentValues[0],false) . "\", \"group\":[";			
		} else {
			if ($this->row[1]==1)
				return "{\"position\":\"" .  $this->escquote($currentValues[1],false) . "\", \"personel\":[";
			else
				return ",{\"position\":\"" .  $this->escquote($currentValues[1],false) . "\", \"personel\":[";
		}
   }
   
   public function onDetail($currentValues) {
		$this->row[2]++;	   
		if ($this->row[2]==1)
			return "{\"name\":\"" .  $this->escquote($currentValues[2],false) . "\", \"extn\":\"" .  $this->escquote($currentValues[3],false) . "\", \"start_data\":\"" .  $this->escquote($currentValues[4],false) . "\",\"salary\":\"" .  $this->escquote($currentValues[5],false) . "\"}";
		else
			return ",{\"name\":\"" .  $this->escquote($currentValues[2],false) . "\", \"extn\":\"" .  $this->escquote($currentValues[3],false) . "\", \"start_data\":\"" .  $this->escquote($currentValues[4],false) . "\",\"salary\":\"" .  $this->escquote($currentValues[5],false) . "\"}";
   }
   
   public function onAfterGroup($index, $previousValues) {		
		if ($index==0) {
			$this->row[1]=0;		
			$this->row[2]=0;
			return "]}";
		} else {
			$this->row[2]=0;
			return "]}";
		}
   }
   
}

//unit test
$con = openConnection();
//start of employee data query
$sql = <<<EOD
	select office,
		position,
		name,
		extn,
		start_date,
		salary
	from employee order by office, position;
EOD;

$myQBF = new MyQueryBaseFree($con, $sql);
$myQBF->setGroupCount(2);
echo $myQBF->query(array());
*/
?>
