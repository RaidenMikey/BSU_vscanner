<?php
session_start();
require_once '../config/config.php';

// Guard access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'guard') {
    header('Location: ../login.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, full_name, email, student_id, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Dashboard - BSU Vehicle Scanner</title>
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
    </style>
</head>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-3 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 sm:gap-2.5 text-lg sm:text-xl md:text-2xl font-bold text-primary-red">
                    <span class="text-xl sm:text-2xl md:text-3xl">🚗</span>
                    <span class="hidden sm:inline">BSU Vehicle Scanner</span>
                    <span class="sm:hidden">BSU Scanner</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? 'Guard'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                    <a href="../logout.php" onclick="return confirm('Are you sure you want to logout?');" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white mb-6 md:mb-8 animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? 'Guard'); ?>!</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Monitor vehicle registrations and manage campus security</p>
        </div>

        <!-- User Information Card -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Guard Information</h2>
            <div class="grid sm:grid-cols-2 gap-4 md:gap-6">
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Full Name</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></p>
                </div>
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Email Address</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
                <div class="border-2 border-gray-200 rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-2">Student/Employee ID</p>
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

        <!-- Features Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <a href="scan.php" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer block">
                <div class="text-4xl mb-4">📷</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Live Vehicle Scanning</h3>
                <p class="text-gray-600 text-sm mb-4">Use camera-based scanning to verify vehicle registrations in real time.</p>
                <span class="text-primary-red font-medium hover:underline">Launch Scanner →</span>
            </a>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer">
                <div class="text-4xl mb-4">🔍</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Manual Plate Lookup</h3>
                <p class="text-gray-600 text-sm mb-4">Search the database by license plate or student ID for quick validation.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer">
                <div class="text-4xl mb-4">🧾</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Incident Reports</h3>
                <p class="text-gray-600 text-sm mb-4">Log and review incidents and flag unregistered vehicles.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer">
                <div class="text-4xl mb-4">📊</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Access Analytics</h3>
                <p class="text-gray-600 text-sm mb-4">View entry statistics and monitor campus access activity.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer">
                <div class="text-4xl mb-4">📅</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Visitor Management</h3>
                <p class="text-gray-600 text-sm mb-4">Approve visitor entry and manage scheduled appointments.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red cursor-pointer">
                <div class="text-4xl mb-4">⚙️</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">System Settings</h3>
                <p class="text-gray-600 text-sm mb-4">Manage guard preferences and notification settings.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Quick Actions</h2>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 md:gap-4">
                <a href="../index.php" class="bg-gray-100 text-gray-900 px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-gray-200 transition-colors duration-300 text-center">
                    ← Back to Homepage
                </a>
                <a href="scan.php" class="bg-primary-red text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300 inline-block text-center">
                    Launch Scanner
                </a>
                <button class="bg-white text-primary-red border-2 border-primary-red px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-gray-50 transition-colors duration-300">
                    View Reports
                </button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-5 text-center">
            <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
