<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function orderCancelSubmit($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("no_order"));
		
/*
{ 
	"no_order": "ORDER-240305-135943" 
}
*/

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order ;", array("no_order"=>$obj->no_order)) == 0)
        throw new Exception("Nomor sales order " . $obj->no_order . " tidak ditemukan!"); //no_order not Already Exist

    if(querySingleValue($con, "SELECT COUNT(*) FROM tblSalesOrder WHERE NomorSO = :no_order And StatusOrder = 1 ;", array("no_order"=>$obj->no_order)) > 0)
        throw new Exception("Status nomor sales order " . $obj->no_order . " sudah pernah dibuka Penjualan (Invoice) !"); //status order = 1 

    $sqlCreateOrder = <<<EOD
        Update tblSalesOrder Set StatusOrder = 2 Where NomorSO = :no_order ;
EOD;

    $affectedRowsOrder = updateRow($con, $sqlCreateOrder, array(
        "no_order"=>$obj->no_order 
    ));

    if($affectedRowsOrder > 0) {
        $message = '{ "no_order": "' . $obj->no_order . '" }';
		return $message;
    } else {
        throw new Exception("Gagal cancel order!");
    }
}
?>