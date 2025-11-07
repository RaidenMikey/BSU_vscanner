<?php
session_start();
require_once 'config/config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validation
    if (empty($full_name) || empty($email) || empty($student_id) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/', $email)) {
        $error = 'Email must be a valid BatState-U email address (@g.batstate-u.edu.ph).';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms and Conditions.';
    } else {
        // Connect to database
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address is already registered.';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Check if student ID already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Student ID is already registered.';
                $stmt->close();
            } else {
                $stmt->close();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, student_id, password, role) VALUES (?, ?, ?, ?, ?)");
                $role = 'student';
                $stmt->bind_param("sssss", $full_name, $email, $student_id, $hashed_password, $role);
                
                if ($stmt->execute()) {
                    $success = 'Registration successful! Redirecting to login page...';
                    // Clear form data
                    $full_name = $email = $student_id = '';
                    // Redirect to login page after 2 seconds
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'Registration failed. Please try again later.';
                }
                
                $stmt->close();
            }
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BSU Vehicle Scanner</title>
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
            <p class="text-gray-600">Register to access the vehicle scanning system</p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-gray-100">
            <?php if ($error): ?>
                <div class="mb-5 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-5 bg-green-50 border-2 border-green-200 rounded-lg p-4">
                    <p class="text-green-800 text-sm font-medium"><?php echo htmlspecialchars($success); ?></p>
                    <p class="text-green-700 text-xs mt-2">If you are not redirected automatically, <a href="login.php" class="underline font-semibold">click here to login</a>.</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm" class="space-y-5">
                <!-- Full Name Input -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-primary-red">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        placeholder="John Doe"
                        autocomplete="name"
                        required
                        value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                </div>

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
                        autocomplete="email"
                        required
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Must be a valid BatState-U email address
                    </p>
                </div>

                <!-- Student ID Input -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Student ID <span class="text-primary-red">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="student_id" 
                        name="student_id" 
                        placeholder="2020-12345"
                        autocomplete="off"
                        required
                        value="<?php echo isset($student_id) ? htmlspecialchars($student_id) : ''; ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
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
                        minlength="8"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Must be at least 8 characters long
                    </p>
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password <span class="text-primary-red">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        autocomplete="new-password"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <input 
                        type="checkbox" 
                        id="terms" 
                        name="terms" 
                        required
                        class="mt-1 w-4 h-4 rounded text-primary-red focus:ring-primary-red border-gray-300"
                    >
                    <label for="terms" class="ml-2 text-sm text-gray-700">
                        I agree to the <a href="#" class="text-primary-red hover:underline">Terms and Conditions</a> and <a href="#" class="text-primary-red hover:underline">Privacy Policy</a> <span class="text-primary-red">*</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="registerButton"
                    class="w-full bg-primary-red hover:bg-primary-red-dark text-white py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                >
                    <span id="registerText">Create Account</span>
                    <span id="registerSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating account...
                    </span>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-primary-red hover:underline font-medium">
                        Sign in here
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
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);

        // Handle form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Validate passwords match before submission
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            const button = document.getElementById('registerButton');
            const text = document.getElementById('registerText');
            const spinner = document.getElementById('registerSpinner');
            
            // Show loading state
            button.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            
            // Form will submit normally to the server
        });
    </script>
</body>
</html>

