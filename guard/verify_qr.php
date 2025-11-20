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
$qrData = $input['qr_data'] ?? '';

// Debug logging
error_log("QR Verification Request: " . print_r($input, true));
error_log("QR Data: " . $qrData);

if (empty($qrData)) {
    echo json_encode(['success' => false, 'message' => 'QR code data is required']);
    exit();
}

try {
    $conn = getDBConnection();

    // Parse QR code data (supports legacy IDs and new encoded payloads)
    $vehicleId = null;
    $qrLookupData = null;
    $qrParts = [];

    $parsed = json_decode($qrData, true);
    if ($parsed && isset($parsed['vehicle_id'])) {
        $vehicleId = (int) $parsed['vehicle_id'];
    } elseif (strpos($qrData, '|') !== false) {
        $qrParts = explode('|', $qrData);
        // Support both old format (3 parts with hash) and new format (2 parts without hash)
        if (count($qrParts) === 2 || count($qrParts) === 3) {
            $qrLookupData = $qrData;
            if (preg_match('/VEH-\d{4}-(\d+)/i', $qrParts[0], $matches)) {
                $vehicleIdFromTag = (int) ltrim($matches[1], '0');
                if ($vehicleIdFromTag > 0) {
                    $vehicleId = $vehicleIdFromTag;
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid QR code format']);
            $conn->close();
            exit();
        }
    } else {
        $vehicleId = filter_var($qrData, FILTER_VALIDATE_INT);
        if (!$vehicleId && preg_match('/vehicle[_-]?id[=:]?\s*(\d+)/i', $qrData, $matches)) {
            $vehicleId = (int) $matches[1];
        }
    }

    if (!$vehicleId && !$qrLookupData) {
        echo json_encode(['success' => false, 'message' => 'Invalid QR code format']);
        $conn->close();
        exit();
    }

    // Get vehicle information with student details
    if ($qrLookupData) {
        $stmt = $conn->prepare("
            SELECT 
                v.id as vehicle_id,
                v.license_plate,
                v.make,
                v.model,
                v.color,
                v.registered_at,
                v.status,
                v.qr_code_data,
                u.id as student_id,
                u.full_name,
                u.email,
                u.student_id as student_number
            FROM vehicles v
            INNER JOIN users u ON v.user_id = u.id
            WHERE v.qr_code_data = ? AND v.status = 'approved'
        ");
        $stmt->bind_param("s", $qrLookupData);
    } else {
        $stmt = $conn->prepare("
            SELECT 
                v.id as vehicle_id,
                v.license_plate,
                v.make,
                v.model,
                v.color,
                v.registered_at,
                v.status,
                v.qr_code_data,
                u.id as student_id,
                u.full_name,
                u.email,
                u.student_id as student_number
            FROM vehicles v
            INNER JOIN users u ON v.user_id = u.id
            WHERE v.id = ? AND v.status = 'approved'
        ");
        $stmt->bind_param("i", $vehicleId);
    }

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

    if ($qrLookupData) {
        $expectedStudentTag = 'STU-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['student_number'] ?? ''));
        $scannedStudentTag = strtoupper($qrParts[1]);
        
        // Verify student tag matches
        if ($expectedStudentTag !== $scannedStudentTag) {
            echo json_encode(['success' => false, 'message' => 'QR code verification failed. Student ID mismatch.']);
            $conn->close();
            exit();
        }
        
        // If old format with hash (3 parts), verify hash for backward compatibility
        if (count($qrParts) === 3) {
            $expectedHash = strtoupper(hash('sha256', strtoupper($data['license_plate'])));
            $scannedHash = strtoupper($qrParts[2]);
            if ($expectedHash !== $scannedHash) {
                echo json_encode(['success' => false, 'message' => 'QR code verification failed. Please try again.']);
                $conn->close();
                exit();
            }
        }
    }

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

