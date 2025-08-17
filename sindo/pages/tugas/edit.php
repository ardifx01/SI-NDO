<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

// Ambil data tugas
$stmt = $pdo->prepare("SELECT * FROM tugas WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$tugas = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tugas) {
    header('Location: index.php');
    exit();
}

// Ambil daftar mata kuliah
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
        $stmt = $pdo->prepare("UPDATE tugas SET judul = ?, deskripsi = ?, mk_id = ?, deadline = ?, status = ?, prioritas = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$judul, $deskripsi, $mk_id, $deadline, $status, $prioritas, $tugas['id'], $_SESSION['user_id']])) {
            $success = 'Tugas berhasil diperbarui';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal memperbarui tugas';
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Edit Tugas</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="mb-3">
            <label for="judul" class="form-label">Judul Tugas</label>
            <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($tugas['judul']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($tugas['deskripsi']) ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="mk_id" class="form-label">Mata Kuliah</label>
            <select class="form-select" id="mk_id" name="mk_id">
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php foreach ($mata_kuliah as $mk): ?>
                    <option value="<?= $mk['id'] ?>" <?= $mk['id'] == $tugas['mk_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mk['nama_mk']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="deadline" class="form-label">Deadline</label>
            <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                   value="<?= date('Y-m-d\TH:i', strtotime($tugas['deadline'])) ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="Belum Dimulai" <?= $tugas['status'] == 'Belum Dimulai' ? 'selected' : '' ?>>Belum Dimulai</option>
                <option value="Dalam Pengerjaan" <?= $tugas['status'] == 'Dalam Pengerjaan' ? 'selected' : '' ?>>Dalam Pengerjaan</option>
                <option value="Selesai" <?= $tugas['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="prioritas" class="form-label">Prioritas</label>
            <select class="form-select" id="prioritas" name="prioritas">
                <option value="Rendah" <?= $tugas['prioritas'] == 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                <option value="Sedang" <?= $tugas['prioritas'] == 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                <option value="Tinggi" <?= $tugas['prioritas'] == 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>