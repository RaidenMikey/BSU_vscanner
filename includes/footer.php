<?php
if (!isset($root_path)) {
    $root_path = '.';
}
?>
    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-8 sm:py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 sm:gap-10 md:gap-12 mb-8 sm:mb-10 md:mb-12">
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold mb-3 md:mb-4 text-primary-red">BSU Vehicle Scanner</h3>
                    <p class="text-sm sm:text-base text-gray-300 leading-relaxed">Advanced vehicle scanning system for university security management.</p>
                </div>
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold mb-3 md:mb-4">Quick Links</h4>
                    <ul class="list-none">
                        <li class="mb-2 sm:mb-3"><a href="#features" class="text-sm sm:text-base text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">Features</a></li>
                        <li class="mb-2 sm:mb-3"><a href="#how-it-works" class="text-sm sm:text-base text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">How It Works</a></li>
                        <li class="mb-2 sm:mb-3"><a href="#contact" class="text-sm sm:text-base text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg sm:text-xl font-semibold mb-3 md:mb-4">Contact</h4>
                    <ul class="list-none text-sm sm:text-base text-gray-300">
                        <li class="mb-2 sm:mb-3">Security Office</li>
                        <li class="mb-2 sm:mb-3">Email: security@bsu.edu</li>
                        <li class="mb-2 sm:mb-3">Phone: (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <div class="text-center pt-6 sm:pt-8 border-t border-white/10 text-gray-400">
                <p class="text-xs sm:text-sm">&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?php if (file_exists($root_path . '/assets/js/script.js')): ?>
    <script src="<?php echo $root_path; ?>/assets/js/script.js"></script>
    <?php endif; ?>
</body>
</html>
