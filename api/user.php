 <?php
  include "headers.php";

  class User
  {
    function signup($json)
    {
      // {"username":"joe1","email":"joe1@gmailcom","password":"joejoejoe"}
      include "connection.php";
      $data = json_decode($json, true);
      if (recordExists($data["username"], "tbl_user", "user_username")) {
        return -1;
      } else if (recordExists($data["email"], "tbl_user", "user_email")) {
        return -2;
      }

      $image = "emptyImage.jpg";
      $date = getCurrentDate();
      $sql = "INSERT INTO tbl_user(user_username, user_email, user_password, user_image, user_dateCreated, user_level) VALUES(:username, :email, :password, :image, :date, 10)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":username", $data["username"]);
      $stmt->bindParam(":email", $data["email"]);
      $stmt->bindParam(":password", $data["password"]);
      $stmt->bindParam(":image", $image);
      $stmt->bindParam(":date", $date);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function login($json)
    {
      // {"username":"joe","password":"joejoejoe"}
      include "connection.php";
      $data = json_decode($json, true);
      $sql = "SELECT * FROM tbl_user WHERE (user_username = :username OR user_email = :username) AND BINARY user_password = :password";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":username", $data["username"]);
      $stmt->bindParam(":password", $data["password"]);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ? json_encode($result) : 0;
    }

    function createPost($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $date = getCurrentDate();

      $returnValueImage = uploadImage();
      switch ($returnValueImage) {
        case 2:
          // You cannot Upload files of this type!
          return 2;
        case 3:
          // There was an error uploading your file!
          return 3;
        case 4:
          // Your file is too big (25mb maximum)
          return 4;
        default:
          break;
      }
      $sql = "INSERT INTO tbl_post(post_userId, post_title, post_description, post_image, post_dateCreated, post_status) 
    VALUES(:userId, :title, :description, :image, :date, 0)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":userId", $json["userId"]);
      $stmt->bindParam(":title", $json["title"]);
      $stmt->bindParam(":description", $json["description"]);
      $stmt->bindParam(":image", $returnValueImage);
      $stmt->bindParam(":date", $date);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function getProfile($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $sql = "SELECT a.user_username, a.user_image, b.*, COUNT(c.point_id) AS likes 
      FROM tbl_user as a 
      INNER JOIN tbl_post as b ON a.user_id = b.post_userId 
      LEFT JOIN tbl_points as c ON c.point_postId = b.post_id 
      WHERE a.user_id = :userId AND b.post_status = 1 
      GROUP BY b.post_id 
      ORDER BY post_dateCreated DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":userId", $json["userId"]);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
    }

    function getUserDetails($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $sql = "SELECT * FROM tbl_user WHERE user_id = :userId";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam("userId", $json["userId"]);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
    }

    function getApprovedPost()
    {
      include "connection.php";
      $sql = "SELECT a.user_username, a.user_image, b.*, COUNT(c.point_id) AS likes 
      FROM tbl_user as a 
      INNER JOIN tbl_post as b ON a.user_id = b.post_userId 
      LEFT JOIN tbl_points as c ON c.point_postId = b.post_id
      WHERE b.post_status = 1 
      GROUP BY b.post_id 
      ORDER BY b.post_dateCreated DESC";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
    }

    function heartPost($json)
    {
      include "connection.php";
      $json = json_decode($json, true);

      $sql_check = "SELECT * FROM tbl_points WHERE point_postId = :postId AND point_userId = :userId";
      $stmt_check = $conn->prepare($sql_check);
      $stmt_check->bindParam(":postId", $json["postId"]);
      $stmt_check->bindParam(":userId", $json["userId"]);
      $stmt_check->execute();

      if ($stmt_check->fetch()) {
        // return "User already liked the post";
        $sql1 = "DELETE FROM tbl_points WHERE point_postId = :postId AND point_userId = :userId";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(":postId", $json["postId"]);
        $stmt1->bindParam(":userId", $json["userId"]);
        $stmt1->execute();
        return 5;
      }

      $sql_insert = "INSERT INTO tbl_points(point_postId, point_userId) VALUES (:postId, :userId)";
      $stmt_insert = $conn->prepare($sql_insert);
      $stmt_insert->bindParam(":postId", $json["postId"]);
      $stmt_insert->bindParam(":userId", $json["userId"]);
      $stmt_insert->execute();

      return $stmt_insert->rowCount() > 0 ? 1 : 0;
    }


    function getLikes()
    {
      include "connection.php";
      $sql = "SELECT a.user_username, a.user_image, b.*, COUNT(c.point_id) AS likes 
      FROM tbl_user as a 
      INNER JOIN tbl_post as b ON a.user_id = b.post_userId 
      LEFT JOIN tbl_points as c ON c.point_postId = b.post_id
      WHERE b.post_status = 1 
      GROUP BY b.post_id
      ORDER BY b.post_dateCreated DESC";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
    }

    function isUserLiked($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $sql = "SELECT * FROM tbl_points WHERE point_postId = :postId AND point_userId = :userId";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":postId", $json["postId"]);
      $stmt->bindParam(":userId", $json["userId"]);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function deletePost($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $sql = "DELETE FROM tbl_post WHERE post_id = :postId";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":postId", $json["postId"]);
      $stmt->execute();
      return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function updateProfilePicture($json)
    {
      include "connection.php";
      $json = json_decode($json, true);
      $returnValueImage = uploadImage();

      switch ($returnValueImage) {
        case 2:
          // You cannot Upload files of this type!
          return 2;
        case 3:
          // There was an error uploading your file!
          return 3;
        case 4:
          // Your file is too big (25mb maximum)
          return 4;
        default:
          break;
      }

      $sql = "UPDATE tbl_user SET user_image = :image WHERE user_id = :userId";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":image", $returnValueImage);
      $stmt->bindParam(":userId", $json["userId"]);
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

    // $returnValueImage = uploadImage();

    // switch ($returnValueImage) {
    //     case 2:
    //         // You cannot Upload files of this type!
    //         return 2;
    //     case 3:
    //         // There was an error uploading your file!
    //         return 3;
    //     case 4:
    //         // Your file is too big (25mb maximum)
    //         return 4;
    //     default:
    //         break;
    // }
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
    case "createPost":
      echo $user->createPost($json);
      break;
    case "getProfile":
      echo $user->getProfile($json);
      break;
    case "getUserDetails":
      echo $user->getUserDetails($json);
      break;
    case "getApprovedPost":
      echo $user->getApprovedPost();
      break;
    case "heartPost":
      echo $user->heartPost($json);
      break;
    case "getLikes":
      echo $user->getLikes();
      break;
    case "isUserLiked":
      echo $user->isUserLiked($json);
      break;
    case "deletePost":
      echo $user->deletePost($json);
      break;
    case "updateProfilePicture":
      echo $user->updateProfilePicture($json);
      break;
  }
