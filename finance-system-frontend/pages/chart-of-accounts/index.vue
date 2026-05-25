<template>
  <div class="container">
    <div class="header">
      <h1>Chart of Accounts</h1>
      <button class="btn btn-primary" @click="openCreateForm">+ Tambah Akun</button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Memuat data...</p>
    </div>

    <table class="table" v-else-if="accounts.length">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama Akun</th>
          <th>Kategori</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="account in accounts" :key="account.id">
          <td>{{ account.code }}</td>
          <td>{{ account.name }}</td>
          <td>{{ account.category_name }}</td>
          <td class="actions">
            <button class="btn btn-sm btn-warning" @click="editAccount(account)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="deleteAccount(account.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else class="empty">Belum ada data akun.</p>

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal">
        <h2>{{ editingId ? 'Edit Akun' : 'Tambah Akun' }}</h2>
        <form @submit.prevent="saveAccount">
          <div class="form-group">
            <label>Kode Akun</label>
            <input v-model="form.code" placeholder="Contoh: 401" required maxlength="10" />
          </div>
          <div class="form-group">
            <label>Nama Akun</label>
            <input v-model="form.name" placeholder="Nama Akun" required />
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select v-model="form.coa_category_id" required>
              <option value="">-- Pilih Kategori --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" class="btn btn-secondary" @click="closeForm">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const store = useFinanceStore()
const api = useApi()

const accounts = ref<any[]>([])
const categories = ref<any[]>([])
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const loading = ref(false)
const form = ref({ code: '', name: '', coa_category_id: '' })

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([
      store.fetchChartOfAccounts(),
      store.fetchCoaCategories(),
    ])
    accounts.value = store.chartOfAccounts
    categories.value = store.coaCategories
    error.value = ''
  } catch (e) {
    error.value = 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

const openCreateForm = () => {
  editingId.value = null
  form.value = { code: '', name: '', coa_category_id: '' }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingId.value = null
  form.value = { code: '', name: '', coa_category_id: '' }
}

const saveAccount = async () => {
  try {
    const data = {
      code: form.value.code,
      name: form.value.name,
      coa_category_id: Number(form.value.coa_category_id),
    }
    if (editingId.value) {
      await api.updateChartOfAccount(editingId.value, data)
    } else {
      await api.createChartOfAccount(data)
    }
    closeForm()
    await loadData()
  } catch (e) {
    error.value = 'Gagal menyimpan akun'
  }
}

const editAccount = (account: any) => {
  editingId.value = account.id
  form.value = {
    code: account.code,
    name: account.name,
    coa_category_id: String(account.coa_category_id),
  }
  showForm.value = true
}

const deleteAccount = async (id: number) => {
  if (confirm('Yakin ingin menghapus akun ini?')) {
    try {
      await api.deleteChartOfAccount(id)
      await loadData()
    } catch (e) {
      error.value = 'Gagal menghapus akun'
    }
  }
}
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-error {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #ef9a9a;
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th, .table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.table th {
  background: #2c3e50;
  color: white;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-primary { background: #3498db; color: white; }
.btn-warning { background: #f39c12; color: white; }
.btn-danger { background: #e74c3c; color: white; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-sm { padding: 0.35rem 0.65rem; }

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  min-width: 400px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.modal h2 {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.empty {
  text-align: center;
  color: #999;
  padding: 3rem;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: #888;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 4px solid #e0e0e0;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
