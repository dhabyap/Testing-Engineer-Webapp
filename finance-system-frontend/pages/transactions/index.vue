<template>
  <div class="container">
    <div class="header">
      <h1>Transaksi</h1>
      <button class="btn btn-primary" @click="openCreateForm">+ Tambah Transaksi</button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Memuat data...</p>
    </div>

    <table class="table" v-else-if="transactions.length">
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
          <td class="debit">{{ formatCurrency(txn.debit) }}</td>
          <td class="credit">{{ formatCurrency(txn.credit) }}</td>
          <td class="actions">
            <button class="btn btn-sm btn-warning" @click="editTransaction(txn)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="deleteTransaction(txn.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else class="empty">Belum ada data transaksi.</p>

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal">
        <h2>{{ editingId ? 'Edit Transaksi' : 'Tambah Transaksi' }}</h2>
        <form @submit.prevent="saveTransaction">
          <div class="form-group">
            <label>Tanggal</label>
            <input v-model="form.date" type="date" required />
          </div>
          <div class="form-group">
            <label>Akun</label>
            <select v-model="form.coa_id" required>
              <option value="">-- Pilih Akun --</option>
              <option v-for="account in accounts" :key="account.id" :value="account.id">
                {{ account.code }} - {{ account.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <input v-model="form.description" placeholder="Deskripsi transaksi" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Debit</label>
              <input v-model="form.debit" type="number" min="0" placeholder="0" />
            </div>
            <div class="form-group">
              <label>Credit</label>
              <input v-model="form.credit" type="number" min="0" placeholder="0" />
            </div>
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

const transactions = ref<any[]>([])
const accounts = ref<any[]>([])
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const loading = ref(false)
const form = ref({ date: '', coa_id: '', description: '', debit: 0, credit: 0 })

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([
      store.fetchTransactions(),
      store.fetchChartOfAccounts(),
    ])
    transactions.value = store.transactions
    accounts.value = store.chartOfAccounts
    error.value = ''
  } catch (e) {
    error.value = 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

const openCreateForm = () => {
  editingId.value = null
  form.value = { date: '', coa_id: '', description: '', debit: 0, credit: 0 }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingId.value = null
  form.value = { date: '', coa_id: '', description: '', debit: 0, credit: 0 }
}

const saveTransaction = async () => {
  try {
    const data = {
      date: form.value.date,
      coa_id: Number(form.value.coa_id),
      description: form.value.description,
      debit: parseFloat(String(form.value.debit)) || 0,
      credit: parseFloat(String(form.value.credit)) || 0,
    }

    if (editingId.value) {
      await api.updateTransaction(editingId.value, data)
    } else {
      await api.createTransaction(data)
    }

    closeForm()
    await loadData()
  } catch (e) {
    error.value = 'Gagal menyimpan transaksi'
  }
}

const editTransaction = (txn: any) => {
  editingId.value = txn.id
  form.value = {
    date: txn.date,
    coa_id: String(txn.coa_id),
    description: txn.description || '',
    debit: txn.debit,
    credit: txn.credit,
  }
  showForm.value = true
}

const deleteTransaction = async (id: number) => {
  if (confirm('Yakin ingin menghapus transaksi ini?')) {
    try {
      await api.deleteTransaction(id)
      await loadData()
    } catch (e) {
      error.value = 'Gagal menghapus transaksi'
    }
  }
}

const formatDate = (date: string) => {
  return new Date(date + 'T00:00:00').toLocaleDateString('id-ID')
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount)
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

.debit { color: #e74c3c; }
.credit { color: #27ae60; }

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
  min-width: 500px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.modal h2 {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
  flex: 1;
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

.form-row {
  display: flex;
  gap: 1rem;
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
