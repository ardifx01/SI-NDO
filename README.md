# SI-NDO - Student Organizer Web App

![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php)
![MySQL Version](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql)
![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap)
![License](https://img.shields.io/badge/License-MIT-blue)

Aplikasi web untuk manajemen jadwal kuliah dan tugas mahasiswa berbasis PHP dan MySQL.

## 📌 Daftar Isi
- [Fitur](#-fitur)
- [Teknologi](#-teknologi)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Cara Penggunaan](#-cara-penggunaan)
- [Berkontribusi](#-berkontribusi)
- [Lisensi](#-lisensi)
- [Kontak](#-kontak)

## ✨ Fitur

### 📅 Manajemen Jadwal
- Tampilan kalender mingguan/bulanan
- Input jadwal kuliah
- Pengingat jadwal harian

### 📚 Manajemen Tugas
- Sistem prioritas (Rendah/Sedang/Tinggi)
- Notifikasi deadline
- Filter berdasarkan mata kuliah

### 🔐 Sistem Akun
- Registrasi pengguna
- Login dengan remember me
- Reset password via email

### 📊 Laporan
- Ekspor jadwal ke Excel
- Statistik produktivitas

## 🛠 Teknologi

**Frontend:**
- Bootstrap 5
- JavaScript
- FullCalendar.js
- SweetAlert2

**Backend:**
- PHP 8.0+
- MySQL 5.7+
- PHPMailer

**Keamanan:**
- CSRF Protection
- Password Hashing
- Brute-force Prevention

## ⚙️ Instalasi

### Persyaratan Sistem
- PHP 8.0 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- Composer (untuk dependencies)

### Langkah-langkah
1. Clone repository:
   ```bash
   git clone https://github.com/username/SI-NDO.git
   cd SI-NDO