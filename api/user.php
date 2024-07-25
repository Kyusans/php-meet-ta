 <?php
  include "headers.php";

  class User
  {
    function login($json)
    {
      // {"username":"admin","password":"admin"}
      include "connection.php";
      $json = json_decode($json, true);
      $sql = "SELECT * FROM tbl_user WHERE user_username = :username  AND BINARY user_password = :password";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':username', $json['username']);
      $stmt->bindParam(':password', $json['password']);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
    }

    function signup($json)
    {
      // {"username":"joe","password":"joe","email":"joe@gmail.com"}
      include "connection.php";
      $json = json_decode($json, true);
      if (recordExists($json['username'], "tbl_user", "user_username")) {
        return -1;
      } else if (recordExists($json['email'], "tbl_user", "user_email")) {
        return -2;
      }
      $sql = "INSERT INTO tbl_user (user_username, user_password, user_email, user_level) VALUES (:username, :password, :email, 90)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':username', $json['username']);
      $stmt->bindParam(':password', $json['password']);
      $stmt->bindParam(':email', $json['email']);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? 1 : 0;
    }
  } //user

  function recordExists($value, $table, $column)
  {
    include "connection.php";
    $sql = "SELECT COUNT(*) FROM $table WHERE $column = :value";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":value", $value);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
  }

  function uploadImage()
  {
    if (isset($_FILES["file"])) {
      $file = $_FILES['file'];
      // print_r($file);
      $fileName = $_FILES['file']['name'];
      $fileTmpName = $_FILES['file']['tmp_name'];
      $fileSize = $_FILES['file']['size'];
      $fileError = $_FILES['file']['error'];
      // $fileType = $_FILES['file']['type'];

      $fileExt = explode(".", $fileName);
      $fileActualExt = strtolower(end($fileExt));

      $allowed = ["jpg", "jpeg", "png", "gif"];

      if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
          if ($fileSize < 25000000) {
            $fileNameNew = uniqid("", true) . "." . $fileActualExt;
            $fileDestination =  'images/' . $fileNameNew;
            move_uploaded_file($fileTmpName, $fileDestination);
            return $fileNameNew;
          } else {
            return 4;
          }
        } else {
          return 3;
        }
      } else {
        return 2;
      }
    } else {
      return "";
    }
  }

  function getCurrentDate()
  {
    $today = new DateTime("now", new DateTimeZone('Asia/Manila'));
    return $today->format('Y-m-d H:i:s');
  }

  $json = isset($_POST["json"]) ? $_POST["json"] : "0";
  $operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

  $user = new User();

  switch ($operation) {
    case "login":
      echo $user->login($json);
      break;
    case "signup":
      echo $user->signup($json);
      break;
    default:
      echo "WALA KA NAGBUTANG OG OPERATION SA UBOS HAHAHHA";
      break;
  }
