<?php

namespace App;

class DbConfig{
    private $DB_USER='houssem';
    private $DB_PASSWORD='ilovepizza';
    private $DB_HOST='localhost';
    private $DB_PORT=3306;

    private $DB_NAME= 'my_vault_db';

    public function getDsn(){
        return "mysql:host=" . $this->DB_HOST . ";dbname=" . $this->DB_NAME;
    }

    public function getUser(){
        return $this->DB_USER;
    }

    public function getPassword(){
        return $this->DB_PASSWORD;
    }

}