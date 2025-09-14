#!/bin/bash

# Replit Setup Script for Wanaka FC Website
# This script ensures all dependencies are installed and configured

echo "Setting up Wanaka FC Website..."

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not available. This script should run on Replit."
    exit 1
fi

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not available. This script should run on Replit."
    exit 1
fi

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Check if vendor directory was created
if [ ! -d "vendor" ]; then
    echo "Error: Failed to install PHP dependencies"
    exit 1
fi

# Check if PHPMailer is installed
if [ ! -f "vendor/phpmailer/phpmailer/src/PHPMailer.php" ]; then
    echo "Error: PHPMailer not found after installation"
    exit 1
fi

echo "✅ PHP dependencies installed successfully"

# Check for required environment variables
echo "Checking environment variables..."

required_vars=("SMTP2GO_HOST" "SMTP2GO_PORT" "SMTP2GO_USERNAME" "SMTP2GO_PASSWORD" "SMTP2GO_FROM_EMAIL")
missing_vars=()

for var in "${required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        missing_vars+=("$var")
    fi
done

if [ ${#missing_vars[@]} -ne 0 ]; then
    echo "⚠️  Warning: Missing environment variables:"
    printf '%s\n' "${missing_vars[@]}"
    echo "Please configure these in Replit Secrets for email functionality to work."
else
    echo "✅ All required environment variables are configured"
fi

# Create logs directory if it doesn't exist
mkdir -p logs
chmod 755 logs

echo "✅ Setup completed successfully!"
echo ""
echo "To start the server, run: php -S 0.0.0.0:8080"
echo "Or use the Replit run button."
