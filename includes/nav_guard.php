<?php
if (!isset($root_path)) {
    $root_path = '.';
}
// Assumes $user is available
?>
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
