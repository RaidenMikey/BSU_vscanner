<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../auth/admin_login.php');
    exit();
}

$conn = getDBConnection();

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['vehicle_id'])) {
    $vehicleId = (int) $_POST['vehicle_id'];
    
    // Verify vehicle exists
    $checkStmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ?");
    $checkStmt->bind_param("i", $vehicleId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $deleteStmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $deleteStmt->bind_param("i", $vehicleId);
        
        if ($deleteStmt->execute()) {
            $_SESSION['admin_success_message'] = "Vehicle deleted successfully.";
        } else {
            $_SESSION['admin_error_message'] = "Failed to delete vehicle.";
        }
        $deleteStmt->close();
    } else {
        $_SESSION['admin_error_message'] = "Vehicle not found.";
    }
    $checkStmt->close();
    
    header('Location: manage_vehicles.php');
    exit();
}

// Get messages from session
$successMessage = $_SESSION['admin_success_message'] ?? '';
$errorMessage = $_SESSION['admin_error_message'] ?? '';
unset($_SESSION['admin_success_message']);
unset($_SESSION['admin_error_message']);

// Fetch all vehicles
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
    ORDER BY v.registered_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}
$stmt->close();
$conn->close();

$page_title = 'Manage Vehicles - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="mb-2">
            <a href="admin.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Manage Registered Vehicles</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">View and manage all registered vehicles in the system.</p>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4 animate-fade-in-up">
                <p class="text-green-800 text-sm font-medium"><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 animate-fade-in-up">
                <p class="text-red-800 text-sm font-medium"><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>

        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <?php if (empty($vehicles)): ?>
                <p class="text-gray-500 text-sm">No vehicles registered yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle Info</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plate Number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['student_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(ucfirst($vehicle['vehicle_type'])); ?> (<?php echo htmlspecialchars($vehicle['color']); ?>)</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars(strtoupper($vehicle['license_plate'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                                switch ($vehicle['status']) {
                                                    case 'approved': echo 'bg-green-100 text-green-800'; break;
                                                    case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                                    default: echo 'bg-yellow-100 text-yellow-800';
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst($vehicle['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y g:i A', strtotime($vehicle['registered_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="../<?php echo htmlspecialchars($vehicle['driver_license_image']); ?>" target="_blank" class="text-primary-red hover:text-primary-red-dark mr-3">View Docs</a>
                                        <button onclick="openDeleteModal(<?php echo $vehicle['id']; ?>, '<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>', '<?php echo htmlspecialchars($vehicle['license_plate']); ?>')" class="text-red-600 hover:text-red-900">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeDeleteModal()"></div>
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Remove Vehicle</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to remove <strong id="deleteVehicleName"></strong> (<span id="deleteVehiclePlate"></span>)?<br>
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="vehicle_id" id="deleteVehicleId" value="">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Remove
                        </button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-red sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(id, name, plate) {
            document.getElementById('deleteVehicleId').value = id;
            document.getElementById('deleteVehicleName').textContent = name;
            document.getElementById('deleteVehiclePlate').textContent = plate;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
