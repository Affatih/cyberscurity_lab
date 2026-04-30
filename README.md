#  CobaEkspor – E-Commerce Rentan untuk Pelatihan Cybersecurity

**CobaEkspor** adalah aplikasi e-commerce yang **sengaja dibuat rentan** terhadap berbagai jenis serangan siber. Proyek ini dirancang khusus untuk **tujuan pelatihan, edukasi, dan pembelajaran keamanan web (cybersecurity)**. Jangan gunakan di lingkungan produksi atau dengan data asli.

>  **Peringatan Keras:** Aplikasi ini mengandung celah keamanan nyata. Hanya jalankan di lingkungan lokal (localhost) atau virtual machine terisolasi. Penulis tidak bertanggung jawab atas penyalahgunaan kode ini.

---

##  List

- [Fitur Aplikasi](#-fitur-aplikasi)
- [Kerentanan yang Telah Dipasang](#-kerentanan-yang-telah-dipasang)
- [Cara Instalasi & Menjalankan](#-cara-instalasi--menjalankan)
- [Penggunaan & Skenario Latihan](#-penggunaan--skenario-latihan)
- [Struktur Database](#-struktur-database)
- [Kredensial Default](#-kredensial-default)
- [Peringatan & Disclaimer](#-peringatan--disclaimer)
- [Lisensi](#-lisensi)

---

##  Fitur Aplikasi

Aplikasi ini memiliki fitur layaknya toko online sungguhan:
- Halaman **home** dengan daftar produk, kategori, dan pencarian.
- **Login / Register** (dengan hashing password MD5 – lemah).
- **Admin Dashboard** untuk manajemen produk, pengguna, dan pesanan.
- **Keranjang belanja (Cart)** dan proses **checkout**.
- **Riwayat pesanan** dan detail pesanan.
- Desain responsif dengan Bootstrap.

Namun, semua fitur tersebut **sengaja dibangun dengan kode yang tidak aman** untuk tujuan pembelajaran.

---

##  Kerentanan yang Telah Dipasang

Berikut adalah celah keamanan yang telah diimplementasikan beserta lokasinya:

| No | Jenis Kerentanan | Lokasi / Fitur | Tingkat Kesulitan Eksploitasi |
|----|------------------|----------------|-------------------------------|
| 1  | **SQL Injection (Classic)** | Login, Register, Search produk, Order history | Mudah |
| 2  | **Stored XSS** | Form checkout (catatan pesanan) | Mudah |
| 3  | **Reflected XSS** | Parameter `user_id` di halaman order history | Mudah |
| 4  | **IDOR (Insecure Direct Object Reference)** | Lihat pesanan user lain (`/orders?user_id=2`), hapus produk tanpa otorisasi | Sedang |
| 5  | **Open Redirect** | Parameter `redirect` setelah login dan add to cart | Mudah |
| 6  | **Host Header Injection** | Fitur lupa password (`reset link` terbentuk dari `Host` header) | Sedang |
| 7  | **Email Enumeration** | Fitur lupa password (respons berbeda email terdaftar/tidak) | Mudah |
| 8  | **Weak Password Hashing** | Database – password disimpan dengan **MD5** (tanpa salt) | Mudah |
| 9  | **No CSRF Protection** | Semua form (tambah produk, update pesanan, checkout) | Mudah |
| 10 | **Insecure Session Management** | Keranjang disimpan di session tanpa validasi | Sedang |

> Catatan: Beberapa kerentanan seperti Command Injection, SSTI, XXE, LDAP Injection belum diimplementasikan. Anda dapat menambahkannya sendiri di `ToolsController` dan `ApiController`.

---

##  Cara Instalasi & Menjalankan

### Prasyarat
- PHP 7.4 atau lebih tinggi (disarankan PHP 8.x)
- MySQL / MariaDB (misal XAMPP, LAMPP, atau Laragon)
- Composer (optional, untuk autoloading)
- Web browser modern

# Eksperimen SQL Injection

<img width="1366" height="768" alt="Screenshot at 2026-04-30 00-03-12" src="https://github.com/user-attachments/assets/8c38cc97-f1ea-447c-9f7b-fcb1e750f8fa" />

<img width="1366" height="768" alt="Screenshot at 2026-04-30 00-03-23" src="https://github.com/user-attachments/assets/0c14d77b-61ed-4d7e-b559-dc3745535dcb" />


