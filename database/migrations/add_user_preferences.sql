-- Migration to add user preferences and profile image support
-- Run this if you have an existing database

-- Add profile_image column to users table if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) AFTER address;

-- Create user_preferences table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    theme ENUM('light', 'dark', 'auto') DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
    notifications BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);