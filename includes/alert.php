<?php

if (isset($_SESSION['message'])) {
    // Get message type or default to 'danger' for backward compatibility
    $messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'danger';
    
    // Map message types to Bootstrap alert classes
    $alertClass = 'alert-' . $messageType; // success, warning, danger, info, primary, etc.
    
    echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
    <strong>{$_SESSION['message']}</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    <strong>{$_SESSION['success']}</strong>
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['success']);
}
?>