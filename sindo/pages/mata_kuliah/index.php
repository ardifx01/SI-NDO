<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

// Ambil semua mata kuliah milik user
$stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE user_id = ? ORDER BY nama_mk");
$stmt->execute([$_SESSION['user_id']]);
$mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total SKS
$total_sks = array_sum(array_column($mata_kuliah, 'sks'));
?>

<?php include '../../includes/header.php'; ?>

<div class="container py-4">
    <!-- Judul & Tombol -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <h2 class="fw-bold m-0">Daftar Mata Kuliah</h2>
        <a href="tambah.php" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i> Tambah Mata Kuliah
        </a>
    </div>

    <!-- Info Badge -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <span class="badge bg-primary p-2">Total: <?= count($mata_kuliah) ?> Mata Kuliah</span>
        <span class="badge bg-success p-2"><?= $total_sks ?> SKS</span>
    </div>

    <!-- Tabel -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Ruangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mata_kuliah) > 0): ?>
                            <?php foreach ($mata_kuliah as $mk): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                                    <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                                    <td><?= $mk['sks'] ?></td>
                                    <td><?= htmlspecialchars($mk['dosen']) ?></td>
                                    <td><?= $mk['ruangan'] ? htmlspecialchars($mk['ruangan']) : '-' ?></td>
                                    <td class="text-center">
                                        <a href="edit.php?id=<?= $mk['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $mk['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Hapus" 
                                           onclick="return confirm('Yakin hapus mata kuliah ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle me-1"></i> Belum ada mata kuliah.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Responsif padding di mobile */
    @media (max-width: 576px) {
        .table thead {
            display: none;
        }
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.5rem;
        }
        .table tbody td {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .table tbody td:last-child {
            border-bottom: none;
        }
    }
</style>

<?php include '../../includes/footer.php'; ?>
