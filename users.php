<?php 
    include "headers.php";

    class User{
        function login($json){
            // {"username":"admin","password":"admin"}
            include "connection.php";
            $json = json_decode($json, true);
            $sql = "SELECT * FROM tblusers WHERE user_username = :username AND user_password = :password";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":username", $json["username"]);
            $stmt->bindParam(":password", $json["password"]);
            $returnValue = 0;
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $rs = $stmt->fetch(PDO::FETCH_ASSOC);
                $returnValue = json_encode($rs);
            }else{
                $jsonEncoded = json_encode($json);
                $returnValue = studentLogin($jsonEncoded);
            }
            return $returnValue;
        }
    }//user

    function studentLogin($json){
        // {"username":"02-2223-08840","password":"phinma-coc-cite"}
        include "connection.php";
        $json = json_decode($json, true);
        $sql = "SELECT * FROM tblstudents WHERE stud_schoolId = :username AND stud_password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $json["username"]);
        $stmt->bindParam(":password", $json["password"]);
        $stmt->execute();
        $returnValue = 0;
        if($stmt->rowCount() > 0) {
            $rs = $stmt->fetch(PDO::FETCH_ASSOC);
            $returnValue = json_encode($rs);
        }
        return $returnValue;
    }

    $json = isset($_POST["json"]) ? $_POST["json"] : "0";
    $operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

    $user = new User();

    switch($operation){
        case "login":
            echo $user->login($json);
            break;
    }
?>