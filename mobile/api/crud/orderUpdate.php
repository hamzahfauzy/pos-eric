<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function orderUpdateSubmit($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("no_order", "tgl_order", "id_customer", "nama_customer", "telepon", "id_sales", "nama_sales",  
		"keterangan", "username"));
		
    /*
{ 
	"no_order": "SO-240423-142526",  
	"tgl_order": "2024-04-23",  
	"id_customer": "1", 
	"nama_customer": "EDY GUNAWAN",  
	"telepon": "085261184638", 
	"id_sales": "1",
	"nama_sales": "ANTONI", 
	"keterangan": "PO BULANAN",
	"username": "manager" 
}
*/
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order ;", array("no_order"=>$obj->no_order)) == 0)
        throw new Exception("Nomor sales order " . $obj->no_order . " tidak ditemukan!"); //no_order not Already Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblCustomer WHERE ID_Customer = :id_customer ;", array("id_customer"=>$obj->id_customer)) == 0)
        throw new Exception("Data Customer " . $obj->nama_customer . " (" . $obj->id_customer . ") tidak ditemukan!"); //id_customer not Already Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSales WHERE ID_Sales = :id_sales ;", array("id_sales"=>$obj->id_sales)) == 0)
        throw new Exception("Data Karyawan / Sales " . $obj->nama_sales . " (" . $obj->id_sales . ") tidak ditemukan!"); //id_sales not Already Exist 
	
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order And StatusOrder = 1 ;", array("no_order"=>$obj->no_order)) > 0)
        throw new Exception("Status nomor sales order " . $obj->no_order . " sudah pernah dibuka Penjualan (Invoice) !"); //status order = 1 

    $sqlUpdateOrder = <<<EOD
        Update tblSalesOrder Set TanggalSO = :tgl_order, ID_Customer = :id_customer, NamaCustomer = :nama_customer, 
			Telepon = :telepon, ID_Sales = :id_sales, NamaSales = :nama_sales, Keterangan = :keterangan, 
			M_By = :editby, M_Time = :edittime 
		Where NomorSO = :no_order And StatusOrder = 0 ;
EOD;

    $affectedRowsOrder = updateRow($con, $sqlUpdateOrder, array(
        "no_order"=>$obj->no_order, 
        "tgl_order"=>$obj->tgl_order, 
        "id_customer"=>$obj->id_customer,
        "nama_customer"=>$obj->nama_customer,
        "telepon"=>$obj->telepon, 
        "id_sales"=>$obj->id_sales, 
        "nama_sales"=>$obj->nama_sales, 
        "keterangan"=>$obj->keterangan, 
        "editby"=>$obj->username, 
        "edittime"=>date("Y-m-d H:i:s")
    ));

    if($affectedRowsOrder > 0) {
        $message = '{ "no_order": "' . $obj->no_order . '", "id_customer": "' . $obj->id_customer . '", "nama_customer": "' . $obj->nama_customer . '", 
			"id_sales": "' . $obj->id_sales . '", "nama_sales": "' . $obj->nama_sales . '", "keterangan": "' . $obj->keterangan . '"}'; 
		return $message;
    } else {
        throw new Exception("Gagal update order!");
    }
}
?>
