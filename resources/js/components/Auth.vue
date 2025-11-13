<script setup>
import { ref } from 'vue'

const apiBase = '/api'

const regUser = ref('')
const regEmail = ref('')
const regPassword = ref('')

const loginUserName = ref('')
const loginPassword = ref('')

const output = ref('')
const token = ref(null)

const balances = ref([])   // store balances
const selectedAsset = ref('') // which asset is selected
const selectedBalance = ref(null)
const accountType = ref('spot') // "spot" or "funding"


const savedToken = localStorage.getItem('auth_token')
if (savedToken) {
  token.value = savedToken
}

async function registerUser () {
  try {
    const res = await fetch(`${apiBase}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        username: regUser.value,
        email: regEmail.value,
        password: regPassword.value
      })
    })

    // ---- debug: log status and raw text first
    const raw = await res.text()
    console.log('Register status:', res.status)
    console.log('Register raw response:', raw)

     let data
    try {
      data = JSON.parse(raw)
    } catch (e) {
      // fallback if server returned non-JSON
      output.value = raw
      return
    }

    if (!res.ok) {
      // Laravel sends validation errors as { message, errors:{field:[…]} }
      if (data.errors) {
        // flatten errors into one string or array
        const messages = Object.values(data.errors).flat().join('\n')
        output.value = `❌ ${data.message}\n${messages}`
      } else {
        output.value = `❌ HTTP ${res.status}: ${data.message || raw}`
      }
      return
    }

    // success
    output.value = data
  } catch (err) {
    console.error('Register error:', err)
    output.value = `⚠️ JS error: ${err}`
  }
}

async function loginUser () {
  try {
    const res = await fetch(`${apiBase}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        username: loginUserName.value,
        password: loginPassword.value
      })
    })

    const raw = await res.text()
    console.log('Login status:', res.status)
    console.log('Login raw response:', raw)

    if (!res.ok) {
      output.value = `HTTP ${res.status}\n${raw}`
      return
    }

    const data = JSON.parse(raw)
    output.value = data
    if (data.token) {
      token.value = data.token;
      localStorage.setItem('auth_token', data.token);
      alert('✅ Logged in, token stored!');
    }
  } catch (err) {
    console.error('Login error:', err)
    output.value = `⚠️ JS error: ${err}`
  }
}

function logoutUser () {
  token.value = null
  localStorage.removeItem('auth_token')
  balances.value = []
  selectedBalance.value = null
  output.value = '👋 Logged out'
}

async function getKeys () {
  const res = await fetch(`${apiBase}/binance/keys`, {
    headers: { Authorization: 'Bearer ' + token.value }
  })
  output.value = await res.json()
}

async function getBalance () {
  const endpoint = accountType.value === 'demo'
    ? `${apiBase}/binance/balance?testnet=1`
    : `${apiBase}/binance/balance`

  const res = await fetch(endpoint, {
    headers: { Authorization: `Bearer ${token}` }
  })

  const raw = await res.text()
  console.log('Balance raw:', raw)

  let data
  try {
    data = JSON.parse(raw)
  } catch (e) {
    output.value = `⚠️ Server returned non-JSON:\n${raw}`
    return
  }

  // handle both spot and funding
  if (Array.isArray(data)) {
    balances.value = data
  } else if (data.balances) {
    balances.value = data.balances
  } else {
    balances.value = []
  }

  selectedAsset.value = balances.value[0]?.asset || ''
  showSelectedBalance()

  output.value = data
}


function showSelectedBalance () {
  selectedBalance.value = balances.value.find(
    b => b.asset === selectedAsset.value
  )
}

async function makeTrade () {
  // Route trades depending on accountType
  const endpoint = accountType.value === 'demo'
    ? `${apiBase}/binance/trade?testnet=1`
    : `${apiBase}/binance/trade`

  const res = await fetch(endpoint, {
    method:'POST',
    headers: {
      'Content-Type':'application/json',
      Authorization: 'Bearer ' + token.value
    },
    body: JSON.stringify({
      symbol: 'BTCUSDT',
      side: 'BUY',
      quantity: 0.001
    })
  })
  output.value = await res.json()
}
</script>


<template>
  <div class="p-6 max-w-lg mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Binance Demo</h1>

    <!-- Show Register + Login if no token -->
    <div v-if="!token">
      <div class="border p-4 rounded">
        <h2>Register</h2>
        <input v-model="regUser" placeholder="Username" class="input" />
        <input v-model="regEmail" placeholder="Email" class="input" />
        <input v-model="regPassword" type="password" placeholder="Password" class="input" />
        <button @click="registerUser" class="btn">Register</button>
      </div>

      <div class="border p-4 rounded">
        <h2>Login (username)</h2>
        <input v-model="loginUserName" placeholder="Username" class="input" />
        <input v-model="loginPassword" type="password" placeholder="Password" class="input" />
        <button @click="loginUser" class="btn">Login</button>
      </div>
    </div>

    <!-- Show after login -->
    <div v-else class="border p-4 rounded bg-green-100">
      <h2 class="text-green-800 font-bold">✅ Logged in</h2>
      <button @click="logoutUser" class="btn bg-red-500 hover:bg-red-600">Logout</button>

    <!-- Account type toggle -->
      <div class="mt-4">
        <label><input type="radio" value="spot" v-model="accountType" /> Spot</label>
        <label class="ml-4"><input type="radio" value="demo" v-model="accountType" /> Demo</label>
      </div>

      <!-- Actions -->
      <div class="border p-4 rounded mt-4">
        <button @click="getBalance" class="btn">Get {{ accountType }} Balance</button>
        <button @click="getKeys" class="btn">Get Binance Keys</button>
        <button @click="makeTrade" class="btn">Test Trade</button>
      </div>

      <!-- Asset selector -->
      <div v-if="balances.length" class="border p-4 rounded mt-4">
        <label for="asset">Select Asset:</label>
        <select v-model="selectedAsset" @change="showSelectedBalance" class="input">
          <option v-for="b in balances" :key="b.asset" :value="b.asset">{{ b.asset }}</option>
        </select>

        <div v-if="selectedBalance" class="mt-3">
          <p><strong>{{ selectedBalance.asset }}</strong></p>
          <p>Free: {{ selectedBalance.free }}</p>
          <p>Locked: {{ selectedBalance.locked }}</p>
        </div>
      </div>
    </div>

    <!-- Debug output -->
    <pre class="bg-gray-100 p-4 mt-4 whitespace-pre-wrap">{{ output }}</pre>
  </div>
</template>

<style scoped>
.input {display:block; margin:0.5rem 0; padding:0.5rem; border:1px solid #ccc; border-radius:4px;}
.btn {background:#3b82f6; color:white; padding:0.5rem 1rem; margin:0.25rem; border:none; border-radius:4px; cursor:pointer;}
.btn:hover {background:#2563eb;}
</style>