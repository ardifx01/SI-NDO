<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

// Ambil data tugas mendatang
$stmt = $pdo->prepare("SELECT *, DATEDIFF(deadline, NOW()) as hari_remaining 
                      FROM tugas 
                      WHERE user_id = ? AND deadline > NOW() 
                      ORDER BY deadline ASC 
                      LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$tugas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jadwal hari ini
$nama_hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][date('w')];
$stmt = $pdo->prepare("SELECT j.*, mk.nama_mk, mk.kode_mk, mk.ruangan, mk.dosen 
                      FROM jadwal_kuliah j
                      JOIN mata_kuliah mk ON j.mk_id = mk.id
                      WHERE mk.user_id = ? AND j.hari = ?
                      ORDER BY j.jam_mulai ASC");
$stmt->execute([$_SESSION['user_id'], $nama_hari]);
$jadwal_hari_ini = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Acara mendatang
$stmt = $pdo->prepare("SELECT *, TIMESTAMPDIFF(HOUR, NOW(), tanggal_mulai) as jam_remaining
                      FROM acara 
                      WHERE user_id = ? AND tanggal_mulai > NOW() 
                      ORDER BY tanggal_mulai ASC 
                      LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$acara = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistik tugas
$tugas_belum = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Belum Dimulai'");
$tugas_belum->execute([$_SESSION['user_id']]);
$tugas_belum = $tugas_belum->fetchColumn();

$tugas_proses = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Dalam Pengerjaan'");
$tugas_proses->execute([$_SESSION['user_id']]);
$tugas_proses = $tugas_proses->fetchColumn();

$tugas_selesai = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Selesai'");
$tugas_selesai->execute([$_SESSION['user_id']]);
$tugas_selesai = $tugas_selesai->fetchColumn();

$total_matkul = $pdo->prepare("SELECT COUNT(*) FROM mata_kuliah WHERE user_id = ?");
$total_matkul->execute([$_SESSION['user_id']]);
$total_matkul = $total_matkul->fetchColumn();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Dashboard Sindo</h2>
            <p class="text-muted">Selamat datang kembali, <?= htmlspecialchars($user['nama_lengkap']) ?>!</p>
        </div>
        <div class="text-end">
            <small class="text-muted"><?= date('l, d F Y') ?></small>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <?php
        $stats = [
            ['title' => 'Total Mata Kuliah', 'count' => $total_matkul, 'icon' => 'bi-book-fill', 'color' => 'primary'],
            ['title' => 'Tugas Belum Dimulai', 'count' => $tugas_belum, 'icon' => 'bi-list-task', 'color' => 'warning'],
            ['title' => 'Tugas Dalam Proses', 'count' => $tugas_proses, 'icon' => 'bi-hourglass-split', 'color' => 'info'],
            ['title' => 'Tugas Selesai', 'count' => $tugas_selesai, 'icon' => 'bi-check-circle-fill', 'color' => 'success'],
        ];
        foreach ($stats as $stat): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm card-hover h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-<?= $stat['color'] ?> fw-semibold"><?= $stat['title'] ?></h6>
                            <h4 class="fw-bold mb-0"><?= $stat['count'] ?></h4>
                        </div>
                        <div class="icon-circle bg-<?= $stat['color'] ?> text-white shadow-sm">
                            <i class="bi <?= $stat['icon'] ?> fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- Tugas Mendatang -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary">Tugas Mendatang</h6>
                    <a href="../pages/tugas/tambah.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Tambah
                    </a>
                </div>
                <div class="card-body">
                    <?php if (count($tugas) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($tugas as $t): ?>
                                <a href="../pages/tugas/edit.php?id=<?= $t['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($t['judul']) ?></h6>
                                        <span class="badge bg-<?= $t['prioritas'] == 'Tinggi' ? 'danger' : ($t['prioritas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                            <?= $t['prioritas'] ?>
                                        </span>
                                    </div>
                                    <p class="mb-1 text-muted small">
                                        <?= $t['hari_remaining'] > 0 ? "Deadline dalam {$t['hari_remaining']} hari" : "Deadline hari ini" ?>
                                    </p>
                                    <?php if ($t['hari_remaining'] <= 2): ?>
                                        <small class="text-<?= $t['hari_remaining'] <= 0 ? 'danger' : 'warning' ?>">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Deadline Mendekati
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-2"></i>
                            <p class="mt-2">Tidak ada tugas mendatang</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="../pages/tugas/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua Tugas</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Hari Ini -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary">Jadwal Hari Ini (<?= $nama_hari ?>)</h6>
                    <a href="../pages/mata_kuliah/tambah.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Tambah
                    </a>
                </div>
                <div class="card-body">
                    <?php if (count($jadwal_hari_ini) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($jadwal_hari_ini as $j): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <small class="badge bg-primary"><?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?></small>
                                    </div>
                                    <div class="timeline-content mt-2 p-3 bg-light rounded">
                                        <h6 class="mb-1"><?= htmlspecialchars($j['nama_mk']) ?></h6>
                                        <p class="text-muted mb-0 small"><?= htmlspecialchars($j['dosen']) ?> | <?= htmlspecialchars($j['ruangan']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x-fill text-secondary fs-2"></i>
                            <p class="mt-2">Tidak ada jadwal hari ini</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="../pages/jadwal/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua Jadwal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acara Mendatang -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary">Acara Mendatang</h6>
                    <a href="../pages/acara/tambah.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Tambah
                    </a>
                </div>
                <div class="card-body">
                    <?php if (count($acara) > 0): ?>
                        <div class="row">
                            <?php foreach ($acara as $a): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-start border-4 border-<?= $a['jam_remaining'] < 24 ? 'danger' : 'info' ?> shadow-sm card-hover">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($a['judul']) ?></h5>
                                            <h6 class="card-subtitle text-muted mb-2">
                                                <?= date('d M Y H:i', strtotime($a['tanggal_mulai'])) ?>
                                                <?php if ($a['tanggal_selesai']): ?> - <?= date('H:i', strtotime($a['tanggal_selesai'])) ?><?php endif; ?>
                                            </h6>
                                            <?php if ($a['deskripsi']): ?>
                                                <p class="small"><?= htmlspecialchars(substr($a['deskripsi'], 0, 100)) ?>...</p>
                                            <?php endif; ?>
                                            <?php if ($a['lokasi']): ?>
                                                <p class="small text-muted"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($a['lokasi']) ?></p>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span class="badge bg-<?= $a['jam_remaining'] < 24 ? 'danger' : ($a['jam_remaining'] < 72 ? 'warning' : 'success') ?>">
                                                    <?= $a['jam_remaining'] < 24 ? 'Segera' : ($a['jam_remaining'] < 72 ? 'Mendatang' : 'Akan Datang') ?>
                                                </span>
                                                <a href="../pages/acara/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-event-fill text-secondary fs-2"></i>
                            <p class="mt-2">Tidak ada acara mendatang</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="../pages/acara/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua Acara</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Style -->
<style>
    .card-hover:hover {
        transform: scale(1.02);
        transition: 0.3s ease;
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    .icon-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
    }
    .timeline {
        position: relative;
    }
</style>

<?php include '../includes/footer.php'; ?>
