<?php
/**
 * Password Migration Script
 * This script will hash all existing plain text passwords in the database
 * Run this ONCE to migrate existing users to the new hashed password system
 */

include 'components/connect.php';

echo "<h2>Password Migration Script</h2>";
echo "<p>This will hash all plain text passwords in your database.</p>";
echo "<hr>";

// Migrate Users Table
echo "<h3>Migrating Users Table...</h3>";
try {
    $select_users = $conn->prepare("SELECT id, password FROM `users`");
    $select_users->execute();
    $users = $select_users->fetchAll(PDO::FETCH_ASSOC);
    
    $users_updated = 0;
    $users_skipped = 0;
    
    foreach($users as $user) {
        // Check if password is already hashed (bcrypt hashes start with $2y$ and are 60 chars)
        if(strlen($user['password']) == 60 && strpos($user['password'], '$2y$') === 0) {
            $users_skipped++;
            echo "User ID {$user['id']}: Already hashed, skipping...<br>";
            continue;
        }
        
        // Hash the plain text password
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Update the database
        $update_user = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
        $update_user->execute([$hashed_password, $user['id']]);
        
        $users_updated++;
        echo "User ID {$user['id']}: Password hashed successfully ✓<br>";
    }
    
    echo "<p><strong>Users Summary:</strong> {$users_updated} updated, {$users_skipped} skipped (already hashed)</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error migrating users: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Migrate Admins Table
echo "<h3>Migrating Admins Table...</h3>";
try {
    $select_admins = $conn->prepare("SELECT id, password FROM `admins`");
    $select_admins->execute();
    $admins = $select_admins->fetchAll(PDO::FETCH_ASSOC);
    
    $admins_updated = 0;
    $admins_skipped = 0;
    
    foreach($admins as $admin) {
        // Check if password is already hashed
        if(strlen($admin['password']) == 60 && strpos($admin['password'], '$2y$') === 0) {
            $admins_skipped++;
            echo "Admin ID {$admin['id']}: Already hashed, skipping...<br>";
            continue;
        }
        
        // Hash the plain text password
        $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);
        
        // Update the database
        $update_admin = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
        $update_admin->execute([$hashed_password, $admin['id']]);
        
        $admins_updated++;
        echo "Admin ID {$admin['id']}: Password hashed successfully ✓<br>";
    }
    
    echo "<p><strong>Admins Summary:</strong> {$admins_updated} updated, {$admins_skipped} skipped (already hashed)</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error migrating admins: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3 style='color: green;'>✓ Migration Complete!</h3>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (migrate_passwords.php) after running it for security reasons!</p>";
echo "<p>You can now login with your existing credentials.</p>";

?>

