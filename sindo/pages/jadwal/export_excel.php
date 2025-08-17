<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

// Set header untuk file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="jadwal_kuliah_'.date('Ymd').'.xls"');

// Ambil data jadwal
$stmt = $pdo->prepare("SELECT 
                        j.hari, 
                        TIME_FORMAT(j.jam_mulai, '%H:%i') as jam_mulai,
                        TIME_FORMAT(j.jam_selesai, '%H:%i') as jam_selesai,
                        mk.nama_mk, 
                        mk.kode_mk, 
                        mk.dosen, 
                        mk.sks, 
                        mk.ruangan 
                      FROM jadwal_kuliah j
                      JOIN mata_kuliah mk ON j.mk_id = mk.id
                      WHERE mk.user_id = ?
                      ORDER BY 
                        FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                        j.jam_mulai");
$stmt->execute([$_SESSION['user_id']]);
$jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat tabel HTML untuk Excel
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        td, th { border: 1px solid #dddddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Jadwal Kuliah</h2>
        <p>Nama: '.htmlspecialchars(getUserData($pdo, $_SESSION['user_id'])['nama_lengkap']).'</p>
        <p>Tanggal Export: '.date('d/m/Y H:i').'</p>
    </div>
    
    <table>
        <tr>
            <th>No</th>
            <th>Hari</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Mata Kuliah</th>
            <th>Kode</th>
            <th>SKS</th>
            <th>Dosen</th>
            <th>Ruangan</th>
        </tr>';

$no = 1;
foreach ($jadwal as $j) {
    echo '<tr>
            <td style="text-align: center">'.$no++.'</td>
            <td>'.$j['hari'].'</td>
            <td style="text-align: center">'.$j['jam_mulai'].'</td>
            <td style="text-align: center">'.$j['jam_selesai'].'</td>
            <td>'.htmlspecialchars($j['nama_mk']).'</td>
            <td>'.htmlspecialchars($j['kode_mk']).'</td>
            <td style="text-align: center">'.$j['sks'].'</td>
            <td>'.htmlspecialchars($j['dosen']).'</td>
            <td>'.htmlspecialchars($j['ruangan']).'</td>
          </tr>';
}

echo '</table>
</body>
</html>';
exit();