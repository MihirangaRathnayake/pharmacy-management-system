# Email Configuration Guide

## Overview

The Pharmacy Management System now supports both **PHP mail()** (simple) and **PHPMailer SMTP** (production-ready) for sending emails.

## Quick Start (Development)

By default, the system uses PHP's `mail()` function. This works for local development but may not work on all hosting environments.

If email sending fails, the verification code will be displayed on screen in development mode.

## Production Setup (SMTP with PHPMailer)

For reliable email delivery in production, configure SMTP:

### Step 1: Create .env file

Copy the example environment file:

```bash
cp .env.example .env
```

### Step 2: Configure SMTP Settings

Edit `.env` and add your SMTP credentials:

```env
SMTP_ENABLED=true
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@pharmacare.com
MAIL_FROM_NAME=New Gampaha Pharmacy
```

### Step 3: Get SMTP Credentials

#### For Gmail:

1. Enable 2-Factor Authentication on your Google Account
2. Go to https://myaccount.google.com/apppasswords
3. Generate an App Password for "Mail"
4. Use this app password (not your regular password) in `SMTP_PASSWORD`

#### For Other Providers:

**Office 365 / Outlook:**

```env
SMTP_HOST=smtp.office365.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

**SendGrid:**

```env
SMTP_HOST=smtp.sendgrid.net
SMTP_PORT=587
SMTP_USERNAME=apikey
SMTP_PASSWORD=your-sendgrid-api-key
```

**Mailgun:**

```env
SMTP_HOST=smtp.mailgun.org
SMTP_PORT=587
SMTP_USERNAME=postmaster@your-domain.mailgun.org
SMTP_PASSWORD=your-mailgun-password
```

**Amazon SES:**

```env
SMTP_HOST=email-smtp.us-east-1.amazonaws.com
SMTP_PORT=587
SMTP_USERNAME=your-ses-access-key
SMTP_PASSWORD=your-ses-secret-key
```

## Testing Email Configuration

You can test if email sending works by using the password reset feature:

1. Go to the login page
2. Click "Forgot Password"
3. Enter an email address
4. Check if you receive the verification code email

## Troubleshooting

### Emails not sending with PHP mail()

- PHP `mail()` requires a working mail server on your system
- Many hosting providers disable `mail()` for security
- **Solution:** Configure SMTP using the steps above

### Authentication failed with Gmail

- Make sure 2-Factor Authentication is enabled
- Use an App Password, not your regular password
- Check that "Less secure app access" is NOT enabled (use App Passwords instead)

### Connection timeout or refused

- Verify SMTP_HOST and SMTP_PORT are correct
- Check if your hosting provider blocks outgoing SMTP connections
- Try using port 465 with SSL encryption instead of 587/TLS:
  ```env
  SMTP_PORT=465
  SMTP_ENCRYPTION=ssl
  ```

### Emails going to spam

- Configure proper SPF and DKIM records for your domain
- Use a verified sender email address
- Consider using a dedicated email service (SendGrid, Mailgun, etc.)

## Security Best Practices

1. **Never commit .env file to version control**
   - The `.env` file contains sensitive credentials
   - Always use `.env.example` for reference only

2. **Use App Passwords**
   - Never use your main email password
   - Generate service-specific passwords when possible

3. **Enable encryption**
   - Always use TLS or SSL encryption
   - Never use unencrypted SMTP (port 25)

## Features

### Password Reset Flow

1. User enters email address
2. System generates 6-digit verification code
3. Code sent via email (with beautiful HTML template)
4. User enters code within 15 minutes
5. Code verified and token marked as verified
6. User can now reset password
7. Token marked as used

### Email Templates

The system includes professional HTML email templates with:

- Responsive design
- New Gampaha Pharmacy branding
- Clear code display with large monospace font
- Security tips and expiration notice
- Mobile-friendly layout

## What's Included

- ✅ PHPMailer v7.0.2 (installed via Composer)
- ✅ Automatic fallback to PHP mail() if SMTP not configured
- ✅ Environment-based configuration (.env file)
- ✅ Beautiful HTML email templates
- ✅ 6-digit verification codes
- ✅ 15-minute expiration
- ✅ Development mode fallback (shows code on screen)
- ✅ Comprehensive error logging

## Need Help?

If you continue to have issues with email sending:

1. Check PHP error logs for detailed error messages
2. Verify your SMTP credentials are correct
3. Test with a simple SMTP testing tool
4. Contact your hosting provider about SMTP support
5. Consider using a dedicated email service provider

---

**Note:** For development/testing, the system will display verification codes on screen if email sending fails. This fallback is removed in production environments.
