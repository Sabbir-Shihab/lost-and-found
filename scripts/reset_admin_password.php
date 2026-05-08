<?php
require_once __DIR__ . '/../initialize.php';
require_once __DIR__ . '/../classes/DBConnection.php';
$db = new DBConnection();
$conn = $db->conn;
$new = password_hash('Admin@1234', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param('ss',$new,$user);
$user = 'admin';
if($stmt->execute()){
    echo "OK\n";
    $res = $conn->query("SELECT id,username,password FROM users WHERE username='admin'");
    $row = $res->fetch_assoc();
    print_r($row);
}else{
    echo "ERR: " . $conn->error . "\n";
}

?>