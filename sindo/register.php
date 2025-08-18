<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    
    // Validasi
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap) || empty($email)) {
        $error = 'Semua field harus diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Cek username sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username atau email sudah terdaftar';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $nama_lengkap, $email])) {
                $_SESSION['registration_success'] = true;
                header('Location: login.php');
                exit();
            } else {
                $error = 'Gagal mendaftar. Silakan coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sindo - Daftar Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #4361ee;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .register-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .register-card:hover {
            transform: translateY(-5px);
        }
        
        .register-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background-color: transparent;
            border-right: none;
        }
        
        .input-with-icon {
            border-left: none;
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
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .footer-links a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed {
            background: var(--success-color);
            color: white;
        }
        
        .step-line {
            flex: 1;
            height: 2px;
            background: #e0e0e0;
            margin: auto 0;
        }
        
        .step-line.active {
            background: var(--primary-color);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="register-container animate__animated animate__fadeIn">
            <div class="register-card">
                <div class="register-header">
                    <div class="logo">
                        <img src="assets/images/putih.png" alt="SINDO" class="img-fluid" style="max-height: 70px;">
                        <span>SI-NDO</span>
                    </div>
                    <p class="mb-0">Buat Akun Mahasiswa Baru</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="step-indicator">
                        <div class="step active">1</div>
                        <div class="step-line"></div>
                        <div class="step">2</div>
                    </div>
                    
                    <form method="post" id="registrationForm">
                        <!-- Step 1 - Personal Info -->
                        <div class="form-step active" id="step1">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control input-with-icon" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control input-with-icon" id="email" name="email" placeholder="Masukkan email" required>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <div></div>
                                <button type="button" class="btn btn-primary btn-next" data-next="step2">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 2 - Account Info -->
                        <div class="form-step" id="step2">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="text" class="form-control input-with-icon" id="username" name="username" placeholder="Buat username" required>
                                </div>
                                <small class="text-muted">Gunakan huruf, angka, dan underscore (_)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Buat password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <div class="password-hint" id="passwordHint">
                                    Password harus minimal 6 karakter
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control input-with-icon" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatch" class="text-danger small"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="step1">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                                <button type="submit" class="btn btn-primary btn-register">
                                    <i class="fas fa-user-plus me-2"></i> Daftar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Sudah punya akun? <a href="login.php" class="text-primary">Login disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Multi-step form functionality
        document.querySelectorAll('.btn-next').forEach(button => {
            button.addEventListener('click', function() {
                const currentStep = this.closest('.form-step');
                const nextStepId = this.getAttribute('data-next');
                
                // Validate current step before proceeding
                let isValid = true;
                currentStep.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (isValid) {
                    currentStep.classList.remove('active');
                    document.getElementById(nextStepId).classList.add('active');
                    
                    // Update step indicator
                    document.querySelectorAll('.step').forEach((step, index) => {
                        if (index < 1) {
                            step.classList.add('completed');
                            step.classList.remove('active');
                        } else {
                            step.classList.add('active');
                        }
                    });
                    
                    document.querySelectorAll('.step-line').forEach(line => {
                        line.classList.add('active');
                    });
                }
            });
        });
        
        document.querySelectorAll('.btn-prev').forEach(button => {
            button.addEventListener('click', function() {
                const currentStep = this.closest('.form-step');
                const prevStepId = this.getAttribute('data-prev');
                
                currentStep.classList.remove('active');
                document.getElementById(prevStepId).classList.add('active');
                
                // Update step indicator
                document.querySelectorAll('.step').forEach((step, index) => {
                    if (index > 0) {
                        step.classList.remove('active', 'completed');
                    } else {
                        step.classList.add('active');
                        step.classList.remove('completed');
                    }
                });
                
                document.querySelectorAll('.step-line').forEach(line => {
                    line.classList.remove('active');
                });
            });
        });
        
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordHint = document.getElementById('passwordHint');
        
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
        
        // Password match checker
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                passwordMatch.textContent = 'Password tidak cocok';
            } else {
                passwordMatch.textContent = '';
            }
        });
        
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Add animation to form elements
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach((input, index) => {
            input.style.animationDelay = `${index * 0.1}s`;
            input.classList.add('animate__animated', 'animate__fadeInUp');
        });
    </script>
</body>
</html>