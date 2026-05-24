<template>
  <div style="padding: 24px; font-family: sans-serif;">
    <h2>Laporan Profit &amp; Loss</h2>

    <div style="display: flex; gap: 12px; align-items: flex-end; margin-bottom: 16px;">
      <div>
        <label>Dari Bulan</label><br />
        <input v-model="from" type="month" />
      </div>
      <div>
        <label>Sampai Bulan</label><br />
        <input v-model="to" type="month" />
      </div>
      <button @click="loadReport">Tampilkan</button>
      <button @click="exportExcel" :disabled="!from || !to">Export Excel</button>
    </div>

    <div v-if="error" style="color: red;">{{ error }}</div>

    <div v-if="report">
      <p>Periode: {{ report.period }}</p>
      <table border="1" cellpadding="6" cellspacing="0">
        <thead>
          <tr>
            <th>Category</th>
            <th>Amount</th>
            <th>Type</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in report.report" :key="row.category_name">
            <td>{{ row.category_name }}</td>
            <td>{{ row.amount.toLocaleString() }}</td>
            <td>{{ row.type }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr><td><b>Total Income</b></td><td>{{ report.total_income.toLocaleString() }}</td><td></td></tr>
          <tr><td><b>Total Expense</b></td><td>{{ report.total_expense.toLocaleString() }}</td><td></td></tr>
          <tr><td><b>Net Income</b></td><td>{{ report.net_income.toLocaleString() }}</td><td></td></tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const from = ref('')
const to = ref('')
const report = ref(null)
const error = ref('')

async function loadReport() {
  if (!from.value) return
  error.value = ''
  try {
    const ym = from.value // format YYYY-MM dari input type="month"
    report.value = await $fetch(`${apiBase}/profit-loss/${ym}`)
  } catch (e: any) {
    error.value = e?.data?.error ?? 'Gagal memuat data'
  }
}

function exportExcel() {
  if (!from.value || !to.value) return
  window.location.href = `${apiBase}/profit-loss-export?from=${from.value}&to=${to.value}`
}
</script>
