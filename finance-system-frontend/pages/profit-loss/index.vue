<template>
  <div class="container">
    <h1>Laporan Profit & Loss</h1>

    <div class="controls">
      <input v-model="selectedMonth" type="month" @change="loadReport" />
      <button class="btn btn-primary" @click="loadReport">Tampilkan</button>
      <button class="btn btn-success" @click="exportExcel">Export Excel</button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Memuat laporan...</p>
    </div>

    <div v-else-if="report" class="report">
      <h2>Periode: {{ report.period }}</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Kategori</th>
            <th class="text-right">Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in report.report" :key="item.category_name" :class="item.type">
            <td>{{ item.category_name }}</td>
            <td class="text-right">{{ formatCurrency(item.amount) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="total-income">
            <td><strong>Total Income</strong></td>
            <td class="text-right"><strong>{{ formatCurrency(report.total_income) }}</strong></td>
          </tr>
          <tr class="total-expense">
            <td><strong>Total Expense</strong></td>
            <td class="text-right"><strong>{{ formatCurrency(report.total_expense) }}</strong></td>
          </tr>
          <tr class="net-income">
            <td><strong>Net Income</strong></td>
            <td class="text-right"><strong>{{ formatCurrency(report.net_income) }}</strong></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <p v-else-if="!error" class="empty">Pilih periode untuk melihat laporan.</p>
  </div>
</template>

<script setup lang="ts">
const api = useApi()

const report = ref<any>(null)
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const error = ref('')
const loading = ref(false)

onMounted(() => {
  loadReport()
})

const loadReport = async () => {
  loading.value = true
  try {
    report.value = await api.getProfitLoss(selectedMonth.value)
    error.value = ''
  } catch (e) {
    error.value = 'Gagal memuat laporan'
    report.value = null
  } finally {
    loading.value = false
  }
}

const exportExcel = async () => {
  try {
    window.location.href = `http://localhost:8000/api/profit-loss-export/${selectedMonth.value}`
  } catch (e) {
    error.value = 'Gagal mengexport laporan'
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount)
}
</script>

<style scoped>
h1 {
  margin-bottom: 1.5rem;
}

.controls {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 2rem;
}

.controls input[type="month"] {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
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

.report {
  margin-top: 1rem;
}

.report h2 {
  margin-bottom: 1rem;
  color: #555;
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

.text-right {
  text-align: right;
}

.income {
  background-color: #e8f5e9;
}

.expense {
  background-color: #ffebee;
}

.total-income {
  background-color: #c8e6c9;
}

.total-expense {
  background-color: #ffcdd2;
}

.net-income {
  background-color: #fff9c4;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-primary { background: #3498db; color: white; }
.btn-success { background: #27ae60; color: white; }

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
