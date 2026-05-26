# 🐛 ISSUE: Laravel Scribe - Permission Denied saat Generate Documentation

## Error Details

```
rename(public/docs/, D:\Latihan\TEST CODE\test pt tbk\finance-system-api\public/vendor/scribe): Access is denied (code: 5)

at vendor\knuckleswtf\scribe\src\Writing\Writer.php:242
```

---

## 🔍 Root Cause Analysis

### **Apa yang terjadi?**

Laravel Scribe mencoba:
1. Generate dokumentasi di folder `public/docs/`
2. Move folder `public/docs/` ke `public/vendor/scribe/` 
3. **FAIL** karena Windows tidak punya permission untuk rename/move folder

### **Mengapa terjadi?**

| Kemungkinan Penyebab | Penjelasan |
|----------------------|-----------|
| **File locked** | Beberapa proses (Vite, NPM, Git) masih lock folder |
| **Permission issue** | User tidak punya write permission di `public/vendor/` |
| **Folder sudah ada** | `public/vendor/scribe/` sudah exist dan locked |
| **Windows limitation** | Windows lebih strict dibanding Linux untuk file operations |
| **Antivirus/Security** | Antivirus mencegah operasi pada folder |

---

## ✅ SOLUTIONS

### **Solution 1: Clean Up & Run as Administrator (EASIEST)**

#### Step 1: Stop semua processes
```bash
# Stop development servers
# Ctrl+C di terminal Vite
# Ctrl+C di terminal Laravel
```

#### Step 2: Delete problematic folders
```bash
# Clear generated docs folder
rmdir /s /q public\docs
rmdir /s /q public\vendor\scribe

# Atau di bash/WSL:
rm -rf public/docs
rm -rf public/vendor/scribe
```

#### Step 3: Run command as Administrator

**Buka Command Prompt/PowerShell as Administrator:**

```bash
cd D:\Latihan\TEST CODE\test pt tbk\finance-system-api
php artisan scribe:generate
```

✅ **Expected output:**
```
✓ Documentation generated successfully!
✓ Wrote file: public/docs/index.html
```

---

### **Solution 2: Change Scribe Configuration**

Jika Solution 1 tidak work, modify `config/scribe.php`:

**File:** `config/scribe.php`

```php
return [
    'title' => 'Finance System API',
    'description' => 'API documentation untuk Simple Finance System',
    'base_url' => env('APP_URL', 'http://localhost:8000') . '/api',
    
    'type' => 'laravel',
    
    // ✅ ADD THIS: Override output path
    'laravel' => [
        'docs_url' => '/docs',
        'assets_directory' => 'vendor/scribe',
        'operations' => [
            'group_by' => 'none', // Disable grouping jika ada issue
        ],
    ],
    
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
            ],
            'include' => [],
            'exclude' => [],
        ],
    ],
];
```

**Then run:**
```bash
# Force regenerate
php artisan scribe:generate --force
```

---

### **Solution 3: Use Static Type Instead**

Jika Laravel type terus error, gunakan static type:

**File:** `config/scribe.php`

```php
return [
    // ... other config ...
    
    // ✅ CHANGE THIS:
    'type' => 'static', // Instead of 'laravel'
    
    // Static type configuration
    'static' => [
        'output_path' => 'public/docs',
    ],
    
    // ... rest of config ...
];
```

**Run:**
```bash
php artisan scribe:generate --force
```

**Output:** Static HTML files di `public/docs/`

**Access:** `http://localhost:8000/docs/index.html`

---

### **Solution 4: Manual Move Folder**

Jika command masih fail, coba manual move:

#### Step 1: Generate dengan debug
```bash
php artisan scribe:generate --debug
```

#### Step 2: Manually move files
```bash
# If docs generated successfully di public/docs
# Copy files to public/vendor/scribe manually

xcopy public\docs public\vendor\scribe\ /Y /E /I

# Atau di bash:
cp -r public/docs/* public/vendor/scribe/
```

#### Step 3: Update view reference
Check `resources/views/scribe/` apakah ada reference yang perlu update

---

### **Solution 5: Use Windows Subsystem for Linux (WSL2)**

Jika issue terus terjadi, gunakan WSL2 (lebih Linux-like):

#### Install WSL2:
```bash
# Di PowerShell (Run as Administrator):
wsl --install
```

#### Access project via WSL:
```bash
cd /mnt/d/Latihan/TEST\ CODE/test\ pt\ tbk/finance-system-api
php artisan scribe:generate
```

✅ **Benefits:**
- Better file permission handling
- Faster operations
- Closer to production environment

---

## 🛠️ **PREVENTION: Jangan sampai error terulang**

### **✅ Best Practices:**

1. **Selalu stop dev server saat run scribe**
   ```bash
   # STOP: Ctrl+C di terminal
   # THEN: Run scribe
   php artisan scribe:generate
   ```

2. **Use .gitignore untuk generated files**
   
   **File:** `.gitignore`
   ```
   # Scribe generated files
   public/docs/
   public/vendor/scribe/
   storage/app/scribe/
   ```

3. **Add scribe to .gitignore**
   ```bash
   # Don't commit generated docs
   echo "public/docs/" >> .gitignore
   echo "public/vendor/scribe/" >> .gitignore
   ```

4. **Create npm/artisan script**
   
   **File:** `package.json`
   ```json
   {
     "scripts": {
       "api-docs": "php artisan scribe:generate",
       "api-docs:force": "php artisan scribe:generate --force"
     }
   }
   ```
   
   **Usage:**
   ```bash
   npm run api-docs
   ```

5. **Setup in Makefile (Optional)**
   
   **File:** `Makefile`
   ```makefile
   .PHONY: docs
   docs:
   	@echo "🔄 Generating API documentation..."
   	@php artisan scribe:generate --force
   	@echo "✅ Documentation generated at: http://localhost:8000/docs"

   .PHONY: docs-clean
   docs-clean:
   	@echo "🗑️  Cleaning documentation..."
   	@rm -rf public/docs public/vendor/scribe
   	@echo "✅ Documentation cleaned"

   .PHONY: docs-regenerate
   docs-regenerate: docs-clean docs
   	@echo "✅ Documentation regenerated"
   ```
   
   **Usage:**
   ```bash
   make docs
   make docs-clean
   make docs-regenerate
   ```

---

## 📋 **QUICK FIX CHECKLIST**

Urutan coba:

- [ ] **1. Stop all processes**
  ```bash
  # Ctrl+C di semua terminal
  ```

- [ ] **2. Delete problematic folders**
  ```bash
  rmdir /s /q public\docs
  rmdir /s /q public\vendor\scribe
  ```

- [ ] **3. Run as Administrator**
  - Buka cmd/PowerShell as Admin
  - Run: `php artisan scribe:generate`

- [ ] **4. If still fail: Force regenerate**
  ```bash
  php artisan scribe:generate --force
  ```

- [ ] **5. If still fail: Change to static type**
  - Edit `config/scribe.php`
  - Change `'type' => 'static'`
  - Run: `php artisan scribe:generate`

- [ ] **6. If still fail: Use WSL2**
  - Install WSL2
  - Run commands di WSL terminal

---

## 🧪 **VERIFY FIX**

Setelah fix, check:

```bash
# 1. Check folder ada
dir public\docs
dir public\vendor\scribe

# 2. Check file ada
dir public\vendor\scribe\index.html

# 3. Open di browser
http://localhost:8000/docs
```

✅ **Success criteria:**
- Folder `public/vendor/scribe/` exist dengan files
- `index.html` exist
- Browser bisa akses `/docs` route
- Documentation UI loadable

---

## 🔧 **ADVANCED: Debug Mode**

Jika ingin see lebih detail error:

```bash
# Run dengan verbose
php artisan scribe:generate --verbose

# Run dengan debug
php artisan scribe:generate --debug

# Output akan show exactly di mana error terjadi
```

---

## 📞 **IF NOTHING WORKS**

### **Option 1: Manual Documentation**
Skip Scribe, buat dokumentasi manual di `API_ENDPOINTS.md`

### **Option 2: Use Online Tools**
- Upload project ke GitHub
- Use Swagger UI dengan OpenAPI spec
- Use Postman untuk share documentation

### **Option 3: Use Docker**
Run Laravel Scribe di Docker (eliminates Windows permission issues)

```dockerfile
FROM php:8.2
# ... setup ...
RUN php artisan scribe:generate
```

---

## 🎓 **LESSON LEARNED**

### **Why This Error Happens:**

Windows file system berbeda dengan Linux:
- ❌ Windows: More strict file locking
- ✅ Linux: More flexible file operations
- ❌ Windows: Case-insensitive paths
- ✅ Linux: Case-sensitive paths

### **Best Practice:**
Development di Windows sebaiknya:
1. Use WSL2 untuk operations yang touch file system
2. Or use Docker untuk consistency
3. Or run as Administrator untuk Windows native

---

## 📚 **RELATED ISSUES**

Similar errors yang mungkin terjadi:
- `rename(...): Permission denied` → File locked
- `Access is denied (code: 5)` → Insufficient permission
- `directory is not empty` → Folder exist dan locked
- `The system cannot find the path specified` → Path error

---

## ✅ **RECOMMENDATION**

**Untuk project ini, gunakan Solution 1:**

```bash
# 1. Stop all processes (Ctrl+C)

# 2. Delete folders
rmdir /s /q public\docs
rmdir /s /q public\vendor\scribe

# 3. Open new cmd window as Administrator

# 4. Navigate dan run
cd D:\Latihan\TEST CODE\test pt tbk\finance-system-api
php artisan scribe:generate

# 5. Verify
http://localhost:8000/docs
```

Ini adalah **safest dan most reliable** solution untuk Windows development environment.

---

## 📝 **LOG THIS ISSUE**

Untuk future reference:
- **Issue**: Scribe permission denied on Windows
- **Root Cause**: File lock/permission on rename operation
- **Solution**: Delete folders + run as Administrator
- **Prevention**: Stop processes before running scribe
- **Alternative**: Use WSL2 or Docker

---

**Good luck! Semoga langsung berhasil di Solution 1 🚀**
