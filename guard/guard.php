<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

// Guard access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'guard') {
    header('Location: ../auth/login.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$page_title = 'Guard Dashboard - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';

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
    <link rel="icon" type="image/png" href="../images/Batangas_State_Logo.png">
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
                    <button onclick="openLogoutModal()" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </button>
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
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-6 md:p-10 text-white shadow-xl animate-fade-in-up">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">
                        Welcome, Officer <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!
                    </h1>
                    <p class="text-lg opacity-90">Ready to scan vehicles?</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <p class="text-sm font-medium opacity-75 uppercase tracking-wider">Employee ID</p>
                    <p class="text-xl font-mono font-bold"><?php echo htmlspecialchars($user['student_id']); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
            <a href="scan.php" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-primary-red flex items-center gap-6">
                <div class="bg-red-50 p-4 rounded-xl group-hover:bg-primary-red group-hover:text-white transition-colors duration-300">
                    <span class="text-4xl">📷</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 group-hover:text-primary-red transition-colors">Live Vehicle Scanning</h2>
                    <p class="text-gray-500 mt-1">Scan QR codes to verify vehicle entry</p>
                </div>
                <div class="ml-auto text-gray-300 group-hover:text-primary-red transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <div class="bg-white rounded-2xl p-6 shadow-lg border-2 border-gray-100 flex items-center gap-6 opacity-75">
                <div class="bg-gray-100 p-4 rounded-xl text-gray-400">
                    <span class="text-4xl">📝</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-400">Manual Entry Log</h2>
                    <p class="text-gray-400 mt-1">Coming soon</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity (Placeholder) -->
        <div class="bg-white rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Scans</h2>
            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <p>No recent scanning activity to display.</p>
            </div>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>
