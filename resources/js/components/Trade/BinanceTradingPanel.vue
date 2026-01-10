<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const API_BASE = '/api'

const API = axios.create({
  baseURL: "/api",
  headers: { Accept: "application/json" }
})

// --- Reactive state ---
const symbols = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT']
const selectedSymbol = ref('BTCUSDT')

const accountType = ref('demo') // 'spot' or 'demo'
const price = ref(0)
const change = ref(0)
const side = ref('BUY')
const orderType = ref('MARKET')
const quantity = ref(0.001)
const lastFetched = ref('')
const balances = ref([])
const selectedBalance = ref(null)
const selectedAsset = ref('') 

console.log(accountType.value);

// --- Computed ---
const changeColor = computed(() => (change.value >= 0 ? 'text-green-500' : 'text-red-500'))
const estimatedCost = computed(() => (price.value * quantity.value).toFixed(2))

/* -------------------------------------------------------------
   API HELPERS
------------------------------------------------------------- */
async function safeGet(url) {
  const res = await API.get(url)
  return res.data
}

async function safePost(url, data) {
  const res = await API.post(url, data)
  return res.data
}

const token = localStorage.getItem('auth_token')
if (!token) {
  alert('⚠️ Please login first.');
  window.location.href = '/login'; // or route to Auth.vue
}
console.log('🔑 Token loaded from localStorage:', token)

// --- Axios defaults ---
API.defaults.headers.common['Authorization'] = `Bearer ${token}`
API.defaults.headers.common['Accept'] = 'application/json'

// Global response interceptor for 401 handling
API.interceptors.response.use(
  r => r,
  err => {
    if (err.response?.status === 401) {
      console.warn("⚠️ API returned 401 → logging out...")
      localStorage.removeItem("auth_token")
      window.location.href = "/login"
    }
    return Promise.reject(err)
  }
)

// --- METHODS ---
async function getKeys() {
  try {
    const res = await axios.get(`${API_BASE}/binance/keys`)
    output.value = res.data
  } catch (err) {
    console.error('Get Keys error:', err)
    output.value = `⚠️ ${err.message}`
  }
}

async function getBalance() {
  try {
    const endpoint =
      accountType.value === "demo"
        ? "/binance/balance?testnet=1"
        : "/binance/balance"

    const data = await safeGet(endpoint)

    console.log("💰 Balance:", data)

    if (Array.isArray(data)) {
      balances.value = data
    } else if (data.balances) {
      balances.value = data.balances
    } else {
      balances.value = []
    }

    selectedBalance.value = balances.value[0] ?? null
  } catch (err) {
    console.error("❌ getBalance failed:", err)
  }
}


function showSelectedBalance() {
  selectedBalance.value = balances.value.find(
    (b) => b.asset === selectedAsset.value
  )
}

async function makeTrade() {
  try {
    const endpoint =
      accountType.value === 'demo'
        ? `${API_BASE}/binance/trade?testnet=1`
        : `${API_BASE}/binance/trade`

    const res = await axios.post(endpoint, {
      symbol: 'BTCUSDT',
      side: 'BUY',
      quantity: 0.001,
    })
    output.value = res.data
  } catch (err) {
    console.error('Trade error:', err)
    output.value = `⚠️ ${err.message}`
  }
}

onMounted(() => {
  console.log('BinanceTradingPanel mounted, using token:', token)
})



// --- Fetch Price ---
async function fetchPrice() {
  try {
    const data = await safeGet(`/binance/price/${selectedSymbol.value}`)

    price.value = parseFloat(data.price || 0)
    change.value = parseFloat(data.change || 0)
    lastFetched.value = new Date().toLocaleTimeString()
  } catch (err) {
    console.error("❌ fetchPrice failed:", err)
  }
}


// --- Place Order ---
async function placeOrder() {
  try {
    const endpoint =
      accountType.value === "demo"
        ? "/binance/trade?testnet=1"
        : "/binance/trade"

    const payload = {
      symbol: selectedSymbol.value,
      side: side.value,
      type: orderType.value,
      quantity: quantity.value
    }

    const data = await safePost(endpoint, payload)

    console.log("🧾 Trade:", data)

    alert(`✅ ${side.value} ${selectedSymbol.value} order successful`)

    await getBalance()
  } catch (err) {
    const msg =
      err.response?.data?.error ||
      err.response?.data?.msg ||
      err.message

    alert(`❌ Order failed: ${msg}`)
  }
}
onMounted(async () => {
  console.log("🔑 Using token:", token)
  await getBalance()
  await fetchPrice()
  setInterval(fetchPrice, 5000)
})

watch(accountType, async (newType, oldType) => {
  console.log(`Account type changed: ${oldType} → ${newType}`)
  await getBalance()
})
</script>

<template>
  <div class="binance-panel p-4 text-white bg-black border-l border-gray-700 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Binance Trading Panel</h2>
      <select v-model="selectedSymbol" @change="fetchPrice" class="bg-gray-800 p-2 rounded">
        <option v-for="sym in symbols" :key="sym" :value="sym">{{ sym }}</option>
      </select>
    </div>

    <!-- Account type -->
 <div class="flex gap-4 mb-4" :key="accountType">
  <label>
    <input type="radio" value="spot" v-model="accountType" @change="getBalance" /> Spot
  </label>
  <label>
    <input type="radio" value="demo" v-model="accountType" @change="getBalance" /> Demo
  </label>
  <button @click="getBalance" class="btn bg-blue-600 px-3 py-1 rounded">Refresh Balance</button>
</div>

    <!-- Price Info -->
    <div class="mb-4">
      <div class="text-3xl font-bold" :class="changeColor">{{ price.toFixed(2) }}</div>
      <div :class="changeColor">{{ change.toFixed(2) }}%</div>
      <div class="text-xs text-gray-400">Last update: {{ lastFetched }}</div>
    </div>

<!-- Balance -->
<div class="border-t border-gray-700 pt-4 mb-4">
  <h3 class="font-bold mb-2">Balances</h3>

  <!-- Scrollable grid -->
  <div class="max-h-64 overflow-y-auto rounded border border-gray-700">
    <ul class=" gap-x-4 gap-y-2 p-2 text-sm">
      <li
        v-for="b in balances"
        :key="b.asset"
        class="flex flex-row bg-gray-800/40 hover:bg-gray-700/60 px-3 py-2 rounded transition"
      >
        <span class="font-medium">{{ b.asset }}: </span>
        <span class="text-gray-300">{{ Number(b.free).toFixed(4) }}</span>
      </li>
    </ul>
  </div>

  <div v-if="selectedBalance" class="mt-2 text-sm text-gray-300">
    Selected: {{ selectedBalance.asset }} — {{ selectedBalance.free }}
  </div>
  <div v-else class="mt-2 text-sm text-gray-300">
    No asset selected.
  </div>
</div>


    <!-- Order form -->
    <div class="border-t border-gray-700 pt-4">
      <div class="flex gap-4 mb-4">
        <label><input type="radio" value="BUY" v-model="side" /> Buy</label>
        <label><input type="radio" value="SELL" v-model="side" /> Sell</label>
      </div>

      <div class="mb-2">
        <label class="block text-gray-400">Order Type</label>
        <select v-model="orderType" class="bg-gray-800 p-2 rounded w-full">
          <option value="MARKET">Market</option>
          <option value="LIMIT">Limit</option>
        </select>
      </div>

      <div class="mb-2">
        <label class="block text-gray-400">Quantity</label>
        <input
          v-model.number="quantity"
          type="number"
          step="0.0001"
          min="0"
          class="w-full p-2 bg-gray-800 rounded"
        />
      </div>

      <div class="mb-2 text-sm text-gray-400">
        Estimated Cost: <span class="text-white">{{ estimatedCost }} USDT</span>
      </div>

      <button
        @click="placeOrder"
        class="w-full mt-2 p-2 rounded font-bold"
        :class="side === 'BUY' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
      >
        {{ side }} {{ selectedSymbol }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.binance-panel select,
.binance-panel input:not([type="radio"]):not([type="checkbox"]) {
  color: white;
  border: 1px solid #555;
}

input[type="radio"] {
  accent-color: #10b981;
  transform: scale(1.2);
  cursor: pointer;
}

.btn {
  font-weight: 500;
}
</style>