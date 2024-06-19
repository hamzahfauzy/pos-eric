<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtSalesOrderList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtSalesOrderList($con, $auth, $nama_customer, $dari_tgl, $sampai_tgl, $limit, $offset) {

$pattern = <<<EOD
{
	"no_order": "{0}",
	"tgl_order": "{1}",
	"id_customer": "{2}",
	"nama_customer": "{3}",
	"telepon": "{4}",
	"id_sales": "{5}",
	"nama_sales": "{6}",
	"keterangan": "{7}",
	"total_order": {8},
	"total_items": {9},
	"status_order": {10} 
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select A.NomorSO, A.TanggalSO, A.ID_Customer, A.NamaCustomer, A.Telepon, A.ID_Sales, A.NamaSales, 
		A.Keterangan, Coalesce(STR(B.TotalOrder), 0) As TotalOrder, Coalesce(B.JlhItems, 0) As TotalItems, 
		A.StatusOrder,  
		ROW_NUMBER() OVER (Order By A.TanggalSO, A.NomorSO) As RowNumber   
	From tblSalesOrder A
		Left Join (Select SUM(Case When Jumlah Is Null Then 0 Else Jumlah End) As TotalOrder, 
			Count(*) As JlhItems, NomorSO From tblSalesOrderDetail Group By NomorSO) B On A.NomorSO = B.NomorSO 
	Where A.NamaCustomer Like :nama 
		And A.TanggalSO >= :daritgl 
		And A.TanggalSO <= :sampaitgl 
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.TanggalSO, Result.NomorSO   
EOD;

	$queryBase = new MtSalesOrderList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("nama"=>"%" . $nama_customer . "%", "daritgl"=>$dari_tgl, "sampaitgl"=>$sampai_tgl), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
