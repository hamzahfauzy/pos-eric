<?php
//echo getcwd();

include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class GetUserIdentity extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function getUserIdentity($con, $auth, $username) {

$pattern = <<<EOD
{
	"username": "{0}",
	"nama_pengguna": "{1}",
	"nohp": "{2}",
	"last_logintime": "{3}",
	"last_logouttime": "{4}",
	"status_user": {5}
}
EOD;

$sql = <<<EOD
	SELECT a.Username, a.NamaPengguna, a.NoHP, a.LastLoginTime, a.LastLogoutTime, a.StatusUser 
	FROM tblLoginApps a 
	WHERE a.Username = :userid ;
EOD;

	$queryBase = new GetUserIdentity($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$sql = "UPDATE tblLoginApps SET LastLoginTime = GETDATE(), StatusLogin = 1 
				WHERE Username = :userid ;";
		$retConfirmation = updateRow($con, $sql, array("userid"=>$username));
		
		$retJSON = $queryBase->query(array("userid"=>$username), true);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>