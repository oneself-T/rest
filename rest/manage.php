<?php
    try{
        include './connect.php';
        if($_SERVER['REQUEST_METHOD'] == "GET"){
            // 获取人员数据
            $member = "SELECT * FROM member";
            $queryM = $link -> query($member) -> fetchAll(PDO::FETCH_ASSOC);
            // 获取运动项目数据
            $item = "SELECT * FROM item";
            $queryI = $link -> query($item) -> fetchAll(PDO::FETCH_ASSOC);
            if($queryI && $queryM){
                $arr = array('state'=>'200','item'=>$queryI,'member'=>$queryM);
                echo json_encode($arr);
            }
        }
        if($_SERVER['REQUEST_METHOD'] == "POST"){
            if(isset($_POST['deletePersonnel']) || isset($_POST['addPersonnel'])){
                // 删除人员      
                if(isset($_POST['deletePersonnel'])){
                    $id = $_POST['id'];      
                    $delete = "DELETE FROM `member` WHERE id = '$id'";
                    $query = $link -> query($delete);
                }
                // 添加人员
                if(isset($_POST['addPersonnel'])){
                    $value = $_POST['addPersonnel'];
                    $insert = "INSERT INTO member(`personnel`) VALUE ('$value')";
                    $query = $link -> query($insert);
                }
                // 输出
                $query_data = "SELECT * FROM `member`";
                $output = $link -> query($query_data) -> fetchAll(PDO::FETCH_ASSOC);
                $arr = array('state'=>'200','member'=>$output);
                echo json_encode($arr);
            }
            if(isset($_POST['deleteItem']) || isset($_POST['addItem'])){
                // 删除项目  
                if(isset($_POST['deleteItem'])){
                    $id = $_POST['id'];
                    $delete = "DELETE FROM `item` WHERE id = '$id'";
                    $query = $link -> query($delete);
                }
                // 添加项目
                if(isset($_POST['addItem'])){
                    $value = $_POST['addItem'];
                    $insert = "INSERT INTO item(`item`) VALUE ('$value')";
                    $query = $link -> query($insert);
                }
                // 输出
                $member = "SELECT * FROM `item`";
                $output = $link -> query($member) -> fetchAll(PDO::FETCH_ASSOC);
                $arr = array('state'=>'200','item'=>$output);
                echo json_encode($arr);
            }
        }
    }catch(PDOException $e){
        echo $e;
    }
    