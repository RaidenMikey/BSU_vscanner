<?php
session_start();

// Simulate a logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'student';

// Set last activity to 6 hours ago (timeout is 5 hours)
$_SESSION['last_activity'] = time() - (6 * 3600);

echo "Current Time: " . time() . "<br>";
echo "Last Activity: " . $_SESSION['last_activity'] . "<br>";
echo "Difference: " . (time() - $_SESSION['last_activity']) . " seconds<br>";
echo "Timeout Duration: 18000 seconds<br>";

// Include the timeout logic
require_once 'lib/session_timeout.php';

// If we reach here, timeout didn't happen (which is wrong for this test)
echo "Test Failed: Session did not time out.";
?>
