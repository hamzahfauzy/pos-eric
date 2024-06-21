<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function itemsDeleteSubmit($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("no_order", "dtl_id", "id_item", "id_itemdetail", "kode_item", "nama_item",
		"qty_order", "satuan"));
		
    /*
{
{
	"no_order": "SO-24042301307",
	"dtl_id": 1, 
	"id_item": "1122334455",
	"id_itemdetail": "123456789012345",  
	"kode_item": "P00001",  
	"nama_item": "AQUA BOTOL 600ML",   
	"qty_order": 4,
	"satuan": "PCS" 
}
}
*/
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order ;", array("no_order"=>$obj->no_order)) == 0)
        throw new Exception("Nomor sales order " . $obj->no_order . " tidak ditemukan!"); //no_order not Already Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order And StatusOrder = 1 ;", array("no_order"=>$obj->no_order)) > 0)
        throw new Exception("Status nomor sales order " . $obj->no_order . " sudah pernah dibuka Penjualan (Invoice) !"); //status order = 1  
	
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrderDetail  
		WHERE NomorSO = :no_order And Dtlid = :dtl_id And ID_ItemDetail = :id_itemdetail ;", array("no_order"=>$obj->no_order, "dtl_id"=>$obj->dtl_id, "id_itemdetail"=>$obj->id_itemdetail)) == 0)
        throw new Exception("Nomor sales order " . $obj->no_order . " dengan items " . $obj->nama_item . " (" . $obj->id_itemdetail . "-" . $obj->dtl_id . ") tidak ditemukan!"); 
		
    $sqlDeleteItems = <<<EOD
        Delete From tblSalesOrderDetail Where NomorSO = :no_order And Dtlid = :dtl_id And ID_ItemDetail = :id_itemdetail ;
EOD;
	
    $affectedRowsItems = deleteRow($con, $sqlDeleteItems, array(
		"no_order"=>$obj->no_order, 
		"dtl_id"=>$obj->dtl_id, 
		"id_itemdetail"=>$obj->id_itemdetail	
    ));
	
	//var_dump($obj);
	
    if($affectedRowsItems > 0) {
        $message = '{ "dtl_id": ' . $obj->dtl_id . ', "no_order": "' . $obj->no_order . '", "id_item": "' . $obj->id_item . '", "id_itemdetail": "' . $obj->id_itemdetail . '", 
			"kode_item": "' . $obj->kode_item . '", "nama_item": "' . $obj->nama_item . '", "qty_order": ' . $obj->qty_order . ', "satuan": "' . $obj->satuan . '" }'; 
		return $message;
    } else {
        throw new Exception("Gagal delete items!");
    }
}
?>
