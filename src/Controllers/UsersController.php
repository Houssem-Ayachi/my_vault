<?php

namespace App\Controllers;

use App\DbConfig;
use App\Utils\DB;


class UsersController{
    public function index(int $id){
        $db = DB::get_instance();
    }

    public function signup(string $name, string $password){
        echo "$name -> $password";
    }
}