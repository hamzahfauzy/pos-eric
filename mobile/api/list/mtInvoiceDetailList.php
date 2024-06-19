<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtInvoiceDetailList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtInvoiceDetailList($con, $auth, $idpenjualan) {

$pattern = <<<EOD
{
	"id_penjualandetail": "{0}",
	"id_penjualan": "{1}",
	"id_item": "{2}",
	"id_itemdetail": "{3}",
	"kode_item": "{4}",
	"nama_item": "{5}",
	"harga_jual_satuan": {6},
	"qty_jual": {7}, 
	"satuan": "{8}", 
	"diskon_item": {9}, 
	"persen_diskon": {10}, 
	"nominal_diskon": {11}, 
	"total_harga_jual": {12} 
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select A.ID_PenjualanDetail, A.ID_Penjualan, A.ID_Item, A.ID_ItemDetail, A.KodeItem, A.NamaItem, 
		A.HargaJualSatuan, A.Jumlah As QtyJual, A.Satuan, A.DiskonItem, A.PersenDiskonItem1, A.NominalDiskonItem, A.TotalHargaJual,  
		ROW_NUMBER() OVER (Order By A.ID_PenjualanDetail) As RowNumber   
	From tblPenjualanDetail A 
	Where A.ID_Penjualan = :idpenjualan  
) Result 
Order By Result.ID_PenjualanDetail  
EOD;

	$queryBase = new MtInvoiceDetailList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("idpenjualan"=>$idpenjualan), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
