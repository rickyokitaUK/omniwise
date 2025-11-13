<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const API_BASE = '/api'
const token = localStorage.getItem('auth_token')

const trades = ref([])
const loading = ref(false)
const error = ref('')
const selectedSymbol = ref('BTCUSDT')

async function fetchTrades() {
  try {
    loading.value = true
    error.value = ''
    const res = await axios.get(`${API_BASE}/binance/trades?symbol=${selectedSymbol.value}&testnet=1`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    trades.value = res.data
  } catch (err) {
    console.error('❌ Fetch trades error:', err)
    error.value = err.response?.data?.error || err.message
  } finally {
    loading.value = false
  }
}

onMounted(fetchTrades)
</script>

<template>
  <div class="p-4 text-white bg-black">
    <h2 class="text-lg font-bold mb-3">Trade History</h2>

    <div class="mb-3">
      <label class="mr-2">Symbol:</label>
      <select v-model="selectedSymbol" @change="fetchTrades" class="bg-gray-800 p-1 rounded">
        <option value="BTCUSDT">BTCUSDT</option>
        <option value="ETHUSDT">ETHUSDT</option>
        <option value="BNBUSDT">BNBUSDT</option>
      </select>
    </div>

    <div v-if="loading">⏳ Loading...</div>
    <div v-if="error" class="text-red-400">{{ error }}</div>

    <table v-if="!loading && trades.length" class="w-full text-sm border-collapse border border-gray-700">
      <thead class="bg-gray-900 text-gray-300">
        <tr>
          <th class="p-2 text-left">Symbol</th>
          <th class="p-2 text-left">Side</th>
          <th class="p-2 text-left">Price</th>
          <th class="p-2 text-left">Qty</th>
          <th class="p-2 text-left">Total (USDT)</th>
          <th class="p-2 text-left">Time</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="t in trades"
          :key="t.id"
          class="border-b border-gray-700 hover:bg-gray-800"
        >
          <td>{{ t.symbol }}</td>
          <td :class="t.isBuyer ? 'text-green-400' : 'text-red-400'">
            {{ t.isBuyer ? 'BUY' : 'SELL' }}
          </td>
          <td>{{ t.price }}</td>
          <td>{{ t.qty }}</td>
          <td>{{ t.quoteQty }}</td>
          <td>{{ new Date(t.time).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="!loading && !trades.length && !error" class="text-gray-500 mt-3">
      No trades found.
    </div>
  </div>
</template>
