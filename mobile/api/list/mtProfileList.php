<?php
include_once dirname(__FILE__) . "/../../framework/library.php";
include_once dirname(__FILE__) . "/../../framework/auth.php";
include_once dirname(__FILE__) . "/../../framework/baseQueryPatterned.php";

class MtProfileList extends QueryBasePatterned {

//Overide user customization
	public function reconvert($row) {				
		$newRow = $row;
		return $newRow;
	}	
}

function mtProfileList($con, $auth, $username) {

$pattern = <<<EOD
{
	"username": "{0}",
	"nama_pengguna": "{1}",
	"nohp": "{2}"
}
EOD;

$sql = <<<EOD
Select Username, NamaPengguna, NoHP 
From tblLoginApps  
Where Username = :userid 
EOD;

	$queryBase = new MtProfileList($con, $sql, $pattern);
	try {
		//query($values, $single=false);
		$retJSON = $queryBase->query(array("userid"=>$username), false);
		return $retJSON;
	}
	catch (Exception $e) {
		throw new Exception($e->getMessage());
	}
}
?>
