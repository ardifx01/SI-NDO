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
    
    if (empty($judul) || empty($deadline) || empty($mk_id)) {
        $error = 'Semua kolom wajib harus diisi';
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

<div class="container my-5 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100 fade-in-up" style="max-width: 600px;">
        <div class="card-body p-4 p-md-5">
            <h2 class="fw-bold mb-4 text-center text-primary">Tambah Tugas Baru</h2>

            <!-- Error & Success Messages -->
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success rounded-3"><?= $success ?></div>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" id="tugasForm" novalidate>
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" required>
                    <div class="invalid-feedback">Judul tugas harus diisi</div>
                </div>
                
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="mk_id" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                    <select class="form-select" id="mk_id" name="mk_id" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        <?php foreach ($mata_kuliah as $mk): ?>
                            <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Mata kuliah harus dipilih</div>
                </div>
                
                <div class="mb-3">
                    <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                    <div class="invalid-feedback">Deadline harus diisi</div>
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
                
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Simpan</button>
                    <a href="index.php" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tugasForm');
    
    form.addEventListener('submit', function(event) {
        // Validasi form sebelum submit
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            
            // Menambahkan class 'was-validated' untuk menampilkan pesan error
            form.classList.add('was-validated');
            
            // Scroll ke field pertama yang error
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Menghapus pesan error saat user mulai mengisi field
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>

<!-- Custom Styling -->
<style>
    body {
        background: linear-gradient(135deg, #f8f9fa, #e9f2ff);
    }

    .card {
        background: #fff;
        animation: fadeInUp 0.8s ease-out;
    }

    .form-label {
        font-weight: 600;
        color: #333;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.6rem 0.9rem;
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }

    .btn {
        transition: transform 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    /* Animasi fade-in + slide-up */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.8s ease-out;
    }
    
    /* Styling untuk validasi */
    .was-validated .form-control:invalid,
    .was-validated .form-select:invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v2'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .was-validated .form-control:valid,
    .was-validated .form-select:valid {
        border-color: #198754;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
