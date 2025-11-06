<?php
/**
 * BSU Vehicle Scanner Database Initialization Script
 * 
 * This script creates the database for the BSU Vehicle Scanner system.
 * Run this file once to initialize the database.
 * 
 * Usage: Navigate to http://localhost/BSU_vscanner/db_init.php in your browser
 *        or run: php db_init.php from command line
 */

// Database configuration
$host = 'localhost';
$username = 'root'; // Change if needed
$password = ''; // Change if needed
$database = 'bsu_vscanner';

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
        <div class='bg-green-50 border border-green-200 rounded-lg p-4 mb-6'>
            <p class='text-green-800 text-sm'>The database has been initialized and is ready for table creation.</p>
        </div>
        <div class='text-center'>
            <a href='index.php' class='inline-block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-200'>
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
            <a href='index.php' class='inline-block bg-primary-red text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-red-dark transition-colors duration-200'>
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
            <p class='text-red-600 text-xs mt-2'>Please check your database configuration in db_init.php</p>
        </div>
    </div>
</body>
</html>";
}
?>

