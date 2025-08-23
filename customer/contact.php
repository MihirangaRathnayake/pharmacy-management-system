<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$success = '';
$error = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Store contact message in database (you might want to create a contacts table)
            $stmt = $pdo->prepare("
                INSERT INTO notifications (title, message, type, created_at) 
                VALUES (?, ?, 'info', NOW())
            ");
            
            $notification_message = "Contact Form Submission\n\nName: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\n\nMessage:\n$message";
            
            if ($stmt->execute(["Contact: $subject", $notification_message])) {
                $success = 'Thank you for your message! We will get back to you soon.';
                
                // Clear form data
                $name = $email = $phone = $subject = $message = '';
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PharmaCare</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/contact.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main class="contact-page">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="header-content">
                    <h1 class="gradient-text">Contact Us</h1>
                    <p>We're here to help you with all your healthcare needs</p>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <div class="container">
                <div class="contact-grid">
                    <!-- Contact Information -->
                    <div class="contact-info">
                        <div class="info-card glass-card">
                            <div class="info-header">
                                <h2>Get in Touch</h2>
                                <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                            </div>

                            <div class="contact-methods">
                                <div class="contact-method">
                                    <div class="method-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="method-info">
                                        <h3>Visit Us</h3>
                                        <p>123 Main Street<br>City, State 12345<br>United States</p>
                                    </div>
                                </div>

                                <div class="contact-method">
                                    <div class="method-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="method-info">
                                        <h3>Call Us</h3>
                                        <p>+1 (234) 567-8900<br>Mon-Fri: 8AM-8PM<br>Sat-Sun: 9AM-6PM</p>
                                    </div>
                                </div>

                                <div class="contact-method">
                                    <div class="method-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="method-info">
                                        <h3>Email Us</h3>
                                        <p>info@pharmacare.com<br>support@pharmacare.com<br>orders@pharmacare.com</p>
                                    </div>
                                </div>

                                <div class="contact-method">
                                    <div class="method-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="method-info">
                                        <h3>Business Hours</h3>
                                        <p>24/7 Online Service<br>Emergency: Always Available<br>Consultation: 8AM-10PM</p>
                                    </div>
                                </div>
                            </div>

                            <div class="social-contact">
                                <h3>Follow Us</h3>
                                <div class="social-links">
                                    <a href="#" class="social-link">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="social-link">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="social-link">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="social-link">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="#" class="social-link">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="contact-form-section">
                        <div class="form-card glass-card">
                            <div class="form-header">
                                <h2>Send us a Message</h2>
                                <p>Fill out the form below and we'll get back to you within 24 hours</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="contact-form" data-validate>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Full Name *</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-user input-icon"></i>
                                            <input type="text" id="name" name="name" class="form-input" 
                                                   placeholder="Enter your full name" 
                                                   value="<?= htmlspecialchars($name ?? '') ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-envelope input-icon"></i>
                                            <input type="email" id="email" name="email" class="form-input" 
                                                   placeholder="Enter your email" 
                                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-phone input-icon"></i>
                                            <input type="tel" id="phone" name="phone" class="form-input" 
                                                   placeholder="Enter your phone number" 
                                                   value="<?= htmlspecialchars($phone ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="subject">Subject *</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-tag input-icon"></i>
                                            <select id="subject" name="subject" class="form-input" required>
                                                <option value="">Select a subject</option>
                                                <option value="General Inquiry" <?= ($subject ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                                <option value="Order Support" <?= ($subject ?? '') === 'Order Support' ? 'selected' : '' ?>>Order Support</option>
                                                <option value="Product Information" <?= ($subject ?? '') === 'Product Information' ? 'selected' : '' ?>>Product Information</option>
                                                <option value="Prescription Help" <?= ($subject ?? '') === 'Prescription Help' ? 'selected' : '' ?>>Prescription Help</option>
                                                <option value="Technical Support" <?= ($subject ?? '') === 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
                                                <option value="Complaint" <?= ($subject ?? '') === 'Complaint' ? 'selected' : '' ?>>Complaint</option>
                                                <option value="Feedback" <?= ($subject ?? '') === 'Feedback' ? 'selected' : '' ?>>Feedback</option>
                                                <option value="Other" <?= ($subject ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="message">Message *</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-comment input-icon"></i>
                                        <textarea id="message" name="message" class="form-input form-textarea" 
                                                  placeholder="Tell us how we can help you..." 
                                                  rows="6" required><?= htmlspecialchars($message ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" name="newsletter">
                                        <span class="checkmark"></span>
                                        Subscribe to our newsletter for health tips and special offers
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-full">
                                    <i class="fas fa-paper-plane"></i>
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="map-section">
            <div class="container">
                <div class="map-header">
                    <h2 class="gradient-text">Find Our Location</h2>
                    <p>Visit our physical store for in-person consultation and immediate medicine pickup</p>
                </div>
                
                <div class="map-container glass-card">
                    <div class="map-wrapper">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663095343008!2d-74.00425878459418!3d40.74844097932681!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1635959872076!5m2!1sen!2sus"
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="map-info">
                        <div class="location-details">
                            <h3>PharmaCare Main Store</h3>
                            <div class="location-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Main Street, City, State 12345</span>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-directions"></i>
                                <a href="https://maps.google.com" target="_blank">Get Directions</a>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-parking"></i>
                                <span>Free parking available</span>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-wheelchair"></i>
                                <span>Wheelchair accessible</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <div class="faq-header">
                    <h2 class="gradient-text">Frequently Asked Questions</h2>
                    <p>Quick answers to common questions about our services</p>
                </div>

                <div class="faq-grid">
                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>How fast is your delivery?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We offer same-day delivery within the city and next-day delivery to surrounding areas. Emergency medications can be delivered within 2 hours.</p>
                        </div>
                    </div>

                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>Do you accept insurance?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, we accept most major insurance plans. You can verify your coverage by calling us or checking with your insurance provider.</p>
                        </div>
                    </div>

                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>Can I upload my prescription online?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Absolutely! You can upload your prescription through our website or mobile app. Our pharmacists will verify it and prepare your medications.</p>
                        </div>
                    </div>

                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>What if I need help choosing medications?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Our licensed pharmacists are available for consultation. You can chat with them online, call us, or visit our store for personalized advice.</p>
                        </div>
                    </div>

                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>Are your medicines authentic?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, we source all medications from licensed distributors and manufacturers. Every product comes with authenticity guarantees and proper documentation.</p>
                        </div>
                    </div>

                    <div class="faq-item glass-card">
                        <div class="faq-question">
                            <h3>What are your return policies?</h3>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>We accept returns of unopened medications within 30 days of purchase. Prescription medications cannot be returned due to safety regulations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/contact.js"></script>
</body>
</html>