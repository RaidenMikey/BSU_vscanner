<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BSU Vehicle Scanner</title>
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
<body class="font-sans text-gray-900 bg-gradient-to-br from-white to-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md animate-fade-in-up">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2.5 text-3xl font-bold text-primary-red mb-4">
                <span class="text-4xl">🚗</span>
                <span>BSU Vehicle Scanner</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Sign in to your account to continue</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-gray-100">
            <form method="POST" action="#" id="loginForm" class="space-y-5">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-primary-red">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="example@g.batstate-u.edu.ph"
                        pattern="^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$"
                        autocomplete="off"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Must be a valid BatState-U email address
                    </p>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-primary-red">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        autocomplete="new-password"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded text-primary-red focus:ring-primary-red border-gray-300"
                        >
                        <span class="ml-2">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-primary-red hover:underline font-medium">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="loginButton"
                    class="w-full bg-primary-red hover:bg-primary-red-dark text-white py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                >
                    <span id="loginText">Sign In</span>
                    <span id="loginSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-primary-red hover:underline font-medium">
                        Register here
                    </a>
                </p>
            </div>

            <!-- Back to Home -->
            <div class="mt-4 text-center">
                <a href="index.php" class="text-sm text-gray-600 hover:text-primary-red transition-colors duration-200 font-medium">
                    ← Back to Homepage
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>&copy; 2024 BSU Vehicle Scanner. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Handle form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const button = document.getElementById('loginButton');
            const text = document.getElementById('loginText');
            const spinner = document.getElementById('loginSpinner');
            
            button.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            
            // Simulate form submission (replace with actual form handling)
            setTimeout(() => {
                button.disabled = false;
                text.classList.remove('hidden');
                spinner.classList.add('hidden');
                alert('Login functionality will be implemented here');
            }, 1500);
        });
    </script>
</body>
</html>

