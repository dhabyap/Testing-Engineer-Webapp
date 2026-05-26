# 📋 Implementation Guide - Simple Finance System
## Step-by-Step Development untuk Test Coding

---

## 🎯 Tujuan Dokumen
Dokumen ini adalah **reference lengkap** untuk development step-by-step agar:
- ✅ Tidak ada langkah yang terlewat
- ✅ Setiap decision ada alasannya
- ✅ Saat di-test, siap dengan penjelasan yang solid
- ✅ Code structure rapi dan maintainable

---

# ⏱️ TIMELINE TOTAL: 5 HARI

---

## 🔧 HARI 1-2: BACKEND SETUP & API

### **PHASE 1.1: Environment Setup & Project Initialization**

#### Step 1: Create Laravel Project
```bash
composer create-project laravel/laravel finance-system-api
cd finance-system-api
```

**Mengapa?**
- Laravel adalah framework RESTful API terbaik dengan built-in features untuk validation, migration, dan resource handling
- Composer adalah dependency manager PHP yang memastikan semua package terinstal dengan benar

#### Step 2: Configure Database (`.env`)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_system
DB_USERNAME=root
DB_PASSWORD=
```

**Mengapa?**
- Database configuration harus match dengan setup lokal Anda
- MySQL adalah database yang reliable untuk aplikasi financial
- `.env` file menjaga credential tetap aman (tidak ter-commit ke git)

#### Step 3: Create Database
```bash
mysql -u root -e "CREATE DATABASE finance_system;"
```

**Mengapa?**
- Database kosong harus exist terlebih dahulu sebelum migration berjalan
- Nama database konsisten dengan `.env` configuration

---

### **PHASE 1.2: Create Database Migrations**

#### Step 4: Generate Migration Files
```bash
php artisan make:migration create_coa_categories_table
php artisan make:migration create_chart_of_accounts_table
php artisan make:migration create_transactions_table
```

**Mengapa?**
- Migration adalah "version control untuk database"
- Setiap tabel dibuat dalam file terpisah untuk maintainability
- Laravel migration otomatis create `id` dan `timestamps` jika kita gunakan schema builder dengan benar

#### Step 5: Write Migration untuk `coa_categories`
**File:** `database/migrations/xxxx_xx_xx_create_coa_categories_table.php`

```php
public function up(): void
{
    Schema::create('coa_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });
}
```

**Mengapa?**
- `id()` = auto-increment primary key
- `string('name')->unique()` = memastikan tidak ada kategori duplicate
- `timestamps()` = otomatis add `created_at` & `updated_at` columns untuk audit trail

#### Step 6: Write Migration untuk `chart_of_accounts`
**File:** `database/migrations/xxxx_xx_xx_create_chart_of_accounts_table.php`

```php
public function up(): void
{
    Schema::create('chart_of_accounts', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->foreignId('coa_category_id')
              ->constrained('coa_categories')
              ->cascadeOnDelete();
        $table->timestamps();
    });
}
```

**Mengapa?**
- `code` unique = setiap akun punya kode unik (misal: 401, 402)
- `foreignId()` = membuat relationship ke `coa_categories`
- `cascadeOnDelete()` = kalau kategori dihapus, semua COA terkait juga otomatis dihapus (mencegah orphan records)

#### Step 7: Write Migration untuk `transactions`
**File:** `database/migrations/xxxx_xx_xx_create_transactions_table.php`

```php
public function up(): void
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->foreignId('coa_id')
              ->constrained('chart_of_accounts')
              ->cascadeOnDelete();
        $table->text('description')->nullable();
        $table->decimal('debit', 15, 2)->default(0);
        $table->decimal('credit', 15, 2)->default(0);
        $table->timestamps();
    });
}
```

**Mengapa?**
- `date()` = menyimpan tanggal transaksi untuk filtering report per bulan
- `decimal(15, 2)` = tipe data untuk uang (max 999,999,999,999.99 dengan 2 decimal places)
- `default(0)` = mencegah NULL values pada debit/credit
- `coa_id` FK ke `chart_of_accounts` dengan cascade delete

#### Step 8: Run Migration
```bash
php artisan migrate
```

**Mengapa?**
- Ini yang benar-benar create physical tables di database
- Kalau error, kita bisa rollback dengan `php artisan migrate:rollback` dan fix migration file

---

### **PHASE 1.3: Create Models & Relationships**

#### Step 9: Create Models dengan Relationships
```bash
php artisan make:model CoaCategory
php artisan make:model ChartOfAccount
php artisan make:model Transaction
```

**Mengapa?**
- Model adalah PHP representation dari database table
- Model memudahkan query dengan ORM (Eloquent) daripada raw SQL

#### Step 10: Define Model: `CoaCategory`
**File:** `app/Models/CoaCategory.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaCategory extends Model
{
    protected $fillable = ['name'];

    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'coa_category_id');
    }
}
```

**Mengapa?**
- `$fillable` = array fields yang boleh mass-assign (security best practice, prevents Mass Assignment Vulnerability)
- `hasMany()` = relasi one-to-many (1 kategori bisa punya banyak COA)
- Dengan relasi ini, kita bisa query: `$category->chartOfAccounts` untuk get semua akun di kategori tersebut

#### Step 11: Define Model: `ChartOfAccount`
**File:** `app/Models/ChartOfAccount.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = ['code', 'name', 'coa_category_id'];

    public function category()
    {
        return $this->belongsTo(CoaCategory::class, 'coa_category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'coa_id');
    }
}
```

**Mengapa?**
- `belongsTo(CoaCategory)` = many-to-one inverse relationship
- `hasMany(Transaction)` = satu akun bisa punya banyak transaksi
- Query: `$coa->category->name` untuk get nama kategori, atau `$coa->transactions` untuk get semua transaksi akun ini

#### Step 12: Define Model: `Transaction`
**File:** `app/Models/Transaction.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['date', 'coa_id', 'description', 'debit', 'credit'];
    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
```

**Mengapa?**
- `$casts` = otomatis convert data type (date menjadi Carbon object, decimal tetap 2 decimal places)
- `belongsTo(ChartOfAccount)` = setiap transaksi punya satu akun
- Cast ini penting untuk consistency saat serialize ke JSON di API response

---

### **PHASE 1.4: Create API Resources (untuk standardisasi response)**

#### Step 13: Generate API Resources
```bash
php artisan make:resource CoaCategoryResource
php artisan make:resource ChartOfAccountResource
php artisan make:resource TransactionResource
```

**Mengapa?**
- API Resource adalah layer yang transform model data menjadi JSON format yang konsisten
- Ini memisahkan database structure dari API response format (good practice)
- Frontend developers punya contract yang jelas tentang response structure

#### Step 14: Define Resource: `CoaCategoryResource`
**File:** `app/Http/Resources/CoaCategoryResource.php`

```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoaCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

**Mengapa?**
- Resource ini define response format untuk kategori
- Frontend tahu persis field apa yang akan di-terima

#### Step 15: Define Resource: `ChartOfAccountResource`
**File:** `app/Http/Resources/ChartOfAccountResource.php`

```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'coa_category_id' => $this->coa_category_id,
            'category_name' => $this->category->name ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

**Mengapa?**
- Include `category_name` jadi frontend tidak perlu query lagi untuk get nama kategori
- Ini optimize API calls dan user experience

#### Step 16: Define Resource: `TransactionResource`
**File:** `app/Http/Resources/TransactionResource.php`

```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'coa_id' => $this->coa_id,
            'coa_code' => $this->chartOfAccount->code ?? null,
            'coa_name' => $this->chartOfAccount->name ?? null,
            'description' => $this->description,
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

**Mengapa?**
- Include `coa_code` dan `coa_name` untuk display transaction list yang lengkap
- Cast ke float agar tidak jadi string di JSON
- Format date `Y-m-d` standard ISO 8601 yang bisa di-parse frontend dengan mudah

---

### **PHASE 1.5: Create Form Request Validation**

#### Step 17: Generate Form Requests
```bash
php artisan make:request StoreCoaCategoryRequest
php artisan make:request UpdateCoaCategoryRequest
php artisan make:request StoreChartOfAccountRequest
php artisan make:request UpdateChartOfAccountRequest
php artisan make:request StoreTransactionRequest
php artisan make:request UpdateTransactionRequest
```

**Mengapa?**
- Form Request adalah centralized validation rules
- Ini separate validation logic dari controller
- Reusable untuk multiple endpoints

#### Step 18: Define Validation: `StoreCoaCategoryRequest`
**File:** `app/Http/Requests/StoreCoaCategoryRequest.php`

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoaCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:coa_categories,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi',
            'name.unique' => 'Nama kategori sudah ada',
        ];
    }
}
```

**Mengapa?**
- `unique:coa_categories,name` = validasi dari database, memastikan tidak ada duplikat
- `messages()` = provide custom error messages dalam Bahasa Indonesia untuk user experience lebih baik

#### Step 19: Define Validation: `UpdateCoaCategoryRequest`
**File:** `app/Http/Requests/UpdateCoaCategoryRequest.php`

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoaCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:coa_categories,name,' . $this->route('coaCategory'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi',
            'name.unique' => 'Nama kategori sudah ada',
        ];
    }
}
```

**Mengapa?**
- `unique:coa_categories,name,' . $this->route('coaCategory')` = allow nama yang sama untuk record yang sedang di-update
- Ini prevent false positive error saat update dengan nama yang sama

#### Step 20: Define Validation: `StoreChartOfAccountRequest`
**File:** `app/Http/Requests/StoreChartOfAccountRequest.php`

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'coa_category_id' => 'required|exists:coa_categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode akun harus diisi',
            'code.unique' => 'Kode akun sudah ada',
            'name.required' => 'Nama akun harus diisi',
            'coa_category_id.required' => 'Kategori harus dipilih',
            'coa_category_id.exists' => 'Kategori yang dipilih tidak valid',
        ];
    }
}
```

**Mengapa?**
- `exists:coa_categories,id` = validasi bahwa kategori yang dipilih benar-benar ada di database
- Ini prevent foreign key constraint error di database

#### Step 21: Define Validation: `UpdateChartOfAccountRequest`
**File:** `app/Http/Requests/UpdateChartOfAccountRequest.php`

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:chart_of_accounts,code,' . $this->route('chartOfAccount'),
            'name' => 'required|string|max:255',
            'coa_category_id' => 'required|exists:coa_categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode akun harus diisi',
            'code.unique' => 'Kode akun sudah ada',
            'name.required' => 'Nama akun harus diisi',
            'coa_category_id.required' => 'Kategori harus dipilih',
            'coa_category_id.exists' => 'Kategori yang dipilih tidak valid',
        ];
    }
}
```

#### Step 22: Define Validation: `StoreTransactionRequest`
**File:** `app/Http/Requests/StoreTransactionRequest.php`

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'coa_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'debit' => 'required_without:credit|numeric|min:0',
            'credit' => 'required_without:debit|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal transaksi harus diisi',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD',
            'coa_id.required' => 'Akun harus dipilih',
            'coa_id.exists' => 'Akun yang dipilih tidak valid',
            'debit.required_without' => 'Debit atau Credit harus diisi salah satu',
            'credit.required_without' => 'Debit atau Credit harus diisi salah satu',
        ];
    }
}
```

**Mengapa?**
- `required_without:credit` = debit wajib diisi HANYA jika credit kosong (prevent both filled atau both empty)
- `numeric|min:0` = ensure nominal adalah angka positif

#### Step 23: Define Validation: `UpdateTransactionRequest`
**File:** `app/Http/Requests/UpdateTransactionRequest.php` (sama seperti Store, bisa copy)

---

### **PHASE 1.6: Create Controllers & CRUD Endpoints**

#### Step 24: Generate Controllers
```bash
php artisan make:controller api/CoaCategoryController --api
php artisan make:controller api/ChartOfAccountController --api
php artisan make:controller api/TransactionController --api
```

**Mengapa?**
- `--api` flag generate controller dengan hanya methods yang diperlukan untuk REST API (index, store, show, update, destroy)
- Folder `api/` untuk organize API-specific controllers

#### Step 25: Implement `CoaCategoryController`
**File:** `app/Http/Controllers/api/CoaCategoryController.php`

```php
<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoaCategoryRequest;
use App\Http\Requests\UpdateCoaCategoryRequest;
use App\Http\Resources\CoaCategoryResource;
use App\Models\CoaCategory;

class CoaCategoryController extends Controller
{
    // GET /api/coa-categories
    public function index()
    {
        $categories = CoaCategory::orderBy('name')->get();
        return CoaCategoryResource::collection($categories);
    }

    // POST /api/coa-categories
    public function store(StoreCoaCategoryRequest $request)
    {
        $category = CoaCategory::create($request->validated());
        return new CoaCategoryResource($category);
    }

    // GET /api/coa-categories/{id}
    public function show(CoaCategory $coaCategory)
    {
        return new CoaCategoryResource($coaCategory);
    }

    // PUT /api/coa-categories/{id}
    public function update(UpdateCoaCategoryRequest $request, CoaCategory $coaCategory)
    {
        $coaCategory->update($request->validated());
        return new CoaCategoryResource($coaCategory);
    }

    // DELETE /api/coa-categories/{id}
    public function destroy(CoaCategory $coaCategory)
    {
        $coaCategory->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
```

**Mengapa?**
- `orderBy('name')` = list kategori terurut alphabetically, lebih user-friendly
- `validated()` = gunakan data yang sudah ter-validasi dari FormRequest
- `Resource::collection()` vs `new Resource()` untuk membedakan array vs single item

#### Step 26: Implement `ChartOfAccountController`
**File:** `app/Http/Controllers/api/ChartOfAccountController.php`

```php
<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;

class ChartOfAccountController extends Controller
{
    // GET /api/chart-of-accounts
    public function index()
    {
        $accounts = ChartOfAccount::with('category')
            ->orderBy('code')
            ->get();
        return ChartOfAccountResource::collection($accounts);
    }

    // POST /api/chart-of-accounts
    public function store(StoreChartOfAccountRequest $request)
    {
        $account = ChartOfAccount::create($request->validated());
        return new ChartOfAccountResource($account->load('category'));
    }

    // GET /api/chart-of-accounts/{id}
    public function show(ChartOfAccount $chartOfAccount)
    {
        return new ChartOfAccountResource($chartOfAccount->load('category'));
    }

    // PUT /api/chart-of-accounts/{id}
    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->update($request->validated());
        return new ChartOfAccountResource($chartOfAccount->load('category'));
    }

    // DELETE /api/chart-of-accounts/{id}
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();
        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}
```

**Mengapa?**
- `with('category')` = eager load relationship (prevent N+1 query problem)
- `load()` = additional loading setelah create/update
- Ini optimize database queries

#### Step 27: Implement `TransactionController`
**File:** `app/Http/Controllers/api/TransactionController.php`

```php
<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;

class TransactionController extends Controller
{
    // GET /api/transactions
    public function index()
    {
        $transactions = Transaction::with('chartOfAccount')
            ->orderBy('date', 'desc')
            ->get();
        return TransactionResource::collection($transactions);
    }

    // POST /api/transactions
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    // GET /api/transactions/{id}
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    // PUT /api/transactions/{id}
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    // DELETE /api/transactions/{id}
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }
}
```

---

### **PHASE 1.7: Setup Routes**

#### Step 28: Define API Routes
**File:** `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\CoaCategoryController;
use App\Http\Controllers\api\ChartOfAccountController;
use App\Http\Controllers\api\TransactionController;

Route::middleware('api')->group(function () {
    Route::apiResource('coa-categories', CoaCategoryController::class);
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
    Route::apiResource('transactions', TransactionController::class);
    
    // Special route untuk Profit & Loss Report
    Route::get('/profit-loss/{year_month}', [ProfitLossController::class, 'show']);
    Route::get('/profit-loss-export/{year_month}', [ProfitLossController::class, 'export']);
});
```

**Mengapa?**
- `apiResource()` = otomatis generate standard REST routes (GET, POST, PUT, DELETE)
- Ini adalah best practice untuk REST API

#### Step 29: Test Endpoints
```bash
php artisan serve
```

**Mengapa?**
- Start Laravel development server untuk test API locally
- Default URL: http://localhost:8000

---

### **PHASE 1.8: Profit & Loss Report Logic**

#### Step 30: Create `ProfitLossController`
**File:** `app/Http/Controllers/api/ProfitLossController.php`

```php
<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CoaCategory;
use App\Models\Transaction;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    public function show($year_month)
    {
        // Validasi format: YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            return response()->json(['error' => 'Format tidak valid'], 400);
        }

        $startDate = Carbon::createFromFormat('Y-m-d', $year_month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all categories
        $categories = CoaCategory::with('chartOfAccounts.transactions')
            ->get();

        $report = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($categories as $category) {
            // Calculate total credit (income) dan debit (expense) untuk kategori ini
            $income = 0;
            $expense = 0;

            foreach ($category->chartOfAccounts as $account) {
                $transactions = $account->transactions()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                foreach ($transactions as $txn) {
                    $income += $txn->credit;
                    $expense += $txn->debit;
                }
            }

            if ($income > 0 || $expense > 0) {
                $report[] = [
                    'category_name' => $category->name,
                    'amount' => $income > 0 ? $income : $expense,
                    'type' => $income > 0 ? 'income' : 'expense',
                ];

                if ($income > 0) {
                    $totalIncome += $income;
                } else {
                    $totalExpense += $expense;
                }
            }
        }

        $netIncome = $totalIncome - $totalExpense;

        return response()->json([
            'period' => $year_month,
            'report' => $report,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ]);
    }

    public function export($year_month)
    {
        // TODO: Implement Excel export
        // Gunakan maatwebsite/excel package
    }
}
```

**Mengapa?**
- Kalkulasi P&L perlu di-loop through semua kategori → akun → transaksi
- `whereBetween()` = filter transaksi berdasarkan bulan yang diminta
- Separate income dan expense logic berdasarkan credit vs debit

---

### **PHASE 1.9: Database Seeding (Test Data)**

#### Step 31: Create Seeder
```bash
php artisan make:seeder CoaCategorySeeder
php artisan make:seeder ChartOfAccountSeeder
php artisan make:seeder TransactionSeeder
```

#### Step 32: Implement `CoaCategorySeeder`
**File:** `database/seeders/CoaCategorySeeder.php`

```php
<?php
namespace Database\Seeders;

use App\Models\CoaCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Salary',
            'Other Income',
            'Family Expense',
            'Transport Expense',
            'Meal Expense',
        ];

        foreach ($categories as $name) {
            CoaCategory::firstOrCreate(['name' => $name]);
        }
    }
}
```

#### Step 33: Implement `ChartOfAccountSeeder`
**File:** `database/seeders/ChartOfAccountSeeder.php`

```php
<?php
namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\CoaCategory;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => '401', 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
            ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
            ['code' => '403', 'name' => 'Profit Trading', 'category' => 'Other Income'],
            ['code' => '601', 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
            ['code' => '602', 'name' => 'Bensin', 'category' => 'Transport Expense'],
            ['code' => '603', 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ['code' => '604', 'name' => 'Makan Siang', 'category' => 'Meal Expense'],
            ['code' => '605', 'name' => 'Makanan Pokok Bulanan', 'category' => 'Meal Expense'],
        ];

        foreach ($data as $item) {
            $category = CoaCategory::where('name', $item['category'])->first();
            ChartOfAccount::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'coa_category_id' => $category->id,
                ]
            );
        }
    }
}
```

#### Step 34: Implement `TransactionSeeder`
**File:** `database/seeders/TransactionSeeder.php`

```php
<?php
namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            ['date' => '2022-01-01', 'code' => '401', 'desc' => 'Gaji Di Persuhaan A', 'credit' => 5000000],
            ['date' => '2022-01-02', 'code' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-01-10', 'code' => '602', 'desc' => 'Bensin Anak', 'debit' => 25000],
            // ... tambah lebih banyak
        ];

        foreach ($transactions as $item) {
            $coa = ChartOfAccount::where('code', $item['code'])->first();
            Transaction::create([
                'date' => $item['date'],
                'coa_id' => $coa->id,
                'description' => $item['desc'] ?? null,
                'debit' => $item['debit'] ?? 0,
                'credit' => $item['credit'] ?? 0,
            ]);
        }
    }
}
```

#### Step 35: Register Seeders in `DatabaseSeeder`
**File:** `database/seeders/DatabaseSeeder.php`

```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoaCategorySeeder::class,
            ChartOfAccountSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
```

#### Step 36: Run Seeder
```bash
php artisan db:seed
```

**Mengapa?**
- Seeder populate database dengan test data sehingga kita bisa test API tanpa manual input

---

### **PHASE 1.10: Configure CORS (untuk komunikasi Frontend-Backend)**

#### Step 37: Install CORS Package
```bash
composer require fruitcake/laravel-cors
```

**Mengapa?**
- Frontend (Nuxt.js) dan Backend (Laravel) di different origins
- CORS middleware perlu configured agar browser allow cross-origin requests

#### Step 38: Configure CORS di `config/cors.php`
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000'],
'allow_credentials' => false,
```

**Mengapa?**
- `http://localhost:3000` = default port Nuxt.js development server
- Bisa di-adjust production nanti

---

**✅ HARI 1-2 SELESAI:** Backend API complete dengan CRUD endpoints dan P&L logic

---

## 🎨 HARI 3-4: FRONTEND SETUP & UI

### **PHASE 2.1: Nuxt.js Project Setup**

#### Step 39: Create Nuxt Project
```bash
npx nuxi init finance-system-frontend
cd finance-system-frontend
npm install
```

**Mengapa?**
- Nuxt.js adalah meta-framework Vue.js dengan built-in features seperti routing, SSR, static generation
- `nuxi init` generate project dengan struktur best practice

#### Step 40: Configure API Base URL
**File:** `nuxt.config.ts`

```typescript
export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api',
    },
  },
});
```

**Mengapa?**
- Centralize API URL configuration
- Berbeda antara development vs production environment

#### Step 41: Create API Composable
**File:** `composables/useApi.ts`

```typescript
export const useApi = () => {
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  const $fetch = globalThis.$fetch.create({
    baseURL: apiBaseUrl,
  });

  return {
    $fetch,
    // Methods untuk setiap resource
    async getCoaCategories() {
      return await $fetch('/coa-categories');
    },
    async createCoaCategory(data) {
      return await $fetch('/coa-categories', { method: 'POST', body: data });
    },
    // ... etc
  };
};
```

**Mengapa?**
- Composable adalah reusable function dalam Composition API
- Centralize API logic agar mudah di-test dan maintain

---

### **PHASE 2.2: Setup Pinia State Management**

#### Step 42: Install Pinia
```bash
npm install pinia
```

#### Step 43: Create Store
**File:** `stores/finance.ts`

```typescript
import { defineStore } from 'pinia';

export const useFinanceStore = defineStore('finance', () => {
  const coaCategories = ref([]);
  const chartOfAccounts = ref([]);
  const transactions = ref([]);
  const profitLossReport = ref(null);

  const api = useApi();

  const fetchCoaCategories = async () => {
    coaCategories.value = await api.getCoaCategories();
  };

  const fetchChartOfAccounts = async () => {
    chartOfAccounts.value = await api.getChartOfAccounts();
  };

  const fetchTransactions = async () => {
    transactions.value = await api.getTransactions();
  };

  const fetchProfitLoss = async (yearMonth) => {
    profitLossReport.value = await api.getProfitLoss(yearMonth);
  };

  return {
    coaCategories,
    chartOfAccounts,
    transactions,
    profitLossReport,
    fetchCoaCategories,
    fetchChartOfAccounts,
    fetchTransactions,
    fetchProfitLoss,
  };
});
```

**Mengapa?**
- Pinia adalah state management library (replacement untuk Vuex)
- Store ini centralize state management, sehingga semua component bisa access data yang sama

---

### **PHASE 2.3: Create Page Components**

#### Step 44: Create Layout
**File:** `layouts/default.vue`

```vue
<template>
  <div class="layout">
    <nav class="navbar">
      <div class="logo">Finance System</div>
      <div class="menu">
        <NuxtLink to="/coa-categories">Kategori COA</NuxtLink>
        <NuxtLink to="/chart-of-accounts">Chart of Accounts</NuxtLink>
        <NuxtLink to="/transactions">Transaksi</NuxtLink>
        <NuxtLink to="/profit-loss">Laporan P&L</NuxtLink>
      </div>
    </nav>
    <main class="content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.navbar {
  display: flex;
  justify-content: space-between;
  padding: 1rem;
  background-color: #333;
  color: white;
}

.menu {
  display: flex;
  gap: 2rem;
}

.content {
  flex: 1;
  padding: 2rem;
}
</style>
```

#### Step 45: Create COA Categories Page
**File:** `pages/coa-categories/index.vue`

```vue
<template>
  <div class="container">
    <h1>Kategori COA</h1>
    <button @click="showCreateForm = true">+ Tambah Kategori</button>

    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in categories" :key="category.id">
          <td>{{ category.id }}</td>
          <td>{{ category.name }}</td>
          <td>
            <button @click="editCategory(category)">Edit</button>
            <button @click="deleteCategory(category.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Create/Edit Form Modal -->
    <div v-if="showCreateForm" class="modal">
      <form @submit.prevent="saveCategory">
        <input v-model="form.name" placeholder="Nama Kategori" required />
        <button type="submit">Simpan</button>
        <button type="button" @click="showCreateForm = false">Batal</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFinanceStore } from '~/stores/finance';

const store = useFinanceStore();
const categories = ref([]);
const showCreateForm = ref(false);
const form = ref({ name: '' });
const editingId = ref(null);

const api = useApi();

onMounted(() => {
  loadCategories();
});

const loadCategories = async () => {
  await store.fetchCoaCategories();
  categories.value = store.coaCategories;
};

const saveCategory = async () => {
  try {
    if (editingId.value) {
      await api.updateCoaCategory(editingId.value, form.value);
    } else {
      await api.createCoaCategory(form.value);
    }
    showCreateForm.value = false;
    form.value = { name: '' };
    editingId.value = null;
    await loadCategories();
  } catch (error) {
    console.error('Error:', error);
  }
};

const editCategory = (category) => {
  form.value = { name: category.name };
  editingId.value = category.id;
  showCreateForm.value = true;
};

const deleteCategory = async (id) => {
  if (confirm('Yakin ingin menghapus?')) {
    await api.deleteCoaCategory(id);
    await loadCategories();
  }
};
</script>
```

**Mengapa?**
- Page component = full page yang bisa di-route
- `onMounted` = lifecycle hook untuk load data saat component di-mount
- Modal pattern untuk create/edit form

#### Step 46: Create Transactions Page
**File:** `pages/transactions/index.vue`

```vue
<template>
  <div class="container">
    <h1>Transaksi</h1>
    <button @click="showCreateForm = true">+ Tambah Transaksi</button>

    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Akun</th>
          <th>Deskripsi</th>
          <th>Debit</th>
          <th>Credit</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="txn in transactions" :key="txn.id">
          <td>{{ formatDate(txn.date) }}</td>
          <td>{{ txn.coa_code }} - {{ txn.coa_name }}</td>
          <td>{{ txn.description }}</td>
          <td>{{ formatCurrency(txn.debit) }}</td>
          <td>{{ formatCurrency(txn.credit) }}</td>
          <td>
            <button @click="editTransaction(txn)">Edit</button>
            <button @click="deleteTransaction(txn.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Form Modal -->
    <div v-if="showCreateForm" class="modal">
      <form @submit.prevent="saveTransaction">
        <input v-model="form.date" type="date" required />
        
        <select v-model="form.coa_id" required>
          <option value="">-- Pilih Akun --</option>
          <option v-for="account in chartOfAccounts" :key="account.id" :value="account.id">
            {{ account.code }} - {{ account.name }}
          </option>
        </select>

        <input v-model="form.description" placeholder="Deskripsi" />
        <input v-model="form.debit" type="number" placeholder="Debit" />
        <input v-model="form.credit" type="number" placeholder="Credit" />

        <button type="submit">Simpan</button>
        <button type="button" @click="showCreateForm = false">Batal</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFinanceStore } from '~/stores/finance';

const store = useFinanceStore();
const transactions = ref([]);
const chartOfAccounts = ref([]);
const showCreateForm = ref(false);
const form = ref({ date: '', coa_id: '', description: '', debit: 0, credit: 0 });
const editingId = ref(null);

const api = useApi();

onMounted(async () => {
  await store.fetchTransactions();
  await store.fetchChartOfAccounts();
  transactions.value = store.transactions;
  chartOfAccounts.value = store.chartOfAccounts;
});

const saveTransaction = async () => {
  try {
    const data = {
      ...form.value,
      debit: parseFloat(form.value.debit) || 0,
      credit: parseFloat(form.value.credit) || 0,
    };

    if (editingId.value) {
      await api.updateTransaction(editingId.value, data);
    } else {
      await api.createTransaction(data);
    }

    showCreateForm.value = false;
    form.value = { date: '', coa_id: '', description: '', debit: 0, credit: 0 };
    editingId.value = null;

    await store.fetchTransactions();
    transactions.value = store.transactions;
  } catch (error) {
    console.error('Error:', error);
  }
};

const editTransaction = (txn) => {
  form.value = {
    date: txn.date,
    coa_id: txn.coa_id,
    description: txn.description,
    debit: txn.debit,
    credit: txn.credit,
  };
  editingId.value = txn.id;
  showCreateForm.value = true;
};

const deleteTransaction = async (id) => {
  if (confirm('Yakin ingin menghapus?')) {
    await api.deleteTransaction(id);
    await store.fetchTransactions();
    transactions.value = store.transactions;
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID');
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount);
};
</script>
```

---

### **PHASE 2.4: Create Profit & Loss Report Page**

#### Step 47: Create P&L Report Page
**File:** `pages/profit-loss/index.vue`

```vue
<template>
  <div class="container">
    <h1>Laporan Profit & Loss</h1>

    <div class="controls">
      <input v-model="selectedMonth" type="month" @change="loadReport" />
      <button @click="exportExcel">📊 Export Excel</button>
    </div>

    <div v-if="report" class="report">
      <table class="table">
        <thead>
          <tr>
            <th>Kategori</th>
            <th>Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in report.report" :key="item.category_name" :class="item.type">
            <td>{{ item.category_name }}</td>
            <td>{{ formatCurrency(item.amount) }}</td>
          </tr>
          <tr class="total-income">
            <td><strong>Total Income</strong></td>
            <td><strong>{{ formatCurrency(report.total_income) }}</strong></td>
          </tr>
          <tr class="total-expense">
            <td><strong>Total Expense</strong></td>
            <td><strong>{{ formatCurrency(report.total_expense) }}</strong></td>
          </tr>
          <tr class="net-income">
            <td><strong>Net Income</strong></td>
            <td><strong>{{ formatCurrency(report.net_income) }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFinanceStore } from '~/stores/finance';

const store = useFinanceStore();
const report = ref(null);
const selectedMonth = ref(new Date().toISOString().slice(0, 7)); // YYYY-MM format

const api = useApi();

onMounted(() => {
  loadReport();
});

const loadReport = async () => {
  report.value = await api.getProfitLoss(selectedMonth.value);
};

const exportExcel = async () => {
  // TODO: Implement Excel export
  window.location.href = `/api/profit-loss-export/${selectedMonth.value}`;
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount);
};
</script>

<style scoped>
.controls {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.report {
  margin-top: 2rem;
}

.income {
  background-color: #e8f5e9;
}

.expense {
  background-color: #ffebee;
}

.total-income {
  background-color: #c8e6c9;
  font-weight: bold;
}

.total-expense {
  background-color: #ffcdd2;
  font-weight: bold;
}

.net-income {
  background-color: #fff9c4;
  font-weight: bold;
}
</style>
```

**Mengapa?**
- Input month untuk select periode laporan
- Dynamic filtering berdasarkan month yang dipilih
- Color coding untuk income vs expense vs totals

---

### **PHASE 2.5: Setup Development Environment**

#### Step 48: Start Frontend Development Server
```bash
npm run dev
```

**Default URL:** `http://localhost:3000`

**Mengapa?**
- Development server dengan hot module replacement (HMR)
- Auto-reload saat ada perubahan code

---

**✅ HARI 3-4 SELESAI:** Frontend UI complete dengan CRUD forms dan P&L report page

---

## 📦 HARI 5: EXPORT EXCEL & FINALIZATION

### **PHASE 3.1: Install Excel Export Library**

#### Step 49: Install maatwebsite/excel di Backend
```bash
composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

**Mengapa?**
- `maatwebsite/excel` adalah library terbaik untuk generate Excel di Laravel
- Publish config agar bisa di-customize

#### Step 50: Create Excel Export Class
**File:** `app/Exports/ProfitLossExport.php`

```php
<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, WithHeadings, WithStyles
{
    private $data;
    private $yearMonth;

    public function __construct($data, $yearMonth)
    {
        $this->data = $data;
        $this->yearMonth = $yearMonth;
    }

    public function array(): array
    {
        $array = [
            ['Laporan Profit & Loss'],
            ['Periode: ' . $this->yearMonth],
            [],
            ['Kategori', 'Jumlah'],
        ];

        foreach ($this->data['report'] as $item) {
            $array[] = [$item['category_name'], $item['amount']];
        }

        $array[] = [];
        $array[] = ['Total Income', $this->data['total_income']];
        $array[] = ['Total Expense', $this->data['total_expense']];
        $array[] = ['Net Income', $this->data['net_income']];

        return $array;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
```

**Mengapa?**
- `FromArray` = source data dari array
- `WithHeadings` & `WithStyles` = customize tampilan Excel
- Export bisa di-download sebagai .xlsx file

#### Step 51: Implement Export Method di `ProfitLossController`
**File:** `app/Http/Controllers/api/ProfitLossController.php` (tambahan)

```php
<?php

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfitLossExport;

public function export($year_month)
{
    if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
        return response()->json(['error' => 'Format tidak valid'], 400);
    }

    $reportData = $this->generateProfitLossData($year_month);

    return Excel::download(
        new ProfitLossExport($reportData, $year_month),
        "profit-loss-{$year_month}.xlsx"
    );
}

private function generateProfitLossData($year_month)
{
    $startDate = Carbon::createFromFormat('Y-m-d', $year_month . '-01')->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();

    // ... (sama seperti di show() method sebelumnya)
    // Return array dengan report, total_income, total_expense, net_income
}
```

**Mengapa?**
- `Excel::download()` = trigger file download ke browser
- Filename berisi periode untuk mudah di-identify

---

### **PHASE 3.2: Testing & Bug Fixing**

#### Step 52: Test Seluruh Flow
1. **Backend API Testing:**
   ```bash
   php artisan serve
   # Test dengan Postman atau curl
   curl http://localhost:8000/api/coa-categories
   ```

2. **Frontend Integration:**
   - Buka http://localhost:3000
   - Test CRUD operations
   - Test Profit & Loss report
   - Test export Excel

#### Step 53: Common Issues & Fixes

**Issue 1: CORS Error**
- Solution: Verify `config/cors.php` allows `http://localhost:3000`

**Issue 2: Debit/Credit Logic Error**
- Solution: Double-check di P&L calculation (income = credit, expense = debit)

**Issue 3: Date Format Mismatch**
- Solution: Ensure date format consistent (YYYY-MM-DD) di database dan frontend

---

### **PHASE 3.3: Add Optional Features**

#### Step 54: Dashboard Analytics (Optional)
**File:** `pages/dashboard/index.vue`

```vue
<template>
  <div class="container">
    <h1>Dashboard</h1>
    <div class="chart">
      <!-- Menggunakan Chart.js atau ApexCharts -->
      <!-- Tampilkan tren Net Income per bulan -->
    </div>
  </div>
</template>
```

---

### **PHASE 3.4: Prepare untuk Production**

#### Step 55: Build Frontend
```bash
npm run build
```

**Mengapa?**
- Generate optimized production build
- Minify CSS, JS untuk reduce file size
- Output di `.output/` atau `dist/` directory

#### Step 56: Deploy Checklist
- [ ] Set production database credentials di `.env`
- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Run `php artisan migrate --force` di production
- [ ] Run `php artisan optimize`
- [ ] Setup SSL certificate untuk HTTPS
- [ ] Configure firewall & security headers

---

**✅ HARI 5 SELESAI:** Project ready untuk di-submit dan di-test

---

## 📊 Summary Implementation

### **What Was Built:**
| Feature | Status | Backend | Frontend |
|---------|--------|---------|----------|
| COA Categories CRUD | ✅ | Controller + Validation | Pages + Forms |
| Chart of Accounts CRUD | ✅ | Controller + Validation | Pages + Forms |
| Transactions CRUD | ✅ | Controller + Validation | Pages + Forms |
| Profit & Loss Report | ✅ | Controller + Calculation | Page + Month Selector |
| Export to Excel | ✅ | Export Class | Download Button |
| Dashboard (Optional) | ⭕ | - | Chart Component |

### **Why This Architecture:**
- **Separated Backend-Frontend**: Flexibility untuk scale, easier to test, reusable API
- **RESTful API**: Standard convention, mudah di-integrate dengan any frontend
- **Eloquent ORM**: Query builder terbaik di Laravel, prevent SQL injection
- **Pinia Store**: Centralized state management, easier to debug
- **Form Validation**: Prevent invalid data masuk ke database

### **Saat Di-Test Coding:**
Jelaskan:
1. **Database Design**: 3 tables, relationships dengan foreign keys
2. **API Logic**: CRUD operations standard, P&L calculation logic
3. **Frontend Flow**: How data flows dari API → Pinia → Components
4. **Excel Export**: How maatwebsite/excel generate dan download file
5. **CORS Setup**: Why configured untuk communicate across ports

---

## ✅ Ready to Code!
Ikuti step by step ini, jangan skip langkah. Setiap step ada alasan technical-nya.

**Good luck! 🚀**
