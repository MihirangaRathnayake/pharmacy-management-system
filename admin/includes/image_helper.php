<?php
/**
 * Image Helper Functions
 * Handles image processing with or without GD extension
 */

function isGDAvailable() {
    return extension_loaded('gd');
}

function resizeImage($source, $destination, $maxWidth = 800, $maxHeight = 600, $quality = 85) {
    if (!isGDAvailable()) {
        // If GD is not available, just copy the file
        return copy($source, $destination);
    }
    
    try {
        // Get image info
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            return false;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        if ($ratio >= 1) {
            // Image is smaller than max dimensions, just copy
            return copy($source, $destination);
        }
        
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);
        
        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($source);
                break;
            default:
                return copy($source, $destination);
        }
        
        if (!$sourceImage) {
            return copy($source, $destination);
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save image based on type
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($newImage, $destination, $quality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($newImage, $destination);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($newImage, $destination);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
        
    } catch (Exception $e) {
        // If anything fails, just copy the original file
        return copy($source, $destination);
    }
}

function generateThumbnail($source, $destination, $size = 150) {
    return resizeImage($source, $destination, $size, $size, 90);
}

function validateImageFile($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.'];
    }
    
    // Additional validation if GD is available
    if (isGDAvailable()) {
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Invalid image file.'];
        }
    }
    
    return ['success' => true, 'message' => 'File is valid.'];
}

function getImageDimensions($file) {
    if (!isGDAvailable()) {
        return ['width' => 0, 'height' => 0];
    }
    
    $imageInfo = getimagesize($file);
    if ($imageInfo) {
        return ['width' => $imageInfo[0], 'height' => $imageInfo[1]];
    }
    
    return ['width' => 0, 'height' => 0];
}

function getProfileImageUrl($userId) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['profile_image'] && file_exists(UPLOADS_DIR . '/profiles/' . $user['profile_image'])) {
            return '/uploads/profiles/' . $user['profile_image'];
        }
    } catch (Exception $e) {
        // Return default image on error
    }
    
    // Return default avatar SVG
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIiBmaWxsPSIjRTVFN0VCIi8+CjxwYXRoIGQ9Ik01MCAyNUM0My4zNzUgMjUgMzggMzAuMzc1IDM4IDM3QzM4IDQzLjYyNSA0My4zNzUgNDkgNTAgNDlDNTYuNjI1IDQ5IDYyIDQzLjYyNSA2MiAzN0M2MiAzMC4zNzUgNTYuNjI1IDI1IDUwIDI1WiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNNTAgNTVDNDAuNjI1IDU1IDMzIDYyLjYyNSAzMyA3MlY3NUg2N1Y3MkM2NyA2Mi42MjUgNTkuMzc1IDU1IDUwIDU1WiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K';
}
?>