<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// требуется для декодирования JWT 
include_once 'config/core.php';
include_once 'objects/data.php';
include_once 'objects/logger.php';
Logger::$PATH = dirname(__FILE__)."/objects/logs";
include_once 'mailer.php';
include_once 'config/database.php';
include_once 'libs/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/php-jwt-master/src/ExpiredException.php';
include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
// получаем значение веб-токена JSON 
$data = json_decode(file_get_contents("php://input"));
$log = "IP:".$_SERVER['REMOTE_ADDR']."\r\n";
// получаем JWT 
$jwt=isset($data->jwt) ? $data->jwt : "";
$depId = $data->depID;
$message = $data->message;
$logname = "logger";

// если JWT не пуст 
if($jwt) {
    // если декодирование выполнено успешно, показать данные пользователя 
    try {
               // декодирование jwt 
        http_response_code(200);
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        $log .= "Сообщение отправляет пользователь: ".$decoded->data->phone."\r\n";
        $message .= "\r\nСообщение от пользователя: ".$decoded->data->phone."\r\n";
        $database = new Database();
        $db = $database->getConnection();
        $dataDB = new dataDB($db);
        $deputies = $dataDB->getDeputiesData($depId);
        $userEmail = $dataDB->getUserEmail($decoded->data->id);
        $log .="Сообщения для депутата ID: ".$data->depID."(".$deputies["name"].")\r\n";
        $message = str_replace("|n", "<br />", $data->message);
        $log .= "Текст сообщения: ".$message."\r\n";
        $subject = $data->subject;
        if($userEmail){
            $message .= "Пользователь оставил свой email для связи: ".$userEmail."\r\n";
        }
        $mailer = new mailer();
        $isSend = $mailer->sendMessage($deputies['email'],$message,$subject);
        if($isSend){
            $log .= "Сообщение успешно отправлено на email: ".$deputies['email']."\r\n";
            // показать детали
            echo json_encode(array(
            "error"=>0,
            "message" => $isSend,
            "data" => $decoded->data
            ));
        }
        else {
            $log .= "Сообщение не было отправлено из-за ошибки";
            $subject = "Ошибка отправки сообщения депутату из приложения DeputatSKO";
            $mailer->sendMessage('obl-maslihat@sko.kz,samdenger@gmail.com',$message,$subject);
            // показать детали
            echo json_encode(array(
            "error"=>1,
            "message" => "Сообщение не было отправленно из-за ошибки на сервере",
            "data" => $decoded->data
            ));
        }
    }
    // если декодирование не удалось, это означает, что JWT является недействительным 
    catch (Exception $e){
    	$log .= "Попытка отправить сообщение с просроченным или недействительным токеном";
        // код ответа 
        http_response_code(401);
        // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
        echo json_encode(array(
            "error"=>1,
            "message" => "Доступ закрыт.".$e->getMessage()
        ));
    }
}
// показать сообщение об ошибке, если jwt пуст 
else {
    // код овтета 
    http_response_code(401);
    $log .= "Попытка отправить сообщение без авторизации";
    // сообщить пользователю что доступ запрещен 
    echo json_encode(array("error"=>1,"message" => "Доступ запрещён. Не передан параметр token"));
}
Logger::getLogger($logname)->log($log);