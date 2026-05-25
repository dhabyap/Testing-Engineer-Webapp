<template>
  <div class="container">
    <div class="header">
      <h1>Kategori COA</h1>
      <button class="btn btn-primary" @click="openCreateForm">+ Tambah Kategori</button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Memuat data...</p>
    </div>

    <table class="table" v-else-if="categories.length">
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
          <td class="actions">
            <button class="btn btn-sm btn-warning" @click="editCategory(category)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="deleteCategory(category.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else class="empty">Belum ada data kategori.</p>

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal">
        <h2>{{ editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Nama Kategori</label>
            <input v-model="form.name" placeholder="Nama Kategori" required />
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

const categories = ref<any[]>([])
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const loading = ref(false)
const form = ref({ name: '' })

onMounted(async () => {
  await loadCategories()
})

const loadCategories = async () => {
  loading.value = true
  try {
    await store.fetchCoaCategories()
    categories.value = store.coaCategories
    error.value = ''
  } catch (e) {
    error.value = 'Gagal memuat data kategori'
  } finally {
    loading.value = false
  }
}

const openCreateForm = () => {
  editingId.value = null
  form.value = { name: '' }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editingId.value = null
  form.value = { name: '' }
}

const saveCategory = async () => {
  try {
    if (editingId.value) {
      await api.updateCoaCategory(editingId.value, form.value)
    } else {
      await api.createCoaCategory(form.value)
    }
    closeForm()
    await loadCategories()
  } catch (e) {
    error.value = 'Gagal menyimpan kategori'
  }
}

const editCategory = (category: any) => {
  editingId.value = category.id
  form.value = { name: category.name }
  showForm.value = true
}

const deleteCategory = async (id: number) => {
  if (confirm('Yakin ingin menghapus kategori ini?')) {
    try {
      await api.deleteCoaCategory(id)
      await loadCategories()
    } catch (e) {
      error.value = 'Gagal menghapus kategori'
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
