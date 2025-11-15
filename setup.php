<?php
echo "BrickMMO Uptime Monitor - Setup Script\n";
echo "=====================================\n\n";

require_once 'config.php';

try {
    $pdo_server = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    $pdo_server->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "✓ Database '" . DB_NAME . "' created/verified\n";
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql_content = file_get_contents('database.sql');
    
    $statements = explode(';', $sql_content);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database tables created/updated\n";
    echo "✓ Sample data inserted\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    $admin_exists = $stmt->fetchColumn();
    
    if (!$admin_exists) {
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $default_password, 'admin@brickmmo.com']);
        echo "✓ Default admin user created (username: admin, password: admin123)\n";
    } else {
        echo "✓ Admin user already exists\n";
    }
    
    $directories = ['screenshots', 'logs'];
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Created directory: $dir\n";
        }
    }
    
    echo "\n";
    echo "Setup completed successfully!\n";
    echo "=============================\n";
    echo "You can now:\n";
    echo "1. Access the dashboard at: " . SITE_URL . "\n";
    echo "2. Login to admin panel with username 'admin' and password 'admin123'\n";
    echo "3. Set up the cron job to run cron.php every 5 minutes\n";
    echo "4. Add your assets to monitor\n\n";
    
    echo "IMPORTANT: Change the default admin password after first login!\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in config.php\n";
} catch (Exception $e) {
    echo "✗ Setup error: " . $e->getMessage() . "\n";
}
?>
