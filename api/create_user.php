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
$log = "/*************************************************/\r\n";
$log .= "IP:".$_SERVER['REMOTE_ADDR']."\r\n";

// подключение к БД 
// файлы, необходимые для подключения к базе данных 
include_once 'config/database.php';
include_once 'objects/user.php';
include_once 'libs/smsc_api.php';
 
// получаем соединение с базой данных 
$database = new Database();
$db = $database->getConnection();
 
// создание объекта 'User' 
$user = new User($db);
 
// получаем данные 
$data = json_decode(file_get_contents("php://input"));
$log .= "Регистрируется телефон ".$data->phone."\r\n"; 
// устанавливаем значения 
$user->firstname = $data->firstname;
$user->lastname = $data->lastname;
$user->thirdname = $data->thirdname;
$user->email = $data->email;
$user->phone = $data->phone;
$user->password = $data->password;
$user->adress_id = $data->adress_id;
$isPhone = $user->phoneExists();
if($isPhone){
    // код ответа 
    http_response_code(200);
            echo json_encode(array(
        "error"=>1,
        "message" => "номер ".$data->phone." уже зарегистрирован",
        "sms"=>""
    ));
            $log .= "номер ".$data->phone." уже зарегистрирован\r\n"; 
}//надо проверить возможно он уже создан но не активирован
else if($user->phoneExistsWithoutActivation()){
     http_response_code(200);
      $phone = preg_replace("/[^0-9]/", '', $data->phone);
            $sms_text = ''.rand(0, 9).rand(10, 99).rand(0, 9);

            list($sms_id, $sms_cnt, $cost, $balance) = send_sms($phone, "Ваш код для Deputat SKO: ".$sms_text, 1);
            if($sms_cnt > 0){
                $sms_status = 1;    
            }else{
                $sms_status = -1;
                $log .= "ошибка отправки СМС\r\n";
            }
            if($sms_status > 0) {
                $values = array('sphone' => $phone, 'stext'=>$sms_text, 'sstatus'=>$sms_status);
                $user->setCode($values);
                $log.= "Телефон уже существует в БД. Отправлена еще одна СМС".$sms_text."\r\n";
                            // покажем сообщение о том, что пользователь был создан 
                $log .= "СМС отправлена, ее код:".$sms_text."\r\n";
            echo json_encode(array("error"=>0,"message" => "Пользователь был создан.","sms"=>$sms_status));
            }

}
else {
// создание пользователя 
    if (
        !empty($user->firstname) &&
        !empty($user->password) &&
        !empty($user->phone) &&
        !empty($user->adress_id) && $user->create($data->adress_id)
    ) {
       try{
        //Тут отправляем СМС

            // устанавливаем код ответа 
            http_response_code(200);
            $phone = preg_replace("/[^0-9]/", '', $data->phone);
            $sms_text = ''.rand(0, 9).rand(10, 99).rand(0, 9);
            list($sms_id, $sms_cnt, $cost, $balance) = send_sms($phone, "Ваш код для Deputat SKO: ".$sms_text, 1);
            if($sms_cnt > 0){
                $sms_status = 1;    
            }else{
                $sms_status = -1;
                $log .= "ошибка отправки СМС\r\n";
            }
            if($sms_status > 0) {
                $values = array('sphone' => $phone, 'stext'=>$sms_text, 'sstatus'=>$sms_status);
                $user->setCode($values);
                $log .= "СМС отправлена, ее код:".$sms_text."\r\n";
                echo json_encode(array("error"=>0,"message" => "Пользователь был создан.","sms"=>$sms_status));
            }
            // покажем сообщение о том, что пользователь был создан 
    	}
    	catch (Exception $e){
        
            // код ответа 
            http_response_code(401);
        
            // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
            echo json_encode(array(
                "error"=>1,
                "message" => "Доступ закрыт.".$e->getMessage(),
            ));
        }
    }
        // сообщение, если не удаётся создать пользователя 
    else {
     
        // устанавливаем код ответа 
        http_response_code(400);
     
        // покажем сообщение о том, что создать пользователя не удалось 
        echo json_encode(array("error"=>1,"message" => "Невозможно создать пользователя."));
    }
}
Logger::getLogger($logname)->log($log);
?>