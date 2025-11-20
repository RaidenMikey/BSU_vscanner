<?php
session_start();
require_once '../config/config.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'admin') {
        header('Location: ../admin/admin.php');
    } elseif ($role === 'guard') {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif ($email !== 'admin' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, full_name, email, student_id, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'Invalid credentials or unauthorized access.';
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['user_role'] = $user['role'];

                header('Location: ../admin/admin.php');
                exit();
            }
            $error = 'Invalid credentials or unauthorized access.';
        }

        $stmt->close();
        $conn->close();
    }
}

$page_title = 'Admin Login - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gradient-to-br from-white to-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md animate-fade-in-up">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 text-xl sm:text-2xl md:text-3xl font-bold text-primary-red mb-4">
                <span class="text-2xl sm:text-3xl md:text-4xl">🛡️</span>
                <span class="break-words">BSU Vehicle Scanner Admin</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Admin Access</h1>
            <p class="text-sm sm:text-base text-gray-600">Authorized personnel only</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 md:p-8 border-2 border-gray-100">
            <?php if ($error): ?>
                <div class="mb-5 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-primary-red">*</span>
                    </label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="admin"
                        autocomplete="username"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                    >
                </div>

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

                <button
                    type="submit"
                    class="w-full bg-primary-red hover:bg-primary-red-dark text-white py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>

