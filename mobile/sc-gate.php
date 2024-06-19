<?php
include_once "framework/library.php";
include_once "framework/base.php";
include_once "framework/auth.php";
  
include_once "api/profile/getUserIdentity.php";
include_once "api/profile/getUserLogout.php";
include_once "api/list/mtCustomerList.php";
include_once "api/list/mtKategoriList.php";
include_once "api/list/mtItemsList.php";
include_once "api/list/mtProfileList.php";
include_once "api/list/mtConfigurasiList.php";
include_once "api/list/mtSalesOrderList.php";
include_once "api/list/mtSalesOrderDetailList.php";
include_once "api/list/mtInvoiceList.php";
include_once "api/list/mtInvoiceDetailList.php";
include_once "api/list/mtSalesList.php";

include_once "api/crud/profileUpdate.php";
include_once "api/crud/orderNew.php";
include_once "api/crud/orderUpdate.php";
include_once "api/crud/orderCancel.php";
include_once "api/crud/itemsNew.php";
include_once "api/crud/itemsUpdate.php";
include_once "api/crud/itemsDelete.php";

//var_dump($_REQUEST);
//die();

//entry point
$requestTime = Date("Y-m-d h:i:s");
$post_raw_json = file_get_contents('php://input');
//mengaktifkan CORS (cross origin resource sharing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$query = isset($_GET['query']) ? $_GET['query'] : NULL;	
$con = openConnection();
$auth = new Auth($con, $_SERVER['HTTP_USER_AGENT'], getClientIPAddress());	

try {
	if ($query!="") {
		if ($query=="logout") {
			$userId = $_REQUEST["username"];
			$password = $_REQUEST["password"];
			if ($auth->isValidUser($userId, $password)>0) {
				$data = getUserLogout($con, $auth, $userId);
				$response = Base::responseSucceedPrepared("Berhasil logout!", $data);
				//$response = Base::responseSucceed("Anda berhasil keluar!", array());
				echo $response;
				die();
			}
			else {
				throw new Exception("Invalid User or Password!");
			}
		}
		else {				
			if ($query=="login") {
				$userId = $_REQUEST["username"];
				$password = $_REQUEST["password"];
				if ($auth->isValidUser($userId, $password)>0) {
					$data = getUserIdentity($con, $auth, $userId);
					$response = Base::responseSucceedPrepared("Berhasil login!", $data);
					echo $response;
					die();												
				}
				else {
					throw new Exception("Invalid User or Password!");
				}
			}					
			else {
				$header_username = isset(getallheaders()["Username"])?getallheaders()["Username"]:null; //default null	
				$header_password = isset(getallheaders()["Password"])?getallheaders()["Password"]:null; //default null
				//var_dump($header_username);
				
				if (!($header_username==null || $header_password==null)) {
					try {
						if ($auth->isValidUserLogin($header_username, $header_password)>0) {
							$message="success"; //default dianggap success
							$data="{}"; //untuk metode POST tidak ada data
							
							//PULL API
							if ($query=="customer-list") { //customer/list ?nama_customer=EDY
								$nama_customer = $_REQUEST["nama_customer"];
								$limit = $_REQUEST["limit"];
								$offset = $_REQUEST["offset"];
								$data = mtCustomerList($con, $auth, $nama_customer, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data customer tidak ditemukan!";
								}
							}
							else if ($query=="kategori-list") { //kategori/list?nama_kategori=FOOD
								$nama_kategori = $_REQUEST["nama_kategori"];
								//var_dump($nama_kategori);
								$limit = $_REQUEST["limit"];
								$offset = $_REQUEST["offset"];
								$data = mtKategoriList($con, $auth, $nama_kategori, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data kategori tidak ditemukan!";
								}
							}
							else if ($query=="configurasi-list") { //configurasi/list
								$data = mtConfigurasiList($con, $auth);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data configurasi tidak ditemukan!";
								}
							}
							else if ($query=="profile-list") { //profile/list?username=manager
								$username = $_REQUEST["username"];
								$data = mtProfileList($con, $auth, $username);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data profile tidak ditemukan!";
								}
							}
							else if ($query=="items-list") { //items/list?nama=NASI&nama_kategori=FOOD&limit=1&offset=5
								$nama_barang = $_REQUEST["nama_barang"];  
								$nama_kategori = $_REQUEST["nama_kategori"];  
								$id_customer = $_REQUEST["id_customer"];  
								$limit = $_REQUEST["limit"];	//record per page
								$offset = $_REQUEST["offset"];	//from offset
								$data = mtItemsList($con, $auth, $nama_barang, $nama_kategori, $id_customer, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data items tidak ditemukan!";
								}
							}
							else if ($query=="order-list") { //order/list?nama_customer=EDY&dari_tanggal=2024-01-01&sampai_tanggal=2024-03-31&limit=1&offset=5
								$nama_customer = $_REQUEST["nama_customer"];  
								$dari_tgl = $_REQUEST["dari_tanggal"];  
								$sampai_tgl = $_REQUEST["sampai_tanggal"];  
								$limit = $_REQUEST["limit"];	//record per page
								$offset = $_REQUEST["offset"];	//from offset
								$data = mtSalesOrderList($con, $auth, $nama_customer, $dari_tgl, $sampai_tgl, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data sales order tidak ditemukan!";
								}
							}
							else if ($query=="order-detail-list") { //order/detail/list?nomor_so=SO-24041800112 
								$nomorso = $_REQUEST["nomor_so"];  
								$data = mtSalesOrderDetailList($con, $auth, $nomorso);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data sales order detail tidak ditemukan!";
								}
							}
							else if ($query=="invoice-list") { //invoice/list?nama_customer=EDY&dari_tanggal=2024-01-01&sampai_tanggal=2024-03-31&limit=1&offset=5
								$nama_customer = $_REQUEST["nama_customer"];  
								$dari_tgl = $_REQUEST["dari_tanggal"];  
								$sampai_tgl = $_REQUEST["sampai_tanggal"];  
								$limit = $_REQUEST["limit"];	//record per page
								$offset = $_REQUEST["offset"];	//from offset
								$data = mtInvoiceList($con, $auth, $nama_customer, $dari_tgl, $sampai_tgl, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data invoice tidak ditemukan!";
								}
							}
							else if ($query=="invoice-detail-list") { //invoice/detail/list?id_penjualan=1
								$idpenjualan = $_REQUEST["id_penjualan"];  
								$data = mtInvoiceDetailList($con, $auth, $idpenjualan);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data invoice detail tidak ditemukan!";
								}
							}
							else if ($query=="karyawan-list") { //karyawan/list?nama_karyawan=DEDI&limit=1&offset=5 
								$nama_karyawan = $_REQUEST["nama_karyawan"];
								$limit = $_REQUEST["limit"];
								$offset = $_REQUEST["offset"];
								$data = mtSalesList($con, $auth, $nama_karyawan, $limit, $offset);
								if ($data == "null" || $data == "" || $data == "[]") {
									$message = "Data karyawan / sales tidak ditemukan!";
								}
							}
							//PUSH API
							else if ($query=="profile-edit") { //profile/edit
								$obj = json_decode($post_raw_json);
								//var_dump($obj);
								$data = profileUpdate($con, $auth, $obj);
								$message = "Berhasil edit profile!";
							}
							else if ($query=="order-new") { //order/new 
								$obj = json_decode($post_raw_json);
								$data = orderNewSubmit($con, $auth, $obj);
								$message = "Berhasil tambah order!";
							}
							else if ($query=="order-update") { //order/update 
								$obj = json_decode($post_raw_json);
								$data = orderUpdateSubmit($con, $auth, $obj);
								$message = "Berhasil update order!";
							}
							else if ($query=="order-cancel") { //order/cancel 
								$obj = json_decode($post_raw_json);
								$data = orderCancelSubmit($con, $auth, $obj);
								$message = "Berhasil cancel order!";
							}
							else if ($query=="items-new") { //items/new 
								$obj = json_decode($post_raw_json);
								$data = itemsNewSubmit($con, $auth, $obj);
								$message = "Berhasil tambah items!";
							}
							else if ($query=="items-update") { //items/update 
								$obj = json_decode($post_raw_json);
								$data = itemsUpdateSubmit($con, $auth, $obj);
								$message = "Berhasil update items!";
							}
							else if ($query=="items-delete") { //items/delete 
								$obj = json_decode($post_raw_json);
								$data = itemsDeleteSubmit($con, $auth, $obj);
								$message = "Berhasil delete items!";
							}
							else {
								throw new Exception("Unknown Service Request OR Not Implemented Yet!");
							}
							
							$response = Base::responseSucceedPrepared($message, $data);
							echo $response;
							die();
						}
						else {
							$message="Invalid Username & Password (Not Login)"; //default dianggap success
							$data="{}";
							
							$response = Base::responseSucceedPrepared($message, $data);
							echo $response;
							die();
						}
					} catch (Exception $e) {
						$response =Base::responseFailed($e->getMessage(),array());
						http_response_code(401);  // set the code 401
						echo $response;
						die();
					}
				} else {
					throw new Exception("Invalid Header"); //Bad Authentication
				}				
			}
		}
	}
	else {
		throw new Exception("Empty Service Request!");
	}
} catch (Exception $e) {
	$response =Base::responseFailed($e->getMessage(),array());
	http_response_code(401);  // set the code 401
	echo $response;
	die();
}		
?>
