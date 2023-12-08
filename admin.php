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

        function addScholar($json){
            include "connection.php";
            $json = json_decode($json, true);
            $sql = "INSERT INTO tbl_scholars(stud_school_Id, stud_last_name, stud_first_name, stud_course_id, stud_year_level, stud_scholarship_type_id, stud_password) 
            VALUES(:schoolId, :lastName, :firstName, :courseId, :yearLevel, :scholarShipId, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":schoolId", $json["schoolId"]);
            $stmt->bindParam(":lastName", $json["lastName"]);
            $stmt->bindParam(":firstName", $json["firstName"]);
            $stmt->bindParam(":courseId", $json["courseId"]);
            $stmt->bindParam(":yearLevel", $json["yearLevel"]);
            $stmt->bindParam(":scholarShipId", $json["scholarShipId"]);
            $stmt->bindValue(":password", $json["lastName"] . $json["schoolId"]);
            $stmt->execute();
            return $stmt->rowCount() > 0 ? 1 : 0;
        }
    }//admin 

    $json = isset($_POST["json"]) ? $_POST["json"] : "0";
    $operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

    $admin = new Admin();

    switch($operation){
        case "login":
            echo $admin->login($json);
            break;
        case "addScholar":
            echo $admin->addScholar($json);
            break;
    }
?>