# Wānaka FC Website - Replit Setup

## Overview
This is the official website for Wānaka FC football club, featuring information about their academy, junior grassroots programs, seniors teams, and tournaments. The site includes a contact form system for inquiries.

## Project Structure
- **Frontend**: HTML/CSS/JavaScript static website
- **Backend**: PHP with contact form functionality
- **Dependencies**: PHPMailer for email handling
- **Deployment**: Configured for Replit Autoscale

## Recent Changes (September 14, 2025)
- ✅ Installed PHP 8.2 and Composer dependencies
- ✅ Fixed PHP syntax errors in send-email.php
- ✅ Configured workflow to serve on port 5000 with 0.0.0.0 host
- ✅ Set up deployment configuration for production
- ✅ Verified website functionality and contact form endpoint

## Email Configuration Required
The contact form requires SMTP2GO credentials to function. Add these to your Replit Secrets:

**Required Environment Variables:**
- `SMTP2GO_USERNAME` - Your SMTP2GO username
- `SMTP2GO_PASSWORD` - Your SMTP2GO password  
- `SMTP2GO_FROM_EMAIL` - The "from" email address for outgoing emails

**Optional Environment Variables (have defaults):**
- `SMTP2GO_HOST` - SMTP server host (default: mail.smtp2go.com)
- `SMTP2GO_PORT` - SMTP server port (default: 587)

Without these credentials, the contact form will return a configuration error message.

## Project Architecture
- **Frontend Server**: PHP built-in server serving static files and handling contact form submissions
- **Email System**: SMTP2GO integration with PHPMailer for reliable email delivery
- **Deployment**: Autoscale configuration for production hosting

## Development
- Run locally: `php -S 0.0.0.0:5000`
- Dependencies: `composer install`
- Logs: Contact form submissions are logged to `contact_submissions.log`

## User Preferences
- Clean, professional website showcasing football club programs
- Contact form functionality for member inquiries
- Responsive design for all devices