<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtSalesList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtSalesList($con, $auth, $nama_karyawan, $limit, $offset) {

$pattern = <<<EOD
{
	"id_sales": "{0}",
	"nama_sales": "{1}"
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select ID_Sales, NamaSales, ROW_NUMBER() OVER (Order By ID_Sales) As RowNumber 
	From tblSales 
	Where NamaSales Like :nama
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.ID_Sales   
EOD;

	$queryBase = new MtSalesList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("nama"=>"%" . $nama_karyawan . "%"), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
