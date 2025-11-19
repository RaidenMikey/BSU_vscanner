<?php
/**
 * QR Code Generator
 * Unified QR code generation with multiple fallback methods
 * Works with or without GD library
 */

/**
 * Main QR code generation function
 * Tries multiple methods automatically
 * 
 * @param string $data Data to encode
 * @param string $filename Output file path
 * @param int $size Image size in pixels
 * @return bool True on success, false on failure
 */
function generateQRCode($data, $filename, $size = 300) {
    // The local GD implementation is incomplete (lacks Error Correction).
    // We force the use of the external API to ensure valid QR codes are generated.
    return generateQRCodeAPI($data, $filename, $size);
}

/**
 * Generate QR code using external API (works without GD library)
 */
function generateQRCodeAPI($data, $filename, $size = 300) {
    $apis = [
        'http://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data),
        'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data),
    ];
    
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            error_log('QR Code API: Cannot create directory: ' . $dir);
            return false;
        }
    }
    
    foreach ($apis as $apiUrl) {
        try {
            $contextOptions = [
                'http' => [
                    'timeout' => 15,
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => ['User-Agent: PHP QR Code Generator', 'Accept: image/png']
                ]
            ];
            
            if (strpos($apiUrl, 'https://') === 0) {
                $contextOptions['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ];
            }
            
            $qrImageContent = false;
            
            // Try cURL first
            if (function_exists('curl_init')) {
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'PHP QR Code Generator');
                
                $qrImageContent = @curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200 || $qrImageContent === false) {
                    $qrImageContent = false;
                }
            }
            
            // Fallback to file_get_contents
            if ($qrImageContent === false) {
                $context = stream_context_create($contextOptions);
                $qrImageContent = @file_get_contents($apiUrl, false, $context);
            }
            
            if ($qrImageContent !== false && strlen($qrImageContent) > 100) {
                if (@file_put_contents($filename, $qrImageContent) !== false) {
                    if (file_exists($filename) && filesize($filename) > 0) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            error_log('QR Code API Error: ' . $e->getMessage());
            continue;
        }
    }
    
    return false;
}

/**
 * Minimal QR code generator using GD (simplest, most reliable)
 */
function generateQRCodeMinimal($data, $filename, $size = 300) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) return false;
    }
    if (!is_writable($dir)) return false;
    
    try {
        $margin = 4;
        $moduleSize = 10;
        $matrixSize = 21;
        $imageSize = ($matrixSize + 2 * $margin) * $moduleSize;
        
        $img = @imagecreatetruecolor($imageSize, $imageSize);
        if (!$img) return false;
        
        $white = @imagecolorallocate($img, 255, 255, 255);
        $black = @imagecolorallocate($img, 0, 0, 0);
        if ($white === false || $black === false) {
            imagedestroy($img);
            return false;
        }
        
        imagefill($img, 0, 0, $white);
        
        // Draw finder patterns
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, 0, 0);
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, 0, 14);
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, 14, 0);
        
        // Draw timing patterns
        for ($i = 8; $i < 13; $i++) {
            $x = ($i + $margin) * $moduleSize;
            $y = (6 + $margin) * $moduleSize;
            if (($i - 8) % 2 == 0) {
                imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
            }
            $x = (6 + $margin) * $moduleSize;
            $y = ($i + $margin) * $moduleSize;
            if (($i - 8) % 2 == 0) {
                imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
            }
        }
        
        // Draw dark module
        $x = (4 + $margin) * $moduleSize;
        $y = (8 + $margin) * $moduleSize;
        imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
        
        // Encode data
        $dataBits = [];
        $dataLen = strlen($data);
        for ($i = 0; $i < $dataLen; $i++) {
            $byte = ord($data[$i]);
            for ($j = 7; $j >= 0; $j--) {
                $dataBits[] = ($byte >> $j) & 1;
            }
        }
        
        // Place data bits
        $bitIndex = 0;
        $direction = -1;
        $col = $matrixSize - 1;
        
        while ($col >= 0 && $bitIndex < count($dataBits)) {
            if ($col == 6) $col--;
            
            $startRow = ($direction == 1) ? 0 : $matrixSize - 1;
            $endRow = ($direction == 1) ? $matrixSize : -1;
            
            for ($row = $startRow; $row != $endRow; $row += $direction) {
                for ($c = 0; $c < 2 && $bitIndex < count($dataBits); $c++) {
                    $currentCol = $col - $c;
                    
                    if (isReserved($row, $currentCol, $matrixSize)) {
                        continue;
                    }
                    
                    if ($row >= 0 && $row < $matrixSize && $currentCol >= 0 && $currentCol < $matrixSize) {
                        if ($dataBits[$bitIndex]) {
                            $x = ($currentCol + $margin) * $moduleSize;
                            $y = ($row + $margin) * $moduleSize;
                            imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
                        }
                        $bitIndex++;
                    }
                }
            }
            
            $direction *= -1;
            $col -= 2;
        }
        
        $result = @imagepng($img, $filename, 9);
        imagedestroy($img);
        
        if (!$result || !file_exists($filename) || filesize($filename) == 0) {
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Simple QR code generator using GD
 */
function generateQRCodeSimple($data, $filename, $size = 300) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) return false;
    }
    
    try {
        $margin = 4;
        $moduleSize = 8;
        $matrixSize = 21;
        $imageSize = ($matrixSize + 2 * $margin) * $moduleSize;
        
        $img = @imagecreatetruecolor($imageSize, $imageSize);
        if (!$img) return false;
        
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);
        
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, 0, 0);
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, 0, $matrixSize - 7);
        drawFinderPattern($img, $black, $white, $margin, $moduleSize, $matrixSize - 7, 0);
        
        $dataBits = [];
        $dataLen = strlen($data);
        for ($i = 0; $i < $dataLen; $i++) {
            $byte = ord($data[$i]);
            for ($j = 7; $j >= 0; $j--) {
                $dataBits[] = ($byte >> $j) & 1;
            }
        }
        
        $bitIndex = 0;
        $direction = -1;
        $col = $matrixSize - 1;
        
        while ($col >= 0 && $bitIndex < count($dataBits)) {
            if ($col == 6) $col--;
            
            $startRow = ($direction == 1) ? 0 : $matrixSize - 1;
            $endRow = ($direction == 1) ? $matrixSize : -1;
            
            for ($row = $startRow; $row != $endRow; $row += $direction) {
                for ($c = 0; $c < 2 && $bitIndex < count($dataBits); $c++) {
                    $currentCol = $col - $c;
                    
                    if (isReserved($row, $currentCol, $matrixSize) || $row == 6 || $currentCol == 6) {
                        continue;
                    }
                    
                    if ($row >= 0 && $row < $matrixSize && $currentCol >= 0 && $currentCol < $matrixSize) {
                        if ($dataBits[$bitIndex]) {
                            $x = ($currentCol + $margin) * $moduleSize;
                            $y = ($row + $margin) * $moduleSize;
                            imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
                        }
                        $bitIndex++;
                    }
                }
            }
            
            $direction *= -1;
            $col -= 2;
        }
        
        $result = @imagepng($img, $filename, 9);
        imagedestroy($img);
        
        return ($result && file_exists($filename) && filesize($filename) > 0);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Full QR code generator using GD (most complete implementation)
 */
function generateQRCodeFull($data, $filename, $size = 300) {
    // This is a placeholder - you can implement the full version if needed
    // For now, it just returns false to use simpler methods
    return false;
}

/**
 * Helper function to draw finder pattern
 */
function drawFinderPattern($img, $black, $white, $margin, $moduleSize, $startRow, $startCol) {
    $pattern = [
        [1,1,1,1,1,1,1],
        [1,0,0,0,0,0,1],
        [1,0,1,1,1,0,1],
        [1,0,1,1,1,0,1],
        [1,0,1,1,1,0,1],
        [1,0,0,0,0,0,1],
        [1,1,1,1,1,1,1]
    ];
    
    for ($i = 0; $i < 7; $i++) {
        for ($j = 0; $j < 7; $j++) {
            $x = ($startCol + $j + $margin) * $moduleSize;
            $y = ($startRow + $i + $margin) * $moduleSize;
            $color = $pattern[$i][$j] ? $black : $white;
            imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $color);
        }
    }
}

/**
 * Helper function to check if position is reserved
 */
function isReserved($row, $col, $size) {
    $finderAreas = [
        [0, 0, 8, 8],
        [0, $size - 8, 8, $size],
        [$size - 8, 0, $size, 8]
    ];
    
    foreach ($finderAreas as $area) {
        if ($row >= $area[0] && $row < $area[2] && $col >= $area[1] && $col < $area[3]) {
            return true;
        }
    }
    
    if ($row == 6 || $col == 6) {
        return true;
    }
    
    if ($row == 8 && $col == 4) {
        return true;
    }
    
    return false;
}

