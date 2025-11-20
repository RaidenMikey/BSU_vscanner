<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../auth/admin_login.php');
    exit();
}

$page_title = 'Admin Dashboard - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Admin Dashboard</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Manage vehicle registrations, registered vehicles, and user accounts.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in-up">
            <!-- Evaluate Vehicle Registrations -->
            <a href="evaluate_registrations.php" class="block group">
                <div class="bg-white rounded-2xl shadow-lg p-6 h-full border-2 border-transparent hover:border-primary-red transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-primary-red transition-colors duration-300">
                        <svg class="w-6 h-6 text-primary-red group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary-red transition-colors duration-300">Evaluate Registrations</h2>
                    <p class="text-gray-600 text-sm">Review pending vehicle registration requests from students.</p>
                </div>
            </a>

            <!-- Manage Registered Vehicles -->
            <a href="manage_vehicles.php" class="block group">
                <div class="bg-white rounded-2xl shadow-lg p-6 h-full border-2 border-transparent hover:border-primary-red transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors duration-300">Manage Vehicles</h2>
                    <p class="text-gray-600 text-sm">View and manage all registered vehicles in the system.</p>
                </div>
            </a>

            <!-- Manage Users -->
            <a href="manage_users.php" class="block group">
                <div class="bg-white rounded-2xl shadow-lg p-6 h-full border-2 border-transparent hover:border-primary-red transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors duration-300">Manage Users</h2>
                    <p class="text-gray-600 text-sm">Manage student and guard accounts.</p>
                </div>
            </a>

            <!-- Manage Vehicle Files -->
            <a href="manage_files.php" class="block group">
                <div class="bg-white rounded-2xl shadow-lg p-6 h-full border-2 border-transparent hover:border-primary-red transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-yellow-600 transition-colors duration-300">
                        <svg class="w-6 h-6 text-yellow-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-yellow-600 transition-colors duration-300">Manage Files</h2>
                    <p class="text-gray-600 text-sm">Browse and manage uploaded vehicle documents.</p>
                </div>
            </a>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>
