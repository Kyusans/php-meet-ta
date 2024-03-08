<?php
include "headers.php";

class Admin
{
  function getPendingPost()
  {
    include "connection.php";
    $sql = "SELECT a.user_username, a.user_image, b.*, COUNT(c.point_id) AS likes 
    FROM tbl_user as a 
    INNER JOIN tbl_post as b ON a.user_id = b.post_userId 
    LEFT JOIN tbl_points as c ON c.point_postId = b.post_id 
    WHERE b.post_status = 0 
    GROUP BY b.post_id 
    ORDER BY b.post_dateCreated DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function approvePost($json){
    include "connection.php";
    $json = json_decode($json, true);
    $sql = "UPDATE tbl_post SET post_status = 1 WHERE post_id = :postId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":postId", $json["postId"]);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function rejectPost($json){
    include "connection.php";
    $json = json_decode($json, true);
    $sql = "DELETE FROM tbl_post WHERE post_id = :postId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":postId", $json["postId"]);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }
} //user


$json = isset($_POST["json"]) ? $_POST["json"] : "0";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

$admin = new Admin();

switch ($operation) {
  case "getPendingPost":
    echo $admin->getPendingPost();
    break;
  case "approvePost":
    echo $admin->approvePost($json);
    break;
  case "rejectPost":
    echo $admin->rejectPost($json);
    break;
}
