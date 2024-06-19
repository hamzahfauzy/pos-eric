<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtKategoriList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtKategoriList($con, $auth, $nama_kategori, $limit, $offset) {

$pattern = <<<EOD
{
	"id_kategori": "{0}",
	"nama_kategori": "{1}",
	"kode_kategori": "{2}"
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select ID_KategoriItem, NamaKategori, KodeKategori, ROW_NUMBER() OVER (Order By ID_KategoriItem) As RowNumber   
	From tblKategoriItem 
	Where NamaKategori Like :nama 
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.ID_KategoriItem   
EOD;

	$queryBase = new MtKategoriList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("nama"=>"%" . $nama_kategori . "%"), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
