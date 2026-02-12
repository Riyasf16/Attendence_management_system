<?php
// Test password verification
$stored_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Test various passwords
$passwords_to_test = ['teacher123', 'student123', 'password', '123456', 'admin'];

echo "Testing password hash verification:\n";
echo "Hash: $stored_hash\n\n";

foreach ($passwords_to_test as $password) {
    $result = password_verify($password, $stored_hash);
    echo "Password '$password': " . ($result ? "✓ MATCH" : "✗ NO MATCH") . "\n";
}

// Show what this hash was created from
echo "\n\nTo find out what password this hash represents, we can generate some test hashes:\n\n";

foreach ($passwords_to_test as $password) {
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Hash for '$password': $new_hash\n";
}
?>
