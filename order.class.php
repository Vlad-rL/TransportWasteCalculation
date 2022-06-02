<?php
class Order  {
public $customer='';
public $orderNumber=0;
public $orderDate='';
public $sourceKladr = '';
public $targetKladr = '';
public $weightKg = 0.0;
public $speedMode='';
public $customDate='';
public $price = 0.0;
public $agent = '';
public $error = '';
public $mysqli;
public $repl = '';
public $createRec;
public $getRec;

private function linkBase()
 {
 if (!$this->mysqli)
	{
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	include_once 'defbaseP.php';
	if ( !($mysqli = @new mysqli(host, user, password, db_name)))
	 { 
	 if ( $mysqli->connect_errno)
		{
		$this->$repl .= " Error: unsuccesful attempt to connect to base:".$mysqli->mysqli_connect_error();
		}
	 return false;
	 }
	 $this->mysqli=$mysqli;
	}
// $mysqli->set_charset('utf8');
 return true;
 }
public function __construct()
{
 if (!self::linkbase()) echo "Error: ".$this->mysqli->connect_error;

}

} // class Order end

class Orders extends Order
{
private function _construct ()
  {
  parent::_construct ();
  }
} // class Orders end

?>