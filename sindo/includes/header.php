<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>SI-NDO - <?= isset($page_title) ? htmlspecialchars($page_title) : 'Manajemen Tugas Mahasiswa' ?></title>
<link rel="icon" href="/sindo/assets/images/logo.png" type="image/png"/>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />

<style>
/* Navbar Premium Gradient + Glass */
.navbar-custom {
    background: linear-gradient(135deg, rgba(13,110,253,0.9), rgba(0, 191, 255, 0.9));
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    transition: all 0.4s ease;
}
.navbar-custom.scrolled {
    background: linear-gradient(135deg, rgba(13,110,253,1), rgba(0, 191, 255, 0.9));
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Brand Style */
.navbar-brand {
    font-weight: 700;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.navbar-brand i {
    font-size: 1.3rem;
}

/* Nav Links */
.nav-link {
    position: relative;
    font-weight: 500;
    color: #fff !important;
    padding: 8px 12px;
    transition: color 0.3s ease;
}
.nav-link:hover {
    color: #ffffffff !important;
}
.nav-link::after {
    content: "";
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 0%;
    height: 3px;
    background: #ffffffff;
    border-radius: 2px;
    transition: width 0.3s ease;
}
.nav-link:hover::after,
.nav-link.active::after {
    width: 60%;
}

/* Dropdown */
.dropdown-menu {
    border-radius: 12px;
    overflow: hidden;
    background-color: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px);
    border: none;
    box-shadow: 0 8px 18px rgba(0,0,0,0.15);
}
.dropdown-item {
    font-weight: 500;
}
.dropdown-item:hover {
    background: rgba(13,110,253,0.1);
}

/* Profile Avatar */
.profile-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd, #42aec1ff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

/* Mobile Optimization */
@media (max-width: 991px) {
    .nav-link::after { display: none; }
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/sindo/index.php">
    <img src="/sindo/assets/images/fontlogo.png" alt="SINDO Logo" style="height: 55px; margin-right: 10px;">
        <a class="navbar-brand" href="/sindo/index.php">
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="/sindo/pages/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'tugas') !== false ? 'active' : '' ?>" href="/sindo/pages/tugas/index.php">Tugas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'jadwal') !== false ? 'active' : '' ?>" href="/sindo/pages/jadwal/index.php">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'acara') !== false ? 'active' : '' ?>" href="/sindo/pages/acara/index.php">Acara</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'mata_kuliah') !== false ? 'active' : '' ?>" href="/sindo/pages/mata_kuliah/index.php">Mata Kuliah</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="profile-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/sindo/pages/profil/index.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/sindo/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/sindo/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/sindo/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-5 pt-5">

<script>
document.addEventListener("scroll", function() {
    const nav = document.querySelector(".navbar-custom");
    if (window.scrollY > 20) {
        nav.classList.add("scrolled");
    } else {
        nav.classList.remove("scrolled");
    }
});
</script>
