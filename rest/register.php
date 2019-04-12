<?php
  try{
    include './connect.php';
    // 获取当前的查找时间段(用作比较)
    date_default_timezone_set("PRC");
    $time = date('Y-m-d H:i:s');    
    date('H') < 15 ? $startTime = '00:00:00' : $startTime = '15:00:00';
    date("H") < 15 ? $endTime = '15:00:00' : $endTime = '23:59:59';
    $ymd = date('Y-n-j');
    $startCompare = $ymd .' '. $startTime;
    $endCompare = $ymd .' '. $endTime;
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];

    // url解析
    function encrypt($string,$operation,$key=''){
      $key=md5($key);
      $key_length=strlen($key);
      $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
      $string_length=strlen($string);
      $rndkey=$box=array();
      $result='';
      for($i=0;$i<=255;$i++){
          $rndkey[$i]=ord($key[$i%$key_length]);
          $box[$i]=$i;
      }
      for($j=$i=0;$i<256;$i++){
          $j=($j+$box[$i]+$rndkey[$i])%256;
          $tmp=$box[$i];
          $box[$i]=$box[$j];
          $box[$j]=$tmp;
      }
      for($a=$j=$i=0;$i<$string_length;$i++){
          $a=($a+1)%256;
          $j=($j+$box[$a])%256;
          $tmp=$box[$a];
          $box[$a]=$box[$j];
          $box[$j]=$tmp;
          $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
      }
      if($operation=='D'){
          if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
              return substr($result,8);
          }else{
              return'';
          }
      }else{
          return str_replace('=','',base64_encode($result));
      }
    }
    // 获取打卡信息
    if( $_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['register']) ){
      $select = "SELECT * FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
      $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
      $status = $_POST['status'];
      $len = count(json_decode($status));
      // 创建最新表
      if(empty($query) && $startCompare == $startDate && $endCompare == $endDate){
        $insert = "INSERT INTO record(`status`,`time`) VALUE ('$status','$time')";
        $query = $link -> query($insert);
      }
      // 输出记录      
      $select = "SELECT `status` FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
      $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
      $output = array('state' => '200' , 'record' => $query);
      echo json_encode($output);
    }
    // 打卡
    if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register']) ){
      // 获取当前时间段是否已有创建表格
      $select = "SELECT * FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
      $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
      if($query){
        // 是否是打卡有效期内
        $status = $_POST['status'];
        if($startCompare == $startDate && $endCompare == $endDate){
          $id = $query[0]['id'];
          $update = "UPDATE `record` SET `status` = '$status' WHERE id = '$id'";
          $query = $link -> query($update);
          if($query){
            $select = "SELECT `status` FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
            $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
            $output = array('state' => '200' , 'record' => $query);
            echo json_encode($output);
          }
        }else{
          $select = "SELECT `status` FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
          $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
          $output = array('state' => '402' , 'record' => $query);
          echo json_encode($output);
        }
      }else{
        // 新建
        $insert = "INSERT INTO record(`status`,`time`) VALUE ('$status','$time')";
        $query = $link -> query($insert);
        if($query){
          $select = "SELECT `status` FROM `record` WHERE `time` BETWEEN '$startDate' AND '$endDate'";
          $query = $link -> query($select) -> fetchAll(PDO::FETCH_ASSOC);
          $output = array('state' => '200' , 'record' => $query);
          echo json_encode($output);
        }
      }
    }
  }catch(PDOException $e){
    echo $e;
  }


  