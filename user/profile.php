<?php

ob_start();

session_start();
include('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /lensify/e-commerce/admin/profile.php");
    } else {
        header("Location: /lensify/e-commerce/user/login.php");
    }
    exit();
}

$userId = $_SESSION['user_id'];

$sqlUser = "SELECT username, email FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userRow = $resultUser->fetch_assoc();
$username = $userRow['username'] ?? '';
$email = $userRow['email'] ?? '';
$stmtUser->close();

$sql = "SELECT * FROM customer WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    $sqlInsert = "INSERT INTO customer 
                  (user_id, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, email, image_path)
                  VALUES (?, '', '', '', '', 'Philippines', 'Metro Manila', '', '', '', ?, '')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("is", $userId, $email);
    $stmtInsert->execute();
    $stmtInsert->close();
    

    $stmtRefetch = $conn->prepare($sql);
    $stmtRefetch->bind_param("i", $userId);
    $stmtRefetch->execute();
    $resultRefetch = $stmtRefetch->get_result();
    $profile = $resultRefetch->fetch_assoc();
    $stmtRefetch->close();
}

if (!$profile) {
    $profile = [
        'fname' => '',
        'lname' => '',
        'addressline' => '',
        'town' => '',
        'country' => 'Philippines',
        'state' => 'Metro Manila',
        'zipcode' => '',
        'phone' => '',
        'date_of_birth' => '',
        'email' => $email,
        'image_path' => ''
    ];
}


$profile['username'] = $username;
$profile['email'] = $email;

$firstTime = false;
if ($profile && empty($profile['fname']) && empty($profile['lname']) && empty($profile['addressline'])) {
    $firstTime = true;
}

 
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors']);
unset($_SESSION['form_data']);

if (isset($_POST['submit'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $usernameForm = trim($_POST['username']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $country = trim($_POST['country']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['date_of_birth']);
    $imagePath = $profile['image_path'] ?? '';

    $errors = [];

    
    $formData = [
        'fname' => $fname,
        'lname' => $lname,
        'username' => $usernameForm,
        'address' => $address,
        'town' => $town,
        'country' => $country,
        'state' => $state,
        'zipcode' => $zipcode,
        'phone' => $phone,
        'date_of_birth' => $dob
    ];

    
    foreach (['fname','lname','username','address','town','country','state','zipcode','phone','date_of_birth'] as $field) {
        if (empty($_POST[$field])) {
            $fieldName = str_replace('_', ' ', $field);
            $errors[$field] = ucfirst($fieldName) . " is required.";
        }
    }

    
    if (!empty($dob)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $dob);
        $errorsInDate = DateTime::getLastErrors();

        if (!$dateObj || $errorsInDate['warning_count'] > 0 || $errorsInDate['error_count'] > 0) {
            $errors['date_of_birth'] = "Please enter a valid date in YYYY-MM-DD format.";
        } elseif ($dob > date('Y-m-d')) {
            $errors['date_of_birth'] = "Date of Birth cannot be a future date.";
        }
    }

    
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg','jpeg','png','gif'];

        if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] <= 5*1024*1024) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = "uploads/" . $fileName;
            } else {
                $errors['image'] = "Error uploading image.";
            }
        } else {
            $errors['image'] = "Invalid file type or too large (>5MB).";
        }
    }

    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    
    $sqlUpdate = "UPDATE customer SET fname=?, lname=?, addressline=?, town=?, country=?, state=?, zipcode=?, phone=?, date_of_birth=?, image_path=?, email=? WHERE user_id=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssssssssssi",$fname,$lname,$address,$town,$country,$state,$zipcode,$phone,$dob,$imagePath,$email,$userId);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    $sqlUserUpdate = "UPDATE users SET username=? WHERE id=?";
    $stmtUserUpdate = $conn->prepare($sqlUserUpdate);
    $stmtUserUpdate->bind_param("si",$usernameForm,$userId);
    $stmtUserUpdate->execute();
    $stmtUserUpdate->close();

    $_SESSION['success'] = "Profile saved successfully!";

    if ($firstTime) {
        header("Location: /lensify/e-commerce/index.php");
    } else {
        header("Location: /lensify/e-commerce/user/profile.php");
    }
    exit();
}

include('../includes/header.php');
?>

<style>
<?php if($firstTime): ?>
nav.navbar { display: none !important; }
body { padding-top: 20px !important; }
<?php endif; ?>

body {
    background-color: #1a1a1a !important;
    color: #ffffff !important;
}

.container-xl {
    max-width: 1200px;
}

.card {
    background-color: #2d2d2d;
    border: 1px solid #404040;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.card-header {
    background-color: #353535;
    color: #ffffff;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #404040;
    border-radius: 12px 12px 0 0;
}

.card-body {
    padding: 1.5rem;
}

.img-account-profile {
    object-fit: cover;
    border: 4px solid #404040;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.form-control {
    background-color: #3a3a3a;
    border: 1px solid #505050;
    color: #ffffff;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    background-color: #404040;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
    color: #ffffff;
}

.form-control::placeholder {
    color: #999999;
}

.form-control:read-only {
    background-color: #2a2a2a;
    cursor: not-allowed;
    opacity: 0.7;
}

.small {
    color: #b0b0b0;
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
}

.btn-primary {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(139, 92, 246, 0.4);
}

.btn-primary:active {
    transform: translateY(0);
}

.text-danger {
    color: #ef4444 !important;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.font-italic {
    font-style: italic;
}

.text-muted {
    color: #999999 !important;
}

.text-center {
    text-align: center;
}

.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.mt-4 { margin-top: 1.5rem; }
.gx-3 { --bs-gutter-x: 1rem; }

input[type="file"] {
    display: none;
}

.welcome-banner {
    background: linear-gradient(135deg, #1f1f1f 0%, #2d2d2d 100%);
    border: 2px solid #8b5cf6;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.welcome-banner h4 {
    color: #ffffff;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.welcome-banner p {
    color: #b0b0b0;
    font-size: 1rem;
    margin: 0;
}
</style>

<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>

    <?php if($firstTime): ?>
    <div class="text-center welcome-banner">
        <h4>Welcome to Lensify!</h4>
        <p>Set up your profile to get started.</p>
    </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-xl-4">
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img id="profilePreview" class="img-account-profile rounded-circle mb-2"
                             src="<?php echo !empty($profile['image_path']) ? '../'.htmlspecialchars($profile['image_path']) : '../uploads/default-profile.png'; ?>"
                             alt="Profile Image" width="200" height="200">
                        <?php if(isset($errors['image'])): ?><small class="text-danger"><?php echo $errors['image']; ?></small><?php endif; ?>
                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                        <input type="file" id="imageInput" name="image">
                        <button type="button" class="btn btn-primary" id="uploadButton">Upload new image</button>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">First name</label>
                                <input class="form-control" type="text" name="fname" 
                                       value="<?php echo htmlspecialchars(!empty($formData['fname']) ? $formData['fname'] : ($profile['fname'] ?? '')); ?>" 
                                       placeholder="Enter first name">
                                <?php if(isset($errors['fname'])): ?><small class="text-danger"><?php echo $errors['fname']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Last name</label>
                                <input class="form-control" type="text" name="lname" 
                                       value="<?php echo htmlspecialchars(!empty($formData['lname']) ? $formData['lname'] : ($profile['lname'] ?? '')); ?>" 
                                       placeholder="Enter last name">
                                <?php if(isset($errors['lname'])): ?><small class="text-danger"><?php echo $errors['lname']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Username</label>
                                <input class="form-control" type="text" name="username" 
                                       value="<?php echo htmlspecialchars(!empty($formData['username']) ? $formData['username'] : ($profile['username'] ?? '')); ?>">
                                <?php if(isset($errors['username'])): ?><small class="text-danger"><?php echo $errors['username']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Email</label>
                                <input class="form-control" type="text" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Address</label>
                                <input class="form-control" type="text" name="address" 
                                       value="<?php echo htmlspecialchars(!empty($formData['address']) ? $formData['address'] : ($profile['addressline'] ?? '')); ?>" 
                                       placeholder="Enter street address">
                                <?php if(isset($errors['address'])): ?><small class="text-danger"><?php echo $errors['address']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Town</label>
                                <input class="form-control" type="text" name="town" 
                                       value="<?php echo htmlspecialchars(!empty($formData['town']) ? $formData['town'] : ($profile['town'] ?? '')); ?>" 
                                       placeholder="Enter town or city">
                                <?php if(isset($errors['town'])): ?><small class="text-danger"><?php echo $errors['town']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Country</label>
                                <input class="form-control" type="text" name="country" 
                                       value="<?php echo htmlspecialchars(!empty($formData['country']) ? $formData['country'] : ($profile['country'] ?? 'Philippines')); ?>">
                                <?php if(isset($errors['country'])): ?><small class="text-danger"><?php echo $errors['country']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">State</label>
                                <input class="form-control" type="text" name="state" 
                                       value="<?php echo htmlspecialchars(!empty($formData['state']) ? $formData['state'] : ($profile['state'] ?? 'Metro Manila')); ?>">
                                <?php if(isset($errors['state'])): ?><small class="text-danger"><?php echo $errors['state']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control" type="text" name="phone" 
                                       value="<?php echo htmlspecialchars(!empty($formData['phone']) ? $formData['phone'] : ($profile['phone'] ?? '')); ?>" 
                                       placeholder="Enter phone number">
                                <?php if(isset($errors['phone'])): ?><small class="text-danger"><?php echo $errors['phone']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Zip code</label>
                                <input class="form-control" type="text" name="zipcode" 
                                       value="<?php echo htmlspecialchars(!empty($formData['zipcode']) ? $formData['zipcode'] : ($profile['zipcode'] ?? '')); ?>" 
                                       placeholder="Enter zip code">
                                <?php if(isset($errors['zipcode'])): ?><small class="text-danger"><?php echo $errors['zipcode']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Date of Birth</label>
                                <input class="form-control" type="text" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars(!empty($formData['date_of_birth']) ? $formData['date_of_birth'] : ($profile['date_of_birth'] ?? '')); ?>" 
                                       placeholder="YYYY-MM-DD">
                                <?php if(isset($errors['date_of_birth'])): ?><small class="text-danger"><?php echo $errors['date_of_birth']; ?></small><?php endif; ?>
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
document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});
document.getElementById('imageInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(ev){ document.getElementById('profilePreview').src = ev.target.result; }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include('../includes/footer.php'); ?>