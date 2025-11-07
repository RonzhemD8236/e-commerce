<?php
session_start();

// Include database connection and header
include('../includes/header.php'); // for navbar
include('../includes/config.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect if the logged-in user is not a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$userId = $_SESSION['user_id'];

// Track whether this is a new profile
$isNewProfile = false;

// Fetch existing profile from customer table
$sql = "SELECT * FROM customer WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// If profile doesn't exist, insert a blank one
if (!$profile) {
    $sqlInsert = "INSERT INTO customer (user_id, fname, lname, title, addressline, town, zipcode, phone, image_path)
                  VALUES (?, '', '', '', '', '', '', '', '')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("i", $userId);
    $stmtInsert->execute();

    $isNewProfile = true; // Mark as new user

    // Fetch the newly inserted row
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $title = trim($_POST['title']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $imagePath = $profile['image_path'] ?? '';

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

    // Update existing customer
    $sql = "UPDATE customer 
            SET title=?, lname=?, fname=?, addressline=?, town=?, zipcode=?, phone=?, image_path=?
            WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $title, $lname, $fname, $address, $town, $zipcode, $phone, $imagePath, $userId);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile saved successfully!';
        if ($isNewProfile) {
            // Redirect new users to index.php
            header("Location: index.php");
        } else {
            // Stay on the same page for existing users
            header("Location: profile.php");
        }
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
                             src="<?php echo !empty($profile['image_path']) ? '../' . htmlspecialchars($profile['image_path']) : 'http://bootdey.com/img/Content/avatar/avatar1.png'; ?>"
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
                                <input class="form-control" type="text" name="address" value="<?php echo htmlspecialchars($profile['addressline'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Town</label>
                                <input class="form-control" type="text" name="town" value="<?php echo htmlspecialchars($profile['town'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Zip code</label>
                                <input class="form-control" type="text" name="zipcode" value="<?php echo htmlspecialchars($profile['zipcode'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Title</label>
                                <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>">
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
