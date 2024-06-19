<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/base.php";

function profileUpdate($con, $auth, $obj) {
    #lakukan pemeriksaan kelengkapan
    assertCompleteJSON($obj, array("username", "password_lama", "password_baru", "nama_pengguna", "nohp"));
		
/*
{ 
    "username": "manager", 
    "password_lama": "eric2024", 
    "password_baru": "eric@2024!", 
    "nama_pengguna": "Eric Wardi", 
    "nohp": "08116201641"
}
*/
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblLoginApps WHERE Username = :userid ;", array("userid"=>$obj->username)) == 0)
        throw new Exception("Username " . $obj->username . " tidak ditemukan!"); //username Already not Exist

	if(querySingleValue($con, "SELECT COUNT(*) FROM tblLoginApps WHERE Username = :userid And Password = :password_lama ;", array("userid"=>$obj->username, "password_lama"=>$obj->password_lama)) == 0)
        throw new Exception("Username dan Password tidak valid!"); //username dan password = 0 
	
	if(querySingleValue($con, "SELECT COUNT(*) FROM tblLoginApps WHERE Username = :userid And Password = :password_baru ;", array("userid"=>$obj->username, "password_baru"=>$obj->password_baru)) == 1)
        throw new Exception("Password baru tidak boleh sama dengan Password lama!"); 

    $sqlUpdateProfile = <<<EOD
        Update tblLoginApps Set Password = :password_baru, NamaPengguna = :nama_pengguna, NoHP = :nohp, 
			M_By = :edituser, M_Time = GETDATE() 
		Where Username = :username And Password = :password_lama ;
EOD;
	
    $affectedRowsItems = updateRow($con, $sqlUpdateProfile, array(
		"username"=>$obj->username, 
		"password_lama"=>$obj->password_lama, 
		"password_baru"=>$obj->password_baru, 
		"nama_pengguna"=>$obj->nama_pengguna,
		"nohp"=>$obj->nohp,
		"edituser"=>$obj->username 
    ));
	
	//var_dump($obj);
	
    if($affectedRowsItems > 0) {
        $message = '{ "username": "' . $obj->username . '" }';
		return $message;
    } else {
        throw new Exception("Gagal edit profile!");
    }
}
?>