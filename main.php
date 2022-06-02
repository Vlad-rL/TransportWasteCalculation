<?php
function linkBase()
 {
 global $repl, $link;
 if (!$link)
	{include_once 'defbaseP.php';
	$link = mysqli_connect(host, user, password, db_name);
	if ( !$link )
	 { 
	 if ( mysqli_connect_errno($link) )
		{
		$repl .= " Error: unsuccesful attempt to connect to base:".mysqli_connect_error($link);
		}
	 return false;
	 }
	}
// mysqli_set_charset($link,'utf8');
 return true;
 }
 $orderDate = date("Y-m-d");
 // var_dump ($_POST);
  require 'order.class.php';
 $order = new Orders();
 
 function setRec(&$order,$customer,$orderDate,$sourceKladr,$targetKladr,$weightKg,$speedMode)
  {
  $query = "SELECT * FROM `orders` WHERE `customer`='".$customer."' AND `from`='".$sourceKladr."' AND `to`='".$targetKladr."' AND `weight`=".$weightKg." AND `timeSelect`='".$speedMode."'";
 // echo "$query"."\r\n";
  if($res = $order->mysqli->query($query)) 
	if ($res->num_rows!=0) return '';
  $res->free();
  $query = "SELECT `orderNum` FROM `orders` WHERE `orderNum`=".$order->orderNumber;
  // echo "$query"."\r\n";
  if($res = $order->mysqli->query($query)) 
	if ($res->num_rows!=0) return '';
  $res->free();
  $query = "INSERT IGNORE INTO `orders` (`customer`,`orderNum`,`order_date`,`from`,`to`,`weight`,`timeSelect`) VALUES ('".$customer."' ,".$order->orderNumber.",'".$orderDate."','".$sourceKladr."','".$targetKladr."',".$weightKg.",'".$speedMode."')";
 // echo "$query"."\r\n";
  if(!($order->mysqli->query($query)))
	return "Error: ".$order->mysqli->error;
  else return '';
  };
 function getRecOrd(&$order)
  {
   $query = "SELECT * FROM `orders`";
   if(!($res = $order->mysqli->query($query)))
	return "Error: ".$res->error;
    else
	   {
		$order->repl = '';
		while ($val=$res->fetch_assoc())
			{
			$order->repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; ".$val['from']." -> ".$val['to']."; вес = ".$val['weight']." кг; тариф: ".$val['timeSelect']."??";
			}
		$res->free();
		return $order->repl; 
		}	
  };
  
 function listing(&$order,$prime,$list)
  {
	$code = "<script type='text/javascript'>
	window.addEventListener('DOMContentLoaded', function ()
	{ 
	orderEl = document.getElementById('".$list."');";
	if($prime != '')
	$code .= "newEl=document.createElement('li');
	newEl.innerHTML = 'Error: ".$prime."';
	orderEl.append(newEl);";
	$repl = getRecOrd($order);
	if($repl == '')
		$code .= "newEl=document.createElement('li');
		newEl.innerHTML = 'Empty list';
		orderEl.append(newEl);";
	   else
		{
		$lines = explode('??', $repl);
		for ($i=0; $i < count($lines); $i++)
			{
			if (strlen($lines[$i]) === 0) continue;
			$code .= "newEl=document.createElement('li');
			newEl.innerHTML = '".$lines[$i]."';
			orderEl.append(newEl);";
			}
		}
	$code .= "});</script>";	
	echo $code;
  };
  
 function printMes(&$order,$mess)
  {  
	echo "<script type='text/javascript'>
	window.addEventListener('DOMContentLoaded', function ()
	{ 
	mesEl = document.getElementById('message');
	newEl=document.createElement('span');
	newEl.innerHTML = '".$mess."';
	mesEl.append(newEl);
	})</script>";
	listing($order,'','orderList');
  }
// -----------------------------------------------------------
// ------------------------ begin --------------------
$link;
$repl = '';

if (!linkBase()) {unset($_POST); exit();}

if (!$_POST) {listing($order,'','orderList'); exit();}
// var_dump($_POST);

//  Удалось подключиться
$query ="CREATE TABLE IF NOT EXISTS `orders` (
	`customer` VARCHAR(32) NOT NULL, 
	`orderNum` INT DEFAULT 0,
	`order_date` VARCHAR(10),
	`from` VARCHAR(32) DEFAULT '',
	`to` VARCHAR(32) DEFAULT '',
	`weight` FLOAT DEFAULT 0,
	`timeSelect` VARCHAR(8),
	`was_read` INT DEFAULT 0,
	`custom_date` VARCHAR(10),
	`error` VARCHAR(240),
	`agent_id` INT 
	)";
// echo "$query"."\r\n";
if (!mysqli_query ($link,$query)) 
	{  // доступа в базе нет 
	$repl .= ' Dialogue base is unsucceedable. '.mysqli_error($link);
	}

$query ="CREATE TABLE IF NOT EXISTS `agents` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`base_url` VARCHAR(64),
	`speed_Mode` VARCHAR(8), /* fast, standard */
	`agent_name` VARCHAR(32)
      )";
// echo "$query".PHP_EOL;
if (!mysqli_query($link,$query)) 
	{  // доступа в базе нет 
	$repl .= ' Objects is unsucceedable';
	}

$query ="CREATE TABLE IF NOT EXISTS `calculations` (
	`orderNum` INT DEFAULT 0,
	`timeSelect` VARCHAR(8),
	`custom_date` VARCHAR(10),
	`receive_time` BIGINT DEFAULT 0,
	`req_status` INT DEFAULT 0,		/* 0 - send, 1 - wait, 2 - closed */
	`price` FLOAT,
	`error` VARCHAR(240),
	`inform` VARCHAR(240),
	`agent_name` VARCHAR(32) 
    )";
// echo "$query"."\r\n";
if (!mysqli_query ($link,$query)) 
	{  // доступа в базе нет 
	$repl .= ' Dialogue base is unsucceedable. '.mysqli_error($link);
	}

//=================='POST'=============================

// Ветвление на запросы редактирования заказов и вывода
if ( isset($_POST['send']) ) 
 {
 $customer = trim($_POST['customer']);
 $sourceKladr = trim($_POST['from']);
 $targetKladr = trim($_POST['to']);
 $weight = $_POST['weight'];
 $weight = preg_replace("/,/", ".", $weight);
 $speedMode = $_POST['time'];
  
 if (empty($customer) || empty($orderDate) || empty($sourceKladr) || empty($targetKladr) || empty($weight))
	{
	printMes($order,'Вы ввели не всю информацию');
	unset($_POST);
	exit();
	}
 if (preg_match('/[0-9.]/',$weight ) != 1) 
	{
	printMes($order,'Неверный формат для веса');
	unset($_POST);
	exit();
	}
	else $weightKg = (float)$weight;
 $query="SELECT MAX(`orderNum`) AS 'MaxNum' FROM `orders`";
 $res = $order->mysqli->query($query);
 // var_dump($res);
 // echo ' $res->num_rows = '.$res->num_rows;
 // echo ' $res->fetch_assoc()[MaxNum] = '.$res->fetch_assoc()['MaxNum'];
 $order->orderNumber = ($res->num_rows==0)? 1:($res->fetch_assoc()['MaxNum'])+1;
 // echo ' $order->orderNumber = '.$order->orderNumber;
 $res->free();
 $repl = setRec($order,$customer,$orderDate,$sourceKladr,$targetKladr,$weightKg,$speedMode);
 listing($order,$repl,'orderList');
 }
  elseif (isset($_POST['show']))
	  {
		listing($order,'','orderList');
	  }
 
//=============================================================
unset($_POST);
exit();
?>