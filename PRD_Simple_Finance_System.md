# Product Requirement Document (PRD) – Simple Finance & Profit/Loss Report System

## 1. Overview & Objective
Proyek ini bertujuan untuk membangun aplikasi manajemen keuangan sederhana berbasis web. Sistem ini memungkinkan pengguna mengelola kategori akun (**COA Category**), daftar akun (**Chart of Accounts / COA**), mencatat transaksi keuangan (**Debit/Credit**), serta menghasilkan laporan laba rugi (**Profit & Loss Statement**) bulanan secara otomatis yang dapat diekspor ke format Excel.

Sistem akan dibuat dengan memisahkan backend dan frontend (**Separated Architecture**) untuk fleksibilitas yang lebih tinggi.
* **Backend:** Laravel (RESTful API)
* **Frontend:** Nuxt.js / Vue.js (Sesuai rekomendasi waktu kerja 5 hari)

---

## 2. User Roles & Permissions
Sistem ini menggunakan *single-role user* (Admin/User Umum) yang memiliki akses penuh terhadap data keuangan:
* Mengelola Master Data (Kategori COA & COA).
* Melakukan pencatatan transaksi (Input, Update, Delete).
* Melihat dan mengekspor Laporan Profit & Loss.

---

## 3. Database Architecture & Migrations
Berdasarkan struktur master dan transaksi pada gambar, berikut adalah rancangan skema database:

### 3.1. Table: `coa_categories` (Master Kategori COA)
Menyimpan nama-nama kategori utama laporan keuangan.
* `id` (Bigint, PK, Autoincrement)
* `name` (Varchar, Unique) – *Contoh: Salary, Other Income, Family Expense, dll.*
* `timestamps`

### 3.2. Table: `chart_of_accounts` (Master Chart of Account)
Menyimpan detail sub-akun yang berelasi dengan kategori COA.
* `id` (Bigint, PK, Autoincrement)
* `code` (Varchar, Unique) – *Contoh: 401, 402, 601*
* `name` (Varchar) – *Contoh: Gaji Karyawan, Bensin*
* `coa_category_id` (Bigint, FK to `coa_categories.id` on Delete Cascade)
* `timestamps`

### 3.3. Table: `transactions` (Transaksi)
Menyimpan data mutasi keuangan harian.
* `id` (Bigint, PK, Autoincrement)
* `date` (Date)
* `coa_id` (Bigint, FK to `chart_of_accounts.id`)
* `description` (Text, Nullable)
* `debit` (Decimal/Bigint, Default: 0) – *Untuk pengeluaran/beban (Expense)*
* `credit` (Decimal/Bigint, Default: 0) – *Untuk pendapatan (Income)*
* `timestamps`

---

## 4. Feature Requirements (Fitur & Fungsi)

### 4.1. CRUD Management (Backend API & Frontend UI)
Sistem harus menyediakan halaman/antarmuka untuk mengelola data berikut:
* **Kategori COA:** List, Create, Update, Delete.
* **Chart of Account (COA):** List (menampilkan nama kategori), Create (dropdown kategori), Update, Delete.
* **Transaksi:** List (menampilkan nama COA & tanggal terformat), Create (dropdown COA, input debit/credit terpisah), Update, Delete.

### 4.2. Profit & Loss Report Generator
Fitur inti untuk mengkalkulasi laporan laba rugi secara dinamis berdasarkan periode bulan (`YYYY-MM`).
* **Rumus Income (Kategori Pendapatan):** Diambil dari total `Credit` pada transaksi terkait.
* **Rumus Expense (Kategori Pengeluaran):** Diambil dari total `Debit` pada transaksi terkait.
* **Kalkulasi Total:**
    * `Total Income` = Jumlah Seluruh Kategori Income
    * `Total Expense` = Jumlah Seluruh Kategori Expense
    * `Net Income` = `Total Income` - `Total Expense`
* **Tampilan Antarmuka:** Format tabel matriks per bulan sesuai contoh pada dokumen *Mockup Laporan Profit/Loss*.

### 4.3. Export to Excel
* Menyediakan tombol **"Export Excel"** pada halaman Laporan Profit & Loss.
* Hasil export harus mempertahankan struktur hierarki laporan (Kategori -> Total Income -> Total Expense -> Net Income).

---

## 5. Technical Specifications (Antigravity Stack)

### Backend (Laravel API)
* Gunakan **API Resources** untuk standardisasi respon JSON.
* Gunakan **Form Request Validation** untuk validasi input (misal: validasi kode COA unik, nominal debit/credit bertipe numeric).
* Gunakan library `maatwebsite/excel` atau `laravel-excel` untuk menangani fitur download laporan.

### Frontend (Nuxt.js / Vue.js)
* Gunakan **State Management** (Pinia) untuk menyimpan data global jika diperlukan.
* Gunakan **Axios** atau **$fetch** (Nuxt 3) untuk komunikasi data dengan API Laravel.
* Implementasikan **DataTables** atau komponen tabel responsif yang mendukung *client-side pagination* dan *search filtering*.

---

## 6. Nilai Tambah (Creative Features)
*Sesuai instruksi: "Silahkan tambah fitur sesuka kalian", berikut usulan fitur opsional untuk meningkatkan nilai proyek:*
1. **Dashboard Analytics:** Grafik tren *Net Income* bulanan menggunakan Chart.js / ApexCharts.
2. **Soft Deletes:** Implementasi soft deletes pada master data agar riwayat transaksi lama tidak rusak secara tidak sengaja ketika COA dihapus.
3. **Input Masking:** Format otomatis Rp (Rupiah) saat user mengetik nominal di form transaksi.

---

## 7. Timeline & Target Penyerahan
Karena Anda memilih opsi **Frontend dipisah (Nuxt.js & Vue.js)** yang lebih disukai:
* **Total Alokasi Waktu:** 5 Hari.
* **Hari 1-2:** Setup project, migrasi database, seeder data contoh, dan pembuatan RESTful API (CRUD + P&L Logic).
* **Hari 3-4:** Setup Nuxt.js/Vue.js, integrasi API, pembuatan UI form & dynamic table Profit/Loss.
* **Hari 5:** Fitur Export Excel, improvisasi fitur opsional, bug fixing, dan deployment/siap review.
