<?php
if (!isset($vehicle) || !is_array($vehicle)) {
    return;
}

$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800'
];

$statusLabel = ucfirst($vehicle['status']);
$statusClass = $statusClasses[$vehicle['status']] ?? 'bg-gray-100 text-gray-800';
$submittedAt = new DateTime($vehicle['registered_at']);
$documentLinks = [
    'Driver’s License' => $vehicle['driver_license_image'],
    'Official Receipt (OR)' => $vehicle['or_image'],
    'Certificate of Registration (CR)' => $vehicle['cr_image'],
];
?>
<div class="border-2 border-gray-200 rounded-xl p-4 sm:p-6">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">
                <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Plate: <span class="font-medium text-gray-800"><?php echo htmlspecialchars(strtoupper($vehicle['license_plate'])); ?></span> •
                Type: <?php echo htmlspecialchars(ucfirst($vehicle['vehicle_type'])); ?> •
                Color: <?php echo htmlspecialchars($vehicle['color']); ?>
            </p>
            <p class="text-sm text-gray-600">
                Student: <span class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['full_name']); ?></span>
                (<?php echo htmlspecialchars($vehicle['student_number']); ?>) • <?php echo htmlspecialchars($vehicle['email']); ?>
            </p>
        </div>
        <span class="status-pill <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($statusLabel); ?>
        </span>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 text-sm text-gray-600">
        <div>
            <p><span class="font-semibold text-gray-800">Driver’s License No:</span> <?php echo htmlspecialchars($vehicle['driver_license_no']); ?></p>
            <p><span class="font-semibold text-gray-800">Submitted:</span> <?php echo $submittedAt->format('F j, Y g:i A'); ?></p>
            <?php if (!empty($vehicle['qr_code_data'])): ?>
                <p><span class="font-semibold text-gray-800">QR Payload:</span> <span class="font-mono break-all"><?php echo htmlspecialchars($vehicle['qr_code_data']); ?></span></p>
            <?php endif; ?>
        </div>
        <div class="flex flex-wrap gap-3 items-center">
            <?php foreach ($documentLinks as $label => $path): ?>
                <?php if (!empty($path)): ?>
                    <a href="../<?php echo htmlspecialchars($path); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                        View <?php echo htmlspecialchars($label); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!empty($vehicle['qr_code_path'])): ?>
                <a href="../<?php echo htmlspecialchars($vehicle['qr_code_path']); ?>" target="_blank" class="text-primary-red text-xs font-medium hover:underline">
                    View QR Code
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-3">
        <?php if ($vehicle['status'] === 'pending'): ?>
            <button 
                type="button" 
                onclick="openConfirmModal(<?php echo (int) $vehicle['id']; ?>, 'approved', '<?php echo htmlspecialchars(addslashes($vehicle['make'] . ' ' . $vehicle['model'])); ?>', '<?php echo htmlspecialchars(addslashes($vehicle['full_name'])); ?>')" 
                class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors duration-300"
            >
                Approve
            </button>
            <button 
                type="button" 
                onclick="openConfirmModal(<?php echo (int) $vehicle['id']; ?>, 'rejected', '<?php echo htmlspecialchars(addslashes($vehicle['make'] . ' ' . $vehicle['model'])); ?>', '<?php echo htmlspecialchars(addslashes($vehicle['full_name'])); ?>')" 
                class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors duration-300"
            >
                Reject
            </button>
        <?php else: ?>
            <button 
                type="button" 
                onclick="openConfirmModal(<?php echo (int) $vehicle['id']; ?>, 'pending', '<?php echo htmlspecialchars(addslashes($vehicle['make'] . ' ' . $vehicle['model'])); ?>', '<?php echo htmlspecialchars(addslashes($vehicle['full_name'])); ?>')" 
                class="bg-gray-200 text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors duration-300"
            >
                Mark as Pending
            </button>
        <?php endif; ?>
    </div>
</div>

