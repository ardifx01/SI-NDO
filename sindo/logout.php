<?php
require_once __DIR__ . '/includes/auth.php';

// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Catat aktivitas logout sebelum menghapus session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'unknown';
    $logout_time = date('Y-m-d H:i:s');
    
    // Anda bisa menyimpan log logout ke database jika diperlukan
    // Contoh: $pdo->prepare("INSERT INTO user_logs (user_id, activity, created_at) VALUES (?, 'logout', ?)")->execute([$user_id, $logout_time]);
    
    error_log("User logout: {$username} (ID: {$user_id}) at {$logout_time}");
}

// Hancurkan semua data session
$_SESSION = array();

// Jika ingin menghapus cookie session juga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Tampilkan halaman logout dengan animasi sebelum redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
            flex-direction: column;
        }
        
        .logout-container {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
        
        .progress-bar {
            width: 100%;
            height: 5px;
            background-color: #e0e0e0;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            width: 0%;
            background-color: #3498db;
            animation: progress 2s linear forwards;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <div class="message">Sedang Logout...</div>
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
    </div>

    <script>
        // Redirect setelah animasi selesai
        setTimeout(function() {
            window.location.href = "login.php?logout=1";
        }, 2000); // 2 detik sebelum redirect
    </script>
</body>
</html>
<?php
exit();
?>