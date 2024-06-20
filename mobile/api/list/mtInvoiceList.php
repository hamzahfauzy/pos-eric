<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtInvoiceList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtInvoiceList($con, $auth, $nama_customer, $dari_tgl, $sampai_tgl, $limit, $offset) {

$pattern = <<<EOD
{
	"id_penjualan": "{0}",
	"nomor_faktur": "{1}",
	"tanggal_faktur": "{2}",
	"tanggal_jtempo": "{3}",
	"id_customer": "{4}",
	"nama_customer": "{5}",
	"alamat_customer": "{6}",
	"telepon": "{7}",
	"id_sales": "{8}",
	"nama_sales": "{9}",
	"id_gudang": "{10}",
	"nama_gudang": "{11}",
	"nama_supir": "{12}",
	"keterangan": "{13}",
	"total_penjualan": {14},
	"potongan": {15},
	"persen_potongan": {16},
	"total_transaksi": {17},
	"dpp": {18},
	"ppn": {19},
	"total_items": {20}
}
EOD;

$sql = <<<EOD
SELECT *
FROM (
	Select 	A.ID_Penjualan, 
		A.NomorFaktur, 
		A.Tanggal, 
		A.TanggalJatuhTempo,
		A.ID_Customer, 
		A.NamaCustomer, 
		A.AlamatLengkap, 
		A.Telepon, 
		A.ID_Sales, 
		A.NamaSales, 
		A.ID_Gudang, 
		A.NamaGudang, 
		A.NamaSupir, 
		A.Keterangan, 
		A.TotalPenjualan, 
		CAST(A.Potongan as numeric(10,0)) Potongan, 
		CAST(A.PersenPotongan as numeric(10,0)) PersenPotongan, 
		A.TotalTransaksi, 
		CAST(A.DPP as numeric(10,0)) DPP, 
		CAST(A.PPN as numeric(10,0)) PPN, 
		B.JlhItems As TotalItems,
		ROW_NUMBER() OVER (Order By A.Tanggal, A.NomorFaktur) As RowNumber   
	From tblPenjualan A
		Left Join (Select Count(*) As JlhItems, ID_Penjualan From tblPenjualanDetail Group By ID_Penjualan) B On A.ID_Penjualan = B.ID_Penjualan 
	Where A.NamaCustomer Like :nama 
		And A.Tanggal >= :daritgl 
		And A.Tanggal <= :sampaitgl 
) Result 
Where Result.RowNumber Between $limit And $offset 
Order By Result.Tanggal, Result.NomorFaktur    
EOD;

	$queryBase = new MtInvoiceList($con, $sql, $pattern);
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
