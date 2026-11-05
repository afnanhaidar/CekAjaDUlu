# AGENTS.md
## Tujuan proyek
Membangun dan memelihara aplikasi web presensi guru (HadirKu) berbasis CodeIgniter 4 untuk mencatat dan memantau status kehadiran 5 murid tetap selama rentang 7 hari ke depan dalam satu layar matriks tunggal secara cepat dan efisien.

## Stack
- Bahasa: PHP 8.x (antarmuka UI, pesan error, dan dokumentasi menggunakan Bahasa Indonesia)
- Framework: CodeIgniter 4 (MVC) & Bootstrap 5 via CDN
- Database: MySQL / MariaDB
- Versi runtime:PHP >= 8.1
## Cara menjalankan
- **Instal dependensi:** `composer install`
- **Setup database:** Buat database `school_attendance`, salin file `env` menjadi `.env`, lalu sesuaikan konfigurasi database (`database.default.*`).
- **Jalankan migrasi & seed:** `php spark migrate` dan `php spark db:seed StudentSeeder` (atau impor script SQL skema awal).
- **Jalankan aplikasi:** `php spark serve` (akses via browser di `http://localhost:8080/presensi`).
- **Jalankan tes:** `php spark test`
## Konvensi
- Penamaan file dan folder:
  - Controller & Model menggunakan format PascalCase (contoh: `AttendanceController.php`, `StudentModel.php`).
  - Views menggunakan format snake_case (contoh: `attendance_view.php`).
  - Migration menggunakan format penamaan standar bawaan CodeIgniter 4.
- Gaya kode:Standar PSR-12, gunakan *type hinting* eksplisit, hindari *inline CSS* berlebihan, dan gunakan Query Builder / Prepared Statements untuk mencegah celah keamanan SQL Injection.
- Pesan commit: Gunakan format Conventional Commits dengan awalan:
  - `feat:` penambahan fitur baru.
  - `fix:` perbaikan bug.
  - `docs:` pembaruan dokumentasi atau PRD.
  - `test:` penambahan atau perbaikan unit test.
  - `refactor:` restrukturisasi kode tanpa mengubah perilaku fitur.
  - `chore:` pemeliharaan rutin, konfigurasi, atau build tools.
## Batasan
1. **Keamanan Kredensial:** Jangan pernah commit file `.env`, konfigurasi database privat, atau kredensial rahasia apa pun ke Git.
2. **Ketergantungan Eksternal:** Jangan menambah library Composer atau CDN baru di luar stack dasar tanpa instruksi eksplisit.
3. **Fokus Perubahan:** Ubah hanya file yang relevan dengan tugas aktif; jangan menyentuh file core CodeIgniter di folder `system/`.
4. **Verifikasi & Pengujian:** Setiap perubahan logika bisnis atau penambahan endpoint wajib diverifikasi dan disertai skenario pengujian.
5. **Integritas Fitur:** Dilarang merusak, menghapus, atau mengubah fungsi yang sudah berjalan sebelumnya saat menambahkan fitur baru atau melakukan refactor.
6. **Konsistensi Bahasa:** Seluruh elemen antarmuka (label tabel, tombol, notifikasi flash message, dan pesan error) wajib menggunakan Bahasa Indonesia yang komunikatif.
## Definisi selesai
- Seluruh Kriteria Penerimaan (Acceptance Criteria) yang tertera pada `docs/PRD.md` terpenuhi dengan sempurna.
- Matriks presensi dapat membaca data dan menyimpan status kehadiran (Hadir, Sakit, Izin, Alpa) ke database tanpa bug.
- Halaman responsif, proteksi CSRF aktif, dan seluruh alur pengetesan lulus tanpa error fatal.
- Dokumentasi `README.md` dan riwayat perubahan diperbarui jika terdapat perubahan skema database atau routing.
