# Wānaka FC Website

## Overview
This is the official website for Wānaka FC, a football club based in New Zealand. The project is a PHP-based website featuring information about the club's programs, tournaments, sponsors, and contact forms.

## Recent Changes (September 14, 2025)
- Successfully imported GitHub repository to Replit environment
- Installed PHP 8.2 and PHPMailer dependencies via Composer
- Fixed PHP syntax error in send-email.php (moved use statements to top of file)
- Configured PHP server to run on port 5000 for Replit compatibility
- Set up workflow for automatic server management
- Added .gitignore file for PHP project best practices
- Fixed JavaScript null pointer error in fixtures update function
- Configured deployment settings for autoscale deployment

## Project Architecture
### Frontend
- **Languages**: HTML5, CSS3, JavaScript
- **Framework**: Vanilla JavaScript with custom animations
- **Structure**: Multi-page website with index.html as homepage
- **Key Pages**: 
  - index.html (homepage)
  - the-academy.html
  - junior-grassroots.html
  - recruitment.html
  - wanaka-tournament.html
  - about.html
  - register.html

### Backend
- **Language**: PHP 8.2
- **Dependencies**: PHPMailer for email functionality
- **Email Handler**: send-email.php with SMTP2GO integration
- **Server**: PHP built-in development server

### Key Features
- Responsive design with mobile navigation
- Contact form with email functionality
- Sponsor carousel
- Fixture/results display
- Program information sections
- Beautiful animations and transitions

## User Preferences
- Project should maintain existing design and functionality
- Email functionality requires SMTP2GO environment variables for production use
- All changes should preserve the club's branding and visual identity

## Environment Setup
- PHP 8.2 with Composer package manager
- PHPMailer 6.10.0 for email functionality
- Served on port 5000 via PHP built-in server
- Configured for Replit deployment with autoscale target

## Email Configuration
The contact form requires these environment variables for email functionality:
- SMTP2GO_HOST
- SMTP2GO_PORT  
- SMTP2GO_USERNAME
- SMTP2GO_PASSWORD
- SMTP2GO_FROM_EMAIL

## Deployment
Configured for Replit autoscale deployment running `composer start` command.