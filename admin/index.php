<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}
include('../admin/header.php');
include('../includes/config.php');

// Make sure the current user ID is set
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Determine if a role filter is applied
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Get search query if exists
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base SQL query with prepared statement
$sql = "
    SELECT 
        u.id, u.username, u.email, u.role, u.active, u.created_at,
        c.fname, c.lname
    FROM users u
    LEFT JOIN customer c ON u.id = c.user_id
    WHERE 1=1
";

// Prepare parameters array
$params = [];
$types = "";

// Add role filter if applied (with validation)
if ($roleFilter === 'customer') {
    $sql .= " AND u.role = ?";
    $params[] = 'customer';
    $types .= "s";
} elseif ($roleFilter === 'admin') {
    $sql .= " AND u.role = ?";
    $params[] = 'admin';
    $types .= "s";
}

// Add search filter if exists
if (!empty($searchQuery)) {
    $searchPattern = "%$searchQuery%";
    $sql .= " AND (
        u.username LIKE ? OR
        u.email LIKE ? OR
        c.fname LIKE ? OR
        c.lname LIKE ?
    )";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $types .= "ssss";
}

$sql .= " ORDER BY u.id ASC";

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

if (!empty($params)) {
    // Dynamically bind parameters
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

if (!mysqli_stmt_execute($stmt)) {
    die("Execute failed: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
?>

<style>
/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.85) 100%),
                url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=400&fit=crop') center/cover;
    color: white;
    padding: 60px 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.hero-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.hero-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 800px;
}

/* Search and Action Bar */
.search-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 20px;
}

.search-box {
    flex: 1;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Role Filter Tabs */
.role-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 25px;
}

.role-tab {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    text-decoration: none;
    transition: all 0.2s;
}

.role-tab:hover {
    color: #667eea;
    background-color: #f9fafb;
}

.role-tab.active {
    color: #667eea;
}

.role-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #667eea;
}

/* Table Styling */
.users-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.users-table table {
    width: 100%;
    border-collapse: collapse;
}

.users-table thead {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.users-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.users-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.users-table tbody tr:hover {
    background-color: #f9fafb;
}

.users-table tbody tr.inactive {
    background-color: #fef2f2;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-active {
    background-color: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background-color: #fee2e2;
    color: #991b1b;
}

/* Role Badges */
.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.role-admin {
    background-color: #dbeafe;
    color: #1e40af;
}

.role-customer {
    background-color: #e0e7ff;
    color: #4338ca;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-edit {
    padding: 6px 16px;
    background-color: #fbbf24;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-edit:hover {
    background-color: #f59e0b;
    color: white;
}

.btn-deactivate {
    padding: 6px 16px;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-deactivate:hover {
    background-color: #dc2626;
    color: white;
}

.btn-activate {
    padding: 6px 16px;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-activate:hover {
    background-color: #059669;
    color: white;
}

.btn-disabled {
    padding: 6px 16px;
    background-color: #d1d5db;
    color: #9ca3af;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: not-allowed;
}

.btn-add-user {
    padding: 10px 20px;
    background-color: #000;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-add-user:hover {
    background-color: #1f2937;
    color: white;
}

/* Page Layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
}

.content-wrapper {
    flex: 1;
    padding: 0;
    margin: 0;
    width: 100%;
    max-width: 100%;
}

.content-inner {
    padding: 20px 40px 0 40px;
}

.users-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 0;
}

</style>
<body>
    
<div class="content-wrapper">
    <div class="content-inner">
        <!-- Hero Banner -->
        <div class="hero-banner">
            <h1>User Management</h1>
            <p>Manage and oversee user roles, activate/deactivate users, and maintain user data securely. Control access and permissions for all system users.</p>
        </div>

        <!-- Search and Add User Bar -->
        <div class="search-action-bar">
            <div class="search-box">
                <form method="GET" action="index.php">
                    <?php if ($roleFilter): ?>
                        <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
                    <?php endif; ?>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name, username, or email..." 
                        value="<?= htmlspecialchars($searchQuery) ?>"
                    >
                </form>
            </div>
            <a href="create.php" class="btn-add-user">Add New User</a>
        </div>

        <!-- Role Filter Tabs -->
        <div class="role-tabs">
            <a href="index.php<?= $searchQuery ? '?search=' . urlencode($searchQuery) : '' ?>" 
               class="role-tab <?= $roleFilter == '' ? 'active' : '' ?>">
                All Users
            </a>
            <a href="index.php?role=customer<?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>" 
               class="role-tab <?= $roleFilter == 'customer' ? 'active' : '' ?>">
                Customers
            </a>
            <a href="index.php?role=admin<?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>" 
               class="role-tab <?= $roleFilter == 'admin' ? 'active' : '' ?>">
                Admins
            </a>
        </div>

        <!-- Users Table -->
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            // Determine display name
                            if ($row['role'] === 'customer') {
                                $displayName = (!empty($row['fname']) && !empty($row['lname'])) 
                                    ? trim($row['fname'] . ' ' . $row['lname']) 
                                    : $row['username'];
                            } else {
                                $displayName = $row['username'];
                            }

                            $email = !empty($row['email']) ? $row['email'] : '-';
                            $createdAt = !empty($row['created_at']) ? date('M d, Y g:i A', strtotime($row['created_at'])) : '-';
                            $rowClass = !$row['active'] ? 'inactive' : '';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= (int)$row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($displayName) ?></strong></td>
                                <td><?= htmlspecialchars($email) ?></td>
                                <td>
                                    <span class="role-badge role-<?= htmlspecialchars($row['role']) ?>">
                                        <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $row['active'] ? 'active' : 'inactive' ?>">
                                        <?= $row['active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($createdAt) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($row['active']): ?>
                                            <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn-edit">Edit</a>
                                        <?php else: ?>
                                            <button class="btn-disabled" disabled>Edit</button>
                                        <?php endif; ?>

                                        <?php if ($row['active']): ?>
                                            <?php if ($row['id'] != $currentUserId): ?>
                                                <a href="toggle_status.php?id=<?= (int)$row['id'] ?>" 
                                                   class="btn-deactivate" 
                                                   onclick="return confirm('Are you sure you want to deactivate this user?');">
                                                    Deactivate
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-disabled" disabled title="You cannot deactivate yourself">Deactivate</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="toggle_status.php?id=<?= (int)$row['id'] ?>" 
                                               class="btn-activate" 
                                               onclick="return confirm('Are you sure you want to activate this user?');">
                                                Activate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                <?php if (!empty($searchQuery)): ?>
                                    No users found matching "<?= htmlspecialchars($searchQuery) ?>".
                                <?php else: ?>
                                    No users found.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
mysqli_stmt_close($stmt);
include('../includes/footer.php'); 
?>