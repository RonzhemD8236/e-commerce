<?php
session_start();
include('../includes/config.php');

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Fetch the user's role and current status
    $stmt = $conn->prepare("SELECT role, active FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];
        $active = $row['active'];

        // Prevent deactivating yourself
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['message'] = "You cannot deactivate your own account.";
            header("Location: index.php");
            exit();
        }

        // If user is admin and is currently active, check if they are the last active admin
        if ($role === 'admin' && $active) {
            $stmtAdmin = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE role='admin' AND active=1");
            $stmtAdmin->execute();
            $resAdmin = $stmtAdmin->get_result();
            $countRow = $resAdmin->fetch_assoc();
            $activeAdmins = $countRow['count'];

            if ($activeAdmins <= 1) {
                $_SESSION['message'] = "Cannot deactivate the last remaining active admin.";
                header("Location: index.php");
                exit();
            }
        }

        // Toggle active status
        $newStatus = $active ? 0 : 1;
        $stmtUpdate = $conn->prepare("UPDATE users SET active=? WHERE id=?");
        $stmtUpdate->bind_param("ii", $newStatus, $userId);
        $stmtUpdate->execute();
    }
}

header("Location: index.php");
exit();
