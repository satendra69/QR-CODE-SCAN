<?php
$baseDir = dirname(__FILE__);
$tempDir = $baseDir . DIRECTORY_SEPARATOR . 'temp_';
$thumbnailDir = $tempDir . DIRECTORY_SEPARATOR . 'thumbnail';

// Function to delete all files in a directory
function deleteFilesInDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    foreach (glob($dir . '/*') as $file) {
        if (is_file($file)) {
            unlink($file); // Delete file
        }
    }
    return true;
}

// Delete all files in temp_ and thumbnail directories
$tempDeleted = deleteFilesInDirectory($tempDir);
$thumbnailDeleted = deleteFilesInDirectory($thumbnailDir);

if ($tempDeleted && $thumbnailDeleted) {
    echo json_encode(["status" => "success", "message" => "Temp and Thumbnail folders cleaned successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to clean some files."]);
}
?>
