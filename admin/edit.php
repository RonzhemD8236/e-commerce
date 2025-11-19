<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../includes/config.php');
include('../includes/header.php');

$id = $_GET['id'];

// Fetch user info
$sqlUser = "SELECT * FROM users WHERE id=?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

// If user is a customer, fetch name from customer profile
$profileName = '';
if ($user['role'] === 'customer') {
    $sqlProfile = "SELECT fname, lname FROM customer WHERE user_id=?";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("i", $id);
    $stmtProfile->execute();
    $resultProfile = $stmtProfile->get_result();
    if ($resultProfile->num_rows > 0) {
        $profile = $resultProfile->fetch_assoc();
        $profileName = trim($profile['fname'] . ' ' . $profile['lname']);
    }
}

// Use profile name if available, otherwise fallback to username
$nameValue = !empty($profileName) ? $profileName : $user['username'];
?>

<style>
    .edit-user-container {
        max-width: 700px;
        margin: 10px auto;
        padding: 0 20px;
    }

    .edit-user-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .card-header-custom h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .card-header-custom p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .card-body-custom {
        padding: 40px;
    }

    .form-group-custom {
        margin-bottom: 25px;
    }

    .form-label-custom {
        display: block;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    .form-control-custom {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: #f7fafc;
    }

    .form-control-custom:focus {
        outline: none;
        border-color: #667eea;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-select-custom {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 15px;
        background-color: #f7fafc;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .form-select-custom:focus {
        outline: none;
        border-color: #667eea;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .password-hint {
        font-size: 12px;
        color: #718096;
        margin-top: 5px;
        font-style: italic;
    }

    .button-group {
        display: flex;
        gap: 12px;
        margin-top: 35px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-custom {
        flex: 1;
        padding: 14px 24px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        display: inline-block;
    }

    .btn-primary-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary-custom {
        background-color: #e2e8f0;
        color: #4a5568;
    }

    .btn-secondary-custom:hover {
        background-color: #cbd5e0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .user-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
    }

    .badge-admin {
        background-color: #fef5e7;
        color: #d68910;
    }

    .badge-customer {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    @media (max-width: 768px) {
        .card-body-custom {
            padding: 25px;
        }

        .button-group {
            flex-direction: column;
        }

        .btn-custom {
            width: 100%;
        }
    }
</style>

<div class="edit-user-container">
    <div class="edit-user-card">
        <div class="card-header-custom">
            <h2>Edit User Account</h2>
            <p>Update user information and permissions</p>
        </div>
        
        <div class="card-body-custom">
            <form action="update.php" method="POST">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                
                <div class="form-group-custom">
                    <label class="form-label-custom">Full Name / Username</label>
                    <input type="text" name="name" class="form-control-custom" 
                           value="<?= htmlspecialchars($nameValue) ?>" required 
                           placeholder="Enter full name">
                </div>
                
                <div class="form-group-custom">
                    <label class="form-label-custom">Email Address</label>
                    <input type="email" name="email" class="form-control-custom" 
                           value="<?= htmlspecialchars($user['email']) ?>" required 
                           placeholder="user@example.com">
                </div>
                
                <div class="form-group-custom">
                    <label class="form-label-custom">Password</label>
                    <input type="password" name="password" class="form-control-custom" 
                           placeholder="Enter new password">
                    <div class="password-hint">Leave blank to keep current password</div>
                </div>
                
                <div class="form-group-custom">
                    <label class="form-label-custom">User Role</label>
                    <select name="role" class="form-select-custom">
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>
                            👑 Administrator
                        </option>
                        <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>
                            👤 Customer
                        </option>
                    </select>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-custom btn-primary-custom" name="submit">
                        💾 Update User
                    </button>
                    <a href="index.php" class="btn-custom btn-secondary-custom">
                        ↩️ Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>