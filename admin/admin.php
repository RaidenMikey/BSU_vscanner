<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../adminlogin.php');
    exit();
}

$conn = getDBConnection();

// Get messages from session (for display after redirect)
$successMessage = $_SESSION['admin_success_message'] ?? '';
$errorMessage = $_SESSION['admin_error_message'] ?? '';
// Clear messages from session after retrieving
unset($_SESSION['admin_success_message']);
unset($_SESSION['admin_error_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'], $_POST['status'])) {
    $vehicleId = (int) $_POST['vehicle_id'];
    $newStatus = $_POST['status'];
    $allowedStatuses = ['approved', 'rejected', 'pending'];

    if (!$vehicleId || !in_array($newStatus, $allowedStatuses, true)) {
        $_SESSION['admin_error_message'] = 'Invalid update request.';
    } else {
        $stmt = $conn->prepare("SELECT v.id, v.status, u.full_name FROM vehicles v INNER JOIN users u ON v.user_id = u.id WHERE v.id = ?");
        $stmt->bind_param("i", $vehicleId);
        $stmt->execute();
        $vehicleResult = $stmt->get_result();
        $vehicle = $vehicleResult->fetch_assoc();
        $stmt->close();

        if (!$vehicle) {
            $_SESSION['admin_error_message'] = 'Vehicle record not found.';
        } else {
            $updateStmt = $conn->prepare("UPDATE vehicles SET status = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("si", $newStatus, $vehicleId);
            if ($updateStmt->execute()) {
                $_SESSION['admin_success_message'] = sprintf(
                    "Vehicle for %s marked as %s.",
                    htmlspecialchars($vehicle['full_name']),
                    htmlspecialchars(ucfirst($newStatus))
                );
            } else {
                $_SESSION['admin_error_message'] = 'Failed to update vehicle status. Please try again.';
            }
            $updateStmt->close();
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: admin.php');
    exit();
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
    <link rel="icon" type="image/png" href="../images/Batangas_State_Logo.png">
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
                    <button onclick="openLogoutModal()" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </button>
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

    <!-- Vehicle Action Confirmation Modal -->
    <div id="actionModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeActionModal()"></div>

        <!-- Modal container - centered -->
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <!-- Modal panel -->
            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="actionModalIcon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Icon will be set by JavaScript -->
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="actionModalTitle">
                                Confirm Action
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="actionModalMessage">
                                    <!-- Message will be set by JavaScript -->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="POST" id="actionModalForm" class="inline">
                        <input type="hidden" name="vehicle_id" id="actionModalVehicleId" value="">
                        <input type="hidden" name="status" id="actionModalStatus" value="">
                        <button type="submit" id="actionModalConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                            Confirm
                        </button>
                    </form>
                    <button type="button" onclick="closeActionModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeLogoutModal()"></div>

        <!-- Modal container - centered -->
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <!-- Modal panel -->
            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirm Logout
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to logout? You will need to sign in again to access your account.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a href="../logout.php" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-red text-base font-medium text-white hover:bg-primary-red-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Logout
                    </a>
                    <button type="button" onclick="closeLogoutModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vehicle Action Modal Functions
        function openConfirmModal(vehicleId, status, vehicleName, studentName) {
            const modal = document.getElementById('actionModal');
            const iconDiv = document.getElementById('actionModalIcon');
            const title = document.getElementById('actionModalTitle');
            const message = document.getElementById('actionModalMessage');
            const form = document.getElementById('actionModalForm');
            const vehicleIdInput = document.getElementById('actionModalVehicleId');
            const statusInput = document.getElementById('actionModalStatus');
            const confirmBtn = document.getElementById('actionModalConfirmBtn');

            // Set form values
            vehicleIdInput.value = vehicleId;
            statusInput.value = status;

            // Clear previous icon classes
            iconDiv.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10';

            // Configure modal based on action
            if (status === 'approved') {
                iconDiv.classList.add('bg-green-100');
                iconDiv.innerHTML = '<svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                title.textContent = 'Approve Vehicle Registration';
                message.innerHTML = `Are you sure you want to <strong>approve</strong> the vehicle registration for <strong>${vehicleName}</strong> owned by <strong>${studentName}</strong>?<br><br>This will allow the student to use this vehicle for campus access.`;
                confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200';
                confirmBtn.textContent = 'Approve';
            } else if (status === 'rejected') {
                iconDiv.classList.add('bg-red-100');
                iconDiv.innerHTML = '<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                title.textContent = 'Reject Vehicle Registration';
                message.innerHTML = `Are you sure you want to <strong>reject</strong> the vehicle registration for <strong>${vehicleName}</strong> owned by <strong>${studentName}</strong>?<br><br>This will deny campus access for this vehicle.`;
                confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200';
                confirmBtn.textContent = 'Reject';
            } else {
                iconDiv.classList.add('bg-yellow-100');
                iconDiv.innerHTML = '<svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
                title.textContent = 'Mark as Pending';
                message.innerHTML = `Are you sure you want to mark the vehicle registration for <strong>${vehicleName}</strong> owned by <strong>${studentName}</strong> as <strong>pending</strong>?<br><br>This will reset the status back to pending review.`;
                confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200';
                confirmBtn.textContent = 'Mark as Pending';
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeActionModal() {
            document.getElementById('actionModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Logout Modal Functions
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const actionModal = document.getElementById('actionModal');
                const logoutModal = document.getElementById('logoutModal');
                
                if (!actionModal.classList.contains('hidden')) {
                    closeActionModal();
                } else if (!logoutModal.classList.contains('hidden')) {
                    closeLogoutModal();
                }
            }
        });
    </script>
</body>
</html>

