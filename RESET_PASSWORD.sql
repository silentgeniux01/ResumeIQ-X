-- Reset password for sakshispatil4196@gmail.com
-- New password will be: sakshi@123

-- First, generate the hash using PHP:
-- password_hash('sakshi@123', PASSWORD_BCRYPT, ['cost' => 10])
-- Result: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'sakshispatil4196@gmail.com';

-- Verify the update
SELECT id, email, role, account_status, email_verified, mobile_verified 
FROM users 
WHERE email = 'sakshispatil4196@gmail.com';
