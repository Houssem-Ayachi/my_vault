<?php

namespace App\Utils;

use App\DbConfig;
use PDO;
use PDOException;

class DB{
    private ?PDO $pdo = null;
    public static DB $db;

    private $connectionOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Indispensable pour voir les erreurs
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Plus pratique que fetch_row
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Sécurité : force les vrais Prepared Statements
    ];

    public static function get_instance(){
        if(empty(self::$db)){
            self::$db = new DB();
        }

        return self::$db;
    }

    public function __construct() {
        $dbConfig = new DbConfig();

        try{
            $this->pdo = new PDO(
                $dbConfig->getDsn(),
                $dbConfig->getUser(),
                $dbConfig->getPassword(),
                $this->connectionOptions,
            );
        }catch(PDOException $e){
            echo "something bad happened while connecting to db" . $e->getMessage();
        }
    }
}