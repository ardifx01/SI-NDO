<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
$user = getUserData($pdo, $_SESSION['user_id']);

// Ambil semua jadwal dengan info mata kuliah
$stmt = $pdo->prepare("SELECT j.*, mk.nama_mk, mk.kode_mk, mk.dosen, mk.sks, mk.ruangan, mk.id as mk_id 
                      FROM jadwal_kuliah j
                      JOIN mata_kuliah mk ON j.mk_id = mk.id
                      WHERE mk.user_id = ?
                      ORDER BY 
                        FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                        j.jam_mulai");
$stmt->execute([$_SESSION['user_id']]);
$jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kelompokkan jadwal per hari
$jadwal_per_hari = [];
foreach ($jadwal as $j) {
    $jadwal_per_hari[$j['hari']][] = $j;
}

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Hitung statistik
$total_jadwal = count($jadwal);
$matkul_aktif = $pdo->prepare("SELECT COUNT(DISTINCT mk_id) FROM jadwal_kuliah j JOIN mata_kuliah mk ON j.mk_id = mk.id WHERE mk.user_id = ?");
$matkul_aktif->execute([$_SESSION['user_id']]);
$total_matkul = $matkul_aktif->fetchColumn();

// Pemetaan hari ke index FullCalendar (Minggu=0 .. Sabtu=6)
$day_to_index = [
    'Minggu' => 0,
    'Senin'  => 1,
    'Selasa' => 2,
    'Rabu'   => 3,
    'Kamis'  => 4,
    'Jumat'  => 5,
    'Sabtu'  => 6,
];

// Fungsi format tanggal Indonesia (fallback sederhana)
function formatTanggalID($timestamp = null) {
    $bulan = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    $hari = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    $ts = $timestamp ? strtotime($timestamp) : time();
    $d = date('j', $ts);
    $m = (int)date('n', $ts) - 1;
    $y = date('Y', $ts);
    $wd = (int)date('w', $ts);
    return "{$hari[$wd]}, {$d} {$bulan[$m]} {$y}";
}

// Warna badge per hari
$day_badge_classes = [
    'Senin' => 'bg-primary text-white',
    'Selasa' => 'bg-success text-white',
    'Rabu' => 'bg-warning text-dark',
    'Kamis' => 'bg-info text-white',
    'Jumat' => 'bg-danger text-white',
    'Sabtu' => 'bg-secondary text-white',
    'Minggu' => 'bg-dark text-white',
];
?>

<?php include '../../includes/header.php'; ?>

<style>
    /* --- General --- */
    body { background: #f4f7fb; }
    .page-header {
        background: linear-gradient(135deg,#0d6efd,#4361ee);
        color: #fff;
        padding: 1.2rem;
        border-radius: 0.75rem;
        box-shadow: 0 6px 18px rgba(20,30,60,0.12);
        margin-bottom: 1.5rem;
    }
    .page-header h2 { margin: 0; font-weight: 700; }
    .page-header p { margin: 0; opacity: 0.92; }

    /* --- Stat cards --- */
    .stat-card { border-radius: 0.9rem; color: #fff; padding: 1rem; display:flex; justify-content:space-between; align-items:center; }
    .stat-primary { background: linear-gradient(135deg,#0dcaf0,#39d6ff); }
    .stat-success { background: linear-gradient(135deg,#0dcaf0,#39d6ff); }
    .stat-info { background: linear-gradient(135deg,#0dcaf0,#39d6ff); }
    .stat-card i { font-size: 2.2rem; opacity: 0.95; }

    /* --- Cards & list --- */
    .card { border-radius: 1rem; overflow:hidden; background:#fff; box-shadow:0 6px 18px rgba(20,30,60,0.04); transition: transform .25s, box-shadow .25s; }
    .card:hover { transform: translateY(-6px); box-shadow:0 12px 30px rgba(20,30,60,0.06); }
    .card-header { font-weight:700; background: linear-gradient(90deg, rgba(13,110,253,0.12), rgba(102,16,242,0.06)); }

    .list-group-item {
        transition: all .18s ease;
        border-left: 3px solid transparent;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(6px);
        border-radius: 8px;
        margin: 0.28rem 0;
        padding: .65rem;
    }
    .list-group-item:hover {
        background: rgba(13,110,253,0.06);
        transform: translateX(4px);
        border-left-color: #0d6efd;
    }

    /* Badges & small UI */
    .badge-day { font-weight:700; padding:.4rem .65rem; border-radius: 12px; }
    .meta-small { font-size:.85rem; color:#6c757d; }

    /* Calendar */
    #calendar { min-height: 480px; }

    /* --- Responsive adjustments --- */
    @media (max-width: 991.98px) {
        .page-header { padding: 1rem; flex-direction: column; gap: .75rem; align-items: flex-start; }
        .page-header .btn-group, .page-header .btn { width: 100%; }
        .page-header .btn-group .btn { width: auto; }
        .stat-card { padding: .9rem; }
        #calendar { min-height: 420px; }
    }

    @media (max-width: 575.98px) {
        .col-day { padding-left: 0.35rem; padding-right: 0.35rem; }
        .list-group-item { padding: .5rem; }
        .card-header h5 { font-size: 1rem; }
        .meta-small { font-size:.78rem; }
        #calendar { min-height: 360px; }
        .page-header .btn-group { display: flex; flex-direction: column; gap: .5rem; width:100%; }
        .page-header .btn-group a, .page-header .btn-group button { width: 100%; justify-content: center; }
    }

    /* Calendar container horizontal on tiny screens */
    .fc {
        font-family: inherit;
    }
    .calendar-wrap { overflow-x: auto; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2>Jadwal Kuliah</h2>
            <p class="text-white-50 mb-0">Manajemen jadwal perkuliahan Anda</p>
        </div>

        <div class="btn-group" role="group" aria-label="aksi">
            <a href="../mata_kuliah/tambah.php" class="btn btn-light">
                <i class="bi bi-plus-circle me-1"></i> Tambah Mata Kuliah
            </a>
            <button class="btn btn-outline-light" id="toggleView">
                <i class="bi bi-calendar-week"></i> Tampilan Kalender
            </button>
            <a href="export_excel.php" class="btn btn-success">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-4">
            <div class="stat-card stat-primary">
                <div>
                    <div class="small text-white-50">Total Jadwal</div>
                    <div style="font-size:1.5rem;font-weight:800;"><?= (int)$total_jadwal ?></div>
                </div>
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card stat-success">
                <div>
                    <div class="small text-white-50">Mata Kuliah Aktif</div>
                    <div style="font-size:1.5rem;font-weight:800;"><?= (int)$total_matkul ?></div>
                </div>
                <i class="bi bi-journal-bookmark"></i>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card stat-info">
                <div>
                    <div class="small text-white-50">Hari Ini</div>
                    <div style="font-size:1.1rem;font-weight:700;"><?= formatTanggalID() ?></div>
                </div>
                <i class="bi bi-calendar-day"></i>
            </div>
        </div>
    </div>

    <!-- Grid / List Jadwal -->
    <div id="gridView">
        <div class="row g-3">
            <?php foreach ($days as $day): ?>
                <div class="col-12 col-sm-6 col-lg-4 col-day">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($day) ?></h5>
                            <span class="badge <?= $day_badge_classes[$day] ?? 'bg-secondary text-white' ?> badge-day">
                                <?= isset($jadwal_per_hari[$day]) ? count($jadwal_per_hari[$day]) : 0 ?> Jadwal
                            </span>
                        </div>

                        <div class="card-body">
                            <?php if (!empty($jadwal_per_hari[$day])): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($jadwal_per_hari[$day] as $j): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3 text-center" style="min-width:72px;">
                                                    <div class="bg-light rounded p-2">
                                                        <div class="text-primary fw-bold"><?= date('H:i', strtotime($j['jam_mulai'])) ?></div>
                                                        <div class="meta-small"><?= date('H:i', strtotime($j['jam_selesai'])) ?></div>
                                                    </div>
                                                </div>

                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div style="max-width:70%;">
                                                            <div style="font-weight:700;"><?= htmlspecialchars($j['nama_mk']) ?></div>
                                                            <div class="meta-small mt-1">
                                                                <span class="me-2"><i class="bi bi-journal-bookmark-fill"></i> <?= htmlspecialchars($j['kode_mk']) ?></span>
                                                                <span class="me-2"><i class="bi bi-credit-card-fill"></i> <?= (int)$j['sks'] ?> SKS</span>
                                                                <span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($j['ruangan']) ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-dark" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="../mata_kuliah/edit.php?id=<?= urlencode($j['mk_id']) ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                                <li><a class="dropdown-item" href="hapus_jadwal.php?id=<?= urlencode($j['id']) ?>" onclick="return confirm('Hapus jadwal ini?')"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <div class="meta-small"><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($j['dosen']) ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x" style="font-size:1.6rem;"></i>
                                    <p class="mb-2">Tidak ada jadwal</p>
                                    <a href="../mata_kuliah/tambah.php" class="btn btn-sm btn-outline-primary">Tambah Jadwal</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Calendar view (hidden by default) -->
    <div id="calendarView" class="d-none mt-4">
        <div class="card">
            <div class="card-body calendar-wrap">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.min.js'></script>

<script>
(function(){
    const toggleBtn = document.getElementById('toggleView');
    const gridView = document.getElementById('gridView');
    const calendarView = document.getElementById('calendarView');
    let calendarInitialized = false;
    let calendarInstance = null;

    toggleBtn.addEventListener('click', function() {
        if (gridView.classList.contains('d-none')) {
            gridView.classList.remove('d-none');
            calendarView.classList.add('d-none');
            this.innerHTML = '<i class="bi bi-calendar-week"></i> Tampilan Kalender';
        } else {
            gridView.classList.add('d-none');
            calendarView.classList.remove('d-none');
            this.innerHTML = '<i class="bi bi-grid"></i> Tampilan Grid';
            if (!calendarInitialized) {
                initCalendar();
                calendarInitialized = true;
            } else {
                // trigger refresh
                setTimeout(()=>{ calendarInstance.render(); }, 50);
            }
        }
    });

    function initCalendar() {
        const calendarEl = document.getElementById('calendar');

        // events array di-generate dari PHP
        const events = [
            <?php foreach ($jadwal as $j):
                $dayIndex = isset($day_to_index[$j['hari']]) ? $day_to_index[$j['hari']] : 1;
            ?>
            {
                id: '<?= addslashes($j['id']) ?>',
                title: '<?= addslashes($j['nama_mk']) ?>',
                daysOfWeek: [<?= $dayIndex ?>],
                startTime: '<?= date('H:i:s', strtotime($j['jam_mulai'])) ?>',
                endTime: '<?= date('H:i:s', strtotime($j['jam_selesai'])) ?>',
                extendedProps: {
                    kode: '<?= addslashes($j['kode_mk']) ?>',
                    sks: '<?= (int)$j['sks'] ?>',
                    dosen: '<?= addslashes($j['dosen']) ?>',
                    ruangan: '<?= addslashes($j['ruangan']) ?>',
                    mk_id: '<?= addslashes($j['mk_id']) ?>'
                }
            },
            <?php endforeach; ?>
        ];

        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: (window.innerWidth < 600) ? 'timeGridDay' : 'timeGridWeek',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            allDaySlot: false,
            slotMinTime: "06:00:00",
            slotMaxTime: "22:00:00",
            events: events,
            eventClick: function(info) {
                const e = info.event;
                // modal sederhana (Bootstrap modal harus ada di footer include Anda)
                const body = `
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <h5>${escapeHtml(e.title)}</h5>
                            <table class="table table-sm">
                                <tr><th>Kode</th><td>${escapeHtml(e.extendedProps.kode)}</td></tr>
                                <tr><th>SKS</th><td>${escapeHtml(e.extendedProps.sks)}</td></tr>
                                <tr><th>Dosen</th><td>${escapeHtml(e.extendedProps.dosen)}</td></tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table class="table table-sm">
                                <tr><th>Ruangan</th><td>${escapeHtml(e.extendedProps.ruangan)}</td></tr>
                                <tr><th>Hari</th><td>${['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][e.start.getDay()]}</td></tr>
                                <tr><th>Waktu</th><td>${e.start.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})} - ${e.end.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="../mata_kuliah/edit.php?id=${encodeURIComponent(e.extendedProps.mk_id)}" class="btn btn-sm btn-warning me-2"><i class="bi bi-pencil"></i> Edit</a>
                        <a href="hapus_jadwal.php?id=${encodeURIComponent(e.id)}" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jadwal ini?')"><i class="bi bi-trash"></i> Hapus</a>
                    </div>
                `;
                showModal('Detail Jadwal', body);
            }
        });

        calendarInstance.render();

        // resize handling supaya calendar responsif
        window.addEventListener('resize', function() {
            if (!calendarInstance) return;
            const view = (window.innerWidth < 600) ? 'timeGridDay' : 'timeGridWeek';
            calendarInstance.changeView(view);
            setTimeout(()=> calendarInstance.render(), 100);
        });
    }

    // Simple Bootstrap modal creator (jika project Anda sudah include Bootstrap JS)
    function showModal(title, bodyHtml) {
        let modalEl = document.getElementById('jadwalModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'jadwalModal';
            modalEl.className = 'modal fade';
            modalEl.tabIndex = -1;
            modalEl.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                    </div>
                </div>
            `;
            document.body.appendChild(modalEl);
        }
        modalEl.querySelector('.modal-title').innerHTML = title;
        modalEl.querySelector('.modal-body').innerHTML = bodyHtml;
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    // Escape simple helper
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
</script>

<?php include '../../includes/footer.php'; ?>
