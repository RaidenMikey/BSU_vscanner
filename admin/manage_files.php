<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../auth/admin_login.php');
    exit();
}

$baseDir = realpath('../uploads/vehicles');
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';

// Security check: Prevent directory traversal
$realBase = realpath($baseDir);
$userPath = $baseDir . DIRECTORY_SEPARATOR . $currentPath;
$realUserPath = realpath($userPath);

if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
    // Invalid path, reset to base
    $currentPath = '';
    $realUserPath = $realBase;
}

// Breadcrumbs
$breadcrumbs = [];
$pathParts = array_filter(explode('/', $currentPath));
$crumbPath = '';
foreach ($pathParts as $part) {
    $crumbPath .= $part . '/';
    $breadcrumbs[] = [
        'name' => $part,
        'path' => rtrim($crumbPath, '/')
    ];
}

// Scan directory
$files = [];
$dirs = [];
if (is_dir($realUserPath)) {
    $items = scandir($realUserPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $itemPath = $realUserPath . DIRECTORY_SEPARATOR . $item;
        $relativePath = ($currentPath ? $currentPath . '/' : '') . $item;
        
        if (is_dir($itemPath)) {
            // Format directory name: Replace underscores with spaces for display
            $displayName = str_replace('_', ' ', $item);
            
            $dirs[] = [
                'name' => $item, // Keep original for link
                'displayName' => $displayName,
                'path' => $relativePath
            ];
        } else {
            // Format file name based on known prefixes
            $displayName = $item;
            $lowerItem = strtolower($item);
            
            if (str_starts_with($lowerItem, 'driver_license')) {
                $displayName = "Driver's License";
            } elseif (str_starts_with($lowerItem, 'official_receipt') || str_starts_with($lowerItem, 'or_')) {
                $displayName = "OR";
            } elseif (str_starts_with($lowerItem, 'certificate_registration') || str_starts_with($lowerItem, 'cr_')) {
                $displayName = "CR";
            } elseif (str_starts_with($lowerItem, 'qr_code')) {
                $displayName = "QR";
            }
            
            $files[] = [
                'name' => $item,
                'displayName' => $displayName,
                'path' => $relativePath,
                'url' => '../uploads/vehicles/' . $relativePath,
                'size' => filesize($itemPath),
                'date' => filemtime($itemPath)
            ];
        }
    }
}

$page_title = 'Manage Files - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8 space-y-6">
        <div class="mb-2">
            <a href="admin.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-2xl p-4 sm:p-6 md:p-8 text-white animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Manage Vehicle Files</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Browse and manage uploaded vehicle documents.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <!-- Breadcrumbs -->
            <nav class="flex mb-6 text-gray-600 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="manage_files.php" class="inline-flex items-center hover:text-primary-red">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            Root
                        </a>
                    </li>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="?path=<?php echo urlencode($crumb['path']); ?>" class="ml-1 text-sm font-medium hover:text-primary-red md:ml-2"><?php echo htmlspecialchars($crumb['name']); ?></a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <!-- Directory Content -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Directories -->
                <?php foreach ($dirs as $dir): ?>
                    <a href="?path=<?php echo urlencode($dir['path']); ?>" class="group block p-4 border border-gray-200 rounded-xl hover:border-primary-red hover:shadow-md transition-all duration-200 text-center">
                        <div class="text-yellow-500 mb-2 group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-red truncate block" title="<?php echo htmlspecialchars($dir['displayName']); ?>">
                            <?php echo htmlspecialchars($dir['displayName']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>

                <!-- Files -->
                <?php foreach ($files as $file): ?>
                    <a href="<?php echo htmlspecialchars($file['url']); ?>" target="_blank" class="group block p-4 border border-gray-200 rounded-xl hover:border-primary-red hover:shadow-md transition-all duration-200 text-center">
                        <div class="text-gray-400 mb-2 group-hover:scale-110 transition-transform duration-200">
                            <?php 
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?php echo htmlspecialchars($file['url']); ?>" alt="<?php echo htmlspecialchars($file['displayName']); ?>" class="w-12 h-12 mx-auto object-cover rounded">
                            <?php else: ?>
                                <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-red truncate block" title="<?php echo htmlspecialchars($file['displayName']); ?>">
                            <?php echo htmlspecialchars($file['displayName']); ?>
                        </span>
                        <span class="text-xs text-gray-500 block mt-1"><?php echo round($file['size'] / 1024, 1); ?> KB</span>
                    </a>
                <?php endforeach; ?>

                <?php if (empty($dirs) && empty($files)): ?>
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
                        <p>This folder is empty.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>
