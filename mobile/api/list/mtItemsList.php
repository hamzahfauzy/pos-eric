<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryHDPatterned.php";

class MtItemsList extends QueryBaseHDPatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtItemsList ($con, $auth, $nama_barang, $nama_kategori, $id_customer, $limit, $offset) {

$patternHD = <<<EOD
{
	"id_item": "{0}",
	"kode_item": "{1}",
	"id_kategori": "{2}",
	"nama_kategori": "{3}",
	"nama_item": "{4}",
	"sisa_stok": {5},
	"hargajual": {6},
	"keterangan": "{7}",
	"limitstok": {8}, 
	"detail": [{details}]
}
EOD;
		
$patternDTL = <<<EOD
{
	"id_itemdetail": "{10}",
	"barcode_number": "{11}",
	"hargajual1": {12},
	"hargajual2": {13},
	"hargajual3": {14},
	"hargajual4": {15},
	"hargajual5": {16},
	"hargajual6": {17},
	"hargajual7": {18},
	"hargajual8": {19},
	"hargajual9": {20},
	"hargajual10": {21},
	"satuan": "{22}",
	"berat": {23}
}
EOD;

$filter = "";
$hargajual = "HargaJual1";

if ($nama_barang != "") {
	$filter = $filter . " And A.NamaItem Like '%$nama_barang%' ";
}
if ($nama_kategori != "") {
	$filter = $filter . " And A.NamaKategori Like '%$nama_kategori%' ";
}	
if ($id_customer != "") {
	if (!($cols = queryArrayValue($con,
		"Select ID_Customer, LevelHarga From tblCustomer Where ID_Customer = ? ;",
		array($id_customer)))==null) {
		$hargajual = $cols[1]; //Level Harga
		if ($hargajual == 0 || $hargajual == "" || $hargajual == null) {
			$hargajual = "HargaJual1";
		}
	}
}	

//1:1 Table item vs itemdetail
$sql = <<<EOD
SELECT *
FROM (	
	Select A.ID_Item, A.KodeItem, A.ID_KategoriItem, A.NamaKategori, A.NamaItem, A.SisaStok, B.$hargajual As HargaJual,
		A.Keterangan, A.LimitStok, A.ID_Item As ID_Item_Data, 
		B.ID_ItemDetail, B.BarcodeNumber, CAST(B.HargaJual1 as numeric(10,0)) As HargaJual1, 
		CAST(B.HargaJual2 as numeric(10,0)) As HargaJual2, CAST(B.HargaJual3 as numeric(10,0)) As HargaJual3, 
		CAST(B.HargaJual4 as numeric(10,0)) As HargaJual4, CAST(B.HargaJual5 as numeric(10,0)) As HargaJual5, 
		CAST(B.HargaJual6 as numeric(10,0)) As HargaJual6, CAST(B.HargaJual7 as numeric(10,0)) As HargaJual7, 
		CAST(B.HargaJual8 as numeric(10,0)) As HargaJual8, CAST(B.HargaJual9 as numeric(10,0)) As HargaJual9, 
		CAST(B.HargaJual10 as numeric(10,0)) As HargaJual10, 
		B.Satuan, B.Berat, ROW_NUMBER() OVER (Order By ID_KategoriItem) As RowNumber 
	From tblItem A 
		Inner Join tblItemDetail B On A.ID_Item = B.ID_Item 
	Where Not A.ID_Item Is Null $filter 
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.ID_Item, Result.ID_ItemDetail  
EOD;

	//var_dump($sql);
	//LIMIT $limit, $offset;
	$queryBaseHD = new MtItemsList($con, $sql, $patternHD, $patternDTL);
	$queryBaseHD->setKey(array(9)); //id_item
	try {
		//query($values, $single=false);
		$retJSON = $queryBaseHD->query(array(), false);
		//$retJSON = $queryBaseHD->query(array(), false);
		return $retJSON;
		//return "{ \"total_activity\": 0, 
		//		\"items\": [{ \"id\": 0, \"parent_id\": 0, \"name\": \"Data tidak ditemukan!\", 
		//		\"info\": \"\", \"time\": \"00:00\", \"categories\": \"\" }] }";
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
	
}
?>