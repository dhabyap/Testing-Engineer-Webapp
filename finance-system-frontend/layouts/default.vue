<template>
  <div class="layout">
    <nav class="navbar">
      <NuxtLink to="/" class="logo">Finance System</NuxtLink>
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

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f5f5f5;
  color: #333;
}

.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: #2c3e50;
  color: white;
}

.navbar a {
  color: white;
  text-decoration: none;
}

.logo {
  font-weight: bold;
  font-size: 1.2rem;
}

.menu {
  display: flex;
  gap: 1.5rem;
}

.menu a:hover {
  text-decoration: underline;
}

.content {
  flex: 1;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}
</style>
