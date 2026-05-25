import { defineStore } from 'pinia'

export const useFinanceStore = defineStore('finance', () => {
  const coaCategories = ref<any[]>([])
  const chartOfAccounts = ref<any[]>([])
  const transactions = ref<any[]>([])
  const profitLossReport = ref<any>(null)

  const api = useApi()

  const fetchCoaCategories = async () => {
    const data = await api.getCoaCategories()
    if (data?.data) coaCategories.value = data.data
    else coaCategories.value = data || []
  }

  const fetchChartOfAccounts = async () => {
    const data = await api.getChartOfAccounts()
    if (data?.data) chartOfAccounts.value = data.data
    else chartOfAccounts.value = data || []
  }

  const fetchTransactions = async () => {
    const data = await api.getTransactions()
    if (data?.data) transactions.value = data.data
    else transactions.value = data || []
  }

  const fetchProfitLoss = async (yearMonth: string) => {
    profitLossReport.value = await api.getProfitLoss(yearMonth)
  }

  return {
    coaCategories,
    chartOfAccounts,
    transactions,
    profitLossReport,
    fetchCoaCategories,
    fetchChartOfAccounts,
    fetchTransactions,
    fetchProfitLoss,
  }
})
