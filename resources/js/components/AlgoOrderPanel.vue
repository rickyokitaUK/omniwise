<template>
  <div id="algo-order-panel">
    <div class="header">
      <div>
        <span>Net Asset Value (HKD): {{ netAssetValue.toFixed(2) }}</span>
        <span>Market Value: {{ marketValue.toFixed(2) }}</span>
        <span>Daily P/L: {{ dailyPL.toFixed(2) }}</span>
      </div>
      <div>
        <label for="accounts">Accounts:</label>
        <select v-model="selectedAccount" id="accounts">
          <option v-for="account in accounts" :key="account.id" :value="account.id">
            {{ account.name }}
          </option>
        </select>
        <button @click="refresh">Refresh</button>
      </div>
    </div>

    <div class="algo-accounts" v-for="algoAccount in algoAccounts" :key="algoAccount.id">
      <div class="account-header">
        <span>{{ algoAccount.name }} | Trade Count: {{ algoAccount.tradeCount }}</span>
        <span>Status: {{ algoAccount.status }}</span>
      </div>
      <div class="account-details">
        <div class="order-status" v-for="order in algoAccount.orders" :key="order.id">
          <span>{{ order.type }} {{ order.marketValue }}</span>
          <span>{{ order.product }} {{ order.orderCount }}</span>
          <span>{{ order.pl }}</span>
          <span>{{ order.plPrice }}</span>
        </div>
        <div class="options">
          <label :for="'algo-' + algoAccount.id">Algo</label>
          <input type="checkbox" v-model="algoAccount.algo" :id="'algo-' + algoAccount.id">
          <label :for="'sync-' + algoAccount.id">Sync</label>
          <input type="checkbox" v-model="algoAccount.sync" :id="'sync-' + algoAccount.id">
          <input type="number" v-model="algoAccount.tradeCount" min="1">
          <button @click="call(algoAccount.id)">Call</button>
          <button @click="put(algoAccount.id)">Put</button>
          <button @click="settle(algoAccount.id)">Settle</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      netAssetValue: 180000,
      marketValue: 1000,
      dailyPL: 2000,
      selectedAccount: '',
      accounts: [
        // Example account data
        { id: 1, name: 'Account 1' },
        { id: 2, name: 'Account 2' }
      ],
      algoAccounts: [
        // Example algo account data
        {
          id: 1,
          name: 'billyMSATest',
          tradeCount: 2,
          status: 'Ready',
          orders: [
            { id: 1, type: 'LONG', marketValue: 23579, product: 'MHI', orderCount: 1, pl: -22, plPrice: -220.00 }
          ],
          algo: true,
          sync: false
        },
        {
          id: 2,
          name: 'billyRKTest',
          tradeCount: 6,
          status: '',
          orders: [
            { id: 1, type: 'SHORT', marketValue: 23861, product: 'HSI', orderCount: 1, pl: 60, plPrice: 3000.00 },
            { id: 2, type: 'SHORT', marketValue: 23862, product: 'HSI', orderCount: 2, pl: 20, plPrice: 2000.00 }
          ],
          algo: false,
          sync: true
        }
      ]
    }
  },
  methods: {
    refresh() {
      // Refresh logic
    },
    call(accountId) {
      // Call logic for the given account
    },
    put(accountId) {
      // Put logic for the given account
    },
    settle(accountId) {
      // Settle logic for the given account
    }
  }
}
</script>

<style scoped>
#algo-order-panel {
  color: white;
}
.header {
  display: flex;
  justify-content: space-between;
  background-color: black;
  padding: 10px;
}
.algo-accounts {
  background-color: #333;
  margin: 10px 0;
  padding: 10px;
}
.account-header {
  display: flex;
  justify-content: space-between;
}
.account-details {
  margin-top: 10px;
}
.order-status {
  display: flex;
  justify-content: space-between;
}
.options {
  display: flex;
  align-items: center;
}
.options > * {
  margin-right: 10px;
}
</style>
