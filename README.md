# Finance System - Testing Engineer Webapp

Sistem manajemen keuangan sederhana dengan fitur Chart of Accounts (COA), transaksi, dan laporan Profit & Loss.

## 📋 Daftar Isi
- [Teknologi](#-teknologi)
- [Struktur Project](#-struktur-project)
- [Instalasi](#-instalasi)
- [Konfigurasi](#%EF%B8%8F-konfigurasi)
- [Menjalankan Aplikasi](#%EF%B8%8F-menjalankan-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [API Endpoints](#-api-endpoints)
- [Troubleshooting](#-troubleshooting)

---

## 🛠 Teknologi

### Backend (API)
- **Laravel 10** - PHP Framework
- **MySQL** - Database
- **Laravel Excel** (`maatwebsite/excel`) - Export Excel
- **Scribe** (`knuckleswtf/scribe ^5.10`) - API Documentation

### Frontend
- **Nuxt 3** - Vue.js Framework
- **TypeScript** - Type-safe JavaScript

---

## 📁 Struktur Project

```
.
├── finance-system-api/      # Backend Laravel
│   ├── app/
│   │   ├── Http/Controllers/api/
│   │   ├── Models/
│   │   ├── Exports/
│   │   └── ...
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
│
└── finance-system-frontend/  # Frontend Nuxt
    ├── app/
    │   ├── pages/
    │   └── composables/
    └── nuxt.config.ts
```

---

## 🚀 Instalasi

### Prasyarat
Pastikan sudah terinstall:
- **PHP 8.1+** ([Download](https://www.php.net/downloads))
- **Composer** ([Download](https://getcomposer.org/download/))
- **Node.js 18+** ([Download](https://nodejs.org/))
- **MySQL 8.0+** ([Download](https://dev.mysql.com/downloads/))

### 1. Clone Repository

```bash
git clone https://github.com/dhabyap/Testing-Engineer-Webapp.git
cd Testing-Engineer-Webapp
```

### 2. Setup Backend (Laravel)

```bash
cd finance-system-api

# Install dependencies
composer install

# Copy file environment
copy .env.example .env
# Atau di Linux/Mac: cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Setup Frontend (Nuxt)

```bash
cd ../finance-system-frontend

# Install dependencies
npm install
```

---

## ⚙️ Konfigurasi

### Backend Configuration

Edit file `finance-system-api/.env`:

```env
APP_NAME="Finance System"
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_system
DB_USERNAME=root
DB_PASSWORD=
```

### Buat Database

Buka MySQL dan jalankan:

```sql
CREATE DATABASE finance_system;
```

### Jalankan Migration & Seeder

```bash
cd finance-system-api

# Jalankan migration (buat tabel)
php artisan migrate

# Isi data dummy (opsional)
php artisan db:seed
```

**Data Dummy yang akan dibuat:**
- 4 COA Categories (Salary, Other Income, Family Expense, Transport Expense, dll)
- 5 Chart of Accounts
- 10 Transactions sample

### Frontend Configuration

File `finance-system-frontend/nuxt.config.ts` sudah dikonfigurasi:

```typescript
runtimeConfig: {
  public: {
    apiBaseUrl: "http://localhost:8000/api"
  }
}
```

Jika backend berjalan di port lain, ubah `apiBaseUrl` sesuai kebutuhan.

---

## ▶️ Menjalankan Aplikasi

### 1. Jalankan Backend

```bash
cd finance-system-api
php artisan serve
```

Backend akan berjalan di: **http://localhost:8000**

### 2. Jalankan Frontend

Buka terminal baru:

```bash
cd finance-system-frontend
npm run dev
```

Frontend akan berjalan di: **http://localhost:3000**

### 3. Akses Aplikasi

- **Frontend**: http://localhost:3000
- **API Documentation**: http://localhost:8000/docs
- **API Base URL**: http://localhost:8000/api

---

## ✨ Fitur Utama

### 1. COA Categories Management
- CRUD kategori COA (Income/Expense)
- Endpoint: `/api/coa-categories`

### 2. Chart of Accounts Management
- CRUD akun keuangan
- Relasi dengan kategori
- Endpoint: `/api/chart-of-accounts`

### 3. Transactions Management
- CRUD transaksi (debit/credit)
- Relasi dengan COA
- Endpoint: `/api/transactions`

### 4. Profit & Loss Report
- Laporan laba rugi per periode
- Export ke Excel dengan format per tanggal
- Endpoint: 
  - View: `/api/profit-loss/{year_month}`
  - Export: `/api/profit-loss-export?from=YYYY-MM&to=YYYY-MM`

---

## 📡 API Endpoints

### COA Categories

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/coa-categories` | List semua kategori |
| POST | `/api/coa-categories` | Buat kategori baru |
| GET | `/api/coa-categories/{id}` | Detail kategori |
| PUT | `/api/coa-categories/{id}` | Update kategori |
| DELETE | `/api/coa-categories/{id}` | Hapus kategori |

**Request Body (POST/PUT):**
```json
{
  "name": "Salary",
  "type": "income"
}
```

### Chart of Accounts

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/chart-of-accounts` | List semua COA |
| POST | `/api/chart-of-accounts` | Buat COA baru |
| GET | `/api/chart-of-accounts/{id}` | Detail COA |
| PUT | `/api/chart-of-accounts/{id}` | Update COA |
| DELETE | `/api/chart-of-accounts/{id}` | Hapus COA |

**Request Body (POST/PUT):**
```json
{
  "coa_category_id": 1,
  "account_code": "1001",
  "account_name": "Gaji Pokok"
}
```

### Transactions

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/transactions` | List semua transaksi |
| POST | `/api/transactions` | Buat transaksi baru |
| GET | `/api/transactions/{id}` | Detail transaksi |
| PUT | `/api/transactions/{id}` | Update transaksi |
| DELETE | `/api/transactions/{id}` | Hapus transaksi |

**Request Body (POST/PUT):**
```json
{
  "chart_of_account_id": 1,
  "date": "2026-05-24",
  "description": "Gaji Bulan Mei",
  "debit": 0,
  "credit": 10000000
}
```

### Profit & Loss Report

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/profit-loss/{year_month}` | View report (JSON) |
| GET | `/api/profit-loss-export?from=YYYY-MM&to=YYYY-MM` | Export Excel |

**Contoh:**
```bash
# View report Mei 2026
GET http://localhost:8000/api/profit-loss/2026-05

# Export Excel Juni-Juli 2026
GET http://localhost:8000/api/profit-loss-export?from=2026-06&to=2026-07
```

**Response JSON:**
```json
{
  "period": "2026-05",
  "report": [
    {
      "category_name": "Salary",
      "amount": 12000000,
      "type": "income"
    }
  ],
  "total_income": 12000000,
  "total_expense": 500000,
  "net_income": 11500000
}
```

**Format Excel Export:**
- Kolom per tanggal (01/06/2026, 02/06/2026, ...)
- Baris per kategori
- Summary: Total Income, Total Expense, Net Income

---

## 🐛 Troubleshooting

### Backend Issues

**Error: "SQLSTATE[HY000] [1045] Access denied"**
- Cek username/password MySQL di `.env`
- Pastikan MySQL service berjalan

**Error: "Class 'Maatwebsite\Excel\...' not found"**
```bash
composer require maatwebsite/excel
```

**Port 8000 sudah digunakan**
```bash
php artisan serve --port=8001
```
Jangan lupa update `apiBaseUrl` di frontend.

### Frontend Issues

**Error: "Cannot connect to API"**
- Pastikan backend sudah berjalan
- Cek `apiBaseUrl` di `nuxt.config.ts`
- Cek CORS di backend (`config/cors.php`)

**Port 3000 sudah digunakan**
```bash
npm run dev -- --port 3001
```

### Database Issues

**Migration error**
```bash
# Reset database
php artisan migrate:fresh

# Dengan seeder
php artisan migrate:fresh --seed
```

---

## 📝 Testing

### Test API dengan Postman/Thunder Client

Import collection atau test manual:

1. **Create Category**
```http
POST http://localhost:8000/api/coa-categories
Content-Type: application/json

{
  "name": "Test Income",
  "type": "income"
}
```

2. **Create Transaction**
```http
POST http://localhost:8000/api/transactions
Content-Type: application/json

{
  "chart_of_account_id": 1,
  "date": "2026-05-24",
  "description": "Test Transaction",
  "debit": 0,
  "credit": 1000000
}
```

3. **View Report**
```http
GET http://localhost:8000/api/profit-loss/2026-05
```

---

## 📚 Dokumentasi API Lengkap

Setelah backend berjalan, akses:

**http://localhost:8000/docs**

Dokumentasi interaktif dibuat dengan Scribe, lengkap dengan:
- Semua endpoint
- Request/response examples
- Try it out feature

---

## 📖 Dokumentasi Lengkap Proyek

Proyek ini memiliki dokumentasi komprehensif yang dibagi menjadi beberapa file:

### 1. **[PRD_Simple_Finance_System.md](PRD_Simple_Finance_System.md)** 📋
**Product Requirement Document** - Spesifikasi lengkap sistem:
- Overview & Objective
- User Roles & Permissions
- Database Architecture & Schema
- Feature Requirements (CRUD + P&L Report + Export Excel)
- Technical Specifications (Backend Laravel + Frontend Nuxt)
- Nilai Tambah (Creative Features)
- Timeline & Target Penyerahan (5 Hari)

**Gunakan untuk:** Memahami requirement lengkap proyek, arsitektur database, dan scope pengerjaan.

---

### 2. **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)** 📋
**Step-by-Step Implementation Guide** - Panduan development detail:
- **FASE 1-2 (Backend Setup & API):**
  - Environment setup & project initialization
  - Database migrations & schema
  - Models & Relationships
  - API Resources (untuk standardisasi response)
  - Form Request Validation
  - Controllers & CRUD Endpoints
  - API Routes
  - Profit & Loss Report Logic
  - Database Seeding (Test Data)
  - Laravel Scribe untuk API Documentation

- **FASE 3-4 (Frontend Development):**
  - Nuxt.js project setup
  - API integration & Composables
  - Page components (COA Categories, Chart of Accounts, Transactions, P&L Report)
  - State management dengan Pinia
  - Form handling & validation
  - Data display dengan DataTables

- **FASE 5 (Polish & Deployment):**
  - Excel export implementation
  - Bug fixes & testing
  - Deployment checklist

**Gunakan untuk:** Referensi step-by-step development, memahami setiap keputusan arsitektur, dan implementasi code.

---

### 3. **[ISSUE_SCRIBE_PERMISSION.md](ISSUE_SCRIBE_PERMISSION.md)** 🐛
**Troubleshooting Guide - Laravel Scribe Permission Error** - Solusi untuk error permissions:
- Root cause analysis
- 5+ Solutions:
  1. Clean Up & Run as Administrator (EASIEST)
  2. Change Scribe Configuration
  3. Use Static Type Instead
  4. Manual Move Folder
  5. Use Windows Subsystem for Linux (WSL2)
- Prevention & Best Practices
- Quick Fix Checklist
- Advanced Debug Mode
- Alternative Options

**Gunakan untuk:** Mengatasi error permission saat generate API documentation dengan Scribe di Windows, atau referensi untuk troubleshooting serupa.

---

## 🏗️ Arsitektur Sistem

### Database Schema (3 Tabel Utama):
```
coa_categories
  ├── id (PK)
  ├── name (Unique)
  └── timestamps

chart_of_accounts
  ├── id (PK)
  ├── code (Unique)
  ├── name
  ├── coa_category_id (FK → coa_categories)
  └── timestamps

transactions
  ├── id (PK)
  ├── date
  ├── coa_id (FK → chart_of_accounts)
  ├── description
  ├── debit (Decimal)
  ├── credit (Decimal)
  └── timestamps
```

### API Layers:
- **Models** - Data representation & relationships
- **Form Requests** - Centralized validation
- **Resources** - Standardized JSON responses
- **Controllers** - Business logic & API endpoints
- **Routes** - Endpoint definitions

---

**Tech Stack:**
- Backend: Laravel 10 + MySQL
- Frontend: Nuxt 3 + TypeScript
- Export: Laravel Excel (maatwebsite/excel)
- Documentation: Scribe (knuckleswtf/scribe ^5.10)
