<?php
include "headers.php";

class Admin
{
  function getPendingPost()
  {
    include "connection.php";

    $sql = "SELECT a.user_username, a.user_image, b.* 
      FROM tbl_user as a 
      INNER JOIN tbl_post as b ON a.user_id = b.post_userId 
      WHERE b.post_status = 0 
      ORDER BY b.post_dateCreated DESC";

    $stmt = $conn->prepare($sql);
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }
} //user


$json = isset($_POST["json"]) ? $_POST["json"] : "0";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

$admin = new Admin();

switch ($operation) {
  case "getPendingPost":
    echo $admin->getPendingPost();
    break;
}
