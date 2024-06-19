<?php
include_once "library.php";
include_once "base.php";

class CRUDHD extends base{
	protected $con;
	private $sqlInsert;
	private $sqlUpdate;
	private $sqlDelete;
	private $sqlCancel;
	
	private $arrPKeyAttributes; //menampung attribut PKey pada header
	private $isPkeyAutoIncrement;// menentukan apakah PKey adalah autoincrement field
	private $arrAttributes; //menampung atribut untuk masing2 detail
	
	private $lastInsertId;
	
	public function __construct($con) {
		$this->con = $con;
		$this->sqlInsert = array();
		$this->sqlUpdate = array();
		$this->sqlDelete = array();
		$this->sqlCancel = array();
		$this->arrAttributes = array();
	}
	
	public function getLastInsertId() {
		return $this->lastInsertId;
	}
	
	public function setPKeyAttributes($arrPKeyAttributes, $isPkeyAutoIncrement=false) {
		$this->arrPKeyAttributes = $arrPKeyAttributes;
		$this->isPkeyAutoIncrement = $isPkeyAutoIncrement;
	}
	
	public function addAttributes($attributes) {
		array_push($this->arrAttributes, $attributes);
	}
	
	public function createPKeyObject($obj) {
		$newObj = clone $obj;
		foreach ($obj as $key=>$value) {
			if (!in_array($key, $this->arrPKeyAttributes))
				unset($newObj->{$key});
			}
		return $newObj;		
	}
	
	//ini adalah array, tergantung sama header & jumlah detail
	public function addSqlInsert($attribute, $sqlInsert) {
		if (in_array($attribute, $this->arrAttributes)) {
			$temp = explode(";",$sqlInsert);
			$this->sqlInsert[$attribute] = $temp[0];
		} else
			throw new Exception("detailAttribute not found on arrDetailAttributes!");
	}
	
	//ini adalah array, tergantung sama header & jumlah detail
	public function addSqlUpdate($attribute, $sqlUpdate) {
		if (in_array($attribute, $this->arrAttributes)) {
			$temp = explode(";",$sqlUpdate);
			$this->sqlUpdate[$attribute] = $temp[0];
		} else
			throw new Exception("detailAttribute not found on arrDetailAttributes!");
	}	
	
	//ini adalah array, tergantung sama header & jumlah detail
	public function addSqlDelete($attribute, $sqlDelete) {
		if (in_array($attribute, $this->arrAttributes)) {
			$temp = explode(";",$sqlDelete);
			$this->sqlDelete[$attribute] = $temp[0];
		} else
			throw new Exception("detailAttribute not found on arrDetailAttributes!");
	}
	
	//ini adalah array, tergantung sama header & jumlah detail
	public function addSqlCancel($attribute, $sqlCancel) {
		if (in_array($attribute, $this->arrAttributes)) {
			$temp = explode(";",$sqlCancel);
			$this->sqlCancel[$attribute] = $temp[0];
		} else
			throw new Exception("detailAttribute not found on arrDetailAttributes!");
	}	

	#memeriksa kelengkapan data, dan kebenaran format data
	public function dataValidation($flag, $attribute, $row, $obj) { //flag 1=add, 2=update, 3=delete
		//$this->pushError("f1","Pesan kegagalan validasi per field");
		//throw new Exception("Pesan kegagalan validasi");
	}

	public function beforeInsert($attribute, $row, $obj) {
		//jika ada kegagalan
		//throw new Exception(""$attribute at $row -> Pesan kegagalan beforeInsert");
	}
	
	public function reconvert($flag, $attribute, $row, $obj) {
		//jika ada kegagalan
		//throw new Exception(""$attribute at $row -> Pesan kegagalan beforeInsert");
		return $obj;
	}	
	
	//newObj adalah hasil pengembalian dari reconvert
	public function doInsert($attribute, $row, $newObj,  $exclude=array()) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doInsert");
		$sql = $this->sqlInsert[$attribute];
		if (!startsWith(strtolower($sql),"insert"))
			throw new Exception("0: Invalid Insert statement($attribute)!");

		try {
			if (!($stmt = $this->con->prepare($sql))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {
				$paramValues = $newObj;
				
				$debug=$sql;
				
				foreach ($paramValues as $key=>$value) {	
					if (!in_array($key, $exclude)) {
						if (!strpos($sql, ":$key"))
							echo "$key tidak ditemukan pada sqlInsert";
						$debug=str_replace(":".$key,"ok",$debug);
						$stmt->bindValue(':'.$key, $value);						
					}
				}
					
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}			
		} catch (PDOException $e) {
			throw new Exception("0: Insert $attribute at $row -> " . $e->getMessage() . "!" . $debug);
		}
	}

	public function afterInsert($attribute, $row, $newObj) {
		//jika ada kegagalan
		//throw new Exception(""$attribute at $row -> Pesan kegagalan afterInsert");
	}
	
	public function insert($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//dataValidation digunakan untuk memeriksa kebenaran dan kelengkapan data
			$this->dataValidation(1, "header", 1, $obj);
			//beforeInsert digunakan untuk memeriksa PK dan FK			
			$this->beforeInsert("header", 1, $obj);
			//pada beforeInsert juga dapat dipersiapkan perubahan nilai $obj
			$newHObj=$this->reconvert(1, "header", 1, $obj);
			$affected=$this->doInsert("header", 1, $newHObj, $this->arrAttributes);	
			
			if ($this->isPkeyAutoIncrement) {
				$this->lastInsertId = $this->con->lastInsertId();

				//update to $newHObj
				foreach ($this->arrPKeyAttributes as $pkey) 
					$newHObj->{$pkey} = $this->lastInsertId;
			}
			
			//pada afterInsert untuk update pada tabel FK
			if ($affected>0)
				$this->afterInsert("header", 1, $newHObj);
					
			//proses masing-masing detail berdasarkan atribut
			for ($i=1;$i<sizeof($this->arrAttributes);$i++) {
				$attribute = $this->arrAttributes[$i];
							
				$arrdtl = $newHObj->{$attribute};
				
				//proses masing-masing obj pada detail
				for ($j=0;$j<sizeof($arrdtl);$j++) {
					$objdtl = $arrdtl[$j];

					foreach ($this->arrPKeyAttributes as $pkey)
						$objdtl->{$pkey} = $newHObj->{$pkey};					
					
					$this->dataValidation(1, $attribute, $j+1, $objdtl);	
					$this->beforeInsert($attribute, $j+1, $objdtl);
					$newDObj=$this->reconvert(1, $attribute, $j+1, $objdtl);

					$affecteddtl=$this->doInsert($attribute, $j+1, $newDObj, array());
					if ($affecteddtl>0)
						$this->afterInsert($attribute, $j+1, $newDObj);					
				}				
			}
			
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) saved", json_encode($newHObj)); //return all header as feedback
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception("Insert -> " . $e->getMessage());
		}
	}

	public function beforeUpdate($attribute, $row, $obj) {
		//untuk details, cara validasi yang benar adalah query kembali dari database
		//jika ada kegagalan
		//throw new Exception("$attribute at $row -> Pesan kegagalan beforeUpdate");
	}
	
	//newObj adalah hasil pengembalian dari beforeUpdate
	public function doUpdate($attribute, $row, $newObj, $exclude=array()) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doUpdate");
		
		if ($attribute=="header") {
			$sql = $this->sqlUpdate[$attribute];
			if (!startsWith(strtolower($sql),"update"))
				throw new Exception("0: $attribute -> Invalid Update statement!");
		} else {
			$sql = $this->sqlInsert[$attribute];
			if (!startsWith(strtolower($sql),"insert"))
				throw new Exception("0: $attribute -> Invalid Insert statement!");			
		}

		try {
			if (!($stmt = $this->con->prepare($sql))) {
				throw new Exception("0:  $attribute  -> $con->error!");
			} else {				
				$paramValues = $newObj;
				
				//print_r($paramValues);
				
				$debug=$sql;
				
				foreach ($paramValues as $key=>$value) {	
					if (!in_array($key, $exclude)) {
						if (!strpos($sql, ":$key"))
							echo "$key tidak ditemukan pada sqlUpdate";
						$debug=str_replace(":".$key,"ok",$debug);
						$stmt->bindValue(':'.$key, $value);						
					}
				}
					
				//$stmt->debugDumpParams();
				
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: Update $attribute at $row -> " . $e->getMessage() . "!" . $debug);
		}		
	}	

	public function afterUpdate($attribute, $row, $newObj) {
		//jika ada kegagalan
		//throw new Exception("$attribute at $row ->Pesan kegagalan afterUpdate");		
	}

	//pada proses update, header adalah update, tetapi detail adalah delete all, baru insert kembali
	public function update($obj) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			//dataValidation digunakan untuk memeriksa kebenaran dan kelengkapan data						
			$this->dataValidation(2, "header", 1, $obj);
			//beforeInsert digunakan untuk memeriksa PK dan FK						
			$this->beforeUpdate("header", 1, $obj);
			//pada beforeInsert juga dapat dipersiapkan perubahan nilai $obj			
			$newHObj=$this->reconvert(2, "header", 1, $obj);
			$affected=$this->doUpdate("header", 1, $newHObj, $this->arrAttributes);
			//pada afterUpdate untuk update pada tabel FK
			if ($affected>0)
				$this->afterUpdate("header", 1, $newHObj);
			
			//buat PKey Object untuk feedback dan hapus details
			$headerKeyObj = $this->createPKeyObject($newHObj);
			
			//proses masing-masing detail berdasarkan atribut
			for ($i=1;$i<sizeof($this->arrAttributes);$i++) {
				$attribute = $this->arrAttributes[$i];
				
				//hapus dulu detail dan baru insert kembali record by record
				$this->beforeDelete($attribute, $headerKeyObj);
				$this->doDelete($attribute, $headerKeyObj);
				$this->afterDelete($attribute, $headerKeyObj);
				
				$arrdtl = $newHObj->{$attribute};
				//proses masing-masing obj pada detail
				for ($j=0;$j<sizeof($arrdtl);$j++) {
					$objdtl = $arrdtl[$j];
					foreach ($this->arrPKeyAttributes as $pkey) {
						$objdtl->{$pkey} = $newHObj->{$pkey};						
					}
					$this->dataValidation(1, $attribute, $j+1, $objdtl);	
					$this->beforeInsert($attribute, $j+1, $objdtl);
					$newDObj=$this->reconvert(1, $attribute, $j+1, $objdtl);
					$affecteddtl=$this->doInsert($attribute, $j+1, $newDObj, array());
					if ($affecteddtl>0)
						$this->afterInsert($attribute, $j+1, $newDObj);
					if ($affected==0 && $affecteddtl!=0) $affected=1;
				}				
			}			
			
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) updated", json_encode($newHObj));  //return all header as feedback
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}
	
	public function beforeDelete($attribute,  $obj) {
		//untuk details, cara validasi yang benar adalah query kembali dari database
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeDelete");		
	}
	
	//newObj adalah hasil pengembalian dari beforeUpdate
	public function doDelete($attribute, $obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doDelete");
		$sql = $this->sqlDelete[$attribute];
		if (!(startsWith(strtolower($sql),"delete") || startsWith(strtolower($sql),"update")))
			throw new Exception("0: Invalid Delete statement!");	
		
		try {
			if (!($stmt = $this->con->prepare($sql))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {				
				$paramValues = $obj;
				//var_dump($obj);
								
				$debug=$sql;
				
				foreach ($paramValues as $key=>$value) {	
					if (!strpos($sql, ":$key"))
						echo "$key tidak ditemukan pada sqlInsert";
					$debug=str_replace(":".$key,"ok",$debug);
					$stmt->bindValue(':'.$key, $value);						
				}
			
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: Delete $attribute -> " . $e->getMessage() . "!" . $debug);
		}		
	}	

	public function afterDelete($attribute, $obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterDelete");		
	}

	public function delete($obj, $isDelete=true) {
		try {
			//menjaga atomicity action
			$this->con->beginTransaction();
			
			$headerKeyObj = $this->createPKeyObject($obj);
			
			//pada beforeDelete dilakukan validasi FK
			if ($isDelete) {
				$this->beforeDelete("header", $headerKeyObj);
			} else {
				$this->beforeDelete("header", $obj);
			}
			
			//proses hapus detail dulu baru header
			for ($i=1;$i<sizeof($this->arrAttributes);$i++) {
				$attribute = $this->arrAttributes[$i];
				
				//hapus dulu detail				
				$this->beforeDelete($attribute, $headerKeyObj);
				$this->doDelete($attribute, $headerKeyObj);
				$this->afterDelete($attribute, $headerKeyObj);			
			}		
			
			if ($isDelete) {
				$affected=$this->doDelete("header", $headerKeyObj);
			} else {
				$affected=$this->doDelete("header", $obj);
			}
			//pada afterDelete untuk update pada tabel FK
			if ($isDelete) {
				$this->afterDelete("header", $headerKeyObj);
			} else {
				$this->afterDelete("header", $obj);
			}
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) deleted", json_encode($headerKeyObj));
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}
	
	public function beforeCancel($attribute,  $obj) {
		//untuk details, cara validasi yang benar adalah query kembali dari database
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan beforeCancel");
	}
	
	//newObj adalah hasil pengembalian dari beforeCancel
	public function doCancel($attribute, $obj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan doCancel");
		
		$sql = $this->sqlCancel[$attribute];
		if ($attribute=="header") {			
			if (!startsWith(strtolower($sql),"update"))
				throw new Exception("0: Invalid Cancel Update statement!");
		} else {
			if (!startsWith(strtolower($sql),"update") && !startsWith(strtolower($sql),"delete"))
				throw new Exception("0: Invalid Cancel Update/Delete statement!");			
		}

		try {
			if (!($stmt = $this->con->prepare($sql))) {
				throw new Exception("0:  (" . $con->errno . ") " . $con->error);
			} else {				
				$paramValues = $obj;
				
				//print_r($paramValues);
				
				foreach ($paramValues as $key=>$value)
						$stmt->bindValue(':'.$key, $value);
					
				//$stmt->debugDumpParams();
				$stmt->execute();
				return $stmt->rowCount();
			}
		} catch (PDOException $e) {
			throw new Exception("0: Cancel $attribute -> " . $e->getMessage() . "!");
		}		
	}	

	public function afterCancel($attribute, $newObj) {
		//jika ada kegagalan
		//throw new Exception("Pesan kegagalan afterUpdate");		
	}

	//pada proses update, header adalah update, tetapi detail adalah delete all, baru insert kembali
	public function cancel($obj, $detail=false) {
		try {
			
			//menjaga atomicity action
			$this->con->beginTransaction();
			
			//buat PKey Object untuk feedback dan hapus details
			$headerKeyObj = $this->createPKeyObject($obj);

			//beforeCancel digunakan untuk memeriksa syarat cancel, misalnya dokumen belum jadi referensi nextStep
			$this->beforeCancel("header", $obj);

			$affected=$this->doCancel("header", $obj);
			//pada afterUpdate untuk update pada tabel FK
			$this->afterCancel("header", $obj);
			
			if ($detail) {
				//proses masing-masing detail berdasarkan atribut
				for ($i=1;$i<sizeof($this->arrAttributes);$i++) {
					$attribute = $this->arrAttributes[$i];
					
					//hapus dulu detail dan baru insert kembali record by record
					$this->beforeCancel($attribute, $headerKeyObj);
					$this->doCancel($attribute, $headerKeyObj);
					$this->afterCancel($attribute, $headerKeyObj);
					
				}
			}
			
			$this->con->commit();
			return $this->responseSucceed("$affected Record(s) cancelled", json_encode($headerKeyObj));
		} catch (Exception $e) {
			$this->con->rollback();
			throw new Exception($e->getMessage());		
		}
	}	
	
}
//unit test
//entry point aplikasi
/*
$texti = <<<EOD
{"nopesanan":"001","tanggal":"2020-01-16","kodedealer":"001","keterangan":"Hendra Soewarno","details":[{"kodeitem":"busi01","keterangan":"Busi Vario 125","qty":11,"harga":12000,"jumlah":132000},{"kodeitem":"busi02","keterangan":"Busi Beat","qty":10,"harga":12000,"jumlah":120000}]}
EOD;

$obj = json_decode($texti);

$con = openConnection();
$crud = new CRUDHD($con);
$crud->addAttributes("header");
$crud->setPKeyAttributes(array("nopesanan"));
$crud->addSqlInsert("header","INSERT INTO pesanan(nopesanan, tanggal, kodedealer, keterangan) values(:nopesanan, :tanggal, :kodedealer, :keterangan);");
$crud->addSqlUpdate("header","UPDATE pesanan set tanggal=:tanggal, kodedealer=:kodedealer, keterangan=:keterangan WHERE nopesanan=:nopesanan;");
$crud->addSqlDelete("header","DELETE FROM pesanan WHERE nopesanan=:nopesanan;");
$crud->addSqlCancel("header","UPDATE pesanan set status='C' WHERE nopesanan=:nopesanan;");
$crud->addAttributes("details");
$crud->addSqlInsert("details","INSERT INTO pesanandtl(nopesanan, kodeitem, keterangan, qty, harga, jumlah) values (:nopesanan, :kodeitem, :keterangan, :qty, :harga, :jumlah);");
$crud->addSqlDelete("details","DELETE FROM pesanandtl WHERE nopesanan=:nopesanan;");
$crud->addSqlCancel("details","UPDATE pesanandtl set qty=0 WHERE nopesanan=:nopesanan;");

//echo $crud->insert($obj);
//echo $crud->update($obj);
//echo $crud->delete($obj);
//echo $crud->cancel($obj);
*/
?>