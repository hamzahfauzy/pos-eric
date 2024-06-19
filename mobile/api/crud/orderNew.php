<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function orderNewSubmit($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("tgl_order", "id_customer", "nama_customer", "telepon", "id_sales", "nama_sales", "keterangan", "username"));
		
    /* 
{ 
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
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblCustomer WHERE ID_Customer = :id_customer ;", array("id_customer"=>$obj->id_customer)) == 0)
        throw new Exception("Data Customer " . $obj->nama_customer . " (" . $obj->id_customer . ") tidak ditemukan!"); //id_customer not Already Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSales WHERE ID_Sales = :id_sales ;", array("id_sales"=>$obj->id_sales)) == 0)
        throw new Exception("Data Karyawan / Sales " . $obj->nama_sales . " (" . $obj->id_sales . ") tidak ditemukan!"); //id_sales not Already Exist
	
    $sqlSimpanOrder = <<<EOD
        INSERT INTO tblSalesOrder (NomorSO, TanggalSO, ID_Customer, NamaCustomer, Telepon, ID_Sales, NamaSales, 
			Keterangan, StatusOrder, C_By, C_Time) VALUES
        (:no_order, :tgl_order, :id_customer, :nama_customer, :telepon, :id_sales, :nama_sales, :keterangan, 0, :cby, :ctime);
EOD;

	$no_order = "SO-" . date("ymd") . "-" . date("His");
    $affectedRowsOrder = createRow($con, $sqlSimpanOrder, array(
        "no_order"=>$no_order, 
        "tgl_order"=>$obj->tgl_order, 
        "id_customer"=>$obj->id_customer,
        "nama_customer"=>$obj->nama_customer,
        "telepon"=>$obj->telepon,
        "id_sales"=>$obj->id_sales, 
        "nama_sales"=>$obj->nama_sales, 
        "keterangan"=>$obj->keterangan, 
        "cby"=>$obj->username, 
        "ctime"=>date("Y-m-d H:i:s")
    ));

    if($affectedRowsOrder > 0) {
		$message = '{ "no_order": "' . $no_order . '", "id_customer": "' . $obj->id_customer . '", "nama_customer": "' . $obj->nama_customer . '", 
			"id_sales": "' . $obj->id_sales . '", "nama_sales": "' . $obj->nama_sales . '", "keterangan": "' . $obj->keterangan . '"}'; 
		return $message;
		//throw new Exception("Berhasil proses order!");
    } else {
		throw new Exception("Gagal proses order!");
    }
}
?>
