<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

// Ambil semua acara
$stmt = $pdo->prepare("SELECT * FROM acara WHERE user_id = ? ORDER BY tanggal_mulai");
$stmt->execute([$_SESSION['user_id']]);
$acara = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kelompokkan acara berdasarkan bulan
$acara_per_bulan = [];
foreach ($acara as $a) {
    $bulan = date('F Y', strtotime($a['tanggal_mulai']));
    $acara_per_bulan[$bulan][] = $a;
}
?>

<?php include '../../includes/header.php'; ?>

<style>
    /* Tema Biru */
    .bg-primary-custom {
        background: linear-gradient(135deg, #007bff, rgba(0, 191, 255, 0.9));
        color: white;
    }
    .btn-primary-custom {
        background: linear-gradient(135deg, #007bff, rgba(0, 191, 255, 0.9));
        border: none;
        color: white;
    }
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #0056b3, rgba(0, 191, 255, 0.9));
        color: white;
    }
    .event-card {
        border-top: 4px solid #007bff;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .event-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .badge-date {
        background-color: #007bff !important;
    }
    .event-title {
        font-weight: 600;
        color: #0056b3;
    }
    @media (max-width: 576px) {
        .event-title {
            font-size: 1rem;
        }
    }
</style>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="mb-3 mb-sm-0 text-primary">Manajemen Acara</h2>
        <a href="tambah.php" class="btn btn-primary-custom shadow-sm">
            <i class="bi bi-plus-circle"></i> Tambah Acara
        </a>
    </div>

    <?php if (count($acara_per_bulan) > 0): ?>
        <?php foreach ($acara_per_bulan as $bulan => $acara_bulan): ?>
            <div class="mb-4">
                <div class="bg-primary-custom p-2 rounded-top">
                    <h5 class="mb-0"><?= $bulan ?></h5>
                </div>
                <div class="row g-3 mt-0">
                    <?php foreach ($acara_bulan as $a): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card event-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="event-title"><?= htmlspecialchars($a['judul']) ?></h6>
                                        <span class="badge badge-date">
                                            <?= date('d M', strtotime($a['tanggal_mulai'])) ?>
                                        </span>
                                    </div>
                                    <p class="mt-2 mb-3 text-muted" style="min-height: 40px;">
                                        <?= nl2br(htmlspecialchars($a['deskripsi'])) ?>
                                    </p>
                                    <small class="text-secondary d-block mb-3">
                                        ⏰ <?= date('H:i', strtotime($a['tanggal_mulai'])) ?>
                                        <?php if ($a['tanggal_selesai']): ?>
                                            - <?= date('H:i', strtotime($a['tanggal_selesai'])) ?>
                                        <?php endif; ?>
                                        <?php if ($a['lokasi']): ?>
                                            <br>📍 <?= htmlspecialchars($a['lokasi']) ?>
                                        <?php endif; ?>
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus acara ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center p-4 shadow-sm rounded">
            Belum ada acara yang ditambahkan
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
