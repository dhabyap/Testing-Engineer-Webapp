<template>
  <div class="container">
    <h1>Dashboard</h1>
    <div class="cards">
      <NuxtLink to="/coa-categories" class="card">
        <h3>Kategori COA</h3>
        <p>Kelola kategori Chart of Accounts</p>
      </NuxtLink>
      <NuxtLink to="/chart-of-accounts" class="card">
        <h3>Chart of Accounts</h3>
        <p>Kelola daftar akun</p>
      </NuxtLink>
      <NuxtLink to="/transactions" class="card">
        <h3>Transaksi</h3>
        <p>Catat transaksi keuangan</p>
      </NuxtLink>
      <NuxtLink to="/profit-loss" class="card">
        <h3>Laporan P&L</h3>
        <p>Lihat laporan laba rugi</p>
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
h1 {
  margin-bottom: 2rem;
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  text-decoration: none;
  color: #333;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card h3 {
  margin-bottom: 0.5rem;
  color: #2c3e50;
}
</style>
