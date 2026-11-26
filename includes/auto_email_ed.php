<?php
mysqli_query($conn, 'ALTER TABLE setting_profile ADD COLUMN IF NOT EXISTS last_email_sent DATETIME NULL');

$profile = mysqli_fetch_assoc(mysqli_query(
    $conn,
    'SELECT last_email_sent FROM setting_profile WHERE id=1'
)) ?: [];

$last_email_sent = trim($profile['last_email_sent'] ?? '');
$should_send = false;

if ($last_email_sent === '') {
    $should_send = true;
} else {
    $last_timestamp = strtotime($last_email_sent);
    $today = date('Y-m-d');
    $last_date = date('Y-m-d', $last_timestamp);

    $is_monday = date('N') === '1';
    $monday_not_sent_today = $is_monday && $last_date !== $today;
    $seven_days_elapsed = (time() - $last_timestamp) >= (7 * 24 * 60 * 60);

    $should_send = $monday_not_sent_today || $seven_days_elapsed;
}

if ($should_send) {
    $email_delivery_silent = true;
    require_once __DIR__ . '/../proses_kirim_email.php';
}
