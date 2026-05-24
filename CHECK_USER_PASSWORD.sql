-- Check the recently registered user's details
SELECT 
    id, 
    name, 
    email, 
    mobile,
    role,
    account_status,
    email_verified,
    mobile_verified,
    company_name,
    LEFT(password, 20) as password_hash_preview,
    LENGTH(password) as password_length,
    created_at
FROM users 
WHERE email = 'sakshispatil4106@gmail.com'
ORDER BY id DESC 
LIMIT 1;
