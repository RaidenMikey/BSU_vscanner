<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSU Vehicle Scanner - University Security System</title>
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
        @keyframes scan {
            0% {
                top: 0;
                opacity: 1;
            }
            50% {
                opacity: 1;
            }
            100% {
                top: 100%;
                opacity: 0;
            }
        }
        .scan-line {
            animation: scan 2s linear infinite;
        }
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
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease;
        }
        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease;
        }
    </style>
</head>
<body class="font-sans text-gray-900 bg-white overflow-x-hidden">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-4">
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
                    <li><a href="register.php" class="text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline">Register</a></li>
                    <li><a href="login.php" class="bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-300 no-underline">Login</a></li>
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
                <li><a href="register.php" class="block text-gray-900 font-medium hover:text-primary-red transition-colors duration-300 no-underline py-2">Register</a></li>
                <li><a href="login.php" class="block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-300 no-underline text-center">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-white to-gray-100 py-20 min-h-[600px] flex items-center">
        <div class="max-w-6xl mx-auto px-5">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="animate-fade-in-up">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">Secure Campus Entry with Advanced Vehicle Scanning</h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">Efficiently verify student vehicle registration and manage campus access with our state-of-the-art security system</p>
                    <div class="flex gap-4 flex-wrap">
                        <a href="login.php" class="bg-primary-red text-white px-8 py-3.5 rounded-lg text-base font-semibold hover:bg-primary-red-dark hover:-translate-y-0.5 hover:shadow-lg hover:shadow-red-500/30 transition-all duration-300 inline-block">Get Started</a>
                        <button class="bg-white text-primary-red border-2 border-primary-red px-8 py-3.5 rounded-lg text-base font-semibold hover:bg-gray-100 transition-colors duration-300">Learn More</button>
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
    <section id="features" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-5">
            <h2 class="text-4xl font-bold text-center text-gray-900 mb-4">Key Features</h2>
            <p class="text-center text-gray-600 text-lg mb-16">Comprehensive vehicle management and security solutions</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-6xl mb-6">📷</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Camera Phone Scanning</h3>
                    <p class="text-gray-600 leading-relaxed">Quickly scan license plates using your mobile device camera. Instant verification with advanced OCR technology.</p>
                </div>
                <div class="bg-white p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-6xl mb-6">🔍</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Manual License Plate Check</h3>
                    <p class="text-gray-600 leading-relaxed">Input license plate numbers directly for verification. Fast and accurate registration lookup system.</p>
                </div>
                <div class="bg-white p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-6xl mb-6">👤</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Student Information Display</h3>
                    <p class="text-gray-600 leading-relaxed">View complete student details along with their registered vehicle information in one comprehensive view.</p>
                </div>
                <div class="bg-white p-10 rounded-2xl text-center transition-all duration-300 border-2 border-gray-200 hover:-translate-y-2 hover:shadow-xl hover:shadow-red-500/15 hover:border-primary-red">
                    <div class="text-6xl mb-6">📅</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Visitor Entry Booking</h3>
                    <p class="text-gray-600 leading-relaxed">Schedule and manage visitor vehicle entries. Pre-approved access for guests and temporary visitors.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 bg-gray-100">
        <div class="max-w-6xl mx-auto px-5">
            <h2 class="text-4xl font-bold text-center text-gray-900 mb-16">How It Works</h2>
            <div class="flex flex-col md:flex-row justify-between items-center flex-wrap gap-8 mt-16">
                <div class="flex-1 min-w-[200px] text-center p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[60px] h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Scan or Input</h3>
                    <p class="text-gray-600 leading-relaxed">Use your camera to scan the license plate or manually enter the plate number</p>
                </div>
                <div class="text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="flex-1 min-w-[200px] text-center p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[60px] h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Verify Registration</h3>
                    <p class="text-gray-600 leading-relaxed">System checks if the vehicle is registered in the university database</p>
                </div>
                <div class="text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="flex-1 min-w-[200px] text-center p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[60px] h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">View Information</h3>
                    <p class="text-gray-600 leading-relaxed">Display student details and vehicle information for security verification</p>
                </div>
                <div class="text-3xl text-primary-red font-bold hidden md:block">→</div>
                <div class="flex-1 min-w-[200px] text-center p-8 bg-white rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="w-[60px] h-[60px] bg-primary-red text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">4</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Grant Access</h3>
                    <p class="text-gray-600 leading-relaxed">Approve entry for registered vehicles or process visitor bookings</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-5">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Enhanced Campus Security</h2>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">Our vehicle scanning system provides a comprehensive solution for managing campus access while ensuring the safety and security of all university members.</p>
                    <ul class="list-none">
                        <li class="py-3 text-gray-900 text-lg font-medium">✓ Real-time vehicle verification</li>
                        <li class="py-3 text-gray-900 text-lg font-medium">✓ Streamlined entry process</li>
                        <li class="py-3 text-gray-900 text-lg font-medium">✓ Complete access history tracking</li>
                        <li class="py-3 text-gray-900 text-lg font-medium">✓ Visitor management system</li>
                        <li class="py-3 text-gray-900 text-lg font-medium">✓ Secure and reliable database</li>
                    </ul>
                </div>
                <div class="flex justify-center">
                    <div class="bg-gradient-to-br from-primary-red to-primary-red-dark p-12 rounded-3xl text-white shadow-2xl shadow-red-500/30 w-full max-w-md transition-transform duration-300 hover:scale-105">
                        <div class="text-center py-6 border-b border-white/20">
                            <div class="text-4xl font-bold mb-2">100%</div>
                            <div class="text-base opacity-90">Accurate Scanning</div>
                        </div>
                        <div class="text-center py-6 border-b border-white/20">
                            <div class="text-4xl font-bold mb-2">24/7</div>
                            <div class="text-base opacity-90">System Availability</div>
                        </div>
                        <div class="text-center py-6">
                            <div class="text-4xl font-bold mb-2">Fast</div>
                            <div class="text-base opacity-90">Quick Verification</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-primary-red to-primary-red-dark text-white text-center">
        <div class="max-w-6xl mx-auto px-5">
            <div>
                <h2 class="text-4xl font-bold mb-4">Ready to Enhance Your Campus Security?</h2>
                <p class="text-xl mb-10 opacity-95">Get started today and experience seamless vehicle management</p>
                <a href="login.php" class="bg-white text-primary-red px-10 py-4.5 rounded-lg text-lg font-semibold hover:bg-gray-100 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 inline-block">Contact Security Office</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-16">
        <div class="max-w-6xl mx-auto px-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                <div>
                    <h3 class="text-2xl font-bold mb-4 text-primary-red">BSU Vehicle Scanner</h3>
                    <p class="text-gray-300 leading-relaxed">Advanced vehicle scanning system for university security management.</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-4">Quick Links</h4>
                    <ul class="list-none">
                        <li class="mb-3"><a href="#features" class="text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">Features</a></li>
                        <li class="mb-3"><a href="#how-it-works" class="text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">How It Works</a></li>
                        <li class="mb-3"><a href="#contact" class="text-gray-300 hover:text-primary-red transition-colors duration-300 no-underline">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-4">Contact</h4>
                    <ul class="list-none text-gray-300">
                        <li class="mb-3">Security Office</li>
                        <li class="mb-3">Email: security@bsu.edu</li>
                        <li class="mb-3">Phone: (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <div class="text-center pt-8 border-t border-white/10 text-gray-400">
                <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

