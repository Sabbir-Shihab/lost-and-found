<?php
require_once __DIR__ . '/../initialize.php';
require_once __DIR__ . '/../classes/DBConnection.php';
$db = new DBConnection();
$conn = $db->conn;
$id = isset($argv[1]) ? intval($argv[1]) : 0;
$status = isset($argv[2]) ? intval($argv[2]) : 0;
if($id <= 0){
    echo "Invalid id\n";
    exit(1);
}
$sql = "UPDATE `item_list` SET `status` = {$status} WHERE id = {$id}";
if($conn->query($sql)){
    echo "Updated item {$id} to status {$status}\n";
    exit(0);
}else{
    echo "Failed: " . $conn->error . "\n";
    exit(2);
}
