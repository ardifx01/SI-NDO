<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $error = 'Semua field harus diisi';
    } elseif (!password_verify($password_lama, $user['password'])) {
        $error = 'Password lama tidak sesuai';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Password baru dan konfirmasi password tidak sama';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            $success = 'Password berhasil diubah';
            
            // Kirim email notifikasi
            $to = $user['email'];
            $subject = 'Password Anda Telah Diubah - Sindo';
            $message = "Halo " . $user['nama_lengkap'] . ",\n\n";
            $message .= "Password akun Sindo Anda telah berhasil diubah pada " . date('d M Y H:i') . ".\n\n";
            $message .= "Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.\n\n";
            $message .= "Salam,\nTim Sindo";
            $headers = 'From: no-reply@sindo.app';
            
            mail($to, $subject, $message, $headers);
        } else {
            $error = 'Gagal mengubah password';
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title">Ubah Password</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Ubah Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mt-4">
                <div class="card-body">
                    <h5 class="card-title">Tips Password Aman</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Gunakan minimal 8 karakter</li>
                        <li class="list-group-item">Kombinasikan huruf besar, kecil, angka, dan simbol</li>
                        <li class="list-group-item">Jangan gunakan informasi pribadi seperti nama atau tanggal lahir</li>
                        <li class="list-group-item">Jangan gunakan password yang sama untuk banyak akun</li>
                        <li class="list-group-item">Ganti password secara berkala</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>