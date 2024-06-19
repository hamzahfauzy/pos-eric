<?php
include_once "library.php";

class Auth {
	public $con;
	protected $userAgent;
	protected $ipAddress;
	protected $userId;

	//$token is token in caller header
	public function __construct($con, $userAgent, $ipAddress) { //_SERVER['HTTP_USER_AGENT']
		$this->con = $con;
		$this->userAgent = $userAgent;
		$this->ipAddress = $ipAddress;
	}

	public function isValidUser($userId, $password) {
		return querySingleValue($this->con, "select count(*) from tblLoginApps where Username = :1 and Password = :2 And StatusUser = 1 ;",array($userId, $password));
		$this->userId = $userId; //catat userId
	}
	
	public function isValidUserLogin($userId, $password) {
		return querySingleValue($this->con, "select count(*) from tblLoginApps where Username = :1 and Password = :2 And StatusUser = 1 And StatusLogin = 1 ;",array($userId, $password));
		$this->userId = $userId; //catat userId
	}
	
	public function getUserId() {
		return $this->userId;
	}
	
	function isValidNewPassword($password1, $password2) {
		$ret = "";
		if (strlen($password1) < 6) {
			$ret = "New Password at least 6 character long!";
		} else if ($password1 != $password2) {
			$ret = "New Password and Retype not match!";
		} else if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $password1)) {
			$ret = "New Password must have character and number!";
		}
		return $ret;
	}	
	
}
?>
