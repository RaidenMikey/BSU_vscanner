<?php
session_start();
require_once '../config/config.php';

$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'guard') {
        header('Location: ../guard/guard.php');
    } else {
        header('Location: ../student/student.php');
    }
    exit();
}

// Check for session timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = 'Session expired. Please log in again.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/', $email)) {
        $error = 'Email must be a valid BatState-U email address (@g.batstate-u.edu.ph).';
    } else {
        // Connect to database
        $conn = getDBConnection();
        
        // Get user by email
        $stmt = $conn->prepare("SELECT id, full_name, email, student_id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Invalid email or password.';
            $stmt->close();
        } else {
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['user_role'] = $user['role'];
                
                // Handle remember me (set cookie for 30 days)
                if ($remember) {
                    $cookie_value = base64_encode($user['id'] . ':' . hash('sha256', $user['email'] . $user['password']));
                    setcookie('remember_token', $cookie_value, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                }
                
                // Redirect to dashboard based on role
                if ($user['role'] === 'guard') {
                    header('Location: ../guard/guard.php');
                } else {
                    header('Location: ../student/student.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        }
        
        $conn->close();
    }
}

$page_title = 'Login - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gradient-to-br from-white to-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md animate-fade-in-up">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 text-xl sm:text-2xl md:text-3xl font-bold text-primary-red mb-4">
                <span class="text-2xl sm:text-3xl md:text-4xl">🚗</span>
                <span class="break-words">BSU Vehicle Scanner</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-sm sm:text-base text-gray-600">Sign in to your account to continue</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 md:p-8 border-2 border-gray-100">
            <?php if ($error): ?>
                <div class="mb-5 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm" class="space-y-5">
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
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                        autocomplete="current-password"
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
                <a href="../index.php" class="text-sm text-gray-600 hover:text-primary-red transition-colors duration-200 font-medium">
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
            const button = document.getElementById('loginButton');
            const text = document.getElementById('loginText');
            const spinner = document.getElementById('loginSpinner');
            
            // Show loading state
            button.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            
            // Form will submit normally to the server
        });
    </script>
</body>
</html>

