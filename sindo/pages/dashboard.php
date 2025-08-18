<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redirect if not logged in and get user data
redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

// Date and time configurations
date_default_timezone_set('Asia/Jakarta');

// Day and month names
$nama_hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$nama_hari_pendek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$nama_bulan_pendek = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

/**
 * Fetch data from database with error handling
 */
function fetchData($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get count from database with error handling
 */
function getCount($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return 0;
    }
}

// Current day index (0-6)
$hari_ini = date('w');
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Fetch upcoming tasks
$tugas = fetchData($pdo, 
    "SELECT *, DATEDIFF(deadline, NOW()) as hari_remaining 
     FROM tugas 
     WHERE user_id = ? AND deadline > NOW() 
     ORDER BY deadline ASC 
     LIMIT 5", 
    [$_SESSION['user_id']]
);

// Fetch today's schedule
$jadwal_hari_ini = fetchData($pdo,
    "SELECT j.*, mk.nama_mk, mk.kode_mk, mk.ruangan, mk.dosen 
     FROM jadwal_kuliah j
     JOIN mata_kuliah mk ON j.mk_id = mk.id
     WHERE mk.user_id = ? AND j.hari = ?
     ORDER BY j.jam_mulai ASC",
    [$_SESSION['user_id'], $nama_hari[$hari_ini]]
);

// Fetch upcoming events
$acara = fetchData($pdo,
    "SELECT *, TIMESTAMPDIFF(HOUR, NOW(), tanggal_mulai) as jam_remaining
     FROM acara 
     WHERE user_id = ? AND tanggal_mulai > NOW() 
     ORDER BY tanggal_mulai ASC 
     LIMIT 5",
    [$_SESSION['user_id']]
);

// Fetch today's events
$acara_hari_ini = fetchData($pdo,
    "SELECT * FROM acara 
     WHERE user_id = ? 
     AND (
         (tanggal_mulai BETWEEN ? AND ?) 
         OR (tanggal_selesai BETWEEN ? AND ?)
         OR (? BETWEEN tanggal_mulai AND tanggal_selesai)
     )
     ORDER BY tanggal_mulai ASC",
    [$_SESSION['user_id'], $today_start, $today_end, $today_start, $today_end, date('Y-m-d H:i:s')]
);

// Get task statistics
$tugas_belum = getCount($pdo, 
    "SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Belum Dimulai'",
    [$_SESSION['user_id']]
);
$tugas_proses = getCount($pdo, 
    "SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Dalam Pengerjaan'",
    [$_SESSION['user_id']]
);
$tugas_selesai = getCount($pdo, 
    "SELECT COUNT(*) FROM tugas WHERE user_id = ? AND status = 'Selesai'",
    [$_SESSION['user_id']]
);
$total_matkul = getCount($pdo, 
    "SELECT COUNT(*) FROM mata_kuliah WHERE user_id = ?",
    [$_SESSION['user_id']]
);

// Current time for schedule highlighting
$current_time = date('H:i:s');
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Dashboard</h2>
            <p class="text-muted">
                <?php
                    $hour = (int)date('H');
                    $greeting = match(true) {
                        $hour < 9 => "Selamat pagi",
                        $hour < 15 => "Selamat siang",
                        $hour < 20 => "Selamat sore",
                        default => "Selamat malam"
                    };
                    echo "$greeting, " . htmlspecialchars($user['nama_lengkap']) . "!";
                ?>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <?= $nama_hari[date('w')] ?>, <?= date('j') ?> <?= $nama_bulan[date('n')] ?> <?= date('Y') ?>
            </small>
            <div class="digital-clock fs-5 fw-bold text-primary"></div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $stats = [
            ['title' => 'Total Mata Kuliah', 'count' => $total_matkul, 'icon' => 'bi-book-fill', 'color' => 'primary', 'link' => '../pages/mata_kuliah/index.php'],
            ['title' => 'Tugas Belum Dimulai', 'count' => $tugas_belum, 'icon' => 'bi-hourglass', 'color' => 'warning', 'link' => '../pages/tugas/index.php?filter=belum'],
            ['title' => 'Tugas Dalam Proses', 'count' => $tugas_proses, 'icon' => 'bi-arrow-repeat', 'color' => 'info', 'link' => '../pages/tugas/index.php?filter=proses'],
            ['title' => 'Tugas Selesai', 'count' => $tugas_selesai, 'icon' => 'bi-check-circle', 'color' => 'success', 'link' => '../pages/tugas/index.php?filter=selesai'],
        ];
        
        foreach ($stats as $stat): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="<?= $stat['link'] ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm card-hover h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-<?= $stat['color'] ?> fw-semibold"><?= $stat['title'] ?></h6>
                                <h4 class="fw-bold mb-0"><?= $stat['count'] ?></h4>
                            </div>
                            <div class="icon-circle bg-<?= $stat['color'] ?>-subtle text-<?= $stat['color'] ?> shadow-sm">
                                <i class="bi <?= $stat['icon'] ?> fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <!-- Tugas -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
                    <h6 class="mb-0 fw-bold text-primary">Tugas Mendatang</h6>
                    <div>
                        <a href="../pages/tugas/tambah.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($tugas) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($tugas as $t): 
                                $deadline_class = $t['hari_remaining'] <= 0 ? 'danger' : 
                                                ($t['hari_remaining'] <= 2 ? 'warning' : 'secondary');
                                $priority_class = $t['prioritas'] == 'Tinggi' ? 'danger' : 
                                                ($t['prioritas'] == 'Sedang' ? 'warning' : 'secondary');
                                $status_class = $t['status'] == 'Selesai' ? 'success' : 
                                               ($t['status'] == 'Dalam Pengerjaan' ? 'info' : 'warning');
                                $status_icon = $t['status'] == 'Selesai' ? 'bi-check-circle' : 
                                             ($t['status'] == 'Dalam Pengerjaan' ? 'bi-arrow-repeat' : 'bi-hourglass');
                                $deadline_day = date('w', strtotime($t['deadline']));
                                $deadline_date = date_create($t['deadline']);
                                $deadline_month = date('n', strtotime($t['deadline']));
                            ?>
                                <a href="../pages/tugas/edit.php?id=<?= $t['id'] ?>" class="list-group-item list-group-item-action border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="w-75">
                                            <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($t['judul']) ?></h6>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <span class="badge bg-<?= $status_class ?>">
                                                    <i class="bi <?= $status_icon ?>"></i>
                                                    <?= $t['status'] ?>
                                                </span>
                                                <span class="badge bg-<?= $priority_class ?>">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    <?= $t['prioritas'] ?>
                                                </span>
                                                <small class="text-<?= $deadline_class ?>">
                                                    <i class="bi bi-clock"></i> 
                                                    <?= $t['hari_remaining'] > 0 ? 
                                                        "{$t['hari_remaining']} hari lagi" : 
                                                        "Deadline hari ini" ?>
                                                </small>
                                            </div>
                                            <?php if ($t['deskripsi']): ?>
                                                <p class="small text-muted mb-0 text-truncate">
                                                    <?= htmlspecialchars($t['deskripsi']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">
                                                <?= $nama_hari_pendek[$deadline_day] ?>, <?= date_format($deadline_date, 'd') ?> <?= $nama_bulan_pendek[$deadline_month] ?> <?= date_format($deadline_date, 'Y') ?>
                                            </small>
                                            <small class="text-muted">
                                                <?= date_format($deadline_date, 'H:i') ?> WIB
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-2 opacity-75"></i>
                            <p class="mt-2 text-muted">Tidak ada tugas mendatang</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="../pages/tugas/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                            Lihat Semua Tugas <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Kuliah -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
                    <h6 class="mb-0 fw-bold text-primary">Jadwal Hari Ini (<?= $nama_hari[$hari_ini] ?>)</h6>
                    <div>
                        <a href="../pages/mata_kuliah/tambah.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($jadwal_hari_ini) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($jadwal_hari_ini as $j): 
                                $start_time = strtotime($j['jam_mulai']);
                                $end_time = strtotime($j['jam_selesai']);
                                $current_time = time();
                                
                                $status = match(true) {
                                    $current_time >= $start_time && $current_time <= $end_time => ['text' => 'Sedang Berlangsung', 'class' => 'bg-success'],
                                    $current_time > $end_time => ['text' => 'Selesai', 'class' => 'bg-secondary'],
                                    default => ['text' => 'Akan Dimulai', 'class' => 'bg-info']
                                };
                            ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="badge bg-secondary">
                                            <?= date('H:i', $start_time) ?> - <?= date('H:i', $end_time) ?>
                                        </small>
                                        <small class="badge <?= $status['class'] ?>">
                                            <?= $status['text'] ?>
                                        </small>
                                    </div>
                                    <div class="timeline-content mt-1 p-3 bg-light-subtle rounded border-light border-start border-3">
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($j['nama_mk']) ?>
                                            <small class="text-muted">(<?= htmlspecialchars($j['kode_mk']) ?>)</small>
                                        </h6>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-person-fill"></i> <?= htmlspecialchars($j['dosen']) ?> | 
                                            <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($j['ruangan']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x-fill text-secondary fs-2 opacity-75"></i>
                            <p class="mt-2 text-muted">Tidak ada jadwal hari ini</p>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="../pages/jadwal/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                            Lihat Semua Jadwal <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
       <!-- Acara Hari ini -->
<div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
            <h6 class="mb-0 fw-bold text-primary">
                Acara Hari Ini (<?= $nama_hari[$hari_ini] ?>)
            </h6>
            <div>
                <a href="../pages/acara/tambah.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Tambah
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($acara_hari_ini) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($acara_hari_ini as $a): 
                        $start_time = date('H:i', strtotime($a['tanggal_mulai']));
                        $end_time = $a['tanggal_selesai'] ? date('H:i', strtotime($a['tanggal_selesai'])) : '';
                        
                        $now = time();
                        $start = strtotime($a['tanggal_mulai']);
                        $end = $a['tanggal_selesai'] ? strtotime($a['tanggal_selesai']) : $start;
                        
                        $status = match(true) {
                            $now >= $start && $now <= $end => ['text' => 'Sedang Berlangsung', 'class' => 'danger'],
                            $now < $start => ['text' => 'Akan Datang', 'class' => 'warning'],
                            default => ['text' => 'Selesai', 'class' => 'secondary']
                        };
                    ?>
                        <div class="list-group-item list-group-item-action border-0 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="w-75">
                                    <h6 class="mb-1 fw-semibold">
                                        <?= htmlspecialchars($a['judul']) ?>
                                    </h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <small class="text-primary">
                                            <i class="bi bi-clock"></i> 
                                            <?= $start_time ?>
                                            <?php if ($end_time): ?>
                                                - <?= $end_time ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if ($a['lokasi']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($a['lokasi']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($a['deskripsi']): ?>
                                        <p class="small text-muted mb-0 text-truncate">
                                            <?= htmlspecialchars($a['deskripsi']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $status['class'] ?> text-white">
                                        <?= $status['text'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-event text-secondary fs-2 opacity-75"></i>
                    <p class="mt-2 text-muted">Tidak ada acara hari ini</p>
                </div>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="../pages/acara/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                    Lihat Semua Acara <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

       <!-- Acara Mendatang -->
<div class="col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
            <h6 class="mb-0 fw-bold text-primary">Acara Mendatang</h6>
            <div>
                <a href="../pages/acara/tambah.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Tambah
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($acara) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($acara as $a): 
                        $time_remaining_class = $a['jam_remaining'] < 24 ? 'danger' : 
                                              ($a['jam_remaining'] < 72 ? 'warning' : 'success');
                        $time_remaining_text = $a['jam_remaining'] < 24 ? 'Segera' : 
                                              ($a['jam_remaining'] < 72 ? 'Mendatang' : 'Akan Datang');
                        
                        $start_date = date_create($a['tanggal_mulai']);
                        $start_day = $nama_hari[date('w', strtotime($a['tanggal_mulai']))];
                        $start_month = $nama_bulan[date('n', strtotime($a['tanggal_mulai']))];
                    ?>
                        <div class="list-group-item list-group-item-action border-0 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="w-75">
                                    <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($a['judul']) ?></h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            <?= $start_day ?>, <?= date_format($start_date, 'd') ?> <?= $start_month ?>
                                        </small>
                                        <small class="text-primary">
                                            <i class="bi bi-clock"></i> 
                                            <?= date_format($start_date, 'H:i') ?> WIB
                                        </small>
                                    </div>
                                    <?php if ($a['deskripsi']): ?>
                                        <p class="small text-muted mb-0 text-truncate">
                                            <?= htmlspecialchars($a['deskripsi']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $time_remaining_class ?>">
                                        <?= $time_remaining_text ?>
                                    </span>
                                    <div>
                                        <small class="text-muted">
                                            <?= floor($a['jam_remaining'] / 24) ?> hari <?= $a['jam_remaining'] % 24 ?> jam lagi
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-event text-secondary fs-2 opacity-75"></i>
                    <p class="mt-2 text-muted">Tidak ada acara mendatang</p>
                </div>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="../pages/acara/index.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                    Lihat Semua Acara <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
    .timeline-item:not(:last-child) {
        padding-bottom: 1rem;
        border-left: 2px dashed #dee2e6;
        margin-left: 1rem;
        padding-left: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .animate-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .badge .bi {
        margin-right: 0.25rem;
    }
</style>

<!-- Clock Script -->
<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        document.querySelector('.digital-clock').textContent = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>

<?php include '../includes/footer.php'; ?>