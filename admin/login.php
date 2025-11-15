<?php
require_once '../config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BrickMMO Uptime</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
</head>
<body class="w3-light-grey">

<div class="w3-display-middle" style="width:300px">
    <div class="w3-card w3-white w3-round w3-padding-large w3-center">
        <i class="fas fa-lock w3-jumbo w3-text-blue"></i>
        <h2>Admin Login</h2>
        
        <?php if ($error): ?>
        <div class="w3-panel w3-red w3-round w3-margin-bottom">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="w3-container">
            <div class="w3-section">
                <label><b>Username</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="username" required>
            </div>
            
            <div class="w3-section">
                <label><b>Password</b></label>
                <input class="w3-input w3-border w3-round" type="password" name="password" required>
            </div>
            
            <button class="w3-button w3-block w3-blue w3-section w3-padding" type="submit">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="w3-margin-top">
            <a href="../index.php" class="w3-text-blue">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
    </div>
</div>

</body>
</html>
