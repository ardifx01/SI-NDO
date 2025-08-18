<?php
require_once 'config/database.php';
require_once 'includes/session.php';
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    if (empty($email)) {
        $error = 'Email harus diisi';
    } else {
        // Cek apakah email terdaftar
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate token reset password
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Simpan token ke database
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);
            
            // Kirim email reset password menggunakan PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Ganti dengan SMTP server Anda
                $mail->SMTPAuth   = true;
                $mail->Username   = 'email@anda.com'; // Email pengirim
                $mail->Password   = 'password-email-anda'; // Password email
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('no-reply@sindo.app', 'Sindo App');
                $mail->addAddress($email, $user['username']);
                
                // Content
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
                
                $mail->isHTML(true);
                $mail->Subject = 'Reset Password - Sindo';
                $mail->Body    = '
                    <h2>Reset Password Sindo</h2>
                    <p>Halo ' . htmlspecialchars($user['username']) . ',</p>
                    <p>Kami menerima permintaan reset password untuk akun Anda.</p>
                    <p>Silakan klik tombol berikut untuk mereset password Anda:</p>
                    <p><a href="' . $reset_link . '" style="background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                    <p>Link ini akan kadaluarsa dalam 1 jam.</p>
                    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                    <br>
                    <p>Salam,<br>Tim Sindo</p>
                ';
                
                $mail->AltBody = "Untuk reset password, kunjungi: " . $reset_link;
                
                $mail->send();
                $success = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau spam folder.';
            } catch (Exception $e) {
                $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
            }
        } else {
            $error = 'Email tidak terdaftar dalam sistem';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sindo - Lupa Password</title>
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
                            <h3 class="mb-3">Lupa Password</h3>
                            <p class="text-muted">Masukkan email Anda untuk menerima link reset password</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="Masukkan email yang terdaftar">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Kirim Link Reset Password
                            </button>
                            
                            <div class="text-center mt-4">
                                <p class="mb-0">Ingat password Anda? <a href="login.php">Login disini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>