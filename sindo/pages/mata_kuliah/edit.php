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
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $sks = $_POST['sks'];
    $dosen = trim($_POST['dosen']);
    $ruangan = trim($_POST['ruangan']);
    
    if (empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($dosen)) {
        $error = 'Kode, nama, SKS, dan dosen harus diisi!';
    } else {
        // Cek kode MK sudah ada (kecuali untuk dirinya sendiri)
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode_mk = ? AND user_id = ? AND id != ?");
        $stmt->execute([$kode_mk, $_SESSION['user_id'], $mk['id']]);
        if ($stmt->fetch()) {
            $error = 'Kode mata kuliah sudah digunakan!';
        } else {
            $pdo->beginTransaction();
            try {
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
                    
                    $pdo->commit();
                    $_SESSION['success'] = 'Mata kuliah berhasil diperbarui!';
                    header('Location: index.php');
                    exit();
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Gagal memperbarui mata kuliah: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="bi bi-book me-2"></i>Edit Mata Kuliah</h2>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>
            
            <form method="post" id="form-matkul">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="kode_mk" name="kode_mk" 
                                   value="<?= htmlspecialchars($mk['kode_mk']) ?>" placeholder="MKB001" required>
                            <label for="kode_mk">Kode Mata Kuliah</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nama_mk" name="nama_mk" 
                                   value="<?= htmlspecialchars($mk['nama_mk']) ?>" placeholder="Pemrograman Web" required>
                            <label for="nama_mk">Nama Mata Kuliah</label>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="sks" name="sks" required>
                                <option value="1" <?= $mk['sks'] == 1 ? 'selected' : '' ?>>1 SKS</option>
                                <option value="2" <?= $mk['sks'] == 2 ? 'selected' : '' ?>>2 SKS</option>
                                <option value="3" <?= $mk['sks'] == 3 ? 'selected' : '' ?>>3 SKS</option>
                                <option value="4" <?= $mk['sks'] == 4 ? 'selected' : '' ?>>4 SKS</option>
                            </select>
                            <label for="sks">SKS</label>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="dosen" name="dosen" 
                                   value="<?= htmlspecialchars($mk['dosen']) ?>" placeholder="Dr. John Doe, M.Kom" required>
                            <label for="dosen">Dosen Pengampu</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="ruangan" name="ruangan" 
                                   value="<?= htmlspecialchars($mk['ruangan']) ?>" placeholder="A301">
                            <label for="ruangan">Ruangan</label>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3"><i class="bi bi-calendar-week me-2"></i>Jadwal Kuliah</h5>
                <div id="jadwal-container">
                    <?php if (count($jadwal) > 0): ?>
                        <?php foreach ($jadwal as $j): ?>
                            <div class="row g-3 mb-3 jadwal-item">
                                <div class="col-md-4">
                                    <select class="form-select" name="hari[]" aria-label="Pilih hari">
                                        <option value="">Pilih Hari...</option>
                                        <option value="Senin" <?= $j['hari'] == 'Senin' ? 'selected' : '' ?>>Senin</option>
                                        <option value="Selasa" <?= $j['hari'] == 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                        <option value="Rabu" <?= $j['hari'] == 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                        <option value="Kamis" <?= $j['hari'] == 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                        <option value="Jumat" <?= $j['hari'] == 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                        <option value="Sabtu" <?= $j['hari'] == 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                                    </select>
                                </div>
                                <div class="row align-items-center g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Dari Jam</label>
                                        <input type="time" class="form-control" name="jam_mulai[]" value="<?= $j['jam_mulai'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Sampai Jam</label>
                                        <input type="time" class="form-control" name="jam_selesai[]" value="<?= $j['jam_selesai'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm hapus-jadwal">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="row g-3 mb-3 jadwal-item">
                            <div class="col-md-4">
                                <select class="form-select" name="hari[]" aria-label="Pilih hari">
                                    <option value="">Pilih Hari...</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div class="row align-items-center g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Dari Jam</label>
                                    <input type="time" class="form-control" name="jam_mulai[]">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sampai Jam</label>
                                    <input type="time" class="form-control" name="jam_selesai[]">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-sm hapus-jadwal d-none">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-primary" id="tambah-jadwal">
                        <i class="bi bi-plus-circle"></i> Tambah Jadwal
                    </button>
                    
                    <div>
                        <button type="reset" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-eraser"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jadwalContainer = document.getElementById('jadwal-container');
    
    // Add new schedule row
    document.getElementById('tambah-jadwal').addEventListener('click', function() {
        const newItem = document.querySelector('.jadwal-item').cloneNode(true);
        
        // Clear values
        newItem.querySelectorAll('select, input').forEach(el => el.value = '');
        
        // Show delete button for all except first
        const deleteButtons = jadwalContainer.querySelectorAll('.hapus-jadwal');
        if (deleteButtons.length > 0) {
            deleteButtons.forEach(btn => btn.classList.remove('d-none'));
        }
        
        // Add delete functionality
        const deleteBtn = newItem.querySelector('.hapus-jadwal');
        deleteBtn.classList.remove('d-none');
        deleteBtn.addEventListener('click', function() {
            if (jadwalContainer.children.length > 1) {
                newItem.remove();
            }
        });
        
        jadwalContainer.appendChild(newItem);
    });
    
    // Form validation
    document.getElementById('form-matkul').addEventListener('submit', function(e) {
        let valid = true;
        
        // Validate main form
        const requiredFields = this.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validate schedule
        const scheduleItems = jadwalContainer.querySelectorAll('.jadwal-item');
        scheduleItems.forEach(item => {
            const inputs = item.querySelectorAll('select[name="hari[]"], input[name="jam_mulai[]"], input[name="jam_selesai[]"]');
            const hasValue = Array.from(inputs).some(input => input.value.trim());
            
            if (hasValue) {
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                // Validate time
                const startTime = item.querySelector('input[name="jam_mulai[]"]').value;
                const endTime = item.querySelector('input[name="jam_selesai[]"]').value;
                if (startTime && endTime && startTime >= endTime) {
                    item.querySelector('input[name="jam_selesai[]"]').classList.add('is-invalid');
                    valid = false;
                }
            }
        });
        
        if (!valid) {
            e.preventDefault();
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Harap periksa kembali data yang Anda masukkan!';
            this.querySelector('.card-body').prepend(errorAlert);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    
    // Initialize delete buttons for existing schedules
    const deleteButtons = jadwalContainer.querySelectorAll('.hapus-jadwal');
    if (deleteButtons.length > 1) {
        deleteButtons.forEach(btn => btn.classList.remove('d-none'));
    }
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (jadwalContainer.children.length > 1) {
                btn.closest('.jadwal-item').remove();
            }
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>