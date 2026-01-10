<script setup>
import { ref } from 'vue'
import axios from 'axios'

const symbol   = ref('BTCUSDT')
const interval = ref('1m')
const policy   = ref(`IF MA4 > MA9 AND Nominal > MA4 THEN BUY
IF MA4 < MA9 AND Nominal < MA9 THEN SELL
IF ABS(MA4 - MA9) <= 0.1 THEN HOLD`)

const error = ref('')
const result = ref(null)
const loading = ref(false)

function validate() {
  error.value = ''
  const lines = policy.value.split(/\r?\n/)
  const re = /^IF\s+(.+?)\s+THEN\s+(BUY|SELL|HOLD)$/i
  for (const line of lines) {
    if (!line.trim()) continue
    if (!re.test(line.trim())) {
      error.value = `語法錯誤：${line}`
      return false
    }
    if (/[^A-Za-z0-9\s\.\+\-\*\/\(\)\<\>\=\!\_]/.test(line.replace(/AND|OR|IF|THEN|BUY|SELL|HOLD|ABS/gi,''))) {
      error.value = `含不允許字元：${line}`
      return false
    }
  }
  return true
}

async function runOnce() {
  if (!validate()) return
  loading.value = true
  try {
    const { data } = await axios.post('/api/v1/algo/decision', {
      symbol: symbol.value,
      interval: interval.value,
      policy: policy.value
    })
    result.value = data
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div class="flex gap-2">
      <input v-model="symbol" class="border p-2 rounded" placeholder="Symbol e.g. BTCUSDT" />
      <select v-model="interval" class="border p-2 rounded">
        <option value="1m">1m</option>
        <option value="3m">3m</option>
        <option value="5m">5m</option>
        <option value="15m">15m</option>
      </select>
      <button @click="runOnce" :disabled="loading" class="px-4 py-2 bg-black text-white rounded">
        {{ loading ? 'Running…' : 'Run decision' }}
      </button>
    </div>

    <textarea v-model="policy" rows="8" class="w-full border p-2 rounded font-mono"></textarea>

    <p v-if="error" class="text-red-600">{{ error }}</p>

    <div v-if="result" class="border rounded p-3">
      <div><b>Decision:</b> {{ result.decision }}</div>
      <div><b>MA4:</b> {{ result.MA4 }}</div>
      <div><b>MA9:</b> {{ result.MA9 }}</div>
      <div><b>Nominal:</b> {{ result.Nominal }}</div>
      <div><b>Evaluated:</b> {{ result.evaluated }}</div>
    </div>
  </div>
</template>
