# PRD: [Nama Produk] v[versi]
## 1. Ringkasan produk
HadirKu adalah aplikasi web matriks presensi cepat yang dirancang untuk guru pengampu kelas kecil. Aplikasi ini memungkinkan guru mencatat, memperbarui, dan memantau status kehadiran 5 murid tetap selama rentang waktu 7 hari ke depan dalam satu layar tunggal. Nilai utamanya adalah efisiensi: menghilangkan pencatatan berulang di lembar manual dan menyajikan rekapitulasi kehadiran instan tanpa beban konfigurasi yang rumit.

## 2. Masalah dan latar belakang
- Masalah: Guru sering membuang waktu saat harus membuka beberapa halaman atau lembar kertas terpisah hanya untuk mencatat dan mengecek rekap kehadiran murid dalam kurun satu minggu.
- Siapa yang mengalaminya: Guru privat, pengajar kelompok belajar, atau wali kelas dengan rombongan belajar terfokus (5 murid).
- Bukti atau pengamatan: Pencatatan absensi harian manual memakan waktu 5–10 menit per sesi dan rawan salah hitung saat rekap akhir pekan.
## 3. Tujuan dan non-tujuan
Tujuan (terukur):
1.Memangkas waktu pencatatan presensi mingguan 5 murid menjadi kurang dari 30 detik dalam satu kali simpan (*single submit*).
2. Menampilkan kalkulasi total kehadiran (*attendance summary*) tiap murid secara otomatis dengan tingkat akurasi 100%.
Non-tujuan (tidak dikerjakan pada versi ini):
1. Sistem autentikasi multi-peran (login kepala sekolah, orang tua, atau siswa).
2. Fitur ekspor berkas ke PDF/Excel.
3. Integrasi notifikasi pesan instan (WhatsApp/SMS).
## 4. Pengguna
**Persona 1: Bu Sarah (Guru Kelas Bimbingan)**
- **Peran:** Pengguna tunggal / Pengajar.
- **Kebutuhan:** Antarmuka ringkas berformat tabel untuk mengubah status presensi murid dalam satu rentang minggu sekaligus.
- **Hambatan:** Tidak memiliki banyak waktu luang di sela jam mengajar dan enggan menggunakan aplikasi dengan hierarki menu yang berbelit-belit.
## 5. Fitur dan prioritas (MoSCoW)
- Must:
  - Matriks tabel 5 murid (baris) x 7 hari (kolom).
  - Pilihan status kehadiran: Hadir (H), Sakit (S), Izin (I), Alpa (A).
  - Tombol simpan massal (*batch save*) untuk memperbarui seluruh data ke database.
  - Kolom kalkulasi otomatis total kehadiran per murid.
- Should:
  - Pesan notifikasi status (*flash message*) saat data berhasil diperbarui.
  - Kode warna visual untuk membedakan status (misal: Hadir hijau, Alpa merah).
- Could:
  - Pemilih tanggal awal (*date picker*) untuk melihat histori minggu-minggu sebelumnya.
- Won’t:
  - Manajemen penambahan/pengurangan murid dinamis via antarmuka.
  - Pengunggahan foto surat keterangan izin atau sakit.
## 6. User story dan kriteria penerimaan
### US-01: Pengisian Presensi Cepat Berbasis Matriks
Sebagai Guru, saya ingin mengisi dan memperbarui status kehadiran 5 murid untuk 7 hari dalam satu tabel sekaligus, agar saya tidak perlu membuka formulir terpisah tiap harinya.
- **AC-01:** Diberikan tabel matriks presensi aktif, ketika guru memilih opsi status pada sel tanggal tertentu lalu menekan tombol "Simpan Presensi", maka data langsung tersimpan ke database dan sistem menampilkan notifikasi "Presensi berhasil disimpan!".
- **AC-02:** Diberikan halaman yang baru saja dimuat ulang, ketika data presensi sebelumnya sudah tersimpan, maka dropdown pada setiap sel menampilkan status terakhir yang tersimpan di basis data.
### US-02: Rekapitulasi Otomatis Kehadiran Siswa
Sebagai Guru, saya ingin melihat akumulasi total kehadiran murid secara langsung di baris tabel yang sama, agar dapat memantau keaktifan siswa tanpa menghitung manual.
- **AC-01:** Diberikan data status presensi 7 hari untuk seorang murid, ketika terdapat N status "Hadir", maka kolom "Total Hadir" pada baris murid tersebut secara otomatis menampilkan angka N (rentang 0 sampai 7).

## 7. Alur pengguna
1. Guru membuka URL aplikasi (`/presensi`).
2. Sistem memuat daftar 5 murid, menghitung 7 tanggal ke depan (mulai dari hari aktif), dan mengambil status presensi yang tersimpan dari database.
3. Guru mengubah status kehadiran murid pada sel tanggal yang bersangkutan menggunakan dropdown.
4. Guru menekan tombol **"Simpan Presensi"**.
5. Sistem memproses penyimpanan massal (*upsert*) ke database dan me-refresh halaman dengan pesan notifikasi sukses.
## 8. Rancangan antarmuka
- Daftar layar:1 layar utama tunggal (`attendance_view.php`)
- Sketsa atau wireframe:
  - **Bagian Atas:** Judul halaman ("Presensi Mingguan Guru"), keterangan rentang tanggal aktif, dan kotak notifikasi (*alert*).
  - **Bagian Tengah:** Tabel matriks Bootstrap:
    - *Header:* No | Nama Siswa | [Hari 1, Tgl] | ... | [Hari 7, Tgl] | Total Hadir
    - *Body:* 5 baris nama siswa, masing-masing berisi 7 dropdown status (Hadir, Sakit, Izin, Alpa) dan 1 kolom kalkulasi total.
  - **Bagian Bawah:** Tombol aksi utama `[Simpan Presensi]` berwarna biru di bawah tabel.
## 9. Kebutuhan fungsional
- **FR-01:** Sistem harus menghitung secara dinamis 7 hari berturut-turut terhitung sejak tanggal server hari ini (`date('Y-m-d')` sampai +6 hari).
- **FR-02:** Formulir harus mengelompokkan input dalam bentuk array multidimensi `attendance[student_id][date] = status`.
- **FR-03:** Sistem harus menjalankan mekanisme `upsert` (perbarui jika kombinasi `student_id` dan `date` sudah ada, buat baru jika belum ada).
- **FR-04:** Sistem harus menghitung rekap total kehadiran murid secara *server-side* pada saat data dimuat ke tampilan.
## 10. Kebutuhan non-fungsional
- NFR-01 (performa):Proses pembacaan data dan penyimpanan massal seluruh matriks selesai dalam waktu <1 detik pada lingkungan lokal.
- NFR-02 (keamanan):Seluruh permintaan POST wajib dilengkapi dengan token perlindungan CSRF dan menggunakan parameter binding untuk mencegah SQL Injection.
- NFR-03 (responsivitas):Tabel harus dibungkus dalam *container responsive* (`table-responsive`) sehingga tetap dapat digulir horizontal pada layar smartphone atau tablet.
## 11. Model data
|Entitas | Atribut | Relasi
|:--- | :--- | :--- |
| **`students`** | `id` (INT, PK, AI)<br>`name` (VARCHAR 100)<br>`created_at` (DATETIME) | One-to-Many ke tabel `attendances` |
| **`attendances`** | `id` (INT, PK, AI)<br>`student_id` (INT, FK)<br>`attendance_date` (DATE)<br>`status` (ENUM: Hadir, Sakit, Izin, Alpa)<br>`updated_at` (DATETIME) | Many-to-One ke tabel `students`<br>*(Unique constraint: `student_id` + `attendance_date`)* |
## 12. Kontrak API
| HTTP Method | Route / Path | Request Payload | Response / Output |
| :--- | :--- | :--- | :--- |
| `GET` | `/presensi` | Tidak ada | Merender HTML halaman matriks presensi beserta data awal |
| `POST` | `/presensi/save` | `csrf_token`<br>`attendance[student_id][date]` | HTTP 302 Redirect ke `/presensi` membawa Flash Message sukses |
## 13. Teknologi
- **Bahasa & Framework:** PHP 8.x dengan CodeIgniter 4 (arsitektur MVC rapi, ringan, dan tidak membutuhkan instalasi dependensi rumit).
- **Database:** MySQL / MariaDB (andal untuk penyimpanan relasional dan konsistensi constraint unik).
- **Frontend:** HTML5, Bootstrap 5 via CDN (antarmuka bersih, cepat dimuat, tanpa proses *compile* seperti Node/Webpack).
## 14. Asumsi, batasan, dan risiko
- **Asumsi:** Guru mengakses aplikasi melalui browser modern (Google Chrome, Microsoft Edge, atau Safari) dengan koneksi internet/lokal aktif.
- **Batasan:** Daftar nama 5 murid di-seed langsung ke database atau diisi manual melalui basis data, bukan melalui formulir registrasi murid.
- **Risiko:** Tabrakan data jika guru membuka dua tab browser sekaligus dan menyimpan data lama di atas data baru (dimitigasi dengan pengurutan `updated_at` dan update berbasis tanggal).
## 15. Metrik keberhasilan
- **Integritas Data:** 100% data presensi yang di-submit tersimpan tanpa data ganda (*duplicate record*) pada tanggal yang sama.
- **Kecepatan Operasional:** Guru dapat menyelesaikan proses presensi 1 minggu penuh dalam sekali klik tanpa ada *fatal error* atau *deadlock* basis data.
## 16. Rencana rilis
- **MVP (Fase 1):** Matriks 5 murid x 7 hari, integrasi penyimpanan MySQL, dan kalkulasi total hadir.
- **v1.0 (Fase 2):** Penambahan validasi input, token CSRF, feedback notifikasi sukses, dan penataan gaya Bootstrap.
- **Setelahnya (Fase 3):** Fitur filter rentang bulan dan tombol unduh laporan presensi format CSV/Excel.
## 17. Riwayat dokumen
| Versi | Tanggal | Penulis | Deskripsi Perubahan |
| :--- | :--- | :--- | :--- |
| 0.1 | 06 November 2026 | Tim Pengembang | Draf awal spesifikasi kebutuhan presensi guru |
| 1.0 | 06 November 2026 | Tim Pengembang | Finalisasi kontrak routing, skema database, dan user story MoSCoW |
