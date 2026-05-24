-- Check the users table structure
DESCRIBE users;

-- Check if there are any existing records with the test email/mobile
SELECT * FROM users WHERE email = 'sakshispatil4106@gmail.com' OR mobile LIKE '%9579388941%';

-- Check the otp_temp table to see if OTPs were verified
SELECT * FROM otp_temp WHERE email = 'sakshispatil4106@gmail.com' ORDER BY id DESC LIMIT 5;
