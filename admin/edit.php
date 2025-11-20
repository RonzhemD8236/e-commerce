<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../includes/config.php');
include('../admin/header.php');

$id = $_GET['id'];

 
$sqlUser = "SELECT * FROM users WHERE id=?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

 
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

 
$nameValue = !empty($profileName) ? $profileName : $user['username'];
?>
<style>
 
.edit-user-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 40px 20px;
    background-color: #f4f6f8;  
}

 
.edit-user-container {
    max-width: 1300px;  
    width: 100%;
    margin: 0 auto;
}

 
.edit-user-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

 
.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 10px 10px 0 0;
    color: white;
    margin-bottom: 0;
}

.card-header-custom h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}

.card-header-custom p {
    margin-top: 5px;
    font-size: 14px;
    opacity: 0.9;
}

 
.card-body-custom {
    padding: 25px 25px;
}

 
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 20px;
}

.form-group-custom {
    margin-bottom: 0;  
}

.form-label-custom {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 5px;
    font-size: 14px;
}

.form-control-custom,
.form-select-custom {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 15px;
    background-color: #f9fafb;
    transition: all 0.2s ease;
}

.form-control-custom:focus,
.form-select-custom:focus {
    outline: none;
    border-color: #7f00ff;
    background-color: #ffffff;
    box-shadow: 0 0 0 2px rgba(127, 0, 255, 0.15);
}

.password-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    font-style: italic;
}

 
.button-group {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-custom {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(127, 0, 255, 0.25);
}

.btn-secondary-custom {
    background-color: #e5e7eb;
    color: #374151;
}

.btn-secondary-custom:hover {
    background-color: #d1d5db;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;  
        gap: 20px 0;
    }

    .button-group {
        flex-direction: column;
    }

    .btn-custom {
        width: 100%;
    }
}
</style>

<div class="edit-user-wrapper">
    <div class="edit-user-container">
        <div class="edit-user-card">
            <div class="card-header-custom">
                <h2>Edit User Account</h2>
                <p>Update user information and permissions</p>
            </div>
            <div class="card-body-custom">
                <form action="update.php" method="POST">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">

                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label class="form-label-custom">Username</label>
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
                            <label class="form-label-custom">User Role</label>
                            <select name="role" class="form-select-custom">
                                <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>
                                    Administrator
                                </option>
                                <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>
                                    Customer
                                </option>
                            </select>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">Password</label>
                            <input type="password" name="password" class="form-control-custom" 
                                   placeholder="Enter new password">
                            <div class="password-hint">Leave blank to keep current password</div>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-custom btn-primary-custom" name="submit">
                            Update User
                        </button>
                        <a href="index.php" class="btn-custom btn-secondary-custom">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>