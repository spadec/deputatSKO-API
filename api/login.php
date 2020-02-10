<?php
// заголовки 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// получаем данные 
$data = json_decode(file_get_contents("php://input"));
$api = $data->apiversion;
// подключение файлов jwt 
include_once 'config/core.php';

include_once 'libs/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/php-jwt-master/src/ExpiredException.php';
include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
if($api){
  include_once "version".$api."/login.php";
}
else {
    // файлы необходимые для соединения с БД 
    include_once 'config/database.php';
    include_once 'objects/user.php';

    // получаем соединение с базой данных 
    $database = new Database();
    $db = $database->getConnection();
    
    // создание объекта 'User' 
    $user = new User($db);    
    // устанавливаем значения 
    $user->phone = $data->phone;
    $phone_exists = $user->isUserActivate();
  // существует ли электронная почта и соответствует ли пароль тому, что находится в базе данных 
  if ( $phone_exists && password_verify($data->password, $user->password) ) {
      $token = array(
        "iss" => $iss,
        "aud" => $aud,
        "iat" => $iat,
        "nbf" => $nbf,
        "data" => array(
            "id" => $user->id,
            "firstname" => $user->firstname,
            "lastname" => $user->lastname,
            "phone" => $user->phone
        )
      );
      // код ответа 
      http_response_code(200);
      // создание jwt 

        $jwt = JWT::encode($token, $key);
        $reldata = $user->getUserData($user->id);
        $image = $reldata['deputiesImg'];
        if($image){
            $b64image = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/api/img/'.$image));
        }
        else {
          $b64image="";
        }
        echo json_encode(
            array(
                "error"=> 0,
                "message" => "Успешный вход в систему.",
                "jwt" => $jwt,
                "data" => $reldata,
                "img" => $b64image
            ), JSON_UNESCAPED_UNICODE
        );
  }
  // Если электронная почта не существует или пароль не совпадает, 
  // сообщим пользователю, что он не может войти в систему 
  else {
    // код ответа 
    http_response_code(200);
    // сказать пользователю что войти не удалось 
    echo json_encode(array("error"=>1,"message" => "Ошибка входа, если сообщение повторится пройдите регистрацию заново"));
  }
}
?>