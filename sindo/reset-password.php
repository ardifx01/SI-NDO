<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$error = '';
$success = '';

if (!isset($_GET['token'])) {
    header('Location: forgot-password.php');
    exit();
}

$token = $_GET['token'];

// Cek validitas token
$stmt = $pdo->prepare("SELECT pr.*, u.email 
                      FROM password_resets pr
                      JOIN users u ON pr.user_id = u.id
                      WHERE pr.token = ? AND pr.expires_at > NOW()");
$stmt->execute([$token]);
$reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset_request) {
    $error = 'Link reset password tidak valid atau sudah kadaluarsa';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset_request) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Password dan konfirmasi password harus diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $reset_request['user_id']])) {
            // Hapus token yang sudah digunakan
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Password berhasil direset. Silakan login dengan password baru Anda.';
        } else {
            $error = 'Gagal mereset password. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sindo - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 12px 0;
            font-weight: 600;
        }
        .auth-logo {
            width: 120px;
            margin-bottom: 20px;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.png" alt="Sindo Logo" class="auth-logo">
                            <h3 class="mb-3">Reset Password</h3>
                            <?php if ($reset_request): ?>
                                <p class="text-muted">Masukkan password baru untuk akun <?= htmlspecialchars($reset_request['email']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
                            </div>
                        <?php elseif ($reset_request): ?>
                            <form method="post" id="resetForm">
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           placeholder="Masukkan password baru (minimal 6 karakter)">
                                    <div class="password-strength mt-2">
                                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                    </div>
                                    <small class="text-muted">Gunakan kombinasi huruf, angka, dan simbol</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                                           placeholder="Masukkan kembali password baru">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    Reset Password
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0"><a href="login.php">Kembali ke halaman login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            const width = strength * 20;
            strengthBar.style.width = width + '%';
            
            // Update color
            if (strength <= 1) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength <= 3) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#198754';
            }
        });
        
        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            
            if (password !== confirm_password) {
                alert('Password dan konfirmasi password tidak sama');
                e.preventDefault();
            }
            
            if (password.length < 6) {
                alert('Password minimal 6 karakter');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>