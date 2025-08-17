<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT t.*, mk.nama_mk 
                      FROM tugas t 
                      LEFT JOIN mata_kuliah mk ON t.mk_id = mk.id 
                      WHERE t.user_id = ? 
                      ORDER BY t.deadline ASC");
$stmt->execute([$_SESSION['user_id']]);
$tugas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_counts = [
    'Belum Dimulai' => 0,
    'Dalam Pengerjaan' => 0,
    'Selesai' => 0
];
foreach ($tugas as $t) {
    $status_counts[$t['status']]++;
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-2 text-primary">Manajemen Tugas</h2>
        <a href="tambah.php" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Tugas
        </a>
    </div>

    <!-- Statistik Tugas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-light">Total</h6>
                    <h4 class="fw-bold"><?= count($tugas) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center shadow-sm border-0 bg-info text-white">
                <div class="card-body">
                    <h6 class="text-light">Belum Dimulai</h6>
                    <h4 class="fw-bold"><?= $status_counts['Belum Dimulai'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center shadow-sm border-0 bg-primary-subtle">
                <div class="card-body">
                    <h6 class="text-primary">Proses</h6>
                    <h4 class="fw-bold text-primary"><?= $status_counts['Dalam Pengerjaan'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center shadow-sm border-0 bg-success-subtle">
                <div class="card-body">
                    <h6 class="text-success">Selesai</h6>
                    <h4 class="fw-bold text-success"><?= $status_counts['Selesai'] ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Tugas -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead style="background-color:#0d6efd; color:white;">
                        <tr>
                            <th>Judul</th>
                            <th>Mata Kuliah</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tugas) > 0): ?>
                            <?php foreach ($tugas as $t): ?>
                                <tr class="<?= strtotime($t['deadline']) < time() && $t['status'] != 'Selesai' ? 'table-danger' : '' ?>">
                                    <td><?= htmlspecialchars($t['judul']) ?></td>
                                    <td><?= $t['nama_mk'] ? htmlspecialchars($t['nama_mk']) : '-' ?></td>
                                    <td>
                                        <?= date('d M Y H:i', strtotime($t['deadline'])) ?>
                                        <?php if (strtotime($t['deadline']) < time() && $t['status'] != 'Selesai'): ?>
                                            <span class="badge bg-danger ms-2">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $t['status'] == 'Selesai' ? 'primary' : 
                                            ($t['status'] == 'Dalam Pengerjaan' ? 'info' : 'secondary') 
                                        ?>">
                                            <?= $t['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $t['prioritas'] == 'Tinggi' ? 'primary' : 
                                            ($t['prioritas'] == 'Sedang' ? 'info' : 'secondary') 
                                        ?>">
                                            <?= $t['prioritas'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="hapus.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus tugas ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada tugas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Custom Styling -->
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13,110,253,0.05);
        transition: background-color 0.2s ease;
    }
    .card {
        border-radius: 12px;
    }
    .btn-group .btn {
        padding: 4px 8px;
    }
</style>
