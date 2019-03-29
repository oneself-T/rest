<?php

include '../Controller.php';

class getData extends Controller{


//    参与测试数据
    function GET($request){

        $arr = false;
        $where  = "ORDER BY `quarterID` DESC LIMIT 1";
        if(!empty($request['quarter'])){
            $where  = " AND `quarterName` = '$request[quarter]'";
        }

        $sql_flag = $this->pdo->query("SELECT * FROM `trainee_quarter_v2` WHERE  `quarterState` = 'show' $where")->fetch(PDO::FETCH_ASSOC);
        if($sql_flag){
            $quarter = $sql_flag["quarterName"];
            $quarter_user = $sql_flag["quarterUsers"];
            $sql_score =$this->pdo->query("SELECT * FROM `trainee_score_v2` WHERE `scoreQuarter` = '$quarter'AND `scoreUsers` = '$quarter_user' AND `scoreState` = 'show'")->fetchAll(PDO::FETCH_ASSOC);
            $arr['quarter']= $sql_flag;
            $arr['score']= $sql_score;
        }
        return $this->response($arr, 200);
    }
}

new getData();


