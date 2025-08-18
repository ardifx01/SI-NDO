<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

// Ambil data acara
$stmt = $pdo->prepare("SELECT * FROM acara WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$acara = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$acara) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $lokasi = $_POST['lokasi'];
    
    if (empty($judul) || empty($tanggal_mulai)) {
        $error = '⚠ Judul dan tanggal mulai harus diisi';
    } elseif (!empty($tanggal_selesai) && strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
        $error = '⚠ Tanggal selesai harus setelah tanggal mulai';
    } else {
        $stmt = $pdo->prepare("UPDATE acara SET judul = ?, deskripsi = ?, tanggal_mulai = ?, tanggal_selesai = ?, lokasi = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, $lokasi, $acara['id'], $_SESSION['user_id']])) {
            header('Location: index.php?success=2');
            exit();
        } else {
            $error = '❌ Gagal memperbarui acara';
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
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }
        /* Responsif tombol */
    .action-btn {
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 10px;
    }

    @media (max-width: 576px) {
        .action-btn {
            width: 100%;        /* tombol full width di mobile */
            font-size: 0.9rem;  /* sedikit lebih kecil */
            padding: 8px 14px;
        }
        .d-flex.justify-content-between {
            flex-direction: column; /* biar tombol jadi ke bawah */
            align-items: stretch;
        }
    }

</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="bi bi-pencil-square"></i> Edit Acara
                </div>
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Acara <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                                <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($acara['judul']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($acara['deskripsi']) ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                        value="<?= date('Y-m-d\TH:i', strtotime($acara['tanggal_mulai'])) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai" 
                                        value="<?= $acara['tanggal_selesai'] ? date('Y-m-d\TH:i', strtotime($acara['tanggal_selesai'])) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= htmlspecialchars($acara['lokasi']) ?>">
                            </div>
                        </div>

                            <div class="d-flex justify-content-between flex-wrap gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary action-btn">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary action-btn">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
