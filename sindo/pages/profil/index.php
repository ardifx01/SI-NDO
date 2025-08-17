<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    
    if (empty($nama_lengkap) || empty($email)) {
        $error = 'Nama lengkap dan email harus diisi';
    } else {
        // Cek email sudah digunakan oleh user lain
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan oleh akun lain';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$nama_lengkap, $email, $_SESSION['user_id']])) {
                $success = 'Profil berhasil diperbarui';
                // Update session data
                $_SESSION['username'] = $user['username'];
            } else {
                $error = 'Gagal memperbarui profil';
            }
        }
    }
    
    // Ambil data terbaru
    $user = getUserData($pdo, $_SESSION['user_id']);
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Profil Pengguna</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h4><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                    <p class="text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Mata Kuliah</span>
                            <span class="badge bg-primary">
                                <?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mata_kuliah WHERE user_id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    echo $stmt->fetchColumn();
                                ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tugas Aktif</span>
                            <span class="badge bg-warning">
                                <?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status != 'Selesai'");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    echo $stmt->fetchColumn();
                                ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <span>Acara Mendatang</span>
                            <span class="badge bg-success">
                                <?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM acara WHERE user_id = ? AND tanggal_mulai > NOW()");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    echo $stmt->fetchColumn();
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <a href="ubah_password.php" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-key"></i> Ubah Password
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Edit Profil</h5>
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Daftar</label>
                            <input type="text" class="form-control" 
                                   value="<?= date('d M Y H:i', strtotime($user['created_at'])) ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
            
            <!-- Statistik Aktivitas -->
            <div class="card shadow mt-4">
                <div class="card-body">
                    <h5 class="card-title">Aktivitas Terkini</h5>
                    
                    <div class="list-group">
                        <?php
                        // Ambil 5 tugas terbaru
                        $stmt = $pdo->prepare("SELECT judul, deadline FROM tugas WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute([$_SESSION['user_id']]);
                        $tugas_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($tugas_terbaru as $tugas): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Tugas: <?= htmlspecialchars($tugas['judul']) ?></h6>
                                    <small><?= date('d M', strtotime($tugas['deadline'])) ?></small>
                                </div>
                                <small class="text-muted">Deadline: <?= date('d M Y H:i', strtotime($tugas['deadline'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($tugas_terbaru)): ?>
                            <div class="list-group-item text-muted">Belum ada aktivitas tugas</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>