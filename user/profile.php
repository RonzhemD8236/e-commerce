<?php
session_start();
include('../includes/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $zip_code = trim($_POST['zip_code']);

    // Handle profile photo upload
    $profile_photo = $_POST['current_photo']; // keep current by default
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../uploads/profile_photos/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = "user_" . $user_id . "_" . basename($_FILES["profile_photo"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $profile_photo = $filename;
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, contact_number=?, address=?, zip_code=?, profile_photo=? WHERE user_id=?");
    $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $contact, $address, $zip_code, $profile_photo, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        $_SESSION['full_name'] = $first_name . ' ' . $last_name;
        header("Location: profile.php");
        exit;
    } else {
        $error = "Error updating profile. Try again.";
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile | Lensify</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-7">
    <h2 class="text-center mb-4">My Profile</h2>

    <?php if (!empty($_SESSION['success'])) { echo "<div class='alert alert-success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']); } ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm bg-white">
        <div class="text-center mb-4">
            <?php 
            $photo = !empty($user['profile_photo']) ? "../uploads/profile_photos/".$user['profile_photo'] : "../assets/default-user.png"; 
            ?>
            <img src="<?php echo $photo; ?>" alt="Profile Photo" class="rounded-circle" width="120" height="120">
            <div class="mt-2">
                <input type="file" name="profile_photo" class="form-control">
            </div>
        </div>

        <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($user['profile_photo']); ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>

        <div class="mb-3">
            <label>Zip Code</label>
            <input type="text" name="zip_code" class="form-control" value="<?php echo htmlspecialchars($user['zip_code']); ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Profile</button>

        <div class="text-center mt-3">
            <a href="shop.php" class="btn btn-link">Back to Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </form>
</div>
</body>
</html>
