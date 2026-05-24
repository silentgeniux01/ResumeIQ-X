-- Verify which database you're connected to
SELECT DATABASE();

-- Check if company_name column exists in users table
SHOW COLUMNS FROM users LIKE 'company_name';

-- Alternative way to check
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'company_name';

-- Show all columns in users table
SHOW COLUMNS FROM users;
