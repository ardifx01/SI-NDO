<?php
// Konfigurasi session SEBELUM memulai session
$session_name = 'SINDO_SESSION';
$session_lifetime = 86400; // 1 hari
$session_path = '/';
$session_domain = ''; // Isi dengan domain Anda jika perlu
$session_secure = isset($_SERVER['HTTPS']); // Hanya kirim cookie melalui HTTPS jika situs menggunakan HTTPS
$session_httponly = true; // Mencegah akses cookie melalui JavaScript

// Set nama session SEBELUM session_start()
session_name($session_name);

// Set parameter cookie session
session_set_cookie_params(
    $session_lifetime,
    $session_path,
    $session_domain,
    $session_secure,
    $session_httponly
);

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk redirect jika belum login
function redirectIfNotLoggedIn($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit();
    }
}

// Fungsi untuk redirect jika sudah login
function redirectIfLoggedIn($redirect_url = 'index.php') {
    if (isLoggedIn()) {
        header("Location: $redirect_url");
        exit();
    }
}

// [Fungsi-fungsi lainnya tetap sama seperti sebelumnya...]
// ... (getUserData, checkBruteForce, dll)

?>