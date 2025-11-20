<?php
session_start();
require_once '../lib/session_timeout.php';
require_once '../config/config.php';

// Guard access control
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'guard') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'QR Code Scanner - BSU Vehicle Scanner';
$root_path = '..';
require_once '../includes/header.php';
?>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        #qr-reader {
            width: 100%;
            max-width: 500px;
        }
        #qr-reader video {
            width: 100% !important;
            height: auto !important;
        }
        @media (max-width: 640px) {
            #qr-reader {
                max-width: 100%;
            }
        }
        #qr-reader__dashboard_section_csr {
            display: none;
        }
    </style>
<body class="font-sans text-gray-900 bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-3 md:py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-1.5 sm:gap-2.5 text-lg sm:text-xl md:text-2xl font-bold text-primary-red">
                    <span class="text-xl sm:text-2xl md:text-3xl">🚗</span>
                    <span class="hidden sm:inline">BSU Vehicle Scanner</span>
                    <span class="sm:hidden">BSU Scanner</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="guard.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-xs sm:text-sm md:text-base hidden sm:inline">
                        ← Back to Dashboard
                    </a>
                    <a href="guard.php" class="text-gray-700 hover:text-primary-red transition-colors duration-300 text-xs sm:hidden">
                        ← Back
                    </a>
                    <button onclick="openLogoutModal()" class="bg-primary-red text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm md:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-5 py-6 md:py-8">
        <!-- Header -->
        <div class="bg-gradient-to-br from-primary-red to-primary-red-dark rounded-2xl p-4 sm:p-6 md:p-8 text-white mb-6 md:mb-8 animate-fade-in-up shadow-xl">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">QR Code Scanner</h1>
            <p class="text-sm sm:text-base md:text-lg opacity-90">Scan QR code to verify student and vehicle registration</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 md:gap-8">
            <!-- Scanner Section -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Camera Scanner</h2>
                
                <!-- Scanner Container -->
                <div id="qr-reader" class="mb-4 md:mb-6"></div>
                
                <!-- Manual Input Option -->
                <div class="border-t-2 border-gray-200 pt-4 md:pt-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Or Enter QR Code Manually</h3>
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4">
                        <input 
                            type="text" 
                            id="manualQrInput" 
                            placeholder="Enter QR code data"
                            class="flex-1 px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-red focus:border-transparent transition-all duration-200"
                        >
                        <button 
                            id="verifyManualBtn"
                            class="bg-primary-red text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300 whitespace-nowrap"
                        >
                            Verify
                        </button>
                    </div>
                </div>

                <!-- Control Buttons -->
                <div class="mt-4 md:mt-6 flex flex-col sm:flex-row gap-3 md:gap-4">
                    <button 
                        id="startScanBtn"
                        class="flex-1 bg-primary-red text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary-red-dark transition-colors duration-300"
                    >
                        Start Scanner
                    </button>
                    <button 
                        id="stopScanBtn"
                        class="flex-1 bg-gray-200 text-gray-900 px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-gray-300 transition-colors duration-300"
                        disabled
                    >
                        Stop Scanner
                    </button>
                </div>
            </div>


            <!-- Results Section -->
            <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8 animate-fade-in-up">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 md:mb-6">Verification Result</h2>
                
                <!-- Loading State -->
                <div id="loadingState" class="hidden text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-red"></div>
                    <p class="mt-4 text-gray-600">Verifying QR code...</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="hidden bg-red-50 border-2 border-red-200 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">❌</span>
                        <h3 class="text-xl font-semibold text-red-900">Verification Failed</h3>
                    </div>
                    <p id="errorMessage" class="text-red-800"></p>
                </div>

                <!-- Success State -->
                <div id="successState" class="hidden">
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-3xl">✅</span>
                            <h3 class="text-xl font-semibold text-green-900">Verification Successful</h3>
                        </div>
                        <p class="text-green-800">Student and vehicle registration verified.</p>
                    </div>

                    <!-- Student Information -->
                    <div class="border-2 border-gray-200 rounded-lg p-4 sm:p-6 mb-4 md:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Student Information</h3>
                        <div class="space-y-2 md:space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Full Name</p>
                                <p id="studentName" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Student ID</p>
                                <p id="studentId" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p id="studentEmail" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="border-2 border-gray-200 rounded-lg p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Vehicle Information</h3>
                        <div class="space-y-2 md:space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">License Plate</p>
                                <p id="vehiclePlate" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Vehicle Make</p>
                                <p id="vehicleMake" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Vehicle Model</p>
                                <p id="vehicleModel" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Color</p>
                                <p id="vehicleColor" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Registration Date</p>
                                <p id="vehicleRegDate" class="text-lg font-semibold text-gray-900"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 md:mt-6 flex flex-col sm:flex-row gap-3 md:gap-4">
                        <button 
                            id="allowEntryBtn"
                            class="flex-1 bg-green-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-green-700 transition-colors duration-300"
                        >
                            Allow Entry
                        </button>
                        <button 
                            id="denyEntryBtn"
                            class="flex-1 bg-red-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg text-sm sm:text-base font-semibold hover:bg-red-700 transition-colors duration-300"
                        >
                            Deny Entry
                        </button>
                    </div>
                </div>

                <!-- Initial State -->
                <div id="initialState" class="text-center py-12">
                    <div class="text-6xl mb-4">📷</div>
                    <p class="text-gray-600">Scan a QR code to verify student and vehicle registration</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        let html5QrcodeScanner = null;
        let isScanning = false;

        // Initialize scanner
        function initScanner() {
            try {
                // Standard configuration
                const config = { 
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    verbose: false
                };
                html5QrcodeScanner = new Html5Qrcode("qr-reader", config);
            } catch (err) {
                console.error('Error initializing scanner:', err);
                alert('Error initializing QR scanner. Please refresh the page.');
            }
        }

        // Start scanning
        document.getElementById('startScanBtn').addEventListener('click', async function() {
            if (isScanning) return;
            
            try {
                // Check if browser supports camera
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Camera access is not supported in this browser. Please use Chrome, Firefox, or Safari on a device with a camera.');
                    return;
                }

                // Request camera permissions first
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: "environment" } 
                    });
                    // Stop the test stream
                    stream.getTracks().forEach(track => track.stop());
                } catch (permError) {
                    alert('Camera permission denied. Please allow camera access in your browser settings and try again.');
                    return;
                }

                // Start the scanner
                await html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onScanSuccess,
                    onScanError
                );
                
                isScanning = true;
                document.getElementById('startScanBtn').disabled = true;
                document.getElementById('stopScanBtn').disabled = false;
            } catch (err) {
                console.error('Camera error:', err);
                let errorMsg = 'Error starting camera: ';
                
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errorMsg += 'Camera permission denied. Please allow camera access and try again.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    errorMsg += 'No camera found. Please connect a camera and try again.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    errorMsg += 'Camera is already in use by another application.';
                } else if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
                    errorMsg += 'Camera constraints not satisfied. Trying alternative camera...';
                    // Try with user-facing camera as fallback
                    try {
                        await html5QrcodeScanner.start(
                            { facingMode: "user" },
                            {
                                fps: 20,
                                // qrbox: { width: 250, height: 250 },
                            },
                            onScanSuccess,
                            onScanError
                        );
                        isScanning = true;
                        document.getElementById('startScanBtn').disabled = true;
                        document.getElementById('stopScanBtn').disabled = false;
                        return;
                    } catch (fallbackErr) {
                        errorMsg += ' Fallback also failed.';
                    }
                } else {
                    errorMsg += err.message || err;
                }
                
                alert(errorMsg);
            }
        });

        // Stop scanning
        document.getElementById('stopScanBtn').addEventListener('click', async function() {
            if (!isScanning) return;
            
            try {
                await html5QrcodeScanner.stop();
                isScanning = false;
                document.getElementById('startScanBtn').disabled = false;
                document.getElementById('stopScanBtn').disabled = true;
            } catch (err) {
                console.error('Error stopping scanner:', err);
            }
        });

        // Handle successful scan
        function onScanSuccess(decodedText, decodedResult) {
            verifyQrCode(decodedText);
            // Stop scanning after successful scan
            if (isScanning) {
                document.getElementById('stopScanBtn').click();
            }
        }

        // Handle scan errors
        let lastErrorTime = 0;
        function onScanError(errorMessage) {
            // Log error once every 2 seconds to avoid flooding
            const now = Date.now();
            if (now - lastErrorTime > 2000) {
                console.log("Scanning..."); // Keep console alive
                lastErrorTime = now;
            }
        }

        // Manual verification
        document.getElementById('verifyManualBtn').addEventListener('click', function() {
            const qrData = document.getElementById('manualQrInput').value.trim();
            if (qrData) {
                verifyQrCode(qrData);
            } else {
                alert('Please enter QR code data');
            }
        });


        // Verify QR code
        async function verifyQrCode(qrData) {
            // Show loading state
            document.getElementById('initialState').classList.add('hidden');
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('errorState').classList.add('hidden');
            document.getElementById('successState').classList.add('hidden');

            document.getElementById('errorState').classList.add('hidden');
            document.getElementById('successState').classList.add('hidden');

            try {
                const response = await fetch('verify_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ qr_data: qrData })
                });

                const data = await response.json();

                document.getElementById('loadingState').classList.add('hidden');

                document.getElementById('loadingState').classList.add('hidden');

                if (data.success) {
                    // Show success state
                    document.getElementById('successState').classList.remove('hidden');
                    
                    // Populate student information
                    document.getElementById('studentName').textContent = data.student.full_name || 'N/A';
                    document.getElementById('studentId').textContent = data.student.student_id || 'N/A';
                    document.getElementById('studentEmail').textContent = data.student.email || 'N/A';
                    
                    // Populate vehicle information
                    document.getElementById('vehiclePlate').textContent = data.vehicle.license_plate || 'N/A';
                    document.getElementById('vehicleMake').textContent = data.vehicle.make || 'N/A';
                    document.getElementById('vehicleModel').textContent = data.vehicle.model || 'N/A';
                    document.getElementById('vehicleColor').textContent = data.vehicle.color || 'N/A';
                    
                    if (data.vehicle.registered_at) {
                        const date = new Date(data.vehicle.registered_at);
                        document.getElementById('vehicleRegDate').textContent = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        });
                    } else {
                        document.getElementById('vehicleRegDate').textContent = 'N/A';
                    }

                    // Store verification data for entry actions
                    window.verificationData = data;
                } else {
                    // Show error state
                    document.getElementById('errorState').classList.remove('hidden');
                    document.getElementById('errorMessage').textContent = data.message || 'Verification failed';
                }
            } catch (error) {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('errorState').classList.remove('hidden');
                document.getElementById('errorMessage').textContent = 'Error verifying QR code: ' + error.message;
            }
        }

        // Entry actions
        document.getElementById('allowEntryBtn').addEventListener('click', async function() {
            if (!window.verificationData) return;
            
            try {
                const response = await fetch('log_entry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: window.verificationData.vehicle.id,
                        action: 'allowed'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Entry allowed and logged successfully!');
                    resetScanner();
                } else {
                    alert('Error logging entry: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        document.getElementById('denyEntryBtn').addEventListener('click', async function() {
            if (!window.verificationData) return;
            
            try {
                const response = await fetch('log_entry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: window.verificationData.vehicle.id,
                        action: 'denied'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Entry denied and logged successfully!');
                    resetScanner();
                } else {
                    alert('Error logging entry: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // Reset scanner
        function resetScanner() {
            document.getElementById('initialState').classList.remove('hidden');
            document.getElementById('successState').classList.add('hidden');
            document.getElementById('errorState').classList.add('hidden');
            document.getElementById('manualQrInput').value = '';
            window.verificationData = null;
        }

        // Check browser compatibility on page load
        window.addEventListener('load', function() {
            // Check if running on HTTPS or localhost
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            
            if (!isSecureContext && location.protocol !== 'file:') {
                const warningDiv = document.createElement('div');
                warningDiv.className = 'bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 mb-6';
                warningDiv.innerHTML = '<p class="text-yellow-800 text-sm font-medium">⚠️ Camera access requires HTTPS or localhost. If you\'re having issues, try accessing via <strong>http://localhost</strong> instead of an IP address.</p>';
                document.querySelector('.bg-white.rounded-2xl.shadow-lg.p-8').insertBefore(warningDiv, document.getElementById('qr-reader'));
            }
            
            initScanner();
        });
    </script>

    <?php require_once '../includes/footer.php'; ?>
    <?php require_once '../includes/modal_logout.php'; ?>
</body>
</html>

