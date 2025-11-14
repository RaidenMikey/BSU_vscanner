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
    <link rel="icon" type="image/png" href="../images/Batangas_State_Logo.png">
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
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-3 md:py-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 sm:gap-2.5 text-lg sm:text-xl md:text-2xl font-bold text-primary-red">
                    <span class="text-xl sm:text-2xl md:text-3xl">🚗</span>
                    <span class="hidden sm:inline">BSU Vehicle Scanner</span>
                    <span class="sm:hidden">BSU Scanner</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? 'Student'); ?></p>
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
            }
        });
    </script>
</body>
</html>
