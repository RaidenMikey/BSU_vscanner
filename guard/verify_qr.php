<?php
session_start();
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
$qrData = $input['qr_data'] ?? '';

if (empty($qrData)) {
    echo json_encode(['success' => false, 'message' => 'QR code data is required']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Parse QR code data (assuming format: vehicle_id or JSON with vehicle_id)
    $vehicleId = null;
    
    // Try to parse as JSON first
    $parsed = json_decode($qrData, true);
    if ($parsed && isset($parsed['vehicle_id'])) {
        $vehicleId = $parsed['vehicle_id'];
    } else {
        // Try as direct vehicle ID
        $vehicleId = filter_var($qrData, FILTER_VALIDATE_INT);
        if (!$vehicleId) {
            // Try to extract vehicle ID from string
            if (preg_match('/vehicle[_-]?id[=:]?\s*(\d+)/i', $qrData, $matches)) {
                $vehicleId = (int)$matches[1];
            }
        }
    }
    
    if (!$vehicleId) {
        echo json_encode(['success' => false, 'message' => 'Invalid QR code format']);
        $conn->close();
        exit();
    }
    
    // Get vehicle information with student details
    $stmt = $conn->prepare("
        SELECT 
            v.id as vehicle_id,
            v.license_plate,
            v.make,
            v.model,
            v.color,
            v.registered_at,
            v.status,
            u.id as student_id,
            u.full_name,
            u.email,
            u.student_id as student_number
        FROM vehicles v
        INNER JOIN users u ON v.user_id = u.id
        WHERE v.id = ? AND v.status = 'approved'
    ");
    
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Vehicle not found or not approved']);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
    
    // Format response
    $response = [
        'success' => true,
        'student' => [
            'id' => $data['student_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'student_id' => $data['student_number']
        ],
        'vehicle' => [
            'id' => $data['vehicle_id'],
            'license_plate' => $data['license_plate'],
            'make' => $data['make'],
            'model' => $data['model'],
            'color' => $data['color'],
            'registered_at' => $data['registered_at'],
            'status' => $data['status']
        ]
    ];
    
    echo json_encode($response);
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

