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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
    $userId = (int) $_POST['user_id'];
    
    // Verify user exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Delete user (cascading delete should handle vehicles if configured, otherwise manual cleanup might be needed)
        // Ideally, we should check for related records, but for now we assume DB constraints or simple deletion
        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $userId);
        
        if ($deleteStmt->execute()) {
            $_SESSION['admin_success_message'] = "User deleted successfully.";
        } else {
            $_SESSION['admin_error_message'] = "Failed to delete user.";
        }
        $deleteStmt->close();
    } else {
        $_SESSION['admin_error_message'] = "User not found.";
    }
    $checkStmt->close();
    
    header('Location: manage_users.php');
    exit();
}

// Get messages from session
$successMessage = $_SESSION['admin_success_message'] ?? '';
$errorMessage = $_SESSION['admin_error_message'] ?? '';
unset($_SESSION['admin_success_message']);
unset($_SESSION['admin_error_message']);

// Fetch Students
$students = [];
$studentStmt = $conn->prepare("SELECT id, full_name, email, student_id, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC");
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
while ($row = $studentResult->fetch_assoc()) {
    $students[] = $row;
}
$studentStmt->close();

// Fetch Guards
$guards = [];
$guardStmt = $conn->prepare("SELECT id, full_name, email, student_id AS employee_id, created_at FROM users WHERE role = 'guard' ORDER BY created_at DESC");
$guardStmt->execute();
$guardResult = $guardStmt->get_result();
while ($row = $guardResult->fetch_assoc()) {
    $guards[] = $row;
}
$guardStmt->close();

$conn->close();

$page_title = 'Manage Users - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<style>
    .tab-button {
        border-bottom: 2px solid transparent;
    }
    .tab-button.active {
        border-bottom-color: #DC2626;
        color: #DC2626;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="mb-2">
            <a href="admin.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="bg-gradient-to-br from-green-600 to-green-800 rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Manage Users</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">View and manage student and guard accounts.</p>
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

        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <!-- Tabs -->
            <div class="flex border-b-2 border-gray-200 mb-6">
                <button onclick="switchTab('students')" id="tab-students" class="flex-1 py-3 px-4 text-center font-semibold text-gray-500 hover:text-primary-red transition-colors duration-200 tab-button active">
                    Students (<?php echo count($students); ?>)
                </button>
                <button onclick="switchTab('guards')" id="tab-guards" class="flex-1 py-3 px-4 text-center font-semibold text-gray-500 hover:text-primary-red transition-colors duration-200 tab-button">
                    Guards (<?php echo count($guards); ?>)
                </button>
            </div>

            <!-- Students Tab -->
            <div id="content-students" class="tab-content active">
                <?php if (empty($students)): ?>
                    <p class="text-gray-500 text-sm text-center py-4">No students registered yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($student['email']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($student['student_id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openDeleteModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>', 'student')" class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Guards Tab -->
            <div id="content-guards" class="tab-content">
                <?php if (empty($guards)): ?>
                    <p class="text-gray-500 text-sm text-center py-4">No guards registered yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($guards as $guard): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($guard['full_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($guard['email']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($guard['employee_id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($guard['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openDeleteModal(<?php echo $guard['id']; ?>, '<?php echo htmlspecialchars($guard['full_name']); ?>', 'guard')" class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Remove User</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to remove <strong id="deleteUserName"></strong>?<br>
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="deleteUserId" value="">
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
        function switchTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Deactivate all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                button.classList.remove('text-primary-red');
                button.classList.add('text-gray-500');
            });
            
            // Show selected content
            document.getElementById('content-' + tabName).classList.add('active');
            
            // Activate selected button
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.add('active');
            activeBtn.classList.remove('text-gray-500');
            activeBtn.classList.add('text-primary-red');
        }

        function openDeleteModal(id, name, role) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;
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
