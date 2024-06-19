<?php
include_once "library.php";
include_once "base.php";

class CRUD extends base{
	protected $con;
	private $sqlInsert;
	private $sqlUpdate;
	private $sqlDelete;
	private $sqlCancel;
	
	public function __construct($con, $sqlInsert, $sqlUpdate, $sqlDelete) {
		$this->con = $con;
		$temp = explode(";",$sqlInsert); $this->sqlInsert = trim($temp[0]);
		$temp = explode(";",$sqlUpdate); $this->sqlUpdate = trim($temp[0]);
		$temp = explode(";",$sqlDelete); $this->sqlDelete = trim($temp[0]);
		$this->sqlCancel="";
	}
	
	public function setSqlCancel($sqlCancel) {
		$temp = explode(";",$sqlCancel); $this->sqlCancel = trim($temp[0]);
	}

	#memeriksa kelengkapan data, dan
	#kebenaran format data
	public function dataValidation($flag, $obj) { //flag 1=add, 2=update, 3=delete
		//$this->pushError("f1","Pesan kegagalan validasi per field");
		//throw new Exception("Pesan kegagalan validasi");
	}

	public function beforeInsert($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeInsert");
	}
	
	public function reconvert($flag, $obj) {  //flag 1=add, 2=update, 3=delete
		$newObj = $obj;
		//lakukan perubahan maupun penambahan array jika perlu
		//if ($flag==1)
		//	$newObj->creaby = $this->auth->getUserId();
		//else
		//	$newObj->modiby = $this->auth->getUserId();
		return $newObj;
	}	
	
	//newObj adalah hasil pengembalian dari reconvert
	public function doInsert($newObj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doInsert");
		if (!startsWith(strtolower($this->sqlInsert),"insert"))
			throw new Exception("0: Invalid Insert statement!");

		try {
			if (!($stmt = $this->con->prepare($this->sqlInsert))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {
				$paramValues = $newObj;
						
				$debug=$this->sqlInsert;
				
				foreach ($paramValues as $key=>$value) {	
					if (!strpos($this->sqlInsert, ":$key"))
						echo "$key tidak ditemukan pada sqlInsert";
					$debug=str_replace(":".$key,"ok",$debug);
					$stmt->bindValue(':'.$key, $value);						
				}					
			
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: " . $e->getMessage() . $debug);
		}
	}

	public function afterInsert($newObj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterInsert");
	}
	
	public function insert($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//dataValidation digunakan untuk memeriksa kebenaran dan kelengkapan data
			$this->dataValidation(1, $obj);
			//beforeInsert digunakan untuk memeriksa PK dan FK			
			$this->beforeInsert($obj);
			//pada beforeInsert juga dapat dipersiapkan perubahan nilai $obj
			$newObj=$this->reconvert(1, $obj);
			$affected=$this->doInsert($newObj);	
			//pada afterInsert untuk update pada tabel FK
			if ($affected>0)
				$this->afterInsert($newObj);			
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) saved");
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());
		}
	}

	public function beforeUpdate($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeUpdate");
	}
	
	//newObj adalah hasil pengembalian dari beforeUpdate
	public function doUpdate($newObj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doUpdate");
		//print_r($this->sqlUpdate);
		if (!startsWith(strtolower($this->sqlUpdate),"update"))
			throw new Exception("0: Invalid Update statement!");

		try {
			if (!($stmt = $this->con->prepare($this->sqlUpdate))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {				
				$paramValues = $newObj;
				
				$debug=$this->sqlUpdate;
				
				foreach ($paramValues as $key=>$value) {	
					if (!strpos($this->sqlUpdate, ":$key"))
						echo "$key tidak ditemukan pada sqlUpdate";
					$debug=str_replace(":".$key,"ok",$debug);
					$stmt->bindValue(':'.$key, $value);						
				}					
										
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: " . $e->getMessage() . $debug);
		}		
	}	

	public function afterUpdate($newObj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterUpdate");		
	}

	public function update($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//dataValidation digunakan untuk memeriksa kebenaran dan kelengkapan data						
			$this->dataValidation(2, $obj);
			//beforeInsert digunakan untuk memeriksa PK dan FK						
			$this->beforeUpdate($obj);
			//pada beforeInsert juga dapat dipersiapkan perubahan nilai $obj			
			$newObj=$this->reconvert(2, $obj);
			//print_r($newObj);			
			$affected=$this->doUpdate($newObj);
			//pada afterUpdate untuk update pada tabel FK
			if ($affected>0)
				$this->afterUpdate($newObj);
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) updated");
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}

	public function beforeDelete($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeDelete");		
	}
	
	//newObj adalah hasil pengembalian dari beforeUpdate
	public function doDelete($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doDelete");
		if (!startsWith(strtolower($this->sqlDelete),"delete"))
			throw new Exception("0: Invalid Delete statement!");

		try {
			if (!($stmt = $this->con->prepare($this->sqlDelete))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {				
				$paramValues = $obj;
								
				$debug=$this->sqlDelete;
				
				foreach ($paramValues as $key=>$value) {	
					if (!strpos($this->sqlDelete, ":$key"))
						echo "$key tidak ditemukan pada sqlInsert";
					$debug=str_replace(":".$key,"ok",$debug);
					$stmt->bindValue(':'.$key, $value);						
				}								
			
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: " . $e->getMessage() . $debug);
		}		
	}	

	public function afterDelete($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterDelete");		
	}

	public function delete($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//pada beforeDelete dilakukan validasi FK
			$this->beforeDelete($obj);
			$affected=$this->doDelete($obj);
			//pada afterDelete untuk update pada tabel FK
			if ($affected>0)
				$this->afterDelete($obj);
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) deleted");
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}
	
	public function beforeCancel($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeDelete");		
	}
	
	//newObj adalah hasil pengembalian dari beforeUpdate
	public function doCancel($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doDelete");
		if (!startsWith(strtolower($this->sqlCancel),"update"))
			throw new Exception("0: Invalid Update Cancel statement!" . $this->sqlCancel);

		try {
			if (!($stmt = $this->con->prepare($this->sqlCancel))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {				
				$paramValues = $obj;
					
				$debug=$this->sqlCancel;
				
				foreach ($paramValues as $key=>$value) {	
					if (!strpos($this->sqlCancel, ":$key"))
						echo "$key tidak ditemukan pada sqlInsert";
					$debug=str_replace(":".$key,"ok",$debug);
					$stmt->bindValue(':'.$key, $value);						
				}								
			
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: " . $e->getMessage() . $debug);
		}		
	}	

	public function afterCancel($obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterDelete");		
	}

	public function cancel($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//pada beforeCancel dilakukan validasi FK
			$this->beforeCancel($obj);
			$affected=$this->doCancel($obj);
			//pada afterCancel untuk update pada tabel FK
			if ($affected>0)
				$this->afterCancel($obj);
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) cancelled");
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}	
}
//unit test
//entry point aplikasi
/*
if (isset($_POST["flag"])) {
	$flag = $_POST["flag"]; //1=add, 2=edit, 3=hapus, 4 cancel
	$data = json_decode($_POST["data"],false);
} else {
	$flag = 1;
	$data = json_decode("{\"name\":\"hendra\", \"position\":\"ITE\", \"office\":\"medan\", \"extn\":\"123\", \"start_date\":\"2019-11-04\", \"salary\":1000}" ,false);
}	

$con = openConnection();
$sqlInsert = "insert into employee(name, position, office, extn, start_date, salary) values (:name, :position, :office, :extn, :start_date, :salary);";
$sqlUpdate = "update employee set name=:name, position=:position, office=:office, extn=:extn, start_date=:start_date, salary=:salary where id=:id;";
$sqlDelete = "delete from employee where id=:id;";
$sqlCancel = "update employee set status='C' where id=:id;";
$crud = new CRUD($con, $sqlInsert, $sqlUpdate, $sqlDelete);
$crud->setSqlCancel($sqlCancel);

if ($flag==1)
	echo $crud->insert($data);
else if ($flag==2)
	echo $crud->update($data);
else if ($flag==3)
	echo $crud->delete($data);
else
	echo $crud->cancel($data);

//echo $crud->insert(array("name"=>"hendra", "position"=>"ITE", "office"=>"medan", "extn"=>"123", "start_date"=>"2019-11-04", "salary"=>1000));
//echo $crud->update(array("id"=>347, "name"=>"hendra1", "position"=>"ITE", "office"=>"medan", "extn"=>"123", "start_date"=>"2019-11-04", "salary"=>1000));
//echo $crud->delete(array("id"=>347));
echo $crud->cancel(array("id"=>347));
*/
?>

