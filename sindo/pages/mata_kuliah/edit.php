<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

// Ambil data mata kuliah
$stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
    header('Location: index.php');
    exit();
}

// Ambil jadwal mata kuliah
$stmt = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE mk_id = ?");
$stmt->execute([$mk['id']]);
$jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mk = $_POST['kode_mk'];
    $nama_mk = $_POST['nama_mk'];
    $sks = $_POST['sks'];
    $dosen = $_POST['dosen'];
    $ruangan = $_POST['ruangan'];
    
    if (empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($dosen)) {
        $error = 'Kode, nama, SKS, dan dosen harus diisi';
    } else {
        // Cek kode MK sudah ada (kecuali untuk dirinya sendiri)
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode_mk = ? AND user_id = ? AND id != ?");
        $stmt->execute([$kode_mk, $_SESSION['user_id'], $mk['id']]);
        if ($stmt->fetch()) {
            $error = 'Kode mata kuliah sudah digunakan';
        } else {
            // Update mata kuliah
            $stmt = $pdo->prepare("UPDATE mata_kuliah SET kode_mk = ?, nama_mk = ?, sks = ?, dosen = ?, ruangan = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$kode_mk, $nama_mk, $sks, $dosen, $ruangan, $mk['id'], $_SESSION['user_id']])) {
                // Hapus semua jadwal lama
                $stmt = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE mk_id = ?");
                $stmt->execute([$mk['id']]);
                
                // Tambahkan jadwal baru jika ada
                if (isset($_POST['hari']) && is_array($_POST['hari'])) {
                    foreach ($_POST['hari'] as $index => $hari) {
                        if (!empty($hari) && !empty($_POST['jam_mulai'][$index]) && !empty($_POST['jam_selesai'][$index])) {
                            $stmt = $pdo->prepare("INSERT INTO jadwal_kuliah (mk_id, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$mk['id'], $hari, $_POST['jam_mulai'][$index], $_POST['jam_selesai'][$index]]);
                        }
                    }
                }
                
                $success = 'Mata kuliah berhasil diperbarui';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Gagal memperbarui mata kuliah';
            }
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Edit Mata Kuliah</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="kode_mk" class="form-label">Kode Mata Kuliah</label>
                    <input type="text" class="form-control" id="kode_mk" name="kode_mk" 
                           value="<?= htmlspecialchars($mk['kode_mk']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="nama_mk" class="form-label">Nama Mata Kuliah</label>
                    <input type="text" class="form-control" id="nama_mk" name="nama_mk" 
                           value="<?= htmlspecialchars($mk['nama_mk']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="sks" class="form-label">SKS</label>
                    <select class="form-select" id="sks" name="sks" required>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= $mk['sks'] == $i ? 'selected' : '' ?>>
                                <?= $i ?> SKS
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dosen" class="form-label">Dosen Pengampu</label>
                    <input type="text" class="form-control" id="dosen" name="dosen" 
                           value="<?= htmlspecialchars($mk['dosen']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="ruangan" class="form-label">Ruangan</label>
                    <input type="text" class="form-control" id="ruangan" name="ruangan" 
                           value="<?= htmlspecialchars($mk['ruangan']) ?>">
                </div>
            </div>
        </div>
        
        <h5 class="mt-4">Jadwal Kuliah</h5>
        <div id="jadwal-container">
            <?php if (count($jadwal) > 0): ?>
                <?php foreach ($jadwal as $j): ?>
                    <div class="row mb-3 jadwal-item">
                        <div class="col-md-4">
                            <label class="form-label">Hari</label>
                            <select class="form-select" name="hari[]">
                                <option value="">Pilih Hari</option>
                                <option value="Senin" <?= $j['hari'] == 'Senin' ? 'selected' : '' ?>>Senin</option>
                                <option value="Selasa" <?= $j['hari'] == 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                <option value="Rabu" <?= $j['hari'] == 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                <option value="Kamis" <?= $j['hari'] == 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                <option value="Jumat" <?= $j['hari'] == 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                <option value="Sabtu" <?= $j['hari'] == 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" name="jam_mulai[]" value="<?= $j['jam_mulai'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" name="jam_selesai[]" value="<?= $j['jam_selesai'] ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="row mb-3 jadwal-item">
                    <div class="col-md-4">
                        <label class="form-label">Hari</label>
                        <select class="form-select" name="hari[]">
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai[]">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai[]">
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <button type="button" class="btn btn-secondary btn-sm" id="tambah-jadwal">
            <i class="bi bi-plus"></i> Tambah Jadwal
        </button>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
document.getElementById('tambah-jadwal').addEventListener('click', function() {
    const container = document.getElementById('jadwal-container');
    const newItem = document.querySelector('.jadwal-item').cloneNode(true);
    
    // Clear values in the new item
    newItem.querySelectorAll('select, input').forEach(el => {
        el.value = '';
    });
    
    container.appendChild(newItem);
});
</script>

<?php include '../../includes/footer.php'; ?>