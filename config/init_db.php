<?php
/**
 * BSU Vehicle Scanner Database Initialization Script
 * 
 * This script creates the database for the BSU Vehicle Scanner system.
 * Run this file once to initialize the database.
 * 
 * Usage: Navigate to http://localhost/BSU_vscanner/config/init_db.php in your browser
 *        or run: php config/init_db.php from command line
 */

require_once 'config.php';

// Database configuration (using constants from config.php)
$host = DB_HOST;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$database = DB_NAME;

// Create connection without selecting a database
try {
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$database` 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        // Select the database
        $conn->select_db($database);
        
        // Create users table with role column
        $createUsersTable = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `full_name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `student_id` VARCHAR(50) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('student','guard') NOT NULL DEFAULT 'student',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `student_id` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $tableCreated = $conn->query($createUsersTable);
        $tableMessage = '';
        $migrationMessage = '';
        
        if ($tableCreated === TRUE) {
            $tableMessage = "<div class='bg-green-50 border border-green-200 rounded-lg p-4 mb-4'>
                <p class='text-green-800 text-sm'>✅ Users table created/verified successfully!</p>
            </div>";
            
            // Check if role column exists, if not add it (for existing tables)
            $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
            if ($checkColumn->num_rows === 0) {
                // Add role column to existing table
                $addRoleColumn = "ALTER TABLE users ADD COLUMN role ENUM('student','guard') NOT NULL DEFAULT 'student' AFTER password";
                if ($conn->query($addRoleColumn) === TRUE) {
                    $migrationMessage = "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4'>
                        <p class='text-blue-800 text-sm'>✅ Added 'role' column to existing users table. All users set to 'student' by default.</p>
                    </div>";
                } else {
                    $migrationMessage = "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4'>
                        <p class='text-yellow-800 text-sm'>⚠️ Could not add role column: " . $conn->error . "</p>
                    </div>";
                }
            }
            
            // Create vehicles table
            $createVehiclesTable = "CREATE TABLE IF NOT EXISTS `vehicles` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `license_plate` VARCHAR(20) NOT NULL,
                `make` VARCHAR(100) NOT NULL,
                `model` VARCHAR(100) NOT NULL,
                `color` VARCHAR(50) NOT NULL,
                `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `license_plate` (`license_plate`),
                KEY `user_id` (`user_id`),
                KEY `status` (`status`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $vehiclesTableCreated = $conn->query($createVehiclesTable);
            if ($vehiclesTableCreated === TRUE) {
                $tableMessage .= "<div class='bg-green-50 border border-green-200 rounded-lg p-4 mb-4'>
                    <p class='text-green-800 text-sm'>✅ Vehicles table created successfully!</p>
                </div>";
            } else {
                $tableMessage .= "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4'>
                    <p class='text-yellow-800 text-sm'>⚠️ Vehicles table: " . $conn->error . "</p>
                </div>";
            }
        } else {
            $tableMessage = "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4'>
                <p class='text-yellow-800 text-sm'>⚠️ Users table: " . $conn->error . "</p>
            </div>";
        }
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Initialization - BSU Vehicle Scanner</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-red': '#DC2626',
                        'primary-red-dark': '#B91C1C',
                    },
                }
            }
        }
    </script>
</head>
<body class='bg-gray-100 min-h-screen flex items-center justify-center p-4'>
    <div class='bg-white rounded-lg shadow-lg p-8 max-w-md w-full'>
        <div class='text-center mb-6'>
            <div class='text-4xl mb-4'>✅</div>
            <h1 class='text-2xl font-bold text-gray-900 mb-2'>Database Created Successfully!</h1>
            <p class='text-gray-600'>Database name: <strong class='text-primary-red'>$database</strong></p>
        </div>
        $tableMessage
        $migrationMessage
        <div class='bg-green-50 border border-green-200 rounded-lg p-4 mb-6'>
            <p class='text-green-800 text-sm'>The database has been initialized and is ready to use.</p>
        </div>
        <div class='text-center'>
            <a href='../index.php' class='inline-block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-200'>
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>";
    } else {
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Error - BSU Vehicle Scanner</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-red': '#DC2626',
                    },
                }
            }
        }
    </script>
</head>
<body class='bg-gray-100 min-h-screen flex items-center justify-center p-4'>
    <div class='bg-white rounded-lg shadow-lg p-8 max-w-md w-full'>
        <div class='text-center mb-6'>
            <div class='text-4xl mb-4'>❌</div>
            <h1 class='text-2xl font-bold text-gray-900 mb-2'>Database Creation Failed</h1>
        </div>
        <div class='bg-red-50 border border-red-200 rounded-lg p-4 mb-6'>
            <p class='text-red-800 text-sm'>Error: " . $conn->error . "</p>
        </div>
        <div class='text-center'>
            <a href='../index.php' class='inline-block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-200'>
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Error - BSU Vehicle Scanner</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 min-h-screen flex items-center justify-center p-4'>
    <div class='bg-white rounded-lg shadow-lg p-8 max-w-md w-full'>
        <div class='text-center mb-6'>
            <div class='text-4xl mb-4'>❌</div>
            <h1 class='text-2xl font-bold text-gray-900 mb-2'>Connection Error</h1>
        </div>
        <div class='bg-red-50 border border-red-200 rounded-lg p-4 mb-6'>
            <p class='text-red-800 text-sm'>" . $e->getMessage() . "</p>
            <p class='text-red-600 text-xs mt-2'>Please check your database configuration in config/init_db.php</p>
        </div>
    </div>
</body>
</html>";
}
?>

