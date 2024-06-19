<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtConfigurasiList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtConfigurasiList($con, $auth) {

$pattern = <<<EOD
{
	"alamat_ippublic": "{0}",
	"nama_database": "{1}",
	"lokasi_server": "{2}",
	"alamat_endpoint": "{2}"
}
EOD;

$sql = <<<EOD
	Select AlamatIPPublic, NamaDatabase, LokasiServer, AlamatEndPoint 
	From tblConfigurasi 
EOD;

	$queryBase = new MtConfigurasiList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array(), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
