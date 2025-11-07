<?php
session_start();
require_once '../config/config.php';

// Student access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../login.php');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - BSU Vehicle Scanner</title>
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
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-4">
        <div class="max-w-6xl mx-auto px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2.5 text-2xl font-bold text-primary-red">
                    <span class="text-3xl">🚗</span>
                    <span>BSU Vehicle Scanner</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? 'Student'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-5 py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-8 text-white mb-8 animate-fade-in-up shadow-xl">
            <h1 class="text-4xl font-bold mb-2">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? 'Student'); ?>!</h1>
            <p class="text-lg opacity-90">Manage your vehicle registration and view campus access updates</p>
        </div>

        <!-- Student Information -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 animate-fade-in-up">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Profile</h2>
            <div class="grid md:grid-cols-2 gap-6">
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
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">🚙</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Register Your Vehicle</h3>
                <p class="text-gray-600 text-sm mb-4">Submit vehicle details and upload documents for verification.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2 border-gray-200 hover:border-primary-red">
                <div class="text-4xl mb-4">📄</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Registration Status</h3>
                <p class="text-gray-600 text-sm mb-4">Track the approval status of your submitted vehicle registrations.</p>
                <button class="text-primary-red font-medium hover:underline">Coming Soon →</button>
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
        <div class="bg-white rounded-2xl shadow-lg p-8 animate-fade-in-up">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Helpful Resources</h2>
            <div class="grid md:grid-cols-2 gap-6">
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
</body>
</html>
