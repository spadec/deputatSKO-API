<?php
/**
*
*/
class dataDB {
    // подключение к БД таблице "users" 
    private $conn;
        // конструктор класса User 
    public function __construct($db) {
        $this->conn = $db;
    }
    /**
    * Получаем список областей
    */
    public function getRegions(){
        $query = "SELECT *
        FROM regions";
        // подготовка запроса 
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Получает район по ID области
    */
    public function getDistrics($regID){
        $query = "SELECT *
        FROM districs WHERE regions_id = ?";
        $stmt = $this->conn->prepare( $query );
        // подготовка запроса 
        $regID=htmlspecialchars(strip_tags($regID));
        $stmt->bindParam(1, $regID);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Получает населенный пункт по ID района
    */
    public function getLocality($districtID){
        $query = "SELECT *
        FROM locations WHERE districs_id = ?";
        $stmt = $this->conn->prepare( $query );
        // подготовка запроса 
        $districtID=htmlspecialchars(strip_tags($districtID));
        $stmt->bindParam(1, $districtID);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Получает улицы по ID населенного пункта и кусочку строки
    */
    public function getStreets($localityID, $find){
        // подготовка запроса 
        if($find){
                $query = "SELECT location_adresses.adreses_id,  adreses.street
            FROM location_adresses 
            LEFT JOIN adreses ON location_adresses.adreses_id = adreses.id 
            WHERE locations_id=? 
            AND adreses.street LIKE ? 
            GROUP BY adreses.street ";
            $stmt = $this->conn->prepare( $query );
            $localityID=htmlspecialchars(strip_tags($localityID));
            $find=htmlspecialchars(strip_tags($find));
            $stmt->bindParam(1, $localityID);
            $find = "%".$find."%";
            $stmt->bindParam(2, $find);
        }
        else {
                $query = "SELECT  adreses.street
                FROM location_adresses 
                LEFT JOIN adreses ON location_adresses.adreses_id = adreses.id 
                WHERE locations_id=? 
                GROUP BY adreses.street ";
                         $stmt = $this->conn->prepare( $query );
                            $localityID=htmlspecialchars(strip_tags($localityID));
                $localityID=htmlspecialchars(strip_tags($localityID));
                $stmt->bindParam(1, $localityID);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Возвращает список номеров выбранной улицы
    */
    public function getStreetNumber($localityID, $street){
    	$query = "SELECT adreses.id AS adresses_id,adreses.street,adreses.number, CAST(adreses.number AS SIGNED) AS number2 FROM location_adresses LEFT JOIN adreses ON location_adresses.adreses_id = adreses.id WHERE location_adresses.locations_id = ? AND adreses.street= ? ORDER BY number2 ASC,adreses.number ASC";
        $stmt = $this->conn->prepare( $query );
        // подготовка запроса 
        $street=htmlspecialchars(strip_tags($street));
        $localityID=htmlspecialchars(strip_tags($localityID));
        $stmt->bindParam(1, $localityID);
        $stmt->bindParam(2, $street);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Получаем адрес
    */
    public function getAdreses($id){
        $query = "SELECT count(*) FROM location_adresses LEFT JOIN adreses  ON adreses.id = location_adresses.adreses_id WHERE location_adresses.locations_id = ?";
        $stmt = $this->conn->prepare( $query );
        $id=htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $rows = $stmt->fetchColumn();
        if($rows > 1){
            return false;
        }
        $sql = "SELECT adreses.id AS adreses_id FROM location_adresses LEFT JOIN adreses  ON adreses.id = location_adresses.adreses_id WHERE location_adresses.locations_id = ?";
        $stmt2 = $this->conn->prepare( $sql );
        $stmt2->bindParam(1, $id);
        $stmt2->execute();
        return $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
    * Получаем данные по депутату
    */
    public function getDeputiesData($depId){
        $query = "SELECT * FROM deputies WHERE id = ?";
        $stmt = $this->conn->prepare( $query );
        $depId=htmlspecialchars(strip_tags($depId));
        $stmt->bindParam(1, $depId);
        $stmt->execute();
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows;
    }
    public function fillData(){
        $query = "SELECT id,constituencies_id  FROM `precincts`";
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
    public function insrt($сid,$pid){
        $query = "INSERT INTO precincts_const SET constituencies_id = ?, precincts_id = ?";
        $stmt = $this->conn->prepare($query);
         $stmt->bindParam(1, $сid);
         $stmt->bindParam(2, $pid);
         $stmt->execute();
        // инъекция 
    }
    /**
    *
    */
    public function getUserEmail($userID){
        $query = "SELECT email FROM users WHERE id = ?";
        $userID=htmlspecialchars(strip_tags($userID));   
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userID);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result;
    }
    /**
     * возвращает данные по ID адреса
     */
    public function getAdressData($addressID, $version=""){
        $query = "SELECT 
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
            adreses.street AS userAdressStreet,
            adreses.number AS userAdressNumber
                FROM `adreses` 
                    INNER JOIN location_adresses ON adreses.id=location_adresses.adreses_id
                    INNER JOIN locations ON location_adresses.locations_id = locations.id
                    INNER JOIN districs ON locations.districs_id = districs.id
                    INNER JOIN regions ON districs.regions_id = regions.id
                    INNER JOIN precincts ON adreses.precincts_id = precincts.id
                    INNER JOIN precincts_const ON precincts.id = precincts_const.precincts_id
                    INNER JOIN constituencies ON precincts_const.constituencies_id = constituencies.id
                    INNER JOIN parlament_type ON constituencies.parlament_type_id = parlament_type.id
                    INNER JOIN deputies_const ON deputies_const.constituencies_id = constituencies.id
                    INNER JOIN deputies ON deputies_const.deputies_id = deputies.id
                WHERE adreses.id = :id";
        $addressID=htmlspecialchars(strip_tags($addressID));   
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $addressID);
        $stmt->execute();
        if($version){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}