<?php 
    include "headers.php";

    class Admin{
        function login($json){
            // {"username":"admin","password":"admin"}
            include "connection.php";
            $json = json_decode($json, true);
            $sql = "SELECT * FROM tblusers WHERE adm_employee_id = :username AND adm_password = :password";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":username", $json["username"]);
            $stmt->bindParam(":password", $json["password"]);
            $returnValue = 0;
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $rs = $stmt->fetch(PDO::FETCH_ASSOC);
                $returnValue = json_encode($rs);
            }
            return $returnValue;
        }
    }//admin 

    $json = isset($_POST["json"]) ? $_POST["json"] : "0";
    $operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

    $admin = new Admin();

    switch($operation){
        case "login":
            echo $admin->login($json);
            break;
    }
?>