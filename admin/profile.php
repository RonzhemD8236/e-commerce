<?php
session_start();
include('../includes/config.php');
include('header.php'); // Make sure this is correct path

// ✅ Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

$adminId = $_SESSION['user_id'];
$errors = [];

// Fetch admin info
$sql = "SELECT username, email, profile_img FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['submit'])) {
    $usernameForm = trim($_POST['username']);
    $profileImg = $profile['profile_img'] ?? '';

    // Validate username
    if (empty($usernameForm)) {
        $errors['username'] = "Username cannot be empty.";
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] <= 5 * 1024 * 1024) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // Delete old image if exists and not default
                if (!empty($profileImg) && file_exists("../".$profileImg) && $profileImg !== "uploads/default-profile.png") {
                    unlink("../".$profileImg);
                }
                $profileImg = "uploads/" . $fileName;
            } else {
                $errors['image'] = "Error uploading image.";
            }
        } else {
            $errors['image'] = "Invalid file type or file too large (>5MB).";
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        $sqlUpdate = "UPDATE users SET username=?, profile_img=? WHERE id=?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssi", $usernameForm, $profileImg, $adminId);
        $stmtUpdate->execute();

        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-account-profile { object-fit: cover; }
</style>
</head>
<body>
<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Profile Picture -->
            <div class="col-xl-4">
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img id="profilePreview" class="img-account-profile rounded-circle mb-2"
                             src="<?php echo !empty($profile['profile_img']) ? '../'.htmlspecialchars($profile['profile_img']) : '../uploads/default-profile.png'; ?>"
                             alt="Profile Image" width="200" height="200">
                        <?php if(isset($errors['image'])): ?>
                            <small class="text-danger d-block"><?php echo $errors['image']; ?></small>
                        <?php endif; ?>
                        <div class="small text-muted mb-4">JPG, PNG, GIF no larger than 5 MB</div>
                        <input type="file" id="imageInput" name="image" style="display:none;">
                        <button type="button" class="btn btn-primary" id="uploadButton">Upload new image</button>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Username</label>
                                <input class="form-control" type="text" name="username"
                                       value="<?php echo htmlspecialchars($profile['username']); ?>">
                                <?php if(isset($errors['username'])): ?>
                                    <small class="text-danger"><?php echo $errors['username']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Email</label>
                                <input class="form-control" type="email" name="email"
                                       value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit" name="submit">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});
document.getElementById('imageInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(ev){
            document.getElementById('profilePreview').src = ev.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
