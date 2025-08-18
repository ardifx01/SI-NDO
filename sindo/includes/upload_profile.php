<?php
require_once '../../config/database.php';
require_once 'auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];
    $uploadDir = '../uploads/profiles/';
    
    // Buat folder jika belum ada
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $fileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    // Validasi file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (in_array($_FILES['profile_picture']['type'], $allowedTypes) && 
        $_FILES['profile_picture']['size'] <= $maxSize) {
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            if ($stmt->execute([$fileName, $user_id])) {
                $_SESSION['success'] = 'Foto profil berhasil diupload!';
            } else {
                $_SESSION['error'] = 'Gagal menyimpan ke database';
            }
        } else {
            $_SESSION['error'] = 'Gagal upload file';
        }
    } else {
        $_SESSION['error'] = 'File harus gambar (JPEG/PNG/GIF) dan maksimal 2MB';
    }

    header('Location: ../pages/profile.php');
    exit();
}