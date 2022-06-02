<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5.0 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<TITLE>Модуль расчета транспортных услуг</title>
<META NAME="keywords" CONTENT="стоимость транспортных услуг">
<META name="description" content="модуль расчета транспортных услуг">

<meta http-equiv="Content-Language" content="ru">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type='text/javascript'>
// Создаем экземпляр класса XMLHttpRequest
function CreateRequest()
{
    var Request = false;

    if (window.XMLHttpRequest)
    {
        //Gecko-совместимые браузеры, Safari, Konqueror
        Request = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        //Internet explorer
        try
        {
             Request = new ActiveXObject("Microsoft.XMLHTTP");
        }    
        catch (CatchException)
        {
             Request = new ActiveXObject("Msxml2.XMLHTTP");
        }
    }
 
    if (!Request)
    {
        alert("Невозможно создать XMLHttpRequest");
    }
    
    return Request;
} 
var request = CreateRequest();

// Указываем путь до файла на сервере, который будет обрабатывать наш запрос 
var url = "sendRequest.php";
 
 
function inputOn(altern,listid,orderLine)
{
document.getElementById("message").innerHTML = '';
// Cоставляем строку с данными запроса
let params = 'calc='+altern;
params += (orderLine == '')? '':'&orderLine='+orderLine;
/* соединение будет POST, путь к файлу в переменной url, запрос -
асинхронный*/ 
request.open("POST", url, true);
//В заголовке: тип передаваемых данных закодирован. 
request.setRequestHeader("Content-type","application/x-www-form-urlencoded;charset=UTF-8");

//	Передаем строку с данными, которую сформировали выше. И выполняем запрос. 
console.log(params);
request.send(params);

request.addEventListener("readystatechange", function() {
//	alert ("addEventListener: "+request.readyState);
	if(request.readyState===1||request.readyState===2||request.readyState===3)
		document.getElementById("calculation").style.display = "block";
    if(request.readyState === 4 && request.status === 200) // запрос выполнен и ОК
	{	
	 console.log('listid = '+listid);
	 calcEl = document.getElementById(listid);
	 console.log('calcEl = '+calcEl);
	 if (listid == "calculation")
		{
			calcEl.classList.remove("clOn");
		}
		var responseT=request.responseText;
		console.log('responseT = '+responseT);
		const scrStr = responseT.split('!!');
		scrStrLen = scrStr.length;
		console.log('размер массива scrStrLen = '+scrStrLen);
		console.log('размер 0 строки массива для сравнения с длиной ответа ('+responseT.length+') = '+scrStr[0].length);
		if (scrStrLen != 0 && scrStr[0].length != responseT.length)
		{
		for (let i=0; i<scrStrLen; i++)
			{
			if(scrStr[i] != '' && scrStr[i].substring(0,1) =='%')
				{
				console.log('scrStr['+i+'] = '+scrStr[i]);
				if (document.getElementById("scrId")!== null)
					document.getElementById("scrId").remove();
				newEl=document.createElement("script");
				newEl.setAttribute("type","text/javascript");
				newEl.id = "scrId";
				newEl.innerHTML = scrStr[i].substring(1);
				document.body.append(newEl);
				}
			}
		for (let i=0; i<scrStrLen; i++)
			{
			 responseT.replace('!!%'+scrStr[i]+'!!', '');
			}
		}
		newEl=document.createElement('li');
		if(responseT.substring(0,2) == '??')
			newEl.innerHTML = 'Error: '+responseT.substring(2);
		  else
			{
			if(responseT == '')
				{
				 newEl.innerHTML = 'Error: Empty list';
				}
			 else
				{
				 if (typeof execf === "function")
				 { 
				 if (execf())
					 {
						if (listid == "calculation")
							calcEl.classList.add("clOn");
					 }
				 }
				}
			}
		calcEl.append(newEl);
		prEl = document.getElementById('process');
		console.log('опрос остатка = '+prEl.querySelector('input').value);
		setTimeout(function()
		{
		if (parseInt(prEl.querySelector('input').value) > 0)
			{
			let params = 'calc=restart';
			request.open("POST", url, true);
			request.setRequestHeader("Content-type","application/x-www-form-urlencoded;charset=UTF-8");
			console.log(params);
			request.send(params)
			}
		}, 5000);
	}
});
    
};
</script>
<style type="text/css">
#calculation {
	cursor: pointer;
	display: none;
}
#calculation:before {
	content: 'Полученные данные о стоимости';
	font: small;
	color: blue;
	display: none;
}
#calculation:after {
	content: 'Выбор строки расчета приводит к назначению исполнителя на заказ';
	font: small;
	color: green;
	display: none;
}
#calculation.clOn:before, #calculation.clOn:after {
	display: block;
}
</style>
</head>
<body>
<div id="message"></div>

<form action="" method="POST" name="order_form">
Заказчик:<input type="text" size="20" name="customer" value=""><br><br>
Откуда:<input type="text" size="20" name="from" value=""><br><br>
Куда:<input type="text" size="20" name="to" value=""><br><br>
Сколько:<input type="text" size="20" name="weight" value=""><br><br>
Срок доставки:
<p><input name="time" type="radio" value="fast">срочно</p>
<p><input name="time" type="radio" value="standard">стандарт</p>
<p><input name="time" type="radio" value="all" checked> Все</p>
<br>
<input type="submit" name="send" value="Сформировать заказ">
</form>
<br>
<div>
<form action="" method="POST">
<input type="submit" name="show" value="Показать список заказов">
</form>
</div>
<div><ul id='orderList'></ul></div>

<input type="submit" name="calc" value="Рассчитать заказы" onclick= "inputOn('calculate','calculation','')">
<label id='process' style='display: none'>Количество ожидаемых ответов на запросы <input type="text" size="3" name="process" value="" readonly></label>
<div><ul id='calculation'></ul></div>
<script type='text/javascript'>
calcEl = document.getElementById("calculation");
calcEl.addEventListener('click',function(){
	event.stopPropagation();
	console.log('event.target = '+event.target);
	if (event.target.tagName == 'LI')
	{
		line = event.target.innerHTML;
		console.log('line = '+line);
		if(line.indexOf('ошибка') == -1)
			inputOn('appoint','orderList',line);
		else document.getElementById("message").innerHTML = 'Невозможно назначить - нет расчёта';
	}
})
</script>
<form action="transpAgent.php" method="POST" name="transit_form">
<input type="submit" name="jump" value="Список транспортных агентов">
</form>
<?php include 'main.php';
?>

</body>

</html>
