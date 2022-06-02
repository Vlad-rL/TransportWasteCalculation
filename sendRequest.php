<?php
header("Content-type: text/plain; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
$mysqli;

function mem($data)
{
	file_put_contents('mem.txt',PHP_EOL.$data,FILE_APPEND);
}

function linkBase(&$mysqli)
 {
 $repl='';
 if (!$mysqli)
	{
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	include_once 'defbaseP.php';
	if ( !($mysqli = @new mysqli(host, user, password, db_name)))
	 { 
	 if ( $mysqli->connect_errno)
		{
		$repl .= " Error: unsuccesful attempt to connect to base:".$mysqli->mysqli_connect_error();
		}
	 return $repl;
	 }
	}
// $mysqli->set_charset('utf8');
 return $repl;
 }

function getRecOrd(&$mysqli)
  {
   if(!($res = $mysqli->query("SELECT * FROM `orders` AS `o` INNER JOIN `agents` AS `a` ON `a`.`id` = `o`.`agent_id`")))
   	return "Error: ".$mysqli->error;
    else
	{
	$repl = '';
	while ($val=$res->fetch_assoc())
		{
		if ($val['agent_name'])
			$repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; срок = ".$val['custom_date']."; исполнитель: \"".$val['agent_name']."\"??";
		else
			$repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; срок = ".$val['custom_date']."; исполнитель: Не назначен??";
		}
	$res->free();
	return $repl; 
	}	
  };
 
function getRecCalc($mysqli)
 {
	mem('getRecCalc ...');
	$query = "SELECT `c`.`orderNum` AS `ord`, `c`.`custom_date` AS `cust_d`, `c`.`error` AS `err`, `c`.`agent_name`, `c`.`inform`, `c`.`price`, `o`.* FROM `calculations` AS `c` INNER JOIN `orders` AS `o` WHERE `o`.`orderNum`=`c`.`orderNum`";
	mem('$query = '.$query);
	if(!($res = $mysqli->query($query)))
   	return "??Error: ".$mysqli->error;
    else
	{
	$repl = '';
	while ($val=$res->fetch_assoc())
		{
		mem('from `calculations` заказ = '.$val['orderNum'].', error: '.$val['err'].', inform = '.$val['inform'].PHP_EOL);
		if ($val['err'] == 'no' && $val['inform'] == 'OK!')
			$repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; ".$val['from']." --) ".$val['to']."; вес = ".$val['weight']." кг; тариф: ".$val['timeSelect'].", исполнитель: \"".$val['agent_name']."\", дата выполнения: ".$val['cust_d'].", стоимость = ".$val['price']." руб.??";
		  elseif ($val['err'] == 'no')
				$repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; ".$val['from']." --) ".$val['to']."; вес = ".$val['weight']." кг; тариф: ".$val['timeSelect'].", исполнитель: \"".$val['agent_name']."\" - ".$val['inform']."??";
		  else $repl .= "Заказчик: \"".$val['customer']."\"; № заказа = ".$val['orderNum']."; ".$val['from']." --) ".$val['to']."; вес = ".$val['weight']." кг; тариф: ".$val['timeSelect'].", исполнитель: \"".$val['agent_name']."\", ОШИБКА: ".$val['err']."??";
		}
	$res->free();
	}
	return $repl; 
 }
 
 function listing(&$mysqli,$prime,$list,$count)
  {
	global $repl;
	mem('listing ...$count = '.$count);
	$code = "!!%function execf(){parEl = document.getElementById('".$list."');
	if (parEl) 
		while (parEl.firstChild) 
			{parEl.firstChild.remove();
		};
	parEl.style.display = 'block';";
	if($prime != '')
		$code .= "newEl=document.createElement('li'); newEl.innerHTML = 'Error: ".$prime."'; parEl.append(newEl);";
	$replica = ($list == 'calculation')? getRecCalc($mysqli):getRecOrd($mysqli);
	mem($replica);
	if($replica == '')
		{
		$code .= "newEl=document.createElement('li');
		newEl.innerHTML = 'Error: Empty list';
		parEl.append(newEl);";
		}
	   else
		{
		$lines = explode('??', $replica);
		for ($i=0; $i < count($lines); $i++)
			{
			if (strlen($lines[$i]) === 0) continue;
			
			$code .= "newEl=document.createElement('li');
			newEl.innerHTML = '".$lines[$i]."';
			parEl.append(newEl);";
			}
		}
	if ($count != -1)
	{
	$code .= "
	prEl = document.getElementById('process');
	prEl.style.display = 'block';
	prEl.querySelector('input').value = '".$count."'; return true;}!!";
	}
	else $code .= "return true;}!!";
	$repl .= $code;
	mem($repl);
	echo $repl;
	exit();
  };

 function printMes(&$mysqli,$mess, $miss=true)
  { 
	global $repl;
	mem('printMes ...');
	$repl .= "!!%mesEl = document.getElementById('message');
	mesEl.innerHTML = '".$mess."';!!";
	mem($mess);
	if (!$miss)
		listing($mysqli,'','orderList',-1);
	return;
  }
  
  // цикл опроса каналов связи
	function polling(&$transp,$mysqli)
	{
	 mem('polling...');
//	 $query = "SELECT * FROM `calculations`";
//	 mem('$query = '.$query);
//	 if(!($res = $mysqli->query($query)))
//		return "??Error: ".$mysqli->error;
//	 $val=$res->fetch_assoc();
	 mem(' $transp: (0) = '.serialize($transp[0]));
	 mem(' $transp: (1) = '.serialize($transp[1]));
	 mem(' $transp: (2) = '.serialize($transp[2]));
	 $trLength = count($transp);
	 mem(' count($transp) = '.$trLength);
//	 $timeout = $val['receive_time'];
//	 mem('time = '.hrtime(true));
//	 mem(' $timeout = '.$timeout);
	 while($trLength > 0)
	 {
	 mem(' 1-st cycle ...');
//	 mem('time = '.hrtime(true));
	 $flag = 0;
	 foreach($transp as $key=>$tranObj)
		 {
		 mem(' 2-nd cycle ...');
		 $base_url = $tranObj->base_url;
		 mem(' $base_url = '.$base_url);
		 $path = $tranObj->path.$base_url;
		 mem(' $path = '.$path);
		 $orderNum = $tranObj->orderNum;
		 mem(' $orderNum = '.$orderNum);
		 
		 $exit = $tranObj->receive($orderNum,$path,$tranObj->req_status);
		 
		 mem ('$tranObj->trace = '.$tranObj->trace);
		 $tranObj->trace = '';
		 $query="SELECT * FROM `agents` WHERE `base_url`='".$base_url."'";
		 mem($query);
		 if(!($resag = $mysqli->query($query))) {mem(' $resag '); continue;}
		 $valag=$resag->fetch_assoc();
		 $resag->free();
		 $agent_name= $valag['agent_name'];
		 $price = $tranObj->priceRd;
		 $custom_date = $tranObj->custom_date;
		 if ( $valag['speed_Mode'] == 'standard')
		 {mem('$tranObj->coefficient = '.$tranObj->coefficient);
		 mem('$tranObj->basePrice = '.$tranObj->basePrice);
		 }
		 $error = $tranObj->error.$tranObj->errorRd;
		 mem('$tranObj->error = '.$error);
		 mem('$tranObj->crush = '.$tranObj->crush);
		 $tranObj->error='';
		 $error = (empty($error) OR $tranObj->crush === 0)? 'no':$error;
		 $status = 1;
		 mem(' count($transp) = '.$trLength);
//		 mem('$tranObj->receive_time = '.$tranObj->receive_time.', hrtime(true) = '.hrtime(true).', dif = '.intval($tranObj->receive_time)-hrtime(true));
		 if ($exit === true OR $tranObj->crush === 1)
			{
			unset($transp[$key]);
			$flag++;
			$status++;
			}
		 if ($error == 'no' && $tranObj->inform == '') $tranObj->inform = 'OK!';
		 $tranObj->req_status = $status;
		 $query = "UPDATE `calculations` SET `req_status`=".$status.", `custom_date`='".$tranObj->custom_date."', `price`='".$price."', `error`='".$error."', `inform`='".$tranObj->inform."' WHERE `orderNum`=".$tranObj->orderNum." AND `timeSelect`='".$valag['speed_Mode']."'";
		 mem($query);
		 $tranObj->inform = '';
		 if(!($mysqli->query($query)))
			{mem("Error: ".$mysqli->error); continue;}
//		 unlink($path);
		 $trLength = count($transp);
		 if ($flag != 0) listing($mysqli,'','calculation',$trLength);
		 }
	 }
	 listing($mysqli,'','calculation',$trLength);
	 exit();
	}
	
	// -----------------------------------------------------------
// ------------------------ begin --------------------
$repl = '';
if (($repl=linkBase($mysqli)) != '')
	{unset($_POST); mem('??Bad link:'.$repl); exit();}

 mem('$_POST = '.serialize($_POST));
 
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
	mem($query);
if (!$mysqli->query ($query)) 
	{  // доступа в базе нет 
	unset($_POST); mem(' Dialogue base is unsucceedable. '.$mysqli->error()); exit();
	}
 if (!isset($_POST['calc'])) 
	{
	printMes($mysqli,'Нет команды на расчет');
	unset($_POST);
	mem($repl);
	exit();
	}
 switch ($_POST['calc'])
 {
  case 'calculate':
	{
	$query="SELECT * FROM `orders` WHERE `was_read`=0";
	mem($query);
	if(!($res = $mysqli->query($query)) OR $res->num_rows == 0) { printMes($mysqli,'New `orders` not exist','calculation'); exit();}
	require_once 'transport.class.php';
	$trCnt=0;
	$transp=array();
	// цикл формирования запросов
	while ($val=$res->fetch_assoc())
		{
		 $select=$val['timeSelect'];
		 mem(' $select = '.$select);
		 switch ($select) 
			{
			 case 'all':
			 case 'fast':
				$query="SELECT * FROM `agents` WHERE `speed_Mode`='fast'";
				mem($query);
				if($restr = $mysqli->query($query))
				{
				 while ($valtr=$restr->fetch_assoc())
					{
					mem('$valtr: '.$valtr['base_url'].'+'.$valtr['speed_Mode'].'+'.$valtr['agent_name']);
					if (!class_exists('TransportFast')) { mem('class TransportFast not exists'); continue;}
					$transp[$trCnt]= $t = new TransportFast($valtr['base_url'],$val['orderNum'],$val['order_date'],$val['from'],$val['to'],$val['weight']);
					if (!$t->send($t->base_url,$t->orderNum,$t->orderDate,$t->sourceKladr,$t->targetKladr,$t->weightKg)) 
						{
					//	printMes($mysqli,$t->$error,'calculation');
						mem ($t->$error.$t->$trace);
						unset($transp[$trCnt]);
						}
						else 
						{	
						$error = $t->error;
						$error = ($error == '')? 'no':$error;
						mem(serialize($transp[$trCnt]));
						$query = "INSERT IGNORE INTO `calculations` (`orderNum`,`timeSelect`,`receive_time`, `agent_name`, `error`) VALUES (".$val['orderNum'].", '".$valtr['speed_Mode']."', ".$t->receive_time.", '".$valtr['agent_name']."', '".$error."')";
						mem($query);
						if(!($mysqli->query($query)))
							{mem("Error: ".$mysqli->error);}
						$query = "UPDATE `orders` SET `agent_id`='".$valtr['id']."' WHERE `orderNum`=".$val['orderNum'];
						mem($query);
						if(!$mysqli->query($query)) { mem('Error: '.$mysqli->error);}
						}
					$trCnt++;
					}
				}
				else { mem('??Error: '.$mysqli->error); }
				$restr->free();
				if ($select != 'all')
				break;
			 case 'standard':
				$query="SELECT * FROM `agents` WHERE `speed_Mode`='standard'";
				mem($query);
				if(!($restr = $mysqli->query($query))) {//$repl .='??Error: '.$mysqli->error;
												mem('??Error: '.$mysqli->error);
												break;}
					{while ($valtr=$restr->fetch_assoc())
						{
						mem('$valtr: '.$valtr['base_url'].'+'.$valtr['speed_Mode'].'+'.$valtr['agent_name']);
						if (!class_exists('TransportStandard')) { mem('class TransportStandard not exists'); continue;}
						$transp[$trCnt] = $t = new TransportStandard($valtr['base_url'],$val['orderNum'],$val['order_date'],$val['from'],$val['to'],$val['weight']);
						mem('for "standard" $t->orderNum = '.$t->orderNum);
						if (!$t->send($t->base_url,$t->orderNum,$t->orderDate,$t->sourceKladr,$t->targetKladr,$t->weightKg)) 
							{mem($t->$error.$t->trace);
							// printMes($mysqli,$t->$error,'calculation');
							unset($transp[$trCnt]);
							}
							else 
							{	
							mem(serialize($transp[$trCnt]));
							$query = "INSERT IGNORE INTO `calculations` (`orderNum`,`timeSelect`,`receive_time`, `agent_name`, `error`) VALUES (".$val['orderNum'].", '".$valtr['speed_Mode']."', ".$t->receive_time.", '".$valtr['agent_name']."', '".$error."')"; 
							mem($query);
							if(!($mysqli->query($query)))
								{mem("Error: ".$mysqli->error);}
							$agId = ($select == 'all')? 0:$valtr['id'];
							$query = "UPDATE `orders` SET `agent_id`='".$agId."' WHERE `orderNum`=".$val['orderNum'];
							mem($query);
							if(!$mysqli->query($query)) { mem('Error: '.$mysqli->error);}
							}
						$trCnt++;
						}
					}
				$restr->free();
				break;
			}
//		$query = "UPDATE `orders` SET `was_read`=1 WHERE `orderNum`=".$val['orderNum'];
//		if(!$mysqli->query($query)) { $repl .= '??Error: '.$mysqli->error;}
		}
	$res->free;
	polling($transp,$mysqli);
	break;
	}
	case 'restart':
	{
	$query="SELECT `c`.*, `a`.`base_url`, `o`.* FROM `calculations` AS `c` INNER JOIN `agents` AS `a` ON `c`.`agent_name`=`a`.`agent_name` LEFT JOIN `orders` AS `o` ON IF(`o`.`timeSelect` = 'all',`o`.`orderNum`=`c`.`orderNum`,(`o`.`orderNum`=`c`.`orderNum` AND `o`.`timeSelect`=`c`.`timeSelect`)) WHERE `c`.`req_status`<2";
	mem($query);
	if(!($res = $mysqli->query($query))) {mem(' $res '); break;}
	require_once 'transport.class.php';
	$trCnt=0;
	$transp=array();
	while ($val=$res->fetch_assoc())
		{
		if ($val['timeSelect'] == 'fast')
			{$transp[$trCnt]= $t = new TransportFast($val['base_url'],$val['orderNum'],$val['order_date'],$val['from'],$val['to'],$val['weight']);
			$t->receive_time=$val['receive_time'];
			$t->req_status=$val['req_status'];
			$t->base_url=$val['base_url'];
			mem('for "fast" $t->orderNum = '.$t->orderNum);
			mem('restart "fast" $t->receive_time = '.$t->receive_time.', $t->req_status = '.$t->req_status.',  $t->base_url = '.$t->base_url);
			$trCnt++;
			}
		else
			{$transp[$trCnt] = $t = new TransportStandard($valtr['base_url'],$val['orderNum'],$val['order_date'],$val['from'],$val['to'],$val['weight']);
			 $t->receive_time=$val['receive_time'];
			 $t->req_status=$val['req_status'];
			 $t->base_url=$val['base_url'];
			 mem('for "standard" $t->orderNum = '.$t->orderNum);
			 mem('restart "standard" $t->receive_time = '.$t->receive_time.', $t->req_status = '.$t->req_status.',  $t->base_url = '.$t->base_url);
			 $trCnt++;
			}
		}
	$res->free();
	polling($transp,$mysqli);
	break;
	}
	case 'appoint':
	{
		if (!empty($_POST['orderLine']))
			$orderLine = $_POST['orderLine'];
			mem ('$orderLine = '.$orderLine);
			$ststart = mb_strpos( $orderLine , "№ заказа = ")+11;
			mem ('№ заказа $ststart = '.$ststart);
			$length = mb_strpos( $orderLine , ";",$ststart)-$ststart;
			mem ('заказ $length = '.$length);
			$orderNum = (int)trim(mb_substr ( $orderLine , $ststart ,$length));
			mem ('$orderNum = '.$orderNum);
			$ststart = mb_strpos( $orderLine , "Заказчик: ")+11;
			mem ('Заказчик $ststart = '.$ststart);
			$length = mb_strpos( $orderLine , '"',$ststart)-$ststart;
			mem ('Заказчик $length = '.$length);
			$customer = trim(mb_substr ( $orderLine , $ststart ,$length));
			mem ('Заказчик = '.$customer);
			$ststart = mb_strpos( $orderLine , 'исполнитель: "')+14;
			mem ('исполнитель $ststart = '.$ststart);
			$length = mb_strpos( $orderLine , '"',$ststart)-$ststart;
			mem ('исполнитель $length = '.$length);
			$agent_name = trim(mb_substr ( $orderLine , $ststart ,$length));
			mem ('исполнитель $agent_name = '.$agent_name);
//			$query = "UPDATE `orders` AS `o`, (SELECT `a`.`agent_name` AS `agn`, `c`.* FROM `agents` AS `a` LEFT JOIN `calculations` AS `c` ON `a`.`agn` = '".$agent_name."' AND `c`.`agent_name` = `a`.`agn` AND `c`.`orderNum` = ".$orderNum.") AS `j` SET `o`.`custom_date`= `j`.`custom_date`, `o`.`agent_id`= `j`.`id` WHERE `o`.`orderNum`=".$orderNum;
//			$query = "UPDATE `orders` AS `o`, (SELECT `a`.`agent_name` AS `agn` FROM `agents` AS `a` INNER JOIN `calculations` AS `c` ON `agn` = '".$agent_name."' AND `c`.`agent_name` = `agn` WHERE `c`.`orderNum` = ".$orderNum.") AS `j` SET `o`.`custom_date`= `j`.`custom_date`, `o`.`agent_id`= `j`.`id` WHERE `o`.`orderNum`=".$orderNum;
			$query = "UPDATE `orders` AS `o` SET `o`.`custom_date`= (SELECT `custom_date` FROM `calculations` WHERE `orderNum` = ".$orderNum." AND `agent_name`= '".$agent_name."'), `o`.`agent_id`= (SELECT `id` FROM `agents` WHERE `agent_name`= '".$agent_name."') WHERE `o`.`customer`='".$customer."'";
			mem ('$query = '.$query);
			if(!$mysqli->query($query)) { mem('??Error: '.$mysqli->error);}
			listing($mysqli,'','orderList',-1);
		break;
	}
 }
//=============================================================
unset($_POST);
exit();

?>