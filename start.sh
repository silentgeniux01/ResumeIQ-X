#!/bin/bash
set -e

# Set default PORT if not provided
export PORT=${PORT:-8080}

echo "=========================================="
echo "Starting ResumeIQ-X on PORT: $PORT"
echo "=========================================="

# Start Node.js API server in background
echo "Starting Node.js API server..."
cd node_api
node server.js &
NODE_PID=$!
echo "Node.js API started with PID: $NODE_PID"

# Wait a moment for Node.js to start
sleep 2

# Start PHP built-in server from project root with router
echo "Starting PHP server on 0.0.0.0:$PORT..."
cd ..
php -S 0.0.0.0:$PORT router.php

# If PHP server exits, kill Node.js too
kill $NODE_PID 2>/dev/null || true
