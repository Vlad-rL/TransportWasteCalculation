<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5.0 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<TITLE>Подключение транспортных агентов</title>
<META NAME="keywords" CONTENT="транспортный агент">
<META name="description" content="Подключение транспортных агентов
<meta http-equiv="Content-Language" content="ru">
<meta name="viewport" content="width=device-width, initial-scale=1">

<body>

<div id="message"></div>
<form action="" method="POST" name="orderform">
Наименование:<input type="text" size="20" name="agentName" value=""><br><br>
base_url:<input type="text" size="20" name="to" value=""><br><br>
тип доставки:
<p><input name="time" type="radio" value="fast">срочно</p>
<p><input name="time" type="radio" value="standard" checked>стандарт</p>

<br>
<input id="choice" type="submit" name="send" value="Создать">
<input type="hidden" name="id" value="">
</form>
<div><ul id='agentList'></ul></div>
<script type='text/javascript'>
function agentChoice(obj)
{
	let agentInf = obj.innerHTML;
	let data = agentInf.split(' | ');
	formPoint=document.forms.orderform;
	formPoint.id.value=data[0];
	formPoint.agentName.value=data[1];
	formPoint.to.value=data[2];
	console.log (formPoint.time);
	if (data[3] == 'fast')
		formPoint.time[0].checked=true;
	  else
		  formPoint.time[1].checked=true;
	chEl=document.getElementById('choice');
	chEl.value = 'Редактировать';
}
let agentListEl=document.getElementById('agentList');
agentListEl.addEventListener('click',function(){
	event.stopPropagation();
	agentChoice(event.target);
})
</script>
<form action="index.php" method="GET" name="transit_form">
<input type="submit" name="return" value="Вернуться к заказам">
</form>

<?php include 'transp.php';
?>

</body>

</html>
