<?php
chdir(__DIR__ . '/..');
require_once 'initialize.php';
require_once 'config.php';
require_once 'classes/DBConnection.php';
require_once 'classes/SystemSettings.php';
require_once 'classes/Master.php';

echo "Testing item deletion functionality...\n\n";

// Get first item from database
$db = new DBConnection();
$conn = $db->conn;

$result = $conn->query("SELECT id, title FROM item_list LIMIT 1");
if ($result && $result->num_rows > 0) {
    $item = $result->fetch_assoc();
    $item_id = $item['id'];
    $title = $item['title'];
    
    echo "Found item: ID=$item_id, Title=$title\n";
    echo "Attempting to delete...\n\n";
    
    // Simulate POST request to delete
    $_POST['id'] = $item_id;
    
    $master = new Master();
    $response = $master->delete_item();
    $decoded = json_decode($response, true);
    
    echo "Response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($decoded['status'] == 'success') {
        echo "✓ Deletion successful!\n";
        
        // Verify item is deleted
        $verify = $conn->query("SELECT * FROM item_list WHERE id = '$item_id'");
        if ($verify->num_rows == 0) {
            echo "✓ Verified: Item no longer exists in database\n";
        } else {
            echo "✗ ERROR: Item still exists in database!\n";
        }
    } else {
        echo "✗ Deletion failed: " . $decoded['msg'] . "\n";
    }
} else {
    echo "No items found in database to test\n";
}
?>
