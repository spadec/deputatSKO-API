<?php
// требуемые заголовки 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once 'objects/logger.php';
Logger::$PATH = dirname(__FILE__)."/objects/logs"; 
$logname = "logger";
$log = ""; 
// подключение к БД 
// файлы, необходимые для подключения к базе данных 
include_once 'config/database.php';
include_once 'objects/user.php';
// получаем соединение с базой данных 
$database = new Database();
$db = $database->getConnection();
 
// создание объекта 'User' 
$user = new User($db);
$data = json_decode(file_get_contents("php://input"));

$user->phone = $data->phone;
if($user->phone && $data->code){
	if($user->compareCode($data->code)){
		http_response_code(200);
   		 // покажем сообщение о том, что пользователь был создан 
    	echo json_encode(array("error"=>0,"message" => "Пользователь был активирован."));
    	$log .= "Пользователь был активирован кодом: ".$data->code."\r\n";
	}
	else {
		echo json_encode(array("error"=>1,"message" => "Код активации не верен или просрочен."));
		$log .= "Код активации не верен или просрочен: ".$data->code."\r\n";
	}
}
else {
    // устанавливаем код ответа 
    http_response_code(400);
    // покажем сообщение о том, что создать пользователя не удалось 
    echo json_encode(array("error"=>1,"message" => "Невозможно активировать пользователя."));
}
Logger::getLogger($logname)->log($log);