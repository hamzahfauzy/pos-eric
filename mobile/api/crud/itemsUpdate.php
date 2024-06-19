<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function itemsUpdateSubmit($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("dtl_id", "no_order", "barcode_number", "id_item", "id_itemdetail", "kode_item", "nama_item", "id_kategori", 
		"nama_kategori", "harga_satuan", "qty_order", "satuan", "disc_persen", "disc_nominal", "jumlah_harga"));
		
    /*
{ 
	"dtl_id": 1, 
	"no_order": "SO-24042301307",  
	"barcode_number": "8888888888888", 
	"id_item": "P00001", 
	"id_itemdetail": "123456789012345",  
	"kode_item": "P00001",  
	"nama_item": "AQUA BOTOL 600ML",  
	"id_kategori": "1",
	"nama_kategori": "DRINK",
	"harga_satuan": 5000.00,
	"qty_order": 2,
	"satuan": "PCS", 
	"disc_persen": 0.00,
	"disc_nominal": 0.00,
	"jumlah_harga": 10000.00 
}
*/
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order ;", array("no_order"=>$obj->no_order)) == 1)
        throw new Exception("Nomor sales order " . $obj->no_order . " tidak ditemukan!"); //no_order not Already Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order And StatusOrder = 1 ;", array("no_order"=>$obj->no_order)) > 0)
        throw new Exception("Status nomor sales order " . $obj->no_order . " sudah pernah dibuka Penjualan (Invoice) !"); //status order = 1 
		
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrderDetail  
		WHERE NomorSO = :no_order And Dtlid = :dtl_id And ID_ItemDetail = :id_itemdetail ;", array("no_order"=>$obj->no_order, "dtl_id"=>$obj->dtl_id, "id_itemdetail"=>$obj->id_itemdetail)) == 0)
        throw new Exception("Nomor sales order " . $obj->no_order . " dengan items " . $obj->nama_item . " (" . $obj->id_itemdetail . "-" . $obj->dtl_id . ") tidak ditemukan!");  
		
    $sqlUpdateItems = <<<EOD
        Update tblSalesOrderDetail Set BarcodeNumber = :barcode_number, ID_Item = :id_item, KodeItem = :kode_item, 
			NamaItem = :nama_item, ID_KategoriItem = :id_kategori, NamaKategori = :nama_kategori, HargaSatuan = :harga_satuan, 
			QtyOrder = :qty_order, Satuan = :satuan, PersenDiskonItem = :disc_persen, 
			NominalDiskonItem = :disc_nominal, Jumlah = :jumlah_harga 
		Where NomorSO = :no_order And Dtlid = :dtl_id And ID_ItemDetail = :id_itemdetail ;
EOD;
	
    $affectedRowsItems = updateRow($con, $sqlUpdateItems, array(
		"dtl_id"=>$obj->dtl_id, 
        "no_order"=>$obj->no_order, 
		"barcode_number"=>$obj->barcode_number, 
		"id_item"=>$obj->id_item, 
		"id_itemdetail"=>$obj->id_itemdetail, 
		"kode_item"=>$obj->kode_item, 
		"nama_item"=>$obj->nama_item, 
		"id_kategori"=> $obj->id_kategori,
		"nama_kategori"=> $obj->nama_kategori,
		"harga_satuan"=> $obj->harga_satuan,
		"qty_order"=> $obj->qty_order,
		"satuan"=> $obj->satuan,
		"disc_persen"=> $obj->disc_persen,
		"disc_nominal"=> $obj->disc_nominal,
		"jumlah_harga"=> $obj->jumlah_harga 
    ));
	
	//var_dump($obj);
	
    if($affectedRowsItems > 0) {
        $message = '{ "dtl_id": ' . $obj->dtl_id . ', "no_order": "' . $obj->no_order . '", "id_item": "' . $obj->id_item . '", "id_itemdetail": "' . $obj->id_itemdetail . '", 
			"kode_item": "' . $obj->kode_item . '", "nama_item": "' . $obj->nama_item . '", "id_kategori": "' . $obj->id_kategori . '", 
			"nama_kategori": "' . $obj->nama_kategori . '", "harga_satuan": ' . $obj->harga_satuan . ', "qty_order": ' . $obj->qty_order . ', "satuan": "' . $obj->satuan . '" }';
		return $message;
    } else {
        throw new Exception("Gagal update items!");
    }
}
?>