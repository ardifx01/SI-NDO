<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Hapus jadwal terkait terlebih dahulu
$stmt = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE mk_id = ?");
$stmt->execute([$_GET['id']]);

// Hapus mata kuliah
$stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);

header('Location: index.php');
exit();
?>