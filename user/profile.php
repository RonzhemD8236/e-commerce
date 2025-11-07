<?php
// Include database connection
include('../includes/config.php');
include('../includes/header.php'); // for navbar

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect if the logged-in user is not a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch existing user profile
$sql = "SELECT u.user_id, u.email, c.fname, c.lname, c.phone, c.address 
        FROM users u 
        LEFT JOIN customer c ON u.user_id = c.user_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['submit'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $address = trim($_POST['address']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $imagePath = $profile['profile_photo'] ?? '';

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
                $imagePath = "uploads/" . $fileName;
            } else {
                $_SESSION['error'] = "Error uploading image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or file too large.";
        }
    }

    $isNewProfile = empty($profile['fname']) && empty($profile['lname']);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Profile saved successfully!';
    if ($isNewProfile) {
        header("Location:  ../index.php");
    } else {
        header("Location: profile.php");
    }
    exit;
}


    // Update customer table instead of users
    $sql = "UPDATE customer 
            SET fname=?, lname=?, address=?, phone=? 
            WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fname, $lname, $address, $phone, $userId);

    if ($stmt->execute()) {
    $_SESSION['success'] = 'Profile saved successfully!';
    header("Location:  ../index.php"); // Redirect to homepage after setup
    exit;
} else {
    $_SESSION['error'] = 'Error saving profile.';
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>

    <nav class="nav nav-borders">
        <a class="nav-link active ms-0">Profile</a>
    </nav>
    <hr class="mt-0 mb-4">

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Profile Picture -->
            <div class="col-xl-4">
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img id="profilePreview" class="img-account-profile rounded-circle mb-2"
                             src="<?php echo !empty($profile['profile_photo']) ? '../' . htmlspecialchars($profile['profile_photo']) : 'http://bootdey.com/img/Content/avatar/avatar1.png'; ?>"
                             alt="Profile Image" width="200" height="200">
                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>

                        <input type="file" id="imageInput" name="image" accept=".jpg,.jpeg,.png,.gif" style="display: none;">
                        <button class="btn btn-primary" type="button" id="uploadButton">Upload new image</button>
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
                                <label class="small mb-1">First name</label>
                                <input class="form-control" type="text" name="fname" value="<?php echo htmlspecialchars($profile['fname'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Last name</label>
                                <input class="form-control" type="text" name="lname" value="<?php echo htmlspecialchars($profile['lname'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Address</label>
                                <input class="form-control" type="text" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Zip code</label>
                                <input class="form-control" type="text" name="zipcode" value="<?php echo htmlspecialchars($zipcode ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control" type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Open file picker when button is clicked
document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

// Show preview immediately after selecting a file
document.getElementById('imageInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
