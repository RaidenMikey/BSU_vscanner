<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = getDBConnection();

$userStmt = $conn->prepare("SELECT id, full_name, email, student_id FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$vehicles = [];
$vehiclesStmt = $conn->prepare("
    SELECT 
        id,
        vehicle_type,
        license_plate,
        make,
        model,
        color,
        driver_license_no,
        driver_license_image,
        or_image,
        cr_image,
        qr_code_path,
        qr_code_data,
        status,
        registered_at,
        updated_at
    FROM vehicles
    WHERE user_id = ?
    ORDER BY FIELD(status, 'approved', 'pending', 'rejected'), registered_at DESC
");
$vehiclesStmt->bind_param("i", $user_id);
$vehiclesStmt->execute();
$vehiclesResult = $vehiclesStmt->get_result();
while ($row = $vehiclesResult->fetch_assoc()) {
    $vehicles[] = $row;
}
$vehiclesStmt->close();
$conn->close();

$countApproved = count(array_filter($vehicles, fn($v) => $v['status'] === 'approved'));
$countPending = count(array_filter($vehicles, fn($v) => $v['status'] === 'pending'));
$countRejected = count(array_filter($vehicles, fn($v) => $v['status'] === 'rejected'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/Batangas_State_Logo.png">
    <title>Registered Vehicles - BSU Vehicle Scanner</title>
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
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 sm:gap-2.5 text-lg sm:text-xl md:text-2xl font-bold text-primary-red">
                    <span class="text-xl sm:text-2xl md:text-3xl">🚗</span>
                    <span class="hidden sm:inline">BSU Vehicle Scanner</span>
                    <span class="sm:hidden">BSU Scanner</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="student.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-xs sm:text-sm md:text-base">
                        ← Back to Dashboard
                    </a>
                    <button onclick="openLogoutModal()" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Registered Vehicles</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">
                Review the status of your vehicle registrations and access approved QR codes for campus entry.
            </p>
        </div>

        <section class="grid sm:grid-cols-3 gap-4 md:gap-6 animate-fade-in-up">
            <div class="bg-white rounded-2xl shadow-lg p-5 border-2 border-green-100">
                <p class="text-sm text-gray-500 mb-1">Approved</p>
                <p class="text-3xl font-bold text-green-600"><?php echo $countApproved; ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-5 border-2 border-yellow-100">
                <p class="text-sm text-gray-500 mb-1">Pending</p>
                <p class="text-3xl font-bold text-yellow-500"><?php echo $countPending; ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-5 border-2 border-red-100">
                <p class="text-sm text-gray-500 mb-1">Rejected</p>
                <p class="text-3xl font-bold text-red-500"><?php echo $countRejected; ?></p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Vehicle Submissions</h2>
                    <p class="text-sm text-gray-500">All registered vehicles with their latest status updates.</p>
                </div>
                <a href="register_vehicle.php" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-red hover:underline">
                    + Register another vehicle
                </a>
            </div>

            <?php if (empty($vehicles)): ?>
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
                    <p class="text-gray-600 text-sm">No vehicles registered yet. Submit a vehicle to get started.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <?php
                            $statusClasses = [
                                'approved' => 'bg-green-100 text-green-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $statusClass = $statusClasses[$vehicle['status']] ?? 'bg-gray-100 text-gray-800';
                            $submittedAt = new DateTime($vehicle['registered_at']);
                            $updatedAt = $vehicle['updated_at'] ? new DateTime($vehicle['updated_at']) : null;
                        ?>
                        <div class="border-2 border-gray-200 rounded-xl p-4 sm:p-6">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Plate: <span class="font-medium text-gray-800"><?php echo htmlspecialchars(strtoupper($vehicle['license_plate'])); ?></span> •
                                        Type: <?php echo htmlspecialchars(ucfirst($vehicle['vehicle_type'])); ?> •
                                        Color: <?php echo htmlspecialchars($vehicle['color']); ?>
                                    </p>
                                </div>
                                <span class="status-pill <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($vehicle['status'])); ?>
                                </span>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <p><span class="font-semibold text-gray-800">Driver’s License No:</span> <?php echo htmlspecialchars($vehicle['driver_license_no']); ?></p>
                                    <p><span class="font-semibold text-gray-800">Submitted:</span> <?php echo $submittedAt->format('F j, Y g:i A'); ?></p>
                                    <?php if ($updatedAt && $updatedAt > $submittedAt): ?>
                                        <p><span class="font-semibold text-gray-800">Last Updated:</span> <?php echo $updatedAt->format('F j, Y g:i A'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-wrap gap-3 items-center">
                                    <?php if (!empty($vehicle['driver_license_image'])): ?>
                                        <a href="../<?php echo htmlspecialchars($vehicle['driver_license_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                            View License
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['or_image'])): ?>
                                        <a href="../<?php echo htmlspecialchars($vehicle['or_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                            View OR
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicle['cr_image'])): ?>
                                        <a href="../<?php echo htmlspecialchars($vehicle['cr_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                            View CR
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($vehicle['status'] === 'approved' && !empty($vehicle['qr_code_path'])): ?>
                                        <button onclick="openQrModal('<?php echo htmlspecialchars($vehicle['qr_code_path']); ?>', '<?php echo htmlspecialchars($vehicle['qr_code_data']); ?>')" class="text-primary-red text-xs font-medium hover:underline">
                                            View QR Code
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($vehicle['status'] === 'approved' && !empty($vehicle['qr_code_data'])): ?>
                                <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p class="text-sm text-green-800">
                                        <span class="font-semibold">QR Payload:</span>
                                        <span class="font-mono break-all"><?php echo htmlspecialchars($vehicle['qr_code_data']); ?></span>
                                    </p>
                                </div>
                            <?php elseif ($vehicle['status'] === 'pending'): ?>
                                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">
                                        Awaiting verification from campus security. You will be notified once reviewed.
                                    </p>
                                </div>
                            <?php elseif ($vehicle['status'] === 'rejected'): ?>
                                <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-sm text-red-800">
                                        This registration was rejected. Please contact the security office if you need further assistance.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="max-w-6xl mx-auto px-5 text-center">
            <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
        </div>
    </footer>

    <!-- QR Code Modal -->
    <div id="qrModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeQrModal()"></div>

        <!-- Modal container - centered -->
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <!-- Modal panel -->
            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Vehicle QR Code
                        </h3>
                        <div class="mt-2 flex justify-center bg-white p-4 rounded-lg border-2 border-gray-100">
                            <img id="modalQrImage" src="" alt="Vehicle QR Code" class="max-w-full h-auto" />
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-1">QR Payload:</p>
                            <p id="modalQrData" class="text-xs font-mono bg-gray-100 p-2 rounded break-all text-gray-800"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeQrModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                        Close
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
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutModal();
                closeQrModal();
            }
        });

        // QR Modal Functions
        function openQrModal(imagePath, qrData) {
            document.getElementById('modalQrImage').src = '../' + imagePath;
            document.getElementById('modalQrData').textContent = qrData;
            document.getElementById('qrModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>

