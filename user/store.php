<?php
session_start();
include("../includes/config.php");

 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

 
unset($_SESSION['errors']);
unset($_SESSION['old']);

 
$username = trim($_POST['username'] ?? '');
$email = trim(strtolower($_POST['email'] ?? '')); 
$password = trim($_POST['password'] ?? '');
$confirmPass = trim($_POST['confirmPass'] ?? '');

$errors = [];
$old = [
    'username' => $username,
    'email' => $email
];

 

 
if (empty($username)) {
    $errors['username'] = 'Username is required.';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters.';
} elseif (strlen($username) > 50) {
    $errors['username'] = 'Username must not exceed 50 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
}

 
if (empty($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format.';
} else {
    
    $allowedDomains = ['@gmail.com', '@yahoo.com', '@outlook.com'];
    $validDomain = false;
    foreach ($allowedDomains as $domain) {
        
        $domainLength = strlen($domain);
        if (substr($email, -$domainLength) === $domain) {
            $validDomain = true;
            break;
        }
    }
    if (!$validDomain) {
        $errors['email'] = 'Email must be one of: gmail.com, yahoo.com, outlook.com';
    }
}

 
if (empty($password)) {
    $errors['password'] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long.';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter.';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter.';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one number.';
} elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character.';
}

 
if (empty($confirmPass)) {
    $errors['confirmPass'] = 'Please confirm your password.';
} elseif ($password !== $confirmPass) {
    $errors['confirmPass'] = 'Passwords do not match.';
}

 
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

 

 
$conn->begin_transaction();

try {
    
    $sql_check_username = "SELECT id FROM users WHERE username = ? LIMIT 1";
    $stmt_check_user = $conn->prepare($sql_check_username);
    
    if (!$stmt_check_user) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    $stmt_check_user->store_result();
    
    if ($stmt_check_user->num_rows > 0) {
        $stmt_check_user->close();
        $_SESSION['errors'] = ['username' => 'Username is already in use.'];
        $_SESSION['old'] = $old;
        $conn->rollback();
        header("Location: register.php");
        exit();
    }
    $stmt_check_user->close();
    
    
    $sql_check_email = "SELECT id FROM users WHERE email = ? LIMIT 1";
    $stmt_check_email = $conn->prepare($sql_check_email);
    
    if (!$stmt_check_email) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    
    if ($stmt_check_email->num_rows > 0) {
        $stmt_check_email->close();
        $_SESSION['errors'] = ['email' => 'Email is already in use.'];
        $_SESSION['old'] = $old;
        $conn->rollback();
        header("Location: register.php");
        exit();
    }
    $stmt_check_email->close();
    
    
    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
    
    
    $role = 'customer'; 
    $active = 1; 
    
    $sql_insert_user = "INSERT INTO users (username, email, password, role, active, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt_insert = $conn->prepare($sql_insert_user);
    
    if (!$stmt_insert) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_insert->bind_param("ssssi", $username, $email, $passwordHashed, $role, $active);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Execute failed: " . $stmt_insert->error);
    }
    
    
    $user_id = $conn->insert_id;
    $stmt_insert->close();
    
    
    $default_country = 'Philippines';
    $default_state = 'Metro Manila';
    $empty_string = '';
    
    $sql_insert_customer = "INSERT INTO customer 
                            (user_id, email, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, image_path) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_customer = $conn->prepare($sql_insert_customer);
    
    if (!$stmt_customer) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    
    $stmt_customer->bind_param("isssssssssss", 
        $user_id,           
        $email,             
        $empty_string,      
        $empty_string,      
        $empty_string,      
        $empty_string,      
        $default_country,   
        $default_state,     
        $empty_string,      
        $empty_string,      
        $empty_string,      
        $empty_string       
    );
    
    if (!$stmt_customer->execute()) {
        throw new Exception("Execute failed: " . $stmt_customer->error);
    }
    
    $stmt_customer->close();
    
    
    $conn->commit();
    
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;
    
    
    header("Location: /lensify/e-commerce/user/profile.php");
    exit();
    
} catch (Exception $e) {
    
    $conn->rollback();
    
    
    error_log("Registration error: " . $e->getMessage());
    
    $_SESSION['errors'] = ['general' => 'Registration failed. Please try again later.'];
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

 
$conn->close();
?>