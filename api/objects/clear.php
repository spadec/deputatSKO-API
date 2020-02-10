<?php
class ClearLog {
	public static $file;//файл лога
	public static $descriptor;//его дескриптор 
	public static $period; // кол-во дней за которые сохранять записи в логе
	//$path - путь до каталога где лежат логи
	public function __construct($name,$period=2,$path="ajax/logs/"){
		$this->file = $path.$name;
		$this->period = $period;
		$this->descriptor = fopen($this->file, 'r');
	}
	/**
	* Точка входа. Формирует записи только укзанного периода из исходного лога и перезаписывает его
	*/
	public function toStrings(){
		if ($this->descriptor) {
	    	$arr = array();
	    	while (($string = fgets($this->descriptor)) !== false) {
	       		$stringData = $this->getStringData($string);
	       		if ($stringData["action"]=="time") {
       				$ActualStrings = $this->getActualStrings($stringData["value"]);
       				if($ActualStrings){
       					array_push($arr, $string);
       				}
	       		}
	       		elseif($stringData["action"]=="array") {
	       			array_push($arr, $string);
	       		}	
	    	}
	    	$newarr = $this->getOnlyTimes($arr);
  			$result = $this->clearAndWrite($newarr);
	   		if($result){
	   			return $result;
	   		}
	   		else {
	   			fclose($this->descriptor);
	   			return false;
	   		}
		} 
		else {
	    	return false;
		}
	}
	/**
	* На входе строка из лога, возвращает строку времени strtotime
	*/
	private function getStringTime($string){
		$firstChar = strpos($string, "[")+1;
		$lastChar = strpos($string, "]")-1;
		$newString = substr($string, $firstChar, $lastChar);
		$time = strtotime($newString);
		if($time){
			return $time;
		}
		else {
			return false;
		}
	}
	/**
	* На входе строка из лога возвращает массив в котором либо строка времени либо строка массива из лога(это строка которая пишется в лог в случаи ошибки или успешного выполнения)
	*/
	private function getStringData($string){
		$time = $this->getStringTime($string);
		if($time){
			return array("action"=>"time", "value"=>$time);
		}
		else {
			return array("action"=>"array", "value"=>$string);
		}
	}
	/**
	* На входе массив со строками лога, удаляет из начала массива, те строки которые без метки времени
	*/
	private function getOnlyTimes($arr){
		$newarr = array();
		$i=0;
		while($this->getStringTime($arr[$i])==false){
			unset($arr[$i]);
			$i++;
		}
		return $arr;
	}
	/**
	* Возвращает true если строка в файле 
	*/
	private function getActualStrings($time)
	{
		if($time){
			$lastWeek = strtotime("-".$this->period." day");
			$now = strtotime("now");
			if($time>$lastWeek){
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
		//return array(date("Y-m-d H:i:s", $now), date("Y-m-d H:i:s",$lastWeek));
	}
	/**
	* Очищает старый лог и записывает новые данные, закрывает поток
	*/
	private function clearAndWrite($arr){
		if($arr){
			file_put_contents($this->file, $arr);
			fclose($this->descriptor);
			return true;
		}
		else {
			return false;
		}
	}
}