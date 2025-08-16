<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Logo -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center space-x-3 mb-6">
                <i class="fas fa-pills text-green-600 text-6xl"></i>
                <span class="text-5xl font-bold text-gray-800">PharmaCare</span>
            </div>
            <p class="text-xl text-gray-600">Complete Pharmacy Management System</p>
        </div>

        <!-- Access Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Admin Access -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-shield text-red-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Administrator</h3>
                <p class="text-gray-600 mb-6">Full system access, manage inventory, sales, customers, and view reports</p>
                <a href="auth/login.php" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 inline-flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Admin Login
                </a>
            </div>

            <!-- Pharmacist Access -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-md text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Pharmacist</h3>
                <p class="text-gray-600 mb-6">Verify prescriptions, process sales, and manage customer interactions</p>
                <a href="auth/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 inline-flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Pharmacist Login
                </a>
            </div>

            <!-- Customer Access -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Customer</h3>
                <p class="text-gray-600 mb-6">Browse medicines, upload prescriptions, and place orders online</p>
                <a href="customer/index.html" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 inline-flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Shop Now
                </a>
            </div>
        </div>

        <!-- Demo Credentials -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">Demo Login Credentials</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="font-medium text-red-800">Administrator</p>
                    <p class="text-red-600">admin@pharmacy.com</p>
                    <p class="text-red-600">admin123</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="font-medium text-blue-800">Pharmacist</p>
                    <p class="text-blue-600">pharmacist@pharmacy.com</p>
                    <p class="text-blue-600">pharma123</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="font-medium text-green-800">Customer</p>
                    <p class="text-green-600">customer@pharmacy.com</p>
                    <p class="text-green-600">customer123</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-redirect script -->
    <script>
        // Auto-redirect to login after 10 seconds
        setTimeout(function() {
            window.location.href = 'auth/login.php';
        }, 10000);
    </script>
</body>
</html>