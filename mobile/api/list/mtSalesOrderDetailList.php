<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtSalesOrderDetailList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtSalesOrderDetailList($con, $auth, $nomorso) {

$pattern = <<<EOD
{
	"dtl_id": {0},
	"no_order": "{1}",
	"barcode_number": "{2}",
	"id_item": "{3}",
	"id_itemdetail": "{4}",
	"kode_item": "{5}",
	"nama_item": "{6}",
	"id_kategori": "{7}",
	"nama_kategori": "{8}",
	"harga_satuan": {9},
	"qty_order": {10}, 
	"satuan": "{11}", 
	"persen_diskon": {12}, 
	"nominal_diskon": {13}, 
	"jumlah": {14} 
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select A.Dtlid, A.NomorSO, A.BarcodeNumber, A.ID_Item, A.ID_ItemDetail, A.KodeItem, A.NamaItem, A.ID_KategoriItem, 
		A.NamaKategori, A.HargaSatuan, A.QtyOrder, A.Satuan, STR(A.PersenDiskonItem) Persen_Diskon_Item, STR(A.NominalDiskonItem) Nominal_Diskon_Item, A.Jumlah,  
		ROW_NUMBER() OVER (Order By A.Dtlid) As RowNumber   
	From tblSalesOrderDetail A 
	Where A.NomorSO = :noso 
) Result 
Order By Result.Dtlid 
EOD;

	$queryBase = new MtSalesOrderDetailList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("noso"=>$nomorso), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
