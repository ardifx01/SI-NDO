<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Hapus jadwal
$stmt = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE id = ?");
if ($stmt->execute([$_GET['id']])) {
    $_SESSION['success'] = 'Jadwal berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus jadwal';
}

header('Location: index.php');
exit();
?>