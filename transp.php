<?php
if (!$_POST) {exit();}

// 
if (!isset($_POST['send']) ) {unset($_POST); exit();}
$repl='';
$base_url = trim($_POST['to']);
$agentName = trim($_POST['agentName']);
$speedMode = $_POST['time'];
$mysqli;
function linkBase(&$repl)
 {
 if (!$mysqli)
	{
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	include_once 'defbaseP.php';
	if ( !($mysqli = @new mysqli(host, user, password, db_name)))
	 { 
	 if ( $mysqli->connect_errno)
		{
		$$repl .= " Error: unsuccesful attempt to connect to base:".$mysqli->mysqli_connect_error();
		}
	 return false;
	 }
	}
// $mysqli->set_charset('utf8');
 return $mysqli;
 }
 if (!($mysqli=linkBase($repl))) {echo '??Data base is unsucceedable'; unset($_POST); exit();}
 if (empty($base_url) || empty($agentName)) {printMes($mysqli,'Введены не все данные'); exit();}
 $query ="CREATE TABLE IF NOT EXISTS `agents` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`base_url` VARCHAR(64),
	`speed_Mode` VARCHAR(8), /* fast, standard */
	`agent_name` VARCHAR(32)
      )";
 if (!($mysqli->query($query))) 
	{  // доступа в базе нет 
	echo '??Objects is unsucceedable'; unset($_POST);
	exit();
	}
 if ($_POST['send'] == 'Создать' )
  {
   $query = "SELECT `id` FROM `agents` WHERE `base_url`='".$base_url."'";
   if($res = $mysqli->query($query)) 
	  if ($res->num_rows!=0) {listing($mysqli,'??Objects is already exist'); unset($_POST); exit();}
   $res->free();
   $query = "INSERT INTO `agents` (`base_url`,`speed_Mode`,`agent_name`) VALUES ('".$base_url."' ,'".$speedMode."','".$agentName."')";
   if(!($mysqli->query($query)))
	{listing($mysqli, '??Error: '.$mysqli->error); unset($_POST); exit();}
	
  }
  elseif ($_POST['send'] == 'Редактировать' )
	{
	$id = $_POST['id'];
	if (empty($id)) {listing($mysqli,'??Error of program'); unset($_POST); exit();}
	$query = "SELECT * FROM `agents` WHERE `id`='".$id."'";
   if($res = $mysqli->query($query)) 
	  if ($res->num_rows==0) {listing($mysqli,'??Objects is already exist'); unset($_POST); exit();}
   $res->free();
	 $query = "UPDATE `agents` SET `speed_Mode`='".$speedMode."', `agent_name`='".$agentName."', `base_url`='".$base_url."' WHERE `id`='".$id."'";
   if(!($res = $mysqli->query($query))) {echo '??Unsuccessful attempt to edit record'; unset($_POST); exit();}
	}
	else {listing($mysqli, '??Unknown choice'); unset($_POST); exit();}
 function getRec($mysqli)
  {
   if(!($res = $mysqli->query("SELECT * FROM `agents`")))
   	return "??Error: ".$mysqli->error;
    else
	{
	$repl = '';
	while ($val=$res->fetch_assoc())
		{
		$repl .= $val['id']." | ".$val['agent_name']." | ".$val['base_url']." | ".$val['speed_Mode']."??";
		}
	$res->free();
	return $repl; 
	}	
  };
  
 function listing($mysqli,$prime)
  {
	$code = "<script type='text/javascript'>
	window.addEventListener('DOMContentLoaded', function ()
	{ 
	orderEl = document.getElementById('agentList');
	newEl=document.createElement('li');";
	if($prime != '')
	$code .= "newEl.innerHTML = 'Error: ".substr($prime,2)."';";
	$repl = getRec($mysqli);
	if($repl == '')
		$code .= "newEl.innerHTML = 'Error: Empty list';";
	   else
		{
		$lines = explode('??', $repl);
		for ($i=0; $i < count($lines); $i++)
			{
			if (strlen($lines[$i]) === 0) continue;
			$code .= "newEl.innerHTML = '".$lines[$i]."';
	orderEl.append(newEl);
	newEl=document.createElement('li');";
			}
		$code .= "newEl.innerHTML = '== End of list ==';";
		}
	$code .= "orderEl.append(newEl);});</script>";	
	echo $code;
  };
  
 function printMes($mysqli,$mess)
  {  
	echo "<script type='text/javascript'>
	window.addEventListener('DOMContentLoaded', function ()
	{ 
	mesEl = document.getElementById('message');
	newEl=document.createElement('span');
	newEl.innerHTML = '".$mess."';
	mesEl.append(newEl);
	})</script>";
	listing($mysqli,'');
  }
listing($mysqli,'');
unset($_POST);
?>