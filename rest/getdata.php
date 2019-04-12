<?php
  try{
    include './connect.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['str'])){

      // 获取人员数据
      $member = "SELECT * FROM member";
      $queryM = $link -> query($member) -> fetchAll(PDO::FETCH_ASSOC);

      // 获取运动项目数据
      $item = "SELECT * FROM item";
      $queryI = $link -> query($item) -> fetchAll(PDO::FETCH_ASSOC);

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

      // 获取用户名
      $str = $_POST['str'];
      $key = encrypt(str_replace(' ','+',$str),'D','admin');
      $key ? $info = explode('----', $key) : $info = 'null';

      if($queryI && $queryM && $info){
        $arr = array('state'=>'200','item'=>$queryI,'member'=>$queryM,'info'=>$info);
        echo json_encode($arr);
      }

    }
  }catch(PDOException $e){
    echo $e;
  }