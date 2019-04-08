<?php

class Controller{

//    public $quarter = ['2','3'];

    function __construct(){

        header("Content-type: application/json");
        date_default_timezone_set('PRC');
        header("Access-Control-Allow-Origin: *");

       $this->pdo = new PDO("mysql:host=10.21.40.40;dbname=star",'root','gzittc123456');
        // $this->pdo = new PDO("mysql:host=localhost;dbname=star_v2",'root','');
        $this->pdo->query("set sql_mode='STRICT_TRANS_TABLES'");
        $this->pdo->query("set names utf8");

        $request = array_merge($_POST,$_GET);
        $method = $_SERVER["REQUEST_METHOD"];

        $this->$method($request);

    }

    function response($msg,$code){
        header("Http/1.1 $code");
        echo json_encode($msg);
        die();
    }

}

