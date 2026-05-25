export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBaseUrl = config.public.apiBaseUrl

  const $fetch = globalThis.$fetch.create({
    baseURL: apiBaseUrl,
  })

  const handleError = (error: any) => {
    console.error('API Error:', error)
    throw error
  }

  return {
    // COA Categories
    async getCoaCategories() {
      try { return await $fetch('/coa-categories') } catch (e) { handleError(e) }
    },
    async getCoaCategory(id: number) {
      try { return await $fetch(`/coa-categories/${id}`) } catch (e) { handleError(e) }
    },
    async createCoaCategory(data: any) {
      try { return await $fetch('/coa-categories', { method: 'POST', body: data }) } catch (e) { handleError(e) }
    },
    async updateCoaCategory(id: number, data: any) {
      try { return await $fetch(`/coa-categories/${id}`, { method: 'PUT', body: data }) } catch (e) { handleError(e) }
    },
    async deleteCoaCategory(id: number) {
      try { return await $fetch(`/coa-categories/${id}`, { method: 'DELETE' }) } catch (e) { handleError(e) }
    },

    // Chart of Accounts
    async getChartOfAccounts() {
      try { return await $fetch('/chart-of-accounts') } catch (e) { handleError(e) }
    },
    async getChartOfAccount(id: number) {
      try { return await $fetch(`/chart-of-accounts/${id}`) } catch (e) { handleError(e) }
    },
    async createChartOfAccount(data: any) {
      try { return await $fetch('/chart-of-accounts', { method: 'POST', body: data }) } catch (e) { handleError(e) }
    },
    async updateChartOfAccount(id: number, data: any) {
      try { return await $fetch(`/chart-of-accounts/${id}`, { method: 'PUT', body: data }) } catch (e) { handleError(e) }
    },
    async deleteChartOfAccount(id: number) {
      try { return await $fetch(`/chart-of-accounts/${id}`, { method: 'DELETE' }) } catch (e) { handleError(e) }
    },

    // Transactions
    async getTransactions() {
      try { return await $fetch('/transactions') } catch (e) { handleError(e) }
    },
    async getTransaction(id: number) {
      try { return await $fetch(`/transactions/${id}`) } catch (e) { handleError(e) }
    },
    async createTransaction(data: any) {
      try { return await $fetch('/transactions', { method: 'POST', body: data }) } catch (e) { handleError(e) }
    },
    async updateTransaction(id: number, data: any) {
      try { return await $fetch(`/transactions/${id}`, { method: 'PUT', body: data }) } catch (e) { handleError(e) }
    },
    async deleteTransaction(id: number) {
      try { return await $fetch(`/transactions/${id}`, { method: 'DELETE' }) } catch (e) { handleError(e) }
    },

    // Profit & Loss
    async getProfitLoss(yearMonth: string) {
      try { return await $fetch(`/profit-loss/${yearMonth}`) } catch (e) { handleError(e) }
    },
    async exportProfitLoss(yearMonth: string) {
      try { return await $fetch(`/profit-loss-export/${yearMonth}`) } catch (e) { handleError(e) }
    },
  }
}
