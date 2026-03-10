-- Fix medicine auto-increment to start from 1
-- Run this to reset the auto-increment after cleaning up existing records

-- First, create a temporary table with the data
CREATE TEMPORARY TABLE medicines_backup AS SELECT * FROM medicines;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate to reset auto-increment
TRUNCATE TABLE medicines;

-- Re-insert with new sequential IDs
INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, barcode, description, dosage, unit, purchase_price, selling_price, stock_quantity, min_stock_level, max_stock_level, expiry_date, manufacture_date, prescription_required, image, status, created_at, updated_at)
SELECT name, generic_name, category_id, supplier_id, batch_number, barcode, description, dosage, unit, purchase_price, selling_price, stock_quantity, min_stock_level, max_stock_level, expiry_date, manufacture_date, prescription_required, image, status, created_at, updated_at
FROM medicines_backup ORDER BY id;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Drop temporary table
DROP TEMPORARY TABLE medicines_backup;
