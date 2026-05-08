<?php
require_once __DIR__ . '/../initialize.php';
require_once __DIR__ . '/../classes/DBConnection.php';
$db = new DBConnection();
$conn = $db->conn;
$res = $conn->query("SELECT id,title,status,created_at FROM item_list ORDER BY id DESC LIMIT 50");
if(!$res){
    echo "Query failed: " . $conn->error . "\n"; exit(2);
}
while($r = $res->fetch_assoc()){
    echo "ID: {$r['id']} | status: {$r['status']} | created_at: {$r['created_at']} | title: {$r['title']}\n";
}
