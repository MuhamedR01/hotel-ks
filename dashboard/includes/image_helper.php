<?php
/**
 * Convert BLOB image data to base64 data URL
 * @param mixed $imageData - BLOB data from database
 * @param string $mimeType - Image MIME type (default: image/jpeg)
 * @return string - Base64 encoded data URL or placeholder
 */
function getBlobImageSrc($imageData, $mimeType = 'image/jpeg') {
    if (empty($imageData)) {
        // Return placeholder image if no data
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23ddd" width="200" height="200"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
    }
    
    // Convert BLOB to base64
    $base64 = base64_encode($imageData);
    return "data:{$mimeType};base64,{$base64}";
}

/**
 * Get image source - handles both BLOB and file path
 * @param mixed $imageData - BLOB data or file path
 * @param string $mimeType - Image MIME type
 * @return string - Image source URL
 */
function getImageSrc($imageData, $mimeType = 'image/jpeg') {
    if (empty($imageData)) {
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23ddd" width="200" height="200"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
    }
    
    // Check if it's a file path (string starting with uploads/ or http)
    if (is_string($imageData) && (strpos($imageData, 'uploads/') === 0 || strpos($imageData, 'http') === 0)) {
        return '../' . $imageData;
    }
    
    // Otherwise treat as BLOB
    return getBlobImageSrc($imageData, $mimeType);
}
?>