<?php
// заголовки 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// файлы необходимые для соединения с БД 
include_once 'config/database.php';
include_once 'objects/data.php';
// получаем соединение с базой данных 
$database = new Database();
$db = $database->getConnection();
// создание объекта 'Data' 
 $dataDB = new dataDB($db);
// получаем данные 
$data = json_decode(file_get_contents("php://input"));
 
// устанавливаем значения 
$addressID = $data->addressID;
$api = $data->apiversion;
// подключение файлов jwt 
include_once 'config/core.php';
    // код ответа 
    http_response_code(200);
    function getBase64Img($image){
      if($image){
        return base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/api/img/'.$image));
        }
        return false;
    }
    function getDeputiesArr($arr){
      $deputies = array();
      for ($i=0; $i <count($arr); $i++) { 
        $deputies[$i]['deputiesID'] = $arr[$i]['deputiesID'];
        $deputies[$i]['deputiesName'] = $arr[$i]['deputiesName'];
        $deputies[$i]['deputiesBornDate'] = $arr[$i]['deputiesBornDate'];
        $deputies[$i]['deputiesNation'] = $arr[$i]['deputiesNation'];
        $deputies[$i]['deputiesParty'] = $arr[$i]['deputiesParty'];
        $deputies[$i]['deputiesEmail'] = $arr[$i]['deputiesEmail'];
        $deputies[$i]['constituenciesName'] = $arr[$i]['constituenciesName'];
        $deputies[$i]['constituenciesNumber'] = $arr[$i]['constituenciesNumber'];
        $deputies[$i]['parlamentId'] = $arr[$i]['parlamentId'];
        $deputies[$i]['parlamentName'] = $arr[$i]['parlamentName'];
        $deputies[$i]['img'] = getBase64Img($arr[$i]['deputiesImg']);
      }
      return $deputies;
    }
    //$reldata = $user->getUserData($user->id, $api);
    $reldata = $dataDB->getAdressData($addressID, $api);
    $main = array(
        "locationsName"=> $reldata[0]['locationsName'],
        "districsName" => $reldata[0]['districsName'],
        "regionsName" => $reldata[0]['regionsName'],
        "precinctsID" => $reldata[0]['precinctsID'],
        "precinctsName" => $reldata[0]['precinctsName'],
        "precinctsAdress" => $reldata[0]['precinctsAdress'],
        "precinctsNumber" => $reldata[0]['precinctsNumber'],
        "userAdressStreet" => $reldata[0]['userAdressStreet'],
        "userAdressNumber" => $reldata[0]['userAdressNumber'],
        "deputies" => getDeputiesArr($reldata)
      );
    echo json_encode(
        array(
            "error"=> 0,
            "message" => "Успешный вход в систему.",
            "data" => $main
        ), JSON_UNESCAPED_UNICODE
    );
// Если электронная почта не существует или пароль не совпадает, 
// сообщим пользователю, что он не может войти в систему 
?>