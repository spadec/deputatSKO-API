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
$data = json_decode(file_get_contents("php://input"));
$database = new Database();
$db = $database->getConnection();
$regID = $data->regID;
	$DBData = new dataDB($db);
    // код ответа 
    http_response_code(200);
    // показать детали 
    $res = $DBData->getDistrics($regID);
    if($res){
        echo json_encode(array(
        "error"=>0,
        "message" => $res
    	), JSON_UNESCAPED_UNICODE);
    }
    else {
    	echo json_encode(array(
        "error"=>1,
        "message" => "ошибка получения списка районов из БД"
    	), JSON_UNESCAPED_UNICODE);
    }
?>