<?php
class Conn {
    private $serverName = "";
    private $userName = "";
    private $password = "";
    private $dbName = "";
    private $pdo;
    

    public function __construct($serverName,$userName,$password,$dbName) {
        $this->serverName = $serverName;
        $this->userName = $userName;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    public function open(){
        if (empty($this->pdo)) {
            $s=$this->serverName;
            $d=$this->dbName;
            $this->pdo = new PDO("mysql:host=$s;dbname=$d", $this->userName, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }     
    }

    public function close(){
        $this->pdo = null;
    }

    public function select(string $sql,array $params = []){
        $this->open();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data=$stmt->fetchAll();
        return $data;

    }

    public function exec(string $sql, array $params=[]){
        $this->open();
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    public function lastID(){
        return $this->pdo->lastInsertId();
    }
}
?>