<?php
// Test script to debug image upload
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Simulate $_POST and $_FILES for testing
$_POST['category_id'] = '1';
$_POST['fullname'] = 'Test User';
$_POST['title'] = 'Test Item';
$_POST['contact'] = '123456';
$_POST['description'] = 'Test Description';

// Create a test image file
$testImagePath = sys_get_temp_dir() . '/test_image.png';

// Copy the test image to a known location
if (file_exists($testImagePath)) {
    echo "Test image exists: " . $testImagePath . "\n";
    echo "File size: " . filesize($testImagePath) . " bytes\n";
    echo "File type: " . mime_content_type($testImagePath) . "\n";
    
    // Try to process it like the real code would
    $uploadfile = @imagecreatefrompng($testImagePath);
    if ($uploadfile) {
        echo "Successfully created image from PNG\n";
        $img_info = @getimagesize($testImagePath);
        echo "Image dimensions: " . print_r($img_info, true) . "\n";
        imagedestroy($uploadfile);
    } else {
        echo "ERROR: Could not create image from PNG\n";
    }
} else {
    echo "Test image not found at: " . $testImagePath . "\n";
}

?>
