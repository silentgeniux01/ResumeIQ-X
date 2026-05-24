#!/bin/bash
# Execute SQL on Railway MySQL using Railway shell

echo "Executing SQL on Railway MySQL..."
mysql -u root railway < setup_database_railway.sql
echo "Done!"
