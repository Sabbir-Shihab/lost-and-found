<?php
require_once __DIR__ . '/../initialize.php';
require_once __DIR__ . '/../classes/DBConnection.php';
$db = new DBConnection();
$conn = $db->conn;
$id = isset($argv[1]) ? intval($argv[1]) : 0;
if($id <= 0){
    echo "Invalid id\n";
    exit(1);
}
$res = $conn->query("SELECT * FROM `item_list` WHERE id = {$id}");
if(!$res){
    echo "Query failed: " . $conn->error . "\n"; exit(2);
}
if($res->num_rows <= 0){
    echo "No item with id {$id}\n"; exit(0);
}
$row = $res->fetch_assoc();
print_r($row);
