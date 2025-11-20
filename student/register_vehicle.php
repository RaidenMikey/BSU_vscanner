<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';
require_once '../lib/qrcode_generator.php';


// Ensure only students can access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$successMessage = $_SESSION['vehicle_success'] ?? '';
unset($_SESSION['vehicle_success']);

/**
 * Handle image upload and return relative path
 *
 * @param string $fieldName
 * @param string $targetDir
 * @param string $relativeDir
 * @param string $prefix
 * @param array  $errors
 * @return string|null
 */
function handleImageUpload(string $fieldName, string $targetDir, string $relativeDir, string $prefix, array &$errors): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please upload the {$prefix} image.";
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error for {$prefix} image. Please try again.";
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $errors[] = "{$prefix} image must be less than 5MB.";
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $finfo = null;

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/jpg' => 'jpg'
    ];

    if (!isset($allowedTypes[$mimeType])) {
        $errors[] = "{$prefix} image must be a JPG or PNG file.";
        return null;
    }

    $extension = $allowedTypes[$mimeType];

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        $errors[] = "Unable to create upload directory. Please contact support.";
        return null;
    }

    try {
        $uniqueToken = bin2hex(random_bytes(4));
    } catch (Exception $e) {
        $uniqueToken = time();
    }

    $filename = strtolower($prefix) . '_' . time() . '_' . $uniqueToken . '.' . $extension;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = "Failed to save {$prefix} image. Please try again.";
        return null;
    }

    return rtrim($relativeDir, '/') . '/' . $filename;
}

$conn = getDBConnection();

// Fetch user details
$userStmt = $conn->prepare("SELECT id, full_name, email, student_id FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user) {
    $conn->close();
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleType = trim($_POST['vehicle_type'] ?? '');
    $licensePlate = strtoupper(trim($_POST['plate_number'] ?? ''));
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $driverLicenseNo = trim($_POST['driver_license_no'] ?? '');

    $allowedVehicleTypes = ['car', 'motorcycle', 'van', 'truck', 'others'];

    if (!$vehicleType || !in_array($vehicleType, $allowedVehicleTypes, true)) {
        $errors[] = 'Please select a valid vehicle type.';
    }

    if (!$licensePlate) {
        $errors[] = 'Plate number is required.';
    }

    if (!$brand) {
        $errors[] = 'Vehicle brand is required.';
    }

    if (!$model) {
        $errors[] = 'Vehicle model is required.';
    }

    if (!$color) {
        $errors[] = 'Vehicle color is required.';
    }

    if (!$driverLicenseNo) {
        $errors[] = 'Driver’s license number is required.';
    }

    if (empty($errors)) {
        $baseUploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'vehicles' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR;
        $baseUploadRelative = 'uploads/vehicles/' . $user_id . '/';

        $driverLicensePath = handleImageUpload('driver_license_image', $baseUploadDir, $baseUploadRelative, 'driver_license', $errors);
        $orPath = handleImageUpload('or_image', $baseUploadDir, $baseUploadRelative, 'official_receipt', $errors);
        $crPath = handleImageUpload('cr_image', $baseUploadDir, $baseUploadRelative, 'certificate_registration', $errors);


    }

    if (empty($errors)) {
        // Check for duplicate license plate before attempting insert
        $checkStmt = $conn->prepare("SELECT id FROM vehicles WHERE license_plate = ? LIMIT 1");
        $checkStmt->bind_param("s", $licensePlate);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errors[] = 'A vehicle with license plate "' . htmlspecialchars($licensePlate) . '" is already registered in the system. Please use a different plate number or contact support if this is an error.';
            $checkStmt->close();
        } else {
            $checkStmt->close();
            
            // Proceed with insert
            try {
                $insertStmt = $conn->prepare("
                    INSERT INTO vehicles (
                        user_id,
                        vehicle_type,
                        license_plate,
                        make,
                        model,
                        color,
                        driver_license_no,
                        driver_license_image,
                        or_image,
                        cr_image,
                        status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");

                $insertStmt->bind_param(
                    "isssssssss",
                    $user_id,
                    $vehicleType,
                    $licensePlate,
                    $brand,
                    $model,
                    $color,
                    $driverLicenseNo,
                    $driverLicensePath,
                    $orPath,
                    $crPath
                );

                if ($insertStmt->execute()) {
                    $vehicleId = $insertStmt->insert_id;
                    $insertStmt->close();

                    $vehicleTag = sprintf('VEH-%s-%04d', date('Y'), $vehicleId);
                    $studentTag = 'STU-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $user['student_id'] ?? ''));
                    $qrPayload = $vehicleTag . '|' . $studentTag;

                    $qrImageName = 'qr_vehicle_' . $vehicleId . '_' . time() . '.png';
                    $qrAbsoluteDir = $baseUploadDir;
                    $qrRelativePath = $baseUploadRelative . $qrImageName;

                    if (!is_dir($qrAbsoluteDir) && !mkdir($qrAbsoluteDir, 0755, true)) {
                        $errors[] = 'Vehicle registered but failed to prepare QR directory. Please contact support.';
                    } else {
                        $qrAbsolutePath = $qrAbsoluteDir . $qrImageName;
                        
                        // Generate QR code (automatically tries multiple methods)
                        $qrGenerated = generateQRCode($qrPayload, $qrAbsolutePath, 300);
                        
                        if (!$qrGenerated) {
                            // Log detailed error for debugging
                            $errorDetails = [];
                            
                            // Check GD library
                            $gdAvailable = function_exists('imagecreatetruecolor');
                            if (!$gdAvailable) {
                                $errorDetails[] = 'GD library not available (using API fallback)';
                            }
                            
                            // Check directory
                            if (!is_dir($qrAbsoluteDir)) {
                                $errorDetails[] = 'Directory does not exist: ' . $qrAbsoluteDir;
                            } elseif (!is_writable($qrAbsoluteDir)) {
                                $errorDetails[] = 'Directory not writable: ' . $qrAbsoluteDir;
                            }
                            
                            // Check file
                            if (file_exists($qrAbsolutePath)) {
                                if (filesize($qrAbsolutePath) == 0) {
                                    $errorDetails[] = 'QR file exists but is empty';
                                }
                            } else {
                                $errorDetails[] = 'QR file was not created: ' . $qrAbsolutePath;
                            }
                            
                            // Get last PHP error
                            $lastError = error_get_last();
                            if ($lastError) {
                                $errorDetails[] = 'PHP Error: ' . $lastError['message'] . ' in ' . $lastError['file'] . ':' . $lastError['line'];
                            }
                            
                            // Log to error log
                            error_log('QR Code Generation Failed for vehicle ID: ' . $vehicleId);
                            error_log('QR Payload: ' . $qrPayload);
                            error_log('QR Path: ' . $qrAbsolutePath);
                            foreach ($errorDetails as $detail) {
                                error_log('  - ' . $detail);
                            }
                            
                            $errorMsg = 'Vehicle registered but QR code generation failed.';
                            if (!empty($errorDetails)) {
                                $errorMsg .= ' Details: ' . implode('; ', array_slice($errorDetails, 0, 2));
                            }
                            $errors[] = $errorMsg;
                            
                            // Still update database with QR data even if image generation fails
                            // The QR data can be regenerated later if needed
                            $updateStmt = $conn->prepare("UPDATE vehicles SET qr_code_data = ? WHERE id = ?");
                            $updateStmt->bind_param("si", $qrPayload, $vehicleId);
                            $updateStmt->execute();
                            $updateStmt->close();
                        } else {
                            // Verify file was created
                            if (file_exists($qrAbsolutePath) && filesize($qrAbsolutePath) > 0) {
                                $updateStmt = $conn->prepare("UPDATE vehicles SET qr_code_path = ?, qr_code_data = ? WHERE id = ?");
                                $updateStmt->bind_param("ssi", $qrRelativePath, $qrPayload, $vehicleId);
                                $updateStmt->execute();
                                $updateStmt->close();
                            } else {
                                $errors[] = 'Vehicle registered but QR code file was not created properly.';
                                // Still save QR data
                                $updateStmt = $conn->prepare("UPDATE vehicles SET qr_code_data = ? WHERE id = ?");
                                $updateStmt->bind_param("si", $qrPayload, $vehicleId);
                                $updateStmt->execute();
                                $updateStmt->close();
                            }
                        }
                    }

                    if (empty($errors)) {
                        $_SESSION['vehicle_success'] = 'Vehicle registration submitted successfully! Your request is pending verification.';
                        header('Location: register_vehicle.php');
                        exit();
                    }
                } else {
                    // Handle database errors
                    if ($conn->errno === 1062) {
                        $errors[] = 'A vehicle with license plate "' . htmlspecialchars($licensePlate) . '" is already registered in the system. Please use a different plate number.';
                    } else {
                        $errors[] = 'Unable to register vehicle. Please try again or contact support if the problem persists.';
                        error_log('Vehicle registration error: ' . $conn->error . ' (Error code: ' . $conn->errno . ')');
                    }
                    $insertStmt->close();
                }
            } catch (mysqli_sql_exception $e) {
                // Catch any SQL exceptions
                if ($e->getCode() === 1062 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errors[] = 'A vehicle with license plate "' . htmlspecialchars($licensePlate) . '" is already registered in the system. Please use a different plate number.';
                } else {
                    $errors[] = 'An error occurred while registering your vehicle. Please try again or contact support if the problem persists.';
                    error_log('Vehicle registration exception: ' . $e->getMessage());
                }
            } catch (Exception $e) {
                // Catch any other exceptions
                $errors[] = 'An unexpected error occurred. Please try again or contact support if the problem persists.';
                error_log('Vehicle registration error: ' . $e->getMessage());
            }
        }
    }
}

$vehicles = [];
$vehiclesStmt = $conn->prepare("
    SELECT 
        id,
        vehicle_type,
        license_plate,
        make,
        model,
        color,
        driver_license_no,
        driver_license_image,
        or_image,
        cr_image,
        qr_code_path,
        qr_code_data,
        status,
        registered_at
    FROM vehicles
    WHERE user_id = ?
    ORDER BY registered_at DESC
");
$vehiclesStmt->bind_param("i", $user_id);
$vehiclesStmt->execute();
$vehiclesResult = $vehiclesStmt->get_result();
while ($row = $vehiclesResult->fetch_assoc()) {
    $vehicles[] = $row;
}
$vehiclesStmt->close();
$conn->close();

$page_title = 'Register Vehicle - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <?php require_once '../includes/nav_student.php'; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-5 py-6 md:py-8">
        <div class="mb-6">
            <a href="student.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white mb-6 md:mb-8 animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">Register Your Vehicle</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Submit your vehicle information for campus access verification.</p>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4 mb-6 animate-fade-in-up">
                <p class="text-green-800 text-sm font-medium">
                    <?php echo htmlspecialchars($successMessage); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 mb-6 animate-fade-in-up">
                <h2 class="text-red-900 font-semibold mb-2">Please fix the following:</h2>
                <ul class="list-disc list-inside text-red-800 text-sm space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8 animate-fade-in-up">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid sm:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type <span class="text-primary-red">*</span></label>
                        <select id="vehicle_type" name="vehicle_type" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                            <option value="">Select vehicle type</option>
                            <?php
                                $types = [
                                    'car' => 'Car',
                                    'motorcycle' => 'Motorcycle',
                                    'van' => 'Van',
                                    'truck' => 'Truck',
                                    'others' => 'Others'
                                ];
                                foreach ($types as $value => $label) {
                                    $selected = (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === $value) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($value) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="plate_number" class="block text-sm font-medium text-gray-700 mb-2">Plate Number <span class="text-primary-red">*</span></label>
                        <input type="text" id="plate_number" name="plate_number" placeholder="ABC-1234" value="<?php echo isset($_POST['plate_number']) ? htmlspecialchars($_POST['plate_number']) : ''; ?>" required class="w-full uppercase px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand <span class="text-primary-red">*</span></label>
                        <input type="text" id="brand" name="brand" placeholder="Toyota" value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model <span class="text-primary-red">*</span></label>
                        <input type="text" id="model" name="model" placeholder="Vios" value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                    </div>
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Color <span class="text-primary-red">*</span></label>
                        <input type="text" id="color" name="color" placeholder="Red" value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                    </div>
                    <div>
                        <label for="driver_license_no" class="block text-sm font-medium text-gray-700 mb-2">Driver’s License No. <span class="text-primary-red">*</span></label>
                        <input type="text" id="driver_license_no" name="driver_license_no" placeholder="DLN-1234567" value="<?php echo isset($_POST['driver_license_no']) ? htmlspecialchars($_POST['driver_license_no']) : ''; ?>" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200">
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4 md:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Driver’s License Image <span class="text-primary-red">*</span></label>
                        <input type="file" name="driver_license_image" accept="image/png,image/jpeg" required class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 text-sm text-gray-600 hover:border-primary-red transition-colors duration-200">
                        <p class="mt-1 text-xs text-gray-500">Upload a clear photo of your driver’s license.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Official Receipt (OR) Image <span class="text-primary-red">*</span></label>
                        <input type="file" name="or_image" accept="image/png,image/jpeg" required class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 text-sm text-gray-600 hover:border-primary-red transition-colors duration-200">
                        <p class="mt-1 text-xs text-gray-500">Upload the latest official receipt.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate of Registration (CR) Image <span class="text-primary-red">*</span></label>
                        <input type="file" name="cr_image" accept="image/png,image/jpeg" required class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 text-sm text-gray-600 hover:border-primary-red transition-colors duration-200">
                        <p class="mt-1 text-xs text-gray-500">Upload the vehicle’s certificate of registration.</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary-red text-white px-6 sm:px-8 py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        Submit Registration
                    </button>
                </div>
            </form>
        </div>

        <div id="status" class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Your Vehicle Registrations</h2>
            <?php if (empty($vehicles)): ?>
                <p class="text-gray-600 text-sm">No registrations yet. Submit your first vehicle above.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="border-2 border-gray-200 rounded-xl p-4 sm:p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                        <span class="text-sm text-gray-500">(
                                            <?php echo htmlspecialchars(strtoupper($vehicle['license_plate'])); ?>
                                        )</span>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Type: <?php echo htmlspecialchars(ucfirst($vehicle['vehicle_type'])); ?> •
                                        Color: <?php echo htmlspecialchars($vehicle['color']); ?>
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    <?php
                                        switch ($vehicle['status']) {
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-yellow-100 text-yellow-800';
                                        }
                                    ?>
                                ">
                                    <?php echo htmlspecialchars(ucfirst($vehicle['status'])); ?>
                                </span>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <p><span class="font-semibold text-gray-800">Driver’s License No:</span> <?php echo htmlspecialchars($vehicle['driver_license_no']); ?></p>
                                    <p><span class="font-semibold text-gray-800">Submitted:</span>
                                        <?php
                                            $submittedAt = new DateTime($vehicle['registered_at']);
                                            echo $submittedAt->format('F j, Y g:i A');
                                        ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="../<?php echo htmlspecialchars($vehicle['driver_license_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                        View License
                                    </a>
                                    <a href="../<?php echo htmlspecialchars($vehicle['or_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                        View OR
                                    </a>
                                    <a href="../<?php echo htmlspecialchars($vehicle['cr_image']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                                        View CR
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>

