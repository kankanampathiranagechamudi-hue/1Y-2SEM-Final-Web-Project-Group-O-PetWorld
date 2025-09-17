<?php
session_start();
require_once 'config/database.php';
require_once 'utils/functions.php';


if (is_logged_in()) {
    
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('home.php');
    }
}

$error = '';
$success = '';
$full_name = '';
$email = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        
        $database = new Database();
        $db = $database->getConnection();
        
        
        $query = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists';
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            
            $query = "INSERT INTO users (full_name, email, password) VALUES (:full_name, :email, :password)";
            $stmt = $db->prepare($query);
            
            
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            
            
            if ($stmt->execute()) {
                $success = 'Account created successfully. You can now login.';
                
                $full_name = '';
                $email = '';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Pet World</title>
    <link href="css/bootstrap-4.4.1.css" rel="stylesheet">
    <style>
    body {
        background: #0da315;
        background: radial-gradient(circle, rgba(13, 163, 21, 1) 0%, rgba(1, 1, 56, 1) 100%);
        color: white;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border-radius: 1rem;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
    }

    h1 {
        text-align: center;
    }
    </style>
</head>

<body>

    <div class="container">
        <h1><b>Pet World</b></h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3 class="text-center text-primary">Sign Up</h3>
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group mt-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter full name"
                                value="<?php echo $full_name; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email"
                                value="<?php echo $email; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="Confirm password" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block mt-3">Create Account</button>
                        <p class="text-center mt-3">Already have an account?
                            <a href="login.php">
                                <center>Login</center>
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>