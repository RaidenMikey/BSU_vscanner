<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../auth/admin_login.php');
    exit();
}

$baseDir = realpath('../uploads/vehicles');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $path = $_POST['path'] ?? '';
    
    // Security check
    $realBase = realpath($baseDir);
    $targetPath = $baseDir . DIRECTORY_SEPARATOR . $path;
    $realTargetPath = realpath($targetPath);
    
    if ($realTargetPath === false || strpos($realTargetPath, $realBase) !== 0) {
        $_SESSION['file_error'] = "Invalid path.";
        header('Location: manage_files.php');
        exit();
    }

    if ($action === 'delete') {
        if (is_dir($realTargetPath)) {
            if (deleteDirectory($realTargetPath)) {
                $_SESSION['file_success'] = "Folder deleted successfully.";
            } else {
                $_SESSION['file_error'] = "Failed to delete folder.";
            }
        } else {
            if (unlink($realTargetPath)) {
                $_SESSION['file_success'] = "File deleted successfully.";
            } else {
                $_SESSION['file_error'] = "Failed to delete file.";
            }
        }
        header('Location: manage_files.php?path=' . urlencode(dirname($path) === '.' ? '' : dirname($path)));
        exit();
    } elseif ($action === 'download') {
        if (is_dir($realTargetPath)) {
            $zipName = basename($realTargetPath) . '.zip';
            $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . $zipName;
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($realTargetPath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($realTargetPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                
                if (file_exists($zipPath)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $zipName . '"');
                    header('Content-Length: ' . filesize($zipPath));
                    readfile($zipPath);
                    unlink($zipPath); // Delete temp file
                    exit();
                } else {
                    $_SESSION['file_error'] = "Failed to create zip file.";
                }
            } else {
                $_SESSION['file_error'] = "Failed to open zip archive.";
            }
        } else {
             // Direct file download
             header('Content-Description: File Transfer');
             header('Content-Type: application/octet-stream');
             header('Content-Disposition: attachment; filename="'.basename($realTargetPath).'"');
             header('Expires: 0');
             header('Cache-Control: must-revalidate');
             header('Pragma: public');
             header('Content-Length: ' . filesize($realTargetPath));
             readfile($realTargetPath);
             exit();
        }
        header('Location: manage_files.php?path=' . urlencode($path));
        exit();
    }
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
