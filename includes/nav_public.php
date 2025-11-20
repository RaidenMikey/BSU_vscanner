<?php
if (!isset($root_path)) {
    $root_path = '.';
}
?>
    <!-- Navigation -->
    <nav class="navbar bg-white shadow-sm sticky top-0 z-50 py-4">
        <div class="max-w-6xl mx-auto px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2.5 text-2xl font-bold text-primary-red">
                    <span class="text-3xl">🚗</span>
                    <span>BSU Vehicle Scanner</span>
                </div>
                <ul class="hidden md:flex list-none gap-8 items-center">
                    <li><a href="#features" class="text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline">Features</a></li>
                    <li><a href="#how-it-works" class="text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline">How It Works</a></li>
                    <li><a href="#contact" class="text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline">Contact</a></li>
                    <li><a href="<?php echo $root_path; ?>/auth/register.php" class="text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline">Register</a></li>
                    <li><a href="<?php echo $root_path; ?>/auth/login.php" class="bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-300 no-underline">Login</a></li>
                </ul>
                <!-- Mobile menu button -->
                <button id="mobileMenuBtn" class="md:hidden text-gray-900 text-2xl focus:outline-none">
                    <span id="menuIcon">☰</span>
                </button>
            </div>
            <!-- Mobile menu -->
            <ul id="mobileMenu" class="hidden md:hidden list-none mt-4 space-y-3 pb-4">
                <li><a href="#features" class="block text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline py-2">Features</a></li>
                <li><a href="#how-it-works" class="block text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline py-2">How It Works</a></li>
                <li><a href="#contact" class="block text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline py-2">Contact</a></li>
                <li><a href="<?php echo $root_path; ?>/auth/register.php" class="block text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline py-2">Register</a></li>
                <li><a href="<?php echo $root_path; ?>/auth/login.php" class="block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-300 no-underline text-center">Login</a></li>
            </ul>
        </div>
    </nav>
