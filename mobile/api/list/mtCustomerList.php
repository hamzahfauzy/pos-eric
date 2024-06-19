<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtCustomerList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtCustomerList($con, $auth, $nama_customer, $limit, $offset) {

$pattern = <<<EOD
{
	"id_customer": "{0}",
	"nama_customer": "{1}",
	"telepon": "{2}",
	"alamat": "{3}",
	"kota": "{4}",
	"level_harga": "{5}",
	"member_id": "{6}",
	"sisa_point": {7},
	"sisa_piutang": {8}
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select ID_Customer, NamaCustomer, Telepon, Alamat, Kota, 
		LevelHarga, MemberID, SisaPoint, STR(SisaPiutang, 25, 2) Sisa_Piutang, ROW_NUMBER() OVER (Order By ID_Customer) As RowNumber 
	From tblCustomer 
	Where NamaCustomer Like :nama
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.ID_Customer  
EOD;

	$queryBase = new MtCustomerList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("nama"=>"%" . $nama_customer . "%"), false);
		//var_dump($retJSON);
		//if ($retJSON == "[]") {
		//	$retJSON = null;
		//}
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
