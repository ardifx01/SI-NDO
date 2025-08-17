<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

// Ambil daftar mata kuliah untuk dropdown
$stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $mk_id = $_POST['mk_id'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    $prioritas = $_POST['prioritas'];
    
    if (empty($judul)) {
        $error = 'Judul tugas tidak boleh kosong';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tugas (judul, deskripsi, mk_id, deadline, status, prioritas, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$judul, $deskripsi, $mk_id, $deadline, $status, $prioritas, $_SESSION['user_id']])) {
            $success = 'Tugas berhasil ditambahkan';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal menambahkan tugas';
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Tambah Tugas Baru</h2>
    
    <!-- Error & Success Messages -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post">
        <div class="mb-3">
            <label for="judul" class="form-label">Judul Tugas</label>
            <input type="text" class="form-control" id="judul" name="judul" required>
        </div>
        
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"></textarea>
        </div>
        
        <div class="mb-3">
            <label for="mk_id" class="form-label">Mata Kuliah</label>
            <select class="form-select" id="mk_id" name="mk_id">
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php foreach ($mata_kuliah as $mk): ?>
                    <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="deadline" class="form-label">Deadline</label>
            <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="Belum Dimulai">Belum Dimulai</option>
                <option value="Dalam Pengerjaan">Dalam Pengerjaan</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="prioritas" class="form-label">Prioritas</label>
            <select class="form-select" id="prioritas" name="prioritas">
                <option value="Rendah">Rendah</option>
                <option value="Sedang" selected>Sedang</option>
                <option value="Tinggi">Tinggi</option>
            </select>
        </div>
        
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Custom Styling -->
<style>
    .form-label {
        font-weight: 600;
    }

    .btn {
        font-weight: 600;
    }

    .alert {
        margin-bottom: 1.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.375rem;
    }

    .mb-3 {
        margin-bottom: 1.25rem;
    }

    .container {
        max-width: 800px;
    }
</style>
