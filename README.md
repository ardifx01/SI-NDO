
![Logo](/sindo/assets/images/logo.png)



# SI-NDO | Smart Insight Notification Deadline Organizer

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
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Buat database baru di MySQL

4. Import struktur database:
   ```bash
   mysql -u username -p nama_database < sindo.sql
   ```

## ⚡ Konfigurasi

1. Salin file konfigurasi:
   ```bash
   cp config/database.example.php config/database.php
   ```

2. Edit file `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'username');
   define('DB_PASS', 'password');
   define('DB_NAME', 'nama_database');
   ```


## 📝 Cara Penggunaan

1. **Membuat Jadwal**
   - Klik tombol "+ Jadwal" di kalender
   - Isi detail jadwal
   - Simpan

2. **Menambahkan Tugas**
   - Buka menu "Tugas"
   - Klik "Tugas Baru"
   - Atur prioritas dan deadline

3. **Ekspor Data**
   - Buka menu "Laporan"
   - Pilih periode
   - Klik "Ekspor ke Excel"

## 🤝 Berkontribusi

Kami menerima kontribusi berupa:
- Pelaporan bug
- Permintaan fitur
- Kode program


## 📜 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://github.com/firdyridho/SI-NDO/blob/main/LICENSE).

## 📞 Kontak

**Pengembang:** Firdy Ridho Fillah  
**Email:** firdyridho9@gmail.com  
**Instagram:** @firdyfillaa_ 

---

⭐ Jika Anda menemukan proyek ini bermanfaat, mohon beri star pada repository ini!

```bash
# Selamat menggunakan SI-NDO!
```
