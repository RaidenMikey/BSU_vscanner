<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../adminlogin.php');
    exit();
}

$conn = getDBConnection();
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'], $_POST['status'])) {
    $vehicleId = (int) $_POST['vehicle_id'];
    $newStatus = $_POST['status'];
    $allowedStatuses = ['approved', 'rejected', 'pending'];

    if (!$vehicleId || !in_array($newStatus, $allowedStatuses, true)) {
        $errorMessage = 'Invalid update request.';
    } else {
        $stmt = $conn->prepare("SELECT v.id, v.status, u.full_name FROM vehicles v INNER JOIN users u ON v.user_id = u.id WHERE v.id = ?");
        $stmt->bind_param("i", $vehicleId);
        $stmt->execute();
        $vehicleResult = $stmt->get_result();
        $vehicle = $vehicleResult->fetch_assoc();
        $stmt->close();

        if (!$vehicle) {
            $errorMessage = 'Vehicle record not found.';
        } else {
            $updateStmt = $conn->prepare("UPDATE vehicles SET status = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("si", $newStatus, $vehicleId);
            if ($updateStmt->execute()) {
                $successMessage = sprintf(
                    "Vehicle for %s marked as %s.",
                    htmlspecialchars($vehicle['full_name']),
                    htmlspecialchars(ucfirst($newStatus))
                );
            } else {
                $errorMessage = 'Failed to update vehicle status. Please try again.';
            }
            $updateStmt->close();
        }
    }
}

$vehicles = [];
$stmt = $conn->prepare("
    SELECT
        v.id,
        v.vehicle_type,
        v.license_plate,
        v.make,
        v.model,
        v.color,
        v.driver_license_no,
        v.driver_license_image,
        v.or_image,
        v.cr_image,
        v.qr_code_path,
        v.qr_code_data,
        v.status,
        v.registered_at,
        u.full_name,
        u.email,
        u.student_id AS student_number
    FROM vehicles v
    INNER JOIN users u ON v.user_id = u.id
    ORDER BY FIELD(v.status, 'pending', 'approved', 'rejected'), v.registered_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}
$stmt->close();
$conn->close();

$pending = array_filter($vehicles, fn($v) => $v['status'] === 'pending');
$approved = array_filter($vehicles, fn($v) => $v['status'] === 'approved');
$rejected = array_filter($vehicles, fn($v) => $v['status'] === 'rejected');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BSU Vehicle Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-red': '#DC2626',
                        'primary-red-dark': '#B91C1C',
                        'primary-red-light': '#EF4444',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-3 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 sm:gap-2.5 text-lg sm:text-xl md:text-2xl font-bold text-primary-red">
                    <span class="text-xl sm:text-2xl md:text-3xl">🛡️</span>
                    <span class="hidden sm:inline">BSU Vehicle Scanner - Admin</span>
                    <span class="sm:hidden">BSU Admin</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="../logout.php" onclick="return confirm('Are you sure you want to logout?');" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Vehicle Registration Verification</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Review, approve, or reject vehicle registration requests submitted by students.</p>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4 animate-fade-in-up">
                <p class="text-green-800 text-sm font-medium"><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 animate-fade-in-up">
                <p class="text-red-800 text-sm font-medium"><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>

        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Pending Approvals</h2>
            <?php if (empty($pending)): ?>
                <p class="text-gray-500 text-sm">No pending registrations at the moment.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending as $vehicle): ?>
                        <?php include __DIR__ . '/vehicle_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recently Reviewed</h2>
                <p class="text-sm text-gray-500">Includes both approved and rejected registrations.</p>
            </div>
            <?php if (empty($approved) && empty($rejected)): ?>
                <p class="text-gray-500 text-sm">No reviewed registrations yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_merge($approved, $rejected) as $vehicle): ?>
                        <?php include __DIR__ . '/vehicle_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-5 text-center">
            <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

