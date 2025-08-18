<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $lokasi = $_POST['lokasi'];
    
    if (empty($judul) || empty($tanggal_mulai)) {
        $error = '⚠ Judul dan tanggal mulai harus diisi';
    } elseif (strtotime($tanggal_mulai) < time() && date('Y-m-d', strtotime($tanggal_mulai)) != date('Y-m-d')) {
        $error = '⚠ Tanggal mulai tidak boleh di masa lalu';
    } elseif (!empty($tanggal_selesai) && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
        $error = '⚠ Tanggal selesai harus setelah tanggal mulai';
    } else {
        $stmt = $pdo->prepare("INSERT INTO acara (judul, deskripsi, tanggal_mulai, tanggal_selesai, lokasi, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, $lokasi, $_SESSION['user_id']])) {
            header('Location: index.php?success=1');
            exit();
        } else {
            $error = '❌ Gagal menambahkan acara';
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #ffffffff, #ffffffff);
        background-attachment: fixed;
        font-family: 'Segoe UI', sans-serif;
    }

    .card-custom {
        border: none;
        border-radius: 16px;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeIn 0.6s ease-in-out;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #007bff, #00bfff);
        color: white;
        font-weight: 600;
        padding: 15px 20px;
        font-size: 1.2rem;
        letter-spacing: 0.5px;
        box-shadow: inset 0 -2px 5px rgba(255,255,255,0.2);
    }

    .form-control {
        border-radius: 10px;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #00bfff;
        box-shadow: 0 0 0 0.15rem rgba(0, 191, 255, 0.4);
    }

    .input-group-text {
        background: linear-gradient(135deg, #007bff, #00bfff);
        color: white;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff, #00bfff);
        border: none;
        transition: 0.3s;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 1rem;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0066cc, #00aaff);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.4);
    }

    .btn-secondary {
        border-radius: 10px;
        transition: 0.3s;
    }

    .btn-secondary:hover {
        background-color: #6c757d;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108,117,125,0.4);
    }

    .alert {
        border-radius: 10px;
        font-size: 0.95rem;
    }

    /* Progress Bar */
    .progress {
        height: 6px;
        display: none;
        margin-bottom: 15px;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="bi bi-calendar-plus"></i> Tambah Acara Baru
                </div>
                <div class="card-body p-4">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> Acara berhasil ditambahkan!</div>
                    <?php endif; ?>

                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 100%"></div>
                    </div>
                    
                    <form method="post" id="formAcara">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Acara <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                                <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh: Seminar AI, UTS Matematika" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi detail acara (opsional)"></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Ruang 301, Gedung A, Zoom Meeting">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validasi client-side + progress bar
document.getElementById('formAcara').addEventListener('submit', function(e) {
    const judul = document.getElementById('judul').value.trim();
    const mulai = document.getElementById('tanggal_mulai').value;
    const selesai = document.getElementById('tanggal_selesai').value;
    const progress = document.querySelector('.progress');

    if (!judul || !mulai) {
        alert('Judul dan tanggal mulai wajib diisi');
        e.preventDefault();
        return;
    }
    if (selesai && new Date(selesai) < new Date(mulai)) {
        alert('Tanggal selesai harus setelah tanggal mulai');
        e.preventDefault();
        return;
    }

    progress.style.display = 'block';
});

// Set default waktu
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    document.getElementById('tanggal_mulai').value = new Date(now - offset).toISOString().slice(0, 16);
    const end = new Date(now.getTime() + 60 * 60 * 1000);
    document.getElementById('tanggal_selesai').value = new Date(end - offset).toISOString().slice(0, 16);
});
</script>

<?php include '../../includes/footer.php'; ?>
