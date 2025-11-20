<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

// Student access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, full_name, email, student_id, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$page_title = 'Student Dashboard - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_student.php'; ?>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 sm:px-5 py-6 md:py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white mb-6 md:mb-8 animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? 'Student'); ?>!</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Manage your vehicle registration and view campus access updates</p>
        </div>

        <!-- Student Information -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Your Profile</h2>
            <div class="grid sm:grid-cols-2 gap-4 md:gap-6">
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Full Name</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></p>
                </div>
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">BatState-U Email</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Student ID</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['student_id'] ?? ''); ?></p>
                </div>
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Member Since</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php
                        if (!empty($user['created_at'])) {
                            $date = new DateTime($user['created_at']);
                            echo $date->format('F j, Y');
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Student Tools -->
        <div class="grid sm:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">🚙</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Register Your Vehicle</h3>
                <p class="text-gray-600 text-sm mb-4">Submit vehicle details and upload documents for verification.</p>
                <a href="register_vehicle.php" class="text-primary-red font-medium hover:underline">Register Now →</a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">📄</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Registered Vehicles</h3>
                <p class="text-gray-600 text-sm mb-4">View all of your submitted vehicles and their current status.</p>
                <a href="registered_vehicles.php" class="text-primary-red font-medium hover:underline">View Vehicles →</a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">🔔</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Security Alerts</h3>
                <p class="text-gray-600 text-sm mb-4">Stay updated on campus security notices and reminders.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">📅</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Visitor Passes</h3>
                <p class="text-gray-600 text-sm mb-4">Request temporary access for family or guests visiting campus.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
        </div>

        <!-- Helpful Resources -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Helpful Resources</h2>
            <div class="grid sm:grid-cols-2 gap-4 md:gap-6">
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Campus Parking Guidelines</h3>
                    <p class="text-gray-600 text-sm mb-4">Learn the rules for parking zones, decals, and operating hours.</p>
                    <button class="text-primary-red font-medium hover:underline">Download PDF →</button>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Contact Security Office</h3>
                    <p class="text-gray-600 text-sm mb-4">Need help with your vehicle registration? Reach out for support.</p>
                    <button class="text-primary-red font-medium hover:underline">Contact Now →</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="max-w-6xl mx-auto px-5 text-center">
            <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
        </div>
    </footer>

    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>
