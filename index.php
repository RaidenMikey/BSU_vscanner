<?php
$page_title = 'BSU Vehicle Scanner - University Security System';
$root_path = '.';
require_once 'includes/header.php';
?>
<body class="font-sans text-gray-900 bg-white overflow-x-hidden">
    <?php require_once 'includes/nav_public.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-white to-gray-100 py-12 sm:py-16 md:py-20 min-h-[500px] sm:min-h-[600px] flex items-center">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div class="grid md:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
                <div class="animate-fade-in-up">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-4 md:mb-6 leading-tight">Secure Campus Entry with Advanced Vehicle Scanning</h1>
                    <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 md:mb-8 leading-relaxed">Efficiently verify student vehicle registration and manage campus access with our state-of-the-art security system</p>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 flex-wrap">
                        <a href="auth/login.php" class="bg-primary-red text-white px-6 sm:px-8 py-2.5 sm:py-3.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary-red-dark hover:-translate-y-0.5 hover:shadow-lg hover:shadow-red-500/30 transition-all duration-300 inline-block text-center">Get Started</a>
                        <button class="btn-secondary bg-white text-primary-red border-2 border-primary-red px-6 sm:px-8 py-2.5 sm:py-3.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-gray-100 transition-colors duration-300">Learn More</button>
                    </div>
                </div>
                <div class="flex justify-center items-center animate-fade-in-right">
                    <div class="w-full max-w-[500px] aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center relative overflow-hidden shadow-2xl">
                        <div class="w-4/5 h-3/5 border-4 border-primary-red rounded-xl relative bg-white/90">
                            <div class="scan-line absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-primary-red to-transparent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-12 sm:py-16 md:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center text-gray-900 mb-4">Key Features</h2>
            <p class="text-center text-gray-600 text-base sm:text-lg mb-8 md:mb-16 px-4">Comprehensive vehicle management and security solutions</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <div class="feature-card bg-white p-6 sm:p-8 md:p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-4xl sm:text-5xl md:text-6xl mb-4 md:mb-6">📷</div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 mb-3 md:mb-4">Camera Phone Scanning</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Quickly scan license plates using your mobile device camera. Instant verification with advanced OCR technology.</p>
                </div>
                <div class="feature-card bg-white p-6 sm:p-8 md:p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-4xl sm:text-5xl md:text-6xl mb-4 md:mb-6">🔍</div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 mb-3 md:mb-4">Manual License Plate Check</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Input license plate numbers directly for verification. Fast and accurate registration lookup system.</p>
                </div>
                <div class="feature-card bg-white p-6 sm:p-8 md:p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-4xl sm:text-5xl md:text-6xl mb-4 md:mb-6">👤</div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 mb-3 md:mb-4">Student Information Display</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">View complete student details along with their registered vehicle information in one comprehensive view.</p>
                </div>
                <div class="feature-card bg-white p-6 sm:p-8 md:p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-4xl sm:text-5xl md:text-6xl mb-4 md:mb-6">📅</div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 mb-3 md:mb-4">Visitor Entry Booking</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Schedule and manage visitor vehicle entries. Pre-approved access for guests and temporary visitors.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-12 sm:py-16 md:py-24 bg-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center text-gray-900 mb-8 md:mb-16 px-4">How It Works</h2>
            <div class="flex flex-col md:flex-row justify-between items-center flex-wrap gap-6 sm:gap-8 mt-8 sm:mt-12 md:mt-16">
                <div class="step flex-1 min-w-[200px] text-center p-4 sm:p-6 md:p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[50px] h-[50px] sm:w-[60px] sm:h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold mx-auto mb-4 md:mb-6">1</div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 md:mb-4">Scan or Input</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Use your camera to scan the license plate or manually enter the plate number</p>
                </div>
                <div class="text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="step flex-1 min-w-[200px] text-center p-4 sm:p-6 md:p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[50px] h-[50px] sm:w-[60px] sm:h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold mx-auto mb-4 md:mb-6">2</div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 md:mb-4">Verify Registration</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">System checks if the vehicle is registered in the university database</p>
                </div>
                <div class="text-2xl sm:text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="step flex-1 min-w-[200px] text-center p-4 sm:p-6 md:p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[50px] h-[50px] sm:w-[60px] sm:h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold mx-auto mb-4 md:mb-6">3</div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 md:mb-4">View Information</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Display student details and vehicle information for security verification</p>
                </div>
                <div class="text-2xl sm:text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="step flex-1 min-w-[200px] text-center p-4 sm:p-6 md:p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[50px] h-[50px] sm:w-[60px] sm:h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold mx-auto mb-4 md:mb-6">4</div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 md:mb-4">Grant Access</h3>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Approve entry for registered vehicles or process visitor bookings</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-12 sm:py-16 md:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4">Enhanced Campus Security</h2>
                    <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 leading-relaxed">Our vehicle scanning system provides a comprehensive solution for managing campus access while ensuring the safety and security of all university members.</p>
                    <ul class="list-none">
                        <li class="py-2 sm:py-3 text-gray-900 text-base sm:text-lg font-medium">✓ Real-time vehicle verification</li>
                        <li class="py-2 sm:py-3 text-gray-900 text-base sm:text-lg font-medium">✓ Streamlined entry process</li>
                        <li class="py-2 sm:py-3 text-gray-900 text-base sm:text-lg font-medium">✓ Complete access history tracking</li>
                        <li class="py-2 sm:py-3 text-gray-900 text-base sm:text-lg font-medium">✓ Visitor management system</li>
                        <li class="py-2 sm:py-3 text-gray-900 text-base sm:text-lg font-medium">✓ Secure and reliable database</li>
                    </ul>
                </div>
                <div class="flex justify-center">
                    <div class="stats-card bg-gradient-to-br from-primary-red to-primary-red-dark p-6 sm:p-8 md:p-12 rounded-3xl text-white shadow-2xl shadow-red-500/30 w-full max-w-md transition-transform duration-300 hover:scale-105">
                        <div class="text-center py-4 sm:py-5 md:py-6 border-b border-white/20">
                            <div class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">100%</div>
                            <div class="text-sm sm:text-base opacity-90">Accurate Scanning</div>
                        </div>
                        <div class="text-center py-4 sm:py-5 md:py-6 border-b border-white/20">
                            <div class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">24/7</div>
                            <div class="text-sm sm:text-base opacity-90">System Availability</div>
                        </div>
                        <div class="text-center py-4 sm:py-5 md:py-6">
                            <div class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Fast</div>
                            <div class="text-sm sm:text-base opacity-90">Quick Verification</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 sm:py-16 md:py-24 bg-gradient-to-br from-primary-red to-primary-red-dark text-white text-center">
        <div class="max-w-6xl mx-auto px-4 sm:px-5">
            <div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4 px-4">Ready to Enhance Your Campus Security?</h2>
                <p class="text-base sm:text-lg md:text-xl mb-8 md:mb-10 opacity-95 px-4">Get started today and experience seamless vehicle management</p>
                <a href="auth/login.php" class="bg-white text-primary-red px-6 sm:px-8 md:px-10 py-3 sm:py-3.5 md:py-4.5 rounded-lg text-base sm:text-lg font-semibold hover:bg-gray-100 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 inline-block">Contact Security Office</a>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>

