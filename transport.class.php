<?php
class Transport
{
 public $base_url='';
 public $price = 0.0;
 public $custom_date = '';
 public $error = '';
 public $inform = '';
 public $orderNum = 0;
 public $orderDate = '';
 public $sourceKladr = '';
 public $targetKladr = '';
 public $weightKg = 0.0;
 public $jsonRec;
 public $crush=0;
 public $receive_time = 0;
 public $num_return;
 public $path = 'exchange/';
 public $channel;
 public $time_out=5000000000; // in nano seconds
 public $req_status;
 public $trace = '';
 // returned variables
 public $orderNumRd;
 public $base_urlRd;
 public $priceRd = 0.0;
 public $dateRd;
 public $errorRd;

 public function __construct($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg)
  {
  $this->base_url = $base_url;
  $this->orderNum = $orderNum;
  $this->orderDate = $orderDate;
  $this->sourceKladr = $sourceKladr;
  $this->targetKladr = $targetKladr;
  $this->weightKg = $weightKg;
  }
  function commonView($jsonRec,$req_status,$num_return)
  {return;}
  function emulate($orderNum,$base_url,$sourceKladr,$targetKladr,$weightKg)
  {return;}
  public function send($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg)
  {
	  if ($this->emulate($orderNum,$base_url,$sourceKladr,$targetKladr,$weightKg)) return true;
	  else
	  {
		$this->crush = 1;
		$this->error .= ' Отправка данных не состоялась.';
		return false;
	  }
  }

  public function receive($orderNum,$channel,$req_status)
  {
	if (!file_exists($channel))
		{
		$this->crush = 1;
		$this->error .= ' Нет канала связи "'.$channel.'".';
		return false;
		}
		else
		  {
			if (!($records=file($channel,FILE_SKIP_EMPTY_LINES)))
				{
				$this->crush = 1;
				$this->error .= ' Ошибка канала связи "'.$channel.'".';
				return false;
				}
			$this->crush = 0;
			foreach ($records as $key=>$record)
				{
				if($this->commonView($record,$req_status) != $orderNum) continue;
					else
					{
					if (hrtime(true) >= $this->receive_time)
						{
						$this->commonView($record,$req_status,false);
						unset ($records[$key]);
						if (file_put_contents($channel,$records) === FALSE)
							{
							$this->crush = 0;
							$this->inform .= ' Канал связи "'.$channel.'" работает неправильно.';
							}
						return true;
						}
						else
							{
							$this->inform = ' Данные по заказу '.$orderNum.' от канала связи "'.$channel.'" ещё не поступили.';
							return false;
							}
					}
				}
			$this->crush = 1;
			$this->error .= ' Нет данных по заказу '.$orderNum.' на запрос к "'.$channel.'".';
			return false;
		  }
  }

  protected function sendrec($channel,$jsonRec)
 {
	if (!$file=fopen( $channel, 'a+')) return FALSE;
	if (fwrite($file, $jsonRec.PHP_EOL) === FALSE) return FALSE;
	fclose($file);
	$t=hrtime(true);
	$this->receive_time = rand($t,$t+$this->time_out);
	return TRUE;
 }

} // class Transport end

class TransportStandard extends Transport
{
 public $basePrice = 150.0;
 public $coefficient = 0.0;


 public function __construct($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg)
  {
  parent::__construct($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg);
  }
 public function emulate($orderNum,$base_url,$sourceKladr,$targetKladr,$weightKg)
 {
	$this->error = (rand(0,100)>80)? ' Emulated error.':'';
	if (empty($this->error))
	{
	$this->coefficient = floatval(rand(1, 50).".".rand(0, 99));
	$custom_date = rand(1,60)*24*60*60;	// in seconds
	}
	else
	{
	$this->coefficient = 0.0;
	$custom_date = 0;
	}
	$this->jsonRec = json_encode(array(
	"orderNum"=>$orderNum,
	"base_url"=>$base_url,
	"coefficient"=>$this->coefficient,
	"custom_date"=>$custom_date,
	"error"=>$this->error ),JSON_NUMERIC_CHECK);
	$this->channel = $this->path.$base_url;
	if($this->sendrec($this->channel,$this->jsonRec))return TRUE;
	else return FALSE;
}

 public function commonView($jsonRec,$stat,$num=true)
 {
	$calcAr = json_decode($jsonRec,TRUE);
	$this->orderNumRd = $calcAr['orderNum'];
	if ($num) return $this->orderNumRd;
	if ($stat>1) return true;
	$this->base_urlRd = $calcAr['base_url'];
	$this->priceRd = floatval($calcAr['coefficient'])*floatval($this->basePrice);
	$this->custom_date = date("Y-m-d",strtotime($this->orderDate)+(int)$calcAr['custom_date']);
	$this->errorRd = $calcAr['error'];
	return true;
 }

} // class TransportStandard end

class TransportFast extends Transport
{
 private $period = 0;

 public function __construct($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg)
 {
 parent::__construct($base_url,$orderNum,$orderDate,$sourceKladr,$targetKladr,$weightKg);
 }
 public function emulate($orderNum,$base_url,$sourceKladr,$targetKladr,$weightKg)
 {
	$this->trace .= ' TransportFast::emulate('.$orderNum.', '.$base_url.', '.$sourceKladr.', '.$targetKladr.', '.$weightKg.'); ';
	$this->error = rand(0,100)>80 ? ' Emulated error.':'';
	if ($this->error == '')
	{
	$this->price = rand(150, 5000).".".rand(0, 99);
	$period = rand(1,60);
	}
	else
	{
	$this->price = 0.0;
	$period = 0;
	}
	$this->jsonRec = json_encode( array(
	"orderNum"=>$orderNum,
	"base_url"=>$base_url,
	"price"=>$this->price,
	"period"=>$period,
	"error"=>$this->error),JSON_NUMERIC_CHECK);
	$this->channel = $this->path.$base_url;
	if($this->sendrec($this->channel,$this->jsonRec))return TRUE;
	else return FALSE;
 }

 public function commonView($jsonRec,$stat,$num=true)
 {
 $calcAr = json_decode($jsonRec,TRUE);
 $this->orderNumRd = $calcAr['orderNum'];
 if ($num) return $this->orderNumRd;
 if ($stat>1) return true;
 $this->base_urlRd = $calcAr['base_url'];
 $this->priceRd = floatval($calcAr['price']);
 $this->trace .= ' TransportFast::commonView on exit $this->priceRd = '.$this->priceRd;
 $this->custom_date = date("Y-m-d",strtotime($this->orderDate)+$calcAr['period']*24*60*60);
 $this->errorRd = $calcAr['error'];
 }

} // class TransportFast end
?>
