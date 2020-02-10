<?php
// заголовки 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// требуется для декодирования JWT 
include_once 'config/core.php';
include_once 'objects/data.php';
include_once 'config/database.php';

 
// получаем значение веб-токена JSON 
$data = json_decode(file_get_contents("php://input"));
$database = new Database();
$db = $database->getConnection();
// получаем JWT 
$street = $data->street;
$localityID = $data->localityID;
    // если декодирование выполнено успешно, показать данные пользователя 
    try {
    	$DBData = new dataDB($db);
        // код ответа 
        http_response_code(200);
        // показать детали 
        $res = $DBData->getStreetNumber($localityID,$street);
        if($res){
            echo json_encode(array(
            "error"=>0,
            "message" => $res,
        	),JSON_UNESCAPED_UNICODE);
        }
        else {
        	echo json_encode(array(
            "error"=>1,
            "message" => "ошибка получения улицы из БД",
            "data" => $data,
        	),JSON_UNESCAPED_UNICODE);
        }
    }
    // если декодирование не удалось, это означает, что JWT является недействительным 
    catch (Exception $e){
        // код ответа 
        http_response_code(401);
        // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
        echo json_encode(array(
            "error"=>1,
            "message" => "Доступ запрещён, token invalid",
            "error_desc" => $e->getMessage()
        ),JSON_UNESCAPED_UNICODE);
    }

?>