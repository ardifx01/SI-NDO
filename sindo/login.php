<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login process
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_success'] = true; // Flag untuk notifikasi login berhasil

                header('Location: pages/dashboard.php');
                exit();
            } else {
                $error = 'Username atau password salah';
            }
        }
    }
    
    // Register process
    if (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        
        // Validation
        if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap) || empty($email)) {
            $error = 'Semua field harus diisi';
        } elseif ($password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak sama';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid';
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username atau email sudah terdaftar';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $nama_lengkap, $email])) {
                    $success = 'Pendaftaran berhasil! Silakan login.';
                    // Set session untuk auto-redirect ke login form
                    $_SESSION['register_success'] = true;
                } else {
                    $error = 'Gagal mendaftar. Silakan coba lagi.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-NDO - Login & Register Mahasiswa</title>
    <link rel="icon" href="assets/images/logo.png" type="image/png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5d93e4;
            --secondary-color: #3b72d1;
            --accent-color: #7baaf7;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
            --background-color: #ffffff;
            --text-color: #2c3e50;
            --muted-color: #7f8c8d;
            --success-color: #4cc9a4;
            --error-color: #f72565;
            --border-color: #e6eef9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e6eef9 100%);
            padding: 20px;
        }
        
        .container {
            position: relative;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: var(--background-color);
            border: 1px solid var(--border-color);
        }
        
        .form-box {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            background: var(--background-color);
            transition: 0.6s ease-in-out;
            z-index: 2;
            overflow-y: auto;
        }
        
        .form-box.login {
            left: 0;
            border-radius: 20px 0 0 20px;
        }
        
        .form-box.register {
            right: 0;
            border-radius: 0 20px 20px 0;
            opacity: 0;
            pointer-events: none;
            transform: translateX(100%);
        }
        
        .container.active .form-box.login {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
        }
        
        .container.active .form-box.register {
            transform: translateX(0);
            opacity: 1;
            pointer-events: all;
        }
        
        .form-box h2 {
            font-size: 32px;
            text-align: center;
            margin-bottom: 25px;
            color: var(--primary-color);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .input-box {
            position: relative;
            width: 100%;
            height: 50px;
            margin: 20px 0;
        }
        
        .input-box input {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 16px;
            color: var(--text-color);
            font-weight: 500;
            padding-right: 35px;
            border-bottom: 2px solid #dbe4f0;
            transition: 0.5s;
        }
        
        .input-box input:focus,
        .input-box input:valid {
            border-bottom-color: var(--primary-color);
        }
        
        .input-box label {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            font-size: 16px;
            color: var(--muted-color);
            pointer-events: none;
            transition: 0.5s;
        }
        
        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: -5px;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .input-box i {
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            font-size: 18px;
            color: var(--muted-color);
            transition: 0.5s;
        }
        
        .input-box input:focus ~ i,
        .input-box input:valid ~ i {
            color: var(--primary-color);
        }
        
        .toggle-password {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn {
            position: relative;
            width: 100%;
            height: 45px;
            background: var(--primary-color);
            border: none;
            outline: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            color: white;
            font-weight: 600;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(93, 147, 228, 0.25);
        }
        
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(93, 147, 228, 0.35);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .toggle-link {
            font-size: 14px;
            text-align: center;
            margin: 20px 0 10px;
        }
        
        .toggle-link p {
            color: var(--muted-color);
        }
        
        .toggle-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .toggle-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .info-content {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            color: var(--text-color);
            text-align: center;
            z-index: 1;
            background: linear-gradient(135deg, #e6eef9 0%, #d9e5f5 100%);
        }
        
        .info-content.login {
            right: 0;
            text-align: right;
        }
        
        .info-content.register {
            left: 0;
            text-align: left;
        }
        
        .info-content h2 {
            font-size: 32px;
            line-height: 1.3;
            margin-bottom: 20px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .info-content p {
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.6;
            color: var(--text-color);
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            padding: 12px 15px;
        }
        
        .alert-success {
            background: var(--success-color);
            color: white;
        }
        
        .alert-danger {
            background: var(--error-color);
            color: white;
        }
        
        .logo-img {
            max-height: 100px;
            margin: 0 auto;
            display: block;
            transition: all 0.3s ease;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .social-btn.google {
            background: #DB4437;
        }
        
        .social-btn.facebook {
            background: #4267B2;
        }
        
        .social-btn.twitter {
            background: #1DA1F2;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .floating-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transform: translateX(150%);
            transition: transform 0.5s ease;
            display: flex;
            align-items: center;
        }
        
        .floating-notification.show {
            transform: translateX(0);
        }
        
        .floating-notification.success {
            background: var(--success-color);
        }
        
        .floating-notification.error {
            background: var(--error-color);
        }
        
        .password-strength {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: #ff0000;
            transition: width 0.3s, background 0.3s;
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        /* Animasi keren untuk notifikasi login */
        @keyframes celebrate {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .celebrate {
            animation: celebrate 0.5s ease-in-out;
        }
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f0f;
            opacity: 0.7;
            z-index: 1001;
            pointer-events: none;
        }
        
        /* Responsivitas untuk mobile */
        @media (max-width: 992px) {
            .container {
                width: 100%;
                height: auto;
                min-height: auto;
                max-width: 500px;
            }
            
            .form-box {
                position: relative;
                width: 100%;
                padding: 30px;
                border-radius: 20px;
            }
            
            .form-box.register {
                display: none;
            }
            
            .container.active .form-box.register {
                display: flex;
            }
            
            .info-content {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .container {
                border-radius: 15px;
            }
            
            .form-box {
                padding: 20px 15px;
            }
            
            .form-box h2 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .input-box {
                height: 45px;
                margin: 15px 0;
            }
            
            .input-box input {
                font-size: 14px;
            }
            
            .btn {
                height: 40px;
                font-size: 14px;
            }
            
            .toggle-link {
                font-size: 13px;
            }
            
            .social-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .alert {
                padding: 10px;
                font-size: 14px;
            }
        }

        /* Responsivitas untuk tablet */
        @media (min-width: 577px) and (max-width: 992px) {
            .container {
                max-width: 90%;
            }
            
            .form-box {
                padding: 30px;
            }
            
            .form-box h2 {
                font-size: 28px;
            }
        }

        /* Responsivitas untuk layar besar */
        @media (min-width: 1200px) {
            .container {
                max-width: 1000px;
            }
            
            .form-box {
                padding: 0 60px;
            }
        }

        /* Perbaikan untuk input di iOS */
        input {
            border-radius: 0; /* Menghilangkan border-radius default di iOS */
            -webkit-appearance: none;
        }

        /* Memastikan tombol mudah diklik di perangkat mobile */
        .btn, .social-btn, .toggle-password {
            -webkit-tap-highlight-color: transparent;
        }

        /* Memperbaiki tampilan di browser Safari */
        @supports (-webkit-touch-callout: none) {
            .container {
                -webkit-backdrop-filter: blur(10px);
                backdrop-filter: blur(10px);
            }
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <!-- Login Form -->
        <div class="form-box login">
            <h2>Login</h2>
            
            <!-- Notifikasi sukses -->
            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="alert alert-success animate__animated animate__fadeInDown text-center">
                    <i class="fas fa-check-circle me-2"></i>
                    Pendaftaran berhasil! Silakan login.
                </div>
                <?php unset($_SESSION['register_success']); ?>
            <?php endif; ?>

            <!-- Notifikasi error/success dari proses register -->
            <?php if ($error && !isset($_POST['register'])): ?>
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success animate__animated animate__fadeInDown">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="login" value="1">
                <div class="input-box">
                    <input type="text" id="login_username" name="username" required>
                    <label for="login_username">Username</label>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="input-box">
                    <input type="password" id="login_password" name="password" required>
                    <label for="login_password">Password</label>
                    <span class="toggle-password" onclick="togglePassword('login_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Ingat saya</label>
                    </div>
                    <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 14px;">
                        Lupa password?
                    </a>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
                
                
                <div class="toggle-link">
                    <p>Belum punya akun? <a href="#" id="registerLink">Daftar sekarang</a></p>
                </div>
            </form>
        </div>
        
        <!-- Register Form -->
        <div class="form-box register">
            <h2>Daftar</h2>
            
            <!-- Notifikasi error dari proses register -->
            <?php if ($error && isset($_POST['register'])): ?>
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="register" value="1">
                <div class="input-box">
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>" required>
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="input-box">
                    <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                </div>
                
                <div class="input-box">
                    <input type="text" id="reg_username" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    <label for="reg_username">Username</label>
                    <i class="fas fa-at"></i>
                </div>
                
                <div class="input-box">
                    <input type="password" id="reg_password" name="password" required>
                    <label for="reg_password">Password</label>
                    <span class="toggle-password" onclick="togglePassword('reg_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-hint" id="passwordHint">
                    Password harus minimal 6 karakter
                </div>
                
                <div class="input-box">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <label for="confirm_password">Konfirmasi Password</label>
                    <span class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div id="passwordMatch" class="text-danger small mb-3"></div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus me-2"></i> Daftar
                </button>
                
                <div class="toggle-link">
                    <p>Sudah punya akun? <a href="#" id="loginLink">Login</a></p>
                </div>
            </form>
        </div>
        
        <!-- Info Content -->
        <div class="info-content login">
            <h2>Selamat Datang Kembali!</h2>
            <p>Masuk ke akun SI-NDO Anda untuk mengakses semua fitur pengingat dan jadwal kuliah.</p>
            <img src="assets/images/logo.png" alt="SI-NDO" class="logo-img" onerror="this.style.display='none'">
        </div>
        
        <div class="info-content register">
            <h2>Bergabunglah Dengan Kami!</h2>
            <p>Daftar sekarang untuk mendapatkan akses ke semua fitur SI-NDO yang akan membantu mengatur jadwal kuliah Anda.</p>
            <img src="assets/images/logo.png" alt="SI-NDO" class="logo-img" onerror="this.style.display='none'">
        </div>
    </div>

    <div id="floatingNotification" class="floating-notification">
        <i class="fas fa-bell me-2"></i>
        <span id="notificationText"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = passwordInput.parentElement.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Toggle between login and register forms
        const container = document.getElementById('container');
        const registerLink = document.getElementById('registerLink');
        const loginLink = document.getElementById('loginLink');
        
        registerLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add('active');
        });
        
        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove('active');
        });
        
        // Handle logo image error
        document.addEventListener('DOMContentLoaded', function() {
            const logoImages = document.querySelectorAll('.logo-img');
            logoImages.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                });
            });
            
            // Auto switch to register form if there are registration errors
            <?php if (isset($_POST['register'])): ?>
                container.classList.add('active');
            <?php endif; ?>
            
            // Auto switch to login form after successful registration
            <?php if (isset($_SESSION['register_success'])): ?>
                container.classList.remove('active');
            <?php endif; ?>

            // Menangani perubahan ukuran layar
            handleResponsiveDesign();
            window.addEventListener('resize', handleResponsiveDesign);
        });
        
        // Fungsi untuk menangani desain responsif
        function handleResponsiveDesign() {
            const container = document.getElementById('container');
            if (window.innerWidth <= 992) {
                // Mode mobile
                container.style.maxWidth = '500px';
            } else {
                // Mode desktop
                container.style.maxWidth = '900px';
            }
        }
        
        // Show notification function
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('floatingNotification');
            const notificationText = document.getElementById('notificationText');
            
            notificationText.textContent = message;
            notification.className = 'floating-notification';
            
            if (type === 'success') {
                notification.classList.add('success');
            } else {
                notification.classList.add('error');
            }
            
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Password strength indicator for registration form
        const passwordInput = document.getElementById('reg_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordHint = document.getElementById('passwordHint');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let hint = '';
                
                // Check length
                if (password.length >= 6) strength += 1;
                if (password.length >= 8) strength += 1;
                
                // Check for mixed case
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
                
                // Check for numbers
                if (/\d/.test(password)) strength += 1;
                
                // Check for special chars
                if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
                
                // Update UI
                let width = 0;
                let color = '#ff0000';
                
                switch(strength) {
                    case 1:
                        width = 25;
                        color = '#ff0000';
                        hint = 'Password lemah';
                        break;
                    case 2:
                        width = 50;
                        color = '#ffa500';
                        hint = 'Password cukup';
                        break;
                    case 3:
                        width = 75;
                        color = '#ffff00';
                        hint = 'Password baik';
                        break;
                    case 4:
                    case 5:
                        width = 100;
                        color = '#4bb543';
                        hint = 'Password sangat kuat';
                        break;
                    default:
                        width = 0;
                        hint = 'Password harus minimal 6 karakter';
                }
                
                passwordStrengthBar.style.width = `${width}%`;
                passwordStrengthBar.style.background = color;
                passwordHint.textContent = hint;
                passwordHint.style.color = color;
            });
        }
        
        // Password match checker
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        
        if (confirmPasswordInput && passwordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    passwordMatch.textContent = 'Password tidak cocok';
                } else {
                    passwordMatch.textContent = '';
                }
            });
        }
        
        // Function untuk membuat efek konfeti
        function createConfetti() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
            const container = document.body;
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                container.appendChild(confetti);
                
                // Animasi jatuh
                const animation = confetti.animate([
                    { top: '-10px', transform: `rotate(0deg)` },
                    { top: '100vh', transform: `rotate(${Math.random() * 720}deg)` }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0.1, 0.8, 0.1, 1)'
                });
                
                // Hapus elemen setelah animasi selesai
                animation.onfinish = () => {
                    confetti.remove();
                };
            }
        }
        
        // Cek jika ada notifikasi login sukses
        <?php if (isset($_SESSION['login_success'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Tampilkan notifikasi keren
                showNotification('Login berhasil! Selamat datang <?= $_SESSION['username'] ?>', 'success');
                
                // Tambahkan efek konfeti
                createConfetti();
                
                // Hapus session setelah notifikasi ditampilkan
                <?php unset($_SESSION['login_success']); ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>
