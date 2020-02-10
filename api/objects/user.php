<?php
// объект 'user' 
class User {
 
    // подключение к БД таблице "users" 
    private $conn;
    private $table_name = "users";
 
    // свойства объекта 
    public $id;
    public $firstname;
    public $lastname;
    public $thirdname;
    public $email;
    public $phone;
    public $adress_id;
    public $password;
    // конструктор класса User 
    public function __construct($db) {
        $this->conn = $db;
    }

    // Создание нового пользователя 
    function create($adr) {
    
        // Вставляем запрос 
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    firstname = :firstname,
                    lastname = :lastname,
                    thirdname = :thirdname,
                    email = :email,
                    phone = :phone,
                    password = :password,
                    adreses_id = :adreses_id";
    
        // подготовка запроса 
        $stmt = $this->conn->prepare($query);
    
        // инъекция 
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->lastname=htmlspecialchars(strip_tags($this->lastname));
        $this->thirdname=htmlspecialchars(strip_tags($this->thirdname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->password=htmlspecialchars(strip_tags($this->password));
        $adr=htmlspecialchars(strip_tags($adr));
        // привязываем значения 
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':thirdname', $this->thirdname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':adreses_id', intval($adr));
        // для защиты пароля 
        // хешируем пароль перед сохранением в базу данных 
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);
       
        // Выполняем запрос 
        // Если выполнение успешно, то информация о пользователе будет сохранена в базе данных 
          
        return $stmt->execute();

        
        //return false;
    }
    function phoneExistsWithoutActivation(){
        $query = "SELECT id, firstname, lastname, email, password
                FROM " . $this->table_name . "
                WHERE phone = ?
                LIMIT 0,1";
                        // подготовка запроса 
        $stmt = $this->conn->prepare( $query );
        // инъекция 
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        // привязываем значение e-mail 
        $stmt->bindParam(1, $this->phone);
        // выполняем запрос 
        $stmt->execute();
        // получаем количество строк 
        $num = $stmt->rowCount();
        // если телефон существует, 
        if($num>0) {
            return true;
        }
        return false;
    }
    // Проверка, существует ли телефон в нашей базе данных 
    function phoneExists(){
     
        // запрос, чтобы проверить, существует ли электронная почта 
        $query = "SELECT id, firstname, lastname, email, password
                FROM " . $this->table_name . "
                WHERE phone = ? AND enable = 1
                LIMIT 0,1";

     
        // подготовка запроса 
        $stmt = $this->conn->prepare( $query );
     
        // инъекция 
        $this->phone=htmlspecialchars(strip_tags($this->phone));
     
        // привязываем значение e-mail 
        $stmt->bindParam(1, $this->phone);
     
        // выполняем запрос 
        $stmt->execute();
     
        // получаем количество строк 
        $num = $stmt->rowCount();
     
        // если телефон существует, 
        // присвоим значения свойствам объекта для легкого доступа и использования для php сессий 
        if($num>0) {
     
            // получаем значения 
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
     
            // присвоим значения свойствам объекта 
            $this->id = $row['id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->password = $row['password'];
            $this->email = $row['email'];
            $this->adress_id = $row['adress_id'];
            // вернём 'true', потому что в базе данных существует телефон
            return true;
        }
     
        // вернём 'false', если адрес электронной почты не существует в базе данных 
        return false;
    }
     
    public function isUserActivate(){
        // запрос, чтобы проверить, существует ли электронная почта 
        $query = "SELECT id, firstname, lastname,thirdname, email, password
                FROM " . $this->table_name . "
                WHERE phone = ? AND enable = 1
                LIMIT 0,1";
     
        // подготовка запроса 
        $stmt = $this->conn->prepare( $query );
     
        // инъекция 
        $this->phone=htmlspecialchars(strip_tags($this->phone));
     
        // привязываем значение e-mail 
        $stmt->bindParam(1, $this->phone);
     
        // выполняем запрос 
        $stmt->execute();
     
        // получаем количество строк 
        $num = $stmt->rowCount();
     
        // если телефон существует и активирован, 
        // присвоим значения свойствам объекта для легкого доступа и использования для php сессий 
        if($num>0) {
     
            // получаем значения 
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
     
            // присвоим значения свойствам объекта 
            $this->id = $row['id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->thirdname = $row['thirdname'];
            $this->password = $row['password'];
            $this->email = $row['email'];
     
            // вернём 'true', потому что в базе данных существует телефон
            return true;
        }
     
        // вернём 'false', если адрес электронной почты не существует в базе данных 
        return false;
    }
     
    // обновить запись пользователя 
    public function update($address_id){
        // Если в HTML-форме был введен пароль (необходимо обновить пароль) 
        $password_set=!empty($this->password) ? ", password = :password" : "";
     
        // если не введен пароль - не обновлять пароль 
        $query = "UPDATE " . $this->table_name . "
                SET
                    firstname = :firstname,
                    lastname = :lastname,
                    thirdname = :thirdname,
                    email = :email,
                    phone = :phone,
                    adreses_id = :adreses_id
                    {$password_set}
                WHERE id = :id";
     
        // подготовка запроса 
        $stmt = $this->conn->prepare($query);
     
        // инъекция (очистка) 
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->lastname=htmlspecialchars(strip_tags($this->lastname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->thirdname=htmlspecialchars(strip_tags($this->thirdname));
        $address_id = htmlspecialchars(strip_tags($address_id));
        // привязываем значения с HTML формы 
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone',$this->phone);
        $stmt->bindParam(':thirdname',$this->thirdname);
        $stmt->bindParam(':adreses_id',$address_id);
        // метод password_hash () для защиты пароля пользователя в базе данных 
        if(!empty($this->password)){
            $this->password=htmlspecialchars(strip_tags($this->password));
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $password_hash);
        }
     
        // уникальный идентификатор записи для редактирования 
        $stmt->bindParam(':id', $this->id);
     
        // Если выполнение успешно, то информация о пользователе будет сохранена в базе данных 
        if($stmt->execute()) {
            return true;
        }
     
        return false;
    }
    /**
    * Запись кода активации в БД
    */
    public function setCode($values){
        $query = "INSERT INTO sms SET sphone = :sphone, stext = :stext, sstatus = :sstatus";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sphone', $values["sphone"]);
        $stmt->bindParam(':stext',  $values["stext"]);
        $stmt->bindParam(':sstatus', $values["sstatus"]);
        $stmt->execute($values);
        return true;
    }
    /**
    * Активация пользователя
    */
    public function userActivate(){
        $query = "UPDATE " . $this->table_name . "
                SET enable = 1 WHERE phone = :phone";
        $this->phone=htmlspecialchars(strip_tags($this->phone));   
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->execute();
        return true;
    }
    /**
    * отправка СМС для активации пользователя
    */
    private function getCode(){
        $query = "SELECT stext
                FROM sms
                WHERE sphone = ? ORDER BY sdate desc
                LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        // инъекция 
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        // привязываем значение e-mail 
        $stmt->execute(array($this->phone));
        // выполняем запрос 
        $result = $stmt->fetchColumn();
        return $result; 
    }
    /**
    * Проверяем код активации если код совпадает активируем пользователя
    */
    public function compareCode($code){
        $codeInDB = $this->getCode($this->phone);
        if($codeInDB == $code){
            if($this->userActivate()){
                return true;
            }
        }
        return false;
    }
    /**
    *
    */
    public function getUserData($userID,$version=""){
        $query = "SELECT 
            users.firstname,
            users.lastname,
            users.thirdname,
            users.phone,
            users.email,
            deputies.id AS deputiesID,
            deputies.name AS deputiesName,
            deputies.DoB AS deputiesBornDate,
            deputies.nation AS deputiesNation,
            deputies.party AS deputiesParty,
            deputies.email AS deputiesEmail,
            deputies.image AS deputiesImg,
            constituencies.name AS constituenciesName,
            constituencies.number AS constituenciesNumber,
            locations.name AS locationsName,
            parlament_type.name AS parlamentName,
            parlament_type.id AS parlamentId,
            districs.name AS districsName,
            regions.name AS regionsName,
            precincts.name AS precinctsName,
            precincts.id AS precinctsID,
            precincts.adress AS precinctsAdress,
            precincts.number AS precinctsNumber,
            adreses.id AS userAddressID,
            adreses.street AS userAdressStreet,
            adreses.number AS userAdressNumber
                FROM `users` 
                    INNER JOIN adreses ON users.adreses_id = adreses.id
                    INNER JOIN location_adresses ON users.adreses_id=location_adresses.adreses_id
                    INNER JOIN locations ON location_adresses.locations_id = locations.id
                    INNER JOIN districs ON locations.districs_id = districs.id
                    INNER JOIN regions ON districs.regions_id = regions.id
                    INNER JOIN precincts ON adreses.precincts_id = precincts.id
                    INNER JOIN precincts_const ON precincts.id = precincts_const.precincts_id
                    INNER JOIN constituencies ON precincts_const.constituencies_id = constituencies.id
                    INNER JOIN parlament_type ON constituencies.parlament_type_id = parlament_type.id
                    INNER JOIN deputies_const ON deputies_const.constituencies_id = constituencies.id
                    INNER JOIN deputies ON deputies_const.deputies_id = deputies.id
                WHERE users.id = :id";
        $userID=htmlspecialchars(strip_tags($userID));   
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userID);
        $stmt->execute();
        if($version){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}