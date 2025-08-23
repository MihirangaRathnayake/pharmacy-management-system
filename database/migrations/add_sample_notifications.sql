-- Add sample notifications for testing
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES 
(NULL, 'System Update', 'System has been updated to version 2.1.0', 'info', 0),
(NULL, 'Low Stock Alert', 'Paracetamol 500mg is running low (5 units remaining)', 'warning', 0),
(NULL, 'New Feature', 'Profile management system is now available', 'success', 0),
(1, 'Welcome', 'Welcome to your new profile dashboard!', 'success', 0),
(1, 'Security Alert', 'Your password was changed successfully', 'info', 1);