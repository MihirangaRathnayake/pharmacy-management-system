<?php

/**
 * Seed sample data for Pharmacy Management System
 * Run once: php seed_sample_data.php  OR  visit in browser
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!$pdo) {
    die("Database connection failed.\n");
}

echo "<pre>\n";
echo "=== Pharmacy Sample Data Seeder ===\n\n";

try {
    // Get first admin user id
    $admin = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
    if (!$admin) {
        $admin = $pdo->query("SELECT id FROM users LIMIT 1")->fetch();
    }
    if (!$admin) {
        die("No users found. Please register an admin user first.\n");
    }
    $adminId = $admin['id'];
    echo "Using admin user ID: $adminId\n";

    // --- Ensure categories exist ---
    $catCount = $pdo->query("SELECT COUNT(*) as c FROM categories")->fetch()['c'];
    if ($catCount < 8) {
        $pdo->exec("INSERT IGNORE INTO categories (name, description) VALUES 
            ('Pain Relief', 'Medicines for pain management'),
            ('Antibiotics', 'Antibiotic medications'),
            ('Vitamins', 'Vitamin supplements'),
            ('Cold & Flu', 'Cold and flu medications'),
            ('Diabetes', 'Diabetes management medicines'),
            ('Heart', 'Cardiovascular medicines'),
            ('Skin Care', 'Dermatological products'),
            ('Digestive', 'Digestive system medicines')");
        echo "Categories seeded.\n";
    }

    // --- Ensure suppliers exist ---
    $supCount = $pdo->query("SELECT COUNT(*) as c FROM suppliers")->fetch()['c'];
    if ($supCount < 3) {
        $pdo->exec("INSERT IGNORE INTO suppliers (name, contact_person, email, phone, address) VALUES 
            ('MediCorp Ltd', 'John Smith', 'john@medicorp.com', '+94771234561', '45 Hospital Rd, Colombo'),
            ('PharmaSupply Inc', 'Jane Doe', 'jane@pharmasupply.com', '+94771234562', '12 Med Lane, Kandy'),
            ('HealthDistributors', 'Mike Johnson', 'mike@healthdist.com', '+94771234563', '78 Wellness Ave, Galle')");
        echo "Suppliers seeded.\n";
    }

    // Get supplier IDs
    $suppliers = $pdo->query("SELECT id FROM suppliers WHERE status='active' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $categories = $pdo->query("SELECT id FROM categories WHERE status='active' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

    // --- Seed Medicines (skip if already have enough) ---
    $medCount = $pdo->query("SELECT COUNT(*) as c FROM medicines")->fetch()['c'];
    if ($medCount < 15) {
        $medicines = [
            ['Paracetamol 500mg', 'Paracetamol', 0, 0, 'BATCH-P001', 3.50, 8.00, 200, 20, '2027-06-30', 0],
            ['Amoxicillin 500mg', 'Amoxicillin', 1, 0, 'BATCH-A001', 12.00, 25.00, 150, 15, '2027-03-15', 1],
            ['Vitamin C 1000mg', 'Ascorbic Acid', 2, 1, 'BATCH-V001', 6.00, 15.00, 300, 25, '2027-12-31', 0],
            ['Ibuprofen 400mg', 'Ibuprofen', 0, 0, 'BATCH-I001', 4.00, 10.00, 180, 20, '2027-09-30', 0],
            ['Metformin 500mg', 'Metformin HCl', 4, 2, 'BATCH-M001', 8.00, 18.00, 120, 15, '2027-05-31', 1],
            ['Omeprazole 20mg', 'Omeprazole', 7, 1, 'BATCH-O001', 5.00, 12.00, 250, 20, '2027-08-30', 0],
            ['Cetirizine 10mg', 'Cetirizine HCl', 3, 0, 'BATCH-C001', 2.00, 6.00, 350, 30, '2027-11-30', 0],
            ['Atorvastatin 20mg', 'Atorvastatin', 5, 2, 'BATCH-AT01', 15.00, 35.00, 90, 10, '2027-04-30', 1],
            ['Azithromycin 250mg', 'Azithromycin', 1, 0, 'BATCH-AZ01', 20.00, 45.00, 80, 10, '2027-07-31', 1],
            ['Cough Syrup 100ml', 'Dextromethorphan', 3, 1, 'BATCH-CS01', 10.00, 22.00, 60, 15, '2027-10-31', 0],
            ['Multivitamin Tablets', 'Multivitamin', 2, 1, 'BATCH-MV01', 12.00, 28.00, 200, 20, '2028-01-31', 0],
            ['Diclofenac Gel 30g', 'Diclofenac', 6, 2, 'BATCH-DG01', 8.00, 20.00, 100, 15, '2027-06-30', 0],
            ['Amlodipine 5mg', 'Amlodipine', 5, 2, 'BATCH-AM01', 6.00, 14.00, 140, 15, '2027-12-31', 1],
            ['Loperamide 2mg', 'Loperamide HCl', 7, 1, 'BATCH-LP01', 3.00, 8.00, 220, 20, '2027-09-30', 0],
            ['Betadine Solution 50ml', 'Povidone Iodine', 6, 0, 'BATCH-BD01', 7.00, 16.00, 5, 15, '2027-11-30', 0],
            ['Salbutamol Inhaler', 'Salbutamol', 3, 2, 'BATCH-SB01', 25.00, 55.00, 40, 10, '2027-08-31', 1],
            ['Pantoprazole 40mg', 'Pantoprazole', 7, 1, 'BATCH-PP01', 7.00, 16.00, 3, 15, '2027-05-31', 0],
            ['Clotrimazole Cream', 'Clotrimazole', 6, 0, 'BATCH-CL01', 5.00, 12.00, 8, 10, '2027-10-31', 0],
            ['Losartan 50mg', 'Losartan', 5, 2, 'BATCH-LS01', 10.00, 24.00, 110, 15, '2027-07-31', 1],
            ['Zinc Tablets 20mg', 'Zinc Sulphate', 2, 1, 'BATCH-ZN01', 3.00, 8.00, 280, 25, '2028-03-31', 0],
        ];

        $stmt = $pdo->prepare("INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, purchase_price, selling_price, stock_quantity, min_stock_level, expiry_date, prescription_required, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,'active')");
        foreach ($medicines as $m) {
            $catId = $categories[$m[2] % count($categories)] ?? $categories[0];
            $supId = $suppliers[$m[3] % count($suppliers)] ?? $suppliers[0];
            try {
                $stmt->execute([$m[0], $m[1], $catId, $supId, $m[4], $m[5], $m[6], $m[7], $m[8], $m[9], $m[10]]);
            } catch (Exception $e) {
                // skip duplicates
            }
        }
        echo "Medicines seeded (20 items, some with low stock).\n";
    }

    // --- Seed Customers ---
    $custCount = $pdo->query("SELECT COUNT(*) as c FROM customers")->fetch()['c'];
    if ($custCount < 8) {
        $custs = [
            ['CUST001', 'Nimal Perera', 'nimal@email.com', '+94771001001', '15 Galle Rd, Colombo', '1985-03-15', 'male'],
            ['CUST002', 'Kamala Silva', 'kamala@email.com', '+94771001002', '22 Kandy Rd, Gampaha', '1990-07-22', 'female'],
            ['CUST003', 'Ruwan Fernando', 'ruwan@email.com', '+94771001003', '8 Lake View, Kandy', '1978-11-05', 'male'],
            ['CUST004', 'Dilini Jayawardena', 'dilini@email.com', '+94771001004', '55 Temple Rd, Matara', '1995-01-18', 'female'],
            ['CUST005', 'Saman Kumara', 'saman@email.com', '+94771001005', '3 Station Rd, Kurunegala', '1982-09-30', 'male'],
            ['CUST006', 'Anoma Wickramasinghe', 'anoma@email.com', '+94771001006', '17 Hill St, Nuwara Eliya', '1988-12-12', 'female'],
            ['CUST007', 'Pradeep Bandara', 'pradeep@email.com', '+94771001007', '29 Sea View, Galle', '1975-06-25', 'male'],
            ['CUST008', 'Shanika De Silva', 'shanika@email.com', '+94771001008', '41 Park Ave, Negombo', '1993-04-08', 'female'],
            ['CUST009', 'Tharanga Rajapaksha', 'tharanga@email.com', '+94771001009', '63 Main St, Ratnapura', '1987-08-14', 'male'],
            ['CUST010', 'Hiruni Gunawardena', 'hiruni@email.com', '+94771001010', '11 Green Ln, Badulla', '1992-02-28', 'female'],
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO customers (customer_code, name, email, phone, address, date_of_birth, gender, status) VALUES (?,?,?,?,?,?,?,'active')");
        foreach ($custs as $c) {
            try {
                $stmt->execute($c);
            } catch (Exception $e) {
                // skip duplicates
            }
        }
        echo "Customers seeded (10 customers).\n";
    }

    // --- Seed Sales across multiple months ---
    $saleCount = $pdo->query("SELECT COUNT(*) as c FROM sales")->fetch()['c'];
    if ($saleCount < 5) {
        $customerIds = $pdo->query("SELECT id FROM customers WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);
        $medicineData = $pdo->query("SELECT id, selling_price, stock_quantity FROM medicines WHERE status='active' AND stock_quantity > 0")->fetchAll();

        if (empty($customerIds) || empty($medicineData)) {
            die("Need customers and medicines before creating sales.\n");
        }

        // Create sales across different dates (past 6 months + today)
        $saleDates = [
            // Today
            date('Y-m-d 09:15:00'),
            date('Y-m-d 10:30:00'),
            date('Y-m-d 11:45:00'),
            date('Y-m-d 14:20:00'),
            date('Y-m-d 16:00:00'),
            // Yesterday
            date('Y-m-d H:i:s', strtotime('-1 day 09:00')),
            date('Y-m-d H:i:s', strtotime('-1 day 14:30')),
            // This week
            date('Y-m-d H:i:s', strtotime('-2 days 10:00')),
            date('Y-m-d H:i:s', strtotime('-3 days 11:15')),
            date('Y-m-d H:i:s', strtotime('-4 days 15:45')),
            // This month
            date('Y-m-d H:i:s', strtotime('-7 days 09:30')),
            date('Y-m-d H:i:s', strtotime('-8 days 12:00')),
            date('Y-m-d H:i:s', strtotime('-10 days 10:30')),
            date('Y-m-d H:i:s', strtotime('-14 days 16:00')),
            date('Y-m-d H:i:s', strtotime('-18 days 11:00')),
            date('Y-m-d H:i:s', strtotime('-21 days 14:30')),
            date('Y-m-d H:i:s', strtotime('-25 days 09:45')),
            // Last month
            date('Y-m-d H:i:s', strtotime('-35 days 10:00')),
            date('Y-m-d H:i:s', strtotime('-38 days 14:00')),
            date('Y-m-d H:i:s', strtotime('-42 days 11:30')),
            date('Y-m-d H:i:s', strtotime('-45 days 16:00')),
            date('Y-m-d H:i:s', strtotime('-50 days 09:15')),
            // 2-3 months ago
            date('Y-m-d H:i:s', strtotime('-65 days 10:30')),
            date('Y-m-d H:i:s', strtotime('-70 days 15:00')),
            date('Y-m-d H:i:s', strtotime('-80 days 11:45')),
            date('Y-m-d H:i:s', strtotime('-90 days 14:00')),
            // 4-6 months ago
            date('Y-m-d H:i:s', strtotime('-120 days 10:00')),
            date('Y-m-d H:i:s', strtotime('-140 days 09:30')),
            date('Y-m-d H:i:s', strtotime('-160 days 11:00')),
            date('Y-m-d H:i:s', strtotime('-180 days 14:30')),
        ];

        $paymentMethods = ['cash', 'cash', 'cash', 'card', 'card', 'upi', 'online'];
        $taxRate = 0.18;
        $saleInsert = $pdo->prepare("INSERT INTO sales (invoice_number, customer_id, user_id, sale_date, subtotal, tax_amount, discount_amount, total_amount, payment_method, payment_status, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,'paid','completed',?)");
        $itemInsert = $pdo->prepare("INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price) VALUES (?,?,?,?,?)");

        $saleNum = 1000;
        foreach ($saleDates as $idx => $saleDate) {
            $saleNum++;
            $invoiceNo = 'INV-' . date('Ymd', strtotime($saleDate)) . '-' . str_pad($saleNum, 4, '0', STR_PAD_LEFT);

            // Random customer (sometimes null = walk-in)
            $custId = ($idx % 3 === 0) ? null : $customerIds[array_rand($customerIds)];
            $pm = $paymentMethods[array_rand($paymentMethods)];
            $discount = ($idx % 4 === 0) ? rand(10, 50) : 0;

            // Pick 1-4 random medicines
            $itemCount = rand(1, 4);
            $shuffled = $medicineData;
            shuffle($shuffled);
            $selectedMeds = array_slice($shuffled, 0, $itemCount);

            $subtotal = 0;
            $items = [];
            foreach ($selectedMeds as $med) {
                $qty = rand(1, 5);
                $price = floatval($med['selling_price']);
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;
                $items[] = ['med_id' => $med['id'], 'qty' => $qty, 'price' => $price, 'total' => $lineTotal];
            }

            $tax = round(($subtotal - $discount) * $taxRate, 2);
            $total = round($subtotal + $tax - $discount, 2);

            try {
                $saleInsert->execute([$invoiceNo, $custId, $adminId, $saleDate, $subtotal, $tax, $discount, $total, $pm, $saleDate]);
                $saleId = $pdo->lastInsertId();

                foreach ($items as $it) {
                    $itemInsert->execute([$saleId, $it['med_id'], $it['qty'], $it['price'], $it['total']]);
                }
            } catch (Exception $e) {
                echo "  Skipping sale $invoiceNo: " . $e->getMessage() . "\n";
            }
        }
        echo "Sales seeded (30 transactions across 6 months).\n";
    }

    // --- Seed Prescriptions ---
    $rxCount = $pdo->query("SELECT COUNT(*) as c FROM prescriptions")->fetch()['c'];
    if ($rxCount < 3) {
        $customerIds = $pdo->query("SELECT id FROM customers WHERE status='active' LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($customerIds)) {
            $rxStmt = $pdo->prepare("INSERT INTO prescriptions (customer_id, doctor_name, prescription_date, notes, status) VALUES (?,?,?,?,?)");
            $rxData = [
                [$customerIds[0], 'Dr. Kamal Gunaratne', date('Y-m-d'), 'Diabetes medication refill', 'pending'],
                [$customerIds[1 % count($customerIds)], 'Dr. Nimali Fernando', date('Y-m-d', strtotime('-1 day')), 'Antibiotic course', 'pending'],
                [$customerIds[2 % count($customerIds)], 'Dr. Suresh Perera', date('Y-m-d', strtotime('-2 days')), 'Heart medication', 'verified'],
                [$customerIds[3 % count($customerIds)], 'Dr. Anita Silva', date('Y-m-d', strtotime('-3 days')), 'Pain management', 'processed'],
                [$customerIds[4 % count($customerIds)], 'Dr. Ravi Kumar', date('Y-m-d', strtotime('-5 days')), 'Vitamin supplements', 'pending'],
            ];
            foreach ($rxData as $rx) {
                try {
                    $rxStmt->execute($rx);
                } catch (Exception $e) {
                }
            }
            echo "Prescriptions seeded (5 prescriptions, 3 pending).\n";
        }
    }

    echo "\n=== Sample data seeded successfully! ===\n";
    echo "Summary:\n";
    echo "  Categories:    " . $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() . "\n";
    echo "  Suppliers:     " . $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn() . "\n";
    echo "  Medicines:     " . $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn() . "\n";
    echo "  Customers:     " . $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() . "\n";
    echo "  Sales:         " . $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn() . "\n";
    echo "  Sale Items:    " . $pdo->query("SELECT COUNT(*) FROM sale_items")->fetchColumn() . "\n";
    echo "  Prescriptions: " . $pdo->query("SELECT COUNT(*) FROM prescriptions")->fetchColumn() . "\n";
    echo "\nRefresh your dashboard, sales, and reports pages to see the data!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>\n";