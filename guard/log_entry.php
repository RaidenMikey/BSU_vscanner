<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

// Guard access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'guard') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$vehicleId = $input['vehicle_id'] ?? null;
$action = $input['action'] ?? ''; // 'allowed' or 'denied'

if (!$vehicleId || !in_array($action, ['allowed', 'denied'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $conn = getDBConnection();
    $guardId = $_SESSION['user_id'];
    
    // Check if entry_logs table exists, if not create it
    $checkTable = $conn->query("SHOW TABLES LIKE 'entry_logs'");
    if ($checkTable->num_rows === 0) {
        $createTable = "CREATE TABLE IF NOT EXISTS `entry_logs` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `vehicle_id` INT(11) UNSIGNED NOT NULL,
            `guard_id` INT(11) UNSIGNED NOT NULL,
            `action` ENUM('allowed','denied') NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `vehicle_id` (`vehicle_id`),
            KEY `guard_id` (`guard_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createTable);
    }
    
    // Insert entry log
    $stmt = $conn->prepare("INSERT INTO entry_logs (vehicle_id, guard_id, action) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $vehicleId, $guardId, $action);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Entry logged successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to log entry']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

