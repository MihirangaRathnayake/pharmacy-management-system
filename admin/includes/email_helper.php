<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Helper Functions
 * Email sending functionality using PHPMailer with fallback to PHP mail()
 */

/**
 * Get email configuration
 * Override these values in your config or environment variables
 */
function getEmailConfig()
{
    return [
        'use_smtp' => getenv('SMTP_ENABLED') ?: false, // Set to true to enable SMTP
        'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'smtp_port' => getenv('SMTP_PORT') ?: 587,
        'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls', // 'tls' or 'ssl'
        'smtp_username' => getenv('SMTP_USERNAME') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'noreply@pharmacare.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'PharmaCare',
    ];
}

/**
 * Send an email using PHPMailer (SMTP) or PHP mail() as fallback
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message (HTML supported)
 * @param string $fromName Sender name (default: from config)
 * @param string $fromEmail Sender email (default: from config)
 * @return bool True if email was sent successfully
 */
function sendEmail($to, $subject, $message, $fromName = null, $fromEmail = null)
{
    $config = getEmailConfig();
    $fromName = $fromName ?: $config['from_name'];
    $fromEmail = $fromEmail ?: $config['from_email'];

    // Try PHPMailer if SMTP is enabled and configured
    if ($config['use_smtp'] && !empty($config['smtp_username']) && !empty($config['smtp_password'])) {
        try {
            // Load composer autoloader if available
            $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }

            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new PHPMailer(true);

                // SMTP Configuration
                $mail->isSMTP();
                $mail->Host = $config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = $config['smtp_encryption'];
                $mail->Port = $config['smtp_port'];
                $mail->CharSet = 'UTF-8';

                // Sender and recipient
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($to);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->AltBody = strip_tags($message);

                // Send email
                $success = $mail->send();

                if ($success) {
                    error_log("Email sent via SMTP to: {$to}, Subject: {$subject}");
                }

                return $success;
            }
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            // Fall through to PHP mail() as fallback
        }
    }

    // Fallback to PHP mail() function
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: {$fromName} <{$fromEmail}>";
    $headers[] = "Reply-To: {$fromEmail}";
    $headers[] = "X-Mailer: PHP/" . phpversion();

    // Suppress warnings as mail() may not be configured in development
    $success = @mail($to, $subject, $message, implode("\r\n", $headers));

    if ($success) {
        error_log("Email sent via PHP mail() to: {$to}, Subject: {$subject}");
    } else {
        error_log("Failed to send email to: {$to}, Subject: {$subject} - mail() not configured");
    }

    return $success;
}

/**
 * Send password reset code email
 *
 * @param string $to Recipient email address
 * @param string $name Recipient name
 * @param string $code 6-digit verification code
 * @return bool True if email was sent successfully
 */
function sendPasswordResetCode($to, $name, $code)
{
    $subject = "Password Reset Code - PharmaCare";

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #10b981, #059669);
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                color: #ffffff;
                margin: 0;
                font-size: 28px;
            }
            .content {
                padding: 40px 30px;
            }
            .code-box {
                background: linear-gradient(135deg, #f0fdf4, #e0f2fe);
                border: 2px dashed #10b981;
                border-radius: 12px;
                padding: 30px;
                text-align: center;
                margin: 30px 0;
            }
            .code {
                font-size: 48px;
                font-weight: bold;
                color: #10b981;
                letter-spacing: 8px;
                font-family: 'Courier New', monospace;
            }
            .footer {
                background: #f9fafb;
                padding: 20px 30px;
                text-align: center;
                font-size: 12px;
                color: #6b7280;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #10b981, #059669);
                color: #ffffff;
                text-decoration: none;
                border-radius: 8px;
                margin: 20px 0;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Password Reset</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($name) . ",</h2>
                <p>We received a request to reset your password for your PharmaCare account.</p>
                <p>Please use the following verification code to complete your password reset:</p>

                <div class='code-box'>
                    <p style='margin: 0; color: #6b7280; font-size: 14px;'>Your Verification Code</p>
                    <div class='code'>" . $code . "</div>
                    <p style='margin: 10px 0 0 0; color: #6b7280; font-size: 12px;'>Valid for 15 minutes</p>
                </div>

                <p><strong>Important:</strong></p>
                <ul style='color: #4b5563;'>
                    <li>This code will expire in 15 minutes</li>
                    <li>If you didn't request this, please ignore this email</li>
                    <li>Never share this code with anyone</li>
                </ul>

                <p style='margin-top: 30px; color: #6b7280;'>
                    If you have any questions, please contact our support team.
                </p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " PharmaCare Pharmacy Management System</p>
                <p>This is an automated email. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to, $subject, $message);
}

/**
 * Send password reset link email
 *
 * @param string $to Recipient email address
 * @param string $name Recipient name
 * @param string $resetLink Password reset link URL
 * @return bool True if email was sent successfully
 */
function sendPasswordResetLink($to, $name, $resetLink)
{
    $subject = "Password Reset Link - PharmaCare";

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #10b981, #059669);
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                color: #ffffff;
                margin: 0;
                font-size: 28px;
            }
            .content {
                padding: 40px 30px;
            }
            .button {
                display: inline-block;
                padding: 14px 36px;
                background: linear-gradient(135deg, #10b981, #059669);
                color: #ffffff !important;
                text-decoration: none;
                border-radius: 8px;
                margin: 20px 0;
                font-weight: bold;
                font-size: 16px;
            }
            .button:hover {
                opacity: 0.9;
            }
            .footer {
                background: #f9fafb;
                padding: 20px 30px;
                text-align: center;
                font-size: 12px;
                color: #6b7280;
            }
            .link-box {
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
                word-break: break-all;
                font-size: 12px;
                color: #6b7280;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Password Reset</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($name) . ",</h2>
                <p>We received a request to reset your password for your PharmaCare account.</p>
                <p>Click the button below to reset your password:</p>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . htmlspecialchars($resetLink) . "' class='button'>Reset Password</a>
                </div>

                <p style='color: #6b7280; font-size: 14px;'>Or copy and paste this link into your browser:</p>
                <div class='link-box'>" . htmlspecialchars($resetLink) . "</div>

                <p><strong>Important:</strong></p>
                <ul style='color: #4b5563;'>
                    <li>This link will expire in 1 hour</li>
                    <li>If you didn't request this, please ignore this email</li>
                    <li>Never share this link with anyone</li>
                </ul>

                <p style='margin-top: 30px; color: #6b7280;'>
                    If you have any questions, please contact our support team.
                </p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " PharmaCare Pharmacy Management System</p>
                <p>This is an automated email. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to, $subject, $message);
}

/**
 * Test email configuration
 * Sends a test email to check if email sending is working
 *
 * @param string $to Test email recipient
 * @return bool True if test email was sent successfully
 */
function testEmailConfiguration($to)
{
    $subject = "Test Email - PharmaCare";
    $message = "
    <!DOCTYPE html>
    <html>
    <body style='font-family: Arial, sans-serif; padding: 20px;'>
        <h2 style='color: #10b981;'>Email Configuration Test</h2>
        <p>This is a test email from your PharmaCare Pharmacy Management System.</p>
        <p>If you received this email, your email configuration is working correctly!</p>
        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";

    return sendEmail($to, $subject, $message);
}

/**
 * Generate a 6-digit verification code
 *
 * @return string 6-digit numeric code
 */
function generateVerificationCode()
{
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
