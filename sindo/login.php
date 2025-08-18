<?php
session_start();
require_once 'config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $_SESSION['success'] = "Berhasil login, selamat datang " . $user['username'] . "!";

            // Balik ke login.php supaya bisa tampilkan notifikasi dulu
            header('Location: login.php');
            exit();
        } else {
            $error = 'Username atau password salah';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-NDO - Login Mahasiswa</title>
    <link rel="icon" href="/sindo/assets/images/logo.png" type="image/png"/>
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
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container { max-width: 400px; margin: 0 auto; }
        .login-card {
            border: none; border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .login-card:hover { transform: translateY(-5px); }
        .login-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white; padding: 20px; text-align: center;
        }
        .logo {
            font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;
            display: flex; align-items: center; justify-content: center;
        }
        .logo i { margin-right: 10px; }
        .form-control {
            border-radius: 8px; padding: 12px 15px; border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        .btn-login {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none; border-radius: 8px; padding: 12px;
            font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }
        .input-group-text { background-color: transparent; border-right: none; }
        .input-with-icon { border-left: none; }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="login-container animate__animated animate__fadeIn">
            <div class="login-card">
                <div class="login-header">
                     <div class="logo">
                         <img src="assets/images/putih.png" alt="SINDO" class="img-fluid" style="max-height: 70px;">
                        <span>SI-NDO</span>
                    </div>
                    <p class="mb-0">Sistem Informasi Mahasiswa</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Notifikasi sukses -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success animate__animated animate__fadeInDown text-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $_SESSION['success']; ?>
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = "pages/dashboard.php";
                            }, 2000);
                        </script>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Notifikasi error -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control input-with-icon" id="username" name="username" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Ingat saya</label>
                            </div>
                            <!--- belum jadi
                            <a href="forgot-password.php" class="text-decoration-none">Lupa password?</a>--->
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Belum punya akun? <a href="register.php" class="text-primary">Daftar sekarang</a></p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
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
    </script>
</body>
</html>
