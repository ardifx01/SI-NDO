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
    $nim = $_POST['nim'];
    $fakultas = $_POST['fakultas'];
    $prodi = $_POST['prodi'];
    $semester = $_POST['semester'];
    
    if (empty($nama_lengkap) || empty($email) || empty($nim) || empty($fakultas) || empty($prodi) || empty($semester)) {
        $error = 'Semua field harus diisi';
    } elseif (!is_numeric($semester)) {
        $error = 'Semester harus berupa angka';
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $nim)) {
        $error = 'NIM hanya boleh berisi huruf dan angka';
    } else {
        // Cek email dan NIM sudah digunakan oleh user lain
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR nim = ?) AND id != ?");
        $stmt->execute([$email, $nim, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = 'Email atau NIM sudah digunakan oleh akun lain';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, nim = ?, fakultas = ?, prodi = ?, semester = ? WHERE id = ?");
            if ($stmt->execute([$nama_lengkap, $email, $nim, $fakultas, $prodi, $semester, $_SESSION['user_id']])) {
                $success = 'Profil berhasil diperbarui';
                // Update session data
                $_SESSION['username'] = $user['username'];
                // Refresh data user
                $user = getUserData($pdo, $_SESSION['user_id']);
            } else {
                $error = 'Gagal memperbarui profil';
            }
        }
    }
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
                        <img src="../../assets/images/logo.png" alt="Logo Sindo" class="img-fluid" style="max-height: 100px;">
                    </div>
                    <h4><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                    <p class="text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                    
                    <div class="mt-4">
                        <div class="mb-3">
                            <h6>Informasi Akademik</h6>
                            <p class="mb-1">
                                <small class="text-muted">NIM:</small><br>
                                <?= $user['nim'] ? htmlspecialchars($user['nim']) : '<span class="text-danger">Belum diisi</span>' ?>
                            </p>
                            <p class="mb-1">
                                <small class="text-muted">Fakultas:</small><br>
                                <?= $user['fakultas'] ? htmlspecialchars($user['fakultas']) : '<span class="text-danger">Belum diisi</span>' ?>
                            </p>
                            <p class="mb-1">
                                <small class="text-muted">Program Studi:</small><br>
                                <?= $user['prodi'] ? htmlspecialchars($user['prodi']) : '<span class="text-danger">Belum diisi</span>' ?>
                            </p>
                            <p class="mb-1">
                                <small class="text-muted">Semester:</small><br>
                                <?= $user['semester'] ? 'Semester '.htmlspecialchars($user['semester']) : '<span class="text-danger">Belum diisi</span>' ?>
                            </p>
                        </div>
                        
                        <hr>
                        
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
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim" 
                                   value="<?= htmlspecialchars($user['nim']) ?>" placeholder="Masukkan NIM" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fakultas" class="form-label">Fakultas</label>
                                <input type="text" class="form-control" id="fakultas" name="fakultas" 
                                       value="<?= htmlspecialchars($user['fakultas']) ?>" placeholder="Contoh: Fakultas Teknik" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="prodi" class="form-label">Program Studi</label>
                                <input type="text" class="form-control" id="prodi" name="prodi" 
                                       value="<?= htmlspecialchars($user['prodi']) ?>" placeholder="Contoh: Teknik Informatika" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <input type="number" class="form-control" id="semester" name="semester" 
                                   value="<?= htmlspecialchars($user['semester']) ?>" min="1" max="14" placeholder="1-14" required>
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