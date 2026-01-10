<script setup>
import { ref, watch, onMounted } from 'vue'
import { useStockStore } from '@/js/stores/useStockStore'
import BinanceTradePanel from './BinanceTradingPanel.vue'
import BinanceTradeHistory from './BinanceTradeHistory.vue'
import { API_BASE } from '../../config/config.js'
import axios from 'axios'

import marketinfo from '../Market/MarketInfo.vue'
import watchlist from '../Market/WatchList.vue'
import datastocktable from '../Market/dataStockTable.vue'
import datafuturetable from '../Market/dataFutureTable.vue'
import datacbbctable from '../Market/dataCBBCTable.vue'

const vwapchart = 'https://chart.altodock.com'

// ========== STATE ==========
const stockStore = useStockStore()
const activeProductTab = ref('crypto')
const investingchart = ref('')
const isWatchlistAppear = ref(false)
const showMarketInfo = ref(true)
const isShowGraph = ref(true)

const isDataStockTableVisible = ref(false)
const isDataFutureTableVisible = ref(false)
const isDataCBBCTableVisible = ref(false)
const isInvestingChartVisible = ref(false)
const isBinanceChartVisible = ref(true)
const selectedStock = ref({})

const binanceSymbols = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT', 'DOGEUSDT', 'XRPUSDT']
const selectedCrypto = ref('BTCUSDT')
const binanceChartUrl = ref('')

const binanceTab = ref('chart')

// ========== METHODS ==========

const toggleWatchlist = () => (isWatchlistAppear.value = !isWatchlistAppear.value)

const getInvestingUrl = (stockCode = '') => {
  const list = ['MHImain', 'HTImain', 'MCHmain', 'HSImain', 'HHImain', 'MTWmain', 'MCAmain', 'MNDmain']
  let currentStock = 'HK50.F'

  if (stockCode) {
    const trimmed = stockCode.replace(/^0+/, '')
    currentStock = list.includes(trimmed) ? 'HK50.F' : `HKEX%3A${trimmed}`
  }

  return `https://s.tradingview.com/widgetembed/?hideideas=1&locale=en#%7B%22symbol%22%3A%22${currentStock}%22%2C%22theme%22%3A%22dark%22%7D`
}

const handleProductTabSelect = (tab) => {
  activeProductTab.value = tab.name

  isDataStockTableVisible.value = false
  isDataFutureTableVisible.value = false
  isDataCBBCTableVisible.value = false
  isInvestingChartVisible.value = false
  isBinanceChartVisible.value = false

  switch (tab.name) {
    case 'stocks':
      isDataStockTableVisible.value = true
      break
    case 'cbbc':
      isDataCBBCTableVisible.value = true
      break
    case 'future':
      isDataFutureTableVisible.value = true
      break
    case 'crypto':
        isBinanceChartVisible.value = true
        break
    default:
      isInvestingChartVisible.value = true
  }
}

const changeChartStockCode = (code) => {
  investingchart.value = getInvestingUrl(code)
  console.log('Chart updated with:', code)
}

const handleRowSelected = async (stock) => {
  selectedStock.value = stock
  stockStore.setStock(stock)
  await fetchStocksData(stock)
  changeChartStockCode(stock.code)
  isShowGraph.value = ['MHImain', 'HTImain', 'HSImain'].includes(stock.code)
  handleProductTabSelect({ name: 'default' })
}

const getBinanceChartUrl = (symbol) => {
  const encoded = encodeURIComponent(symbol)
  return `https://s.tradingview.com/widgetembed/?symbol=${encoded}&interval=60&theme=dark&style=1&locale=en&toolbarbg=f1f3f6&hide_side_toolbar=false`
}


const fetchStocksData = async (stock) => {
  try {
    const url = `${API_BASE}/api/fetchStocksData.php`
    const { data } = await axios.get(url, {
      params: { stockCode: stock.code },
      headers: {
        'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4',
      },
      withCredentials: true,
    })

    if (data && Array.isArray(data.datas) && data.datas.length > 0) {
      console.log('Fetched stock data:', data.datas[0])
    } else {
      console.warn('No stock data found')
    }
  } catch (error) {
    console.error('Error fetching stock data:', error)
  }
}

// ========== WATCHERS ==========
watch(
  () => stockStore.selectedStock,
  (newStock) => {
    if (newStock) {
      console.log('Pinia store updated → new stock:', newStock)
      selectedStock.value = newStock
      changeChartStockCode(newStock.code)
    }
  },
  { immediate: true }
);

watch(selectedCrypto, (newSymbol) => {
  binanceChartUrl.value = getBinanceChartUrl(newSymbol)
})


// ========== LIFECYCLE ==========
onMounted(() => {
  handleProductTabSelect({ name: 'crypto' });
  investingchart.value = getInvestingUrl();
  binanceChartUrl.value = getBinanceChartUrl(selectedCrypto.value)
});
</script>

<template>
  <div id="chartview" class="w-full">
    <!-- Top product tabs -->
    <div id="productBar" class="flex justify-between w-full">
      <el-tabs type="card" v-model="activeProductTab" @tab-click="handleProductTabSelect">
        <el-tab-pane label="預設" name="default" />
        <el-tab-pane label="期貨" name="future" />
        <el-tab-pane label="牛熊" name="cbbc" />
        <el-tab-pane label="股票" name="stocks" />
        <el-tab-pane label="Binance" name="crypto" />
      </el-tabs>

      <div id="watchingProduct">
        <button class="watching_btn" @click="toggleWatchlist">★</button>
      </div>
    </div>

    <!-- Watchlist side panel -->
    <div v-if="isWatchlistAppear" class="watchlistPanel">
      <watchlist
        @product-code-change="changeChartStockCode"
        :fetch-stocks-data="fetchStocksData"
        @close-watchlist="isWatchlistAppear = false"
      />
    </div>

    <!-- Conditional Data Tables -->
    <div v-if="isDataStockTableVisible" class="data-table-panel">
      <datastocktable @row-selected="handleRowSelected" />
    </div>
    <div v-if="isDataCBBCTableVisible" class="data-table-panel">
      <datacbbctable @row-selected="handleRowSelected" />
    </div>
    <div v-if="isDataFutureTableVisible" class="data-table-panel">
      <datafuturetable @row-selected="handleRowSelected" />
    </div>

    <!-- Chart View -->
    <div v-if="isInvestingChartVisible" id="content_bound" class="w-full flex flex-row">
      <div class="content_left">
        <keep-alive>
          <marketinfo
            v-if="showMarketInfo"
            :stockProps="selectedStock"
            :showGraph="isShowGraph"
            :isDefaultOrderPanel="true"
          />
        </keep-alive>
      </div>

      <div class="content_right">
        <div class="flex flex-row">
          <span class="text-sm mx-2 text-green-500">
            <a :href="investingchart" target="chart_iframe" class="hover:text-teal-100">Full Chart</a>
          </span>
          <span class="text-sm mx-2 text-green-500">
            <a :href="vwapchart" target="chart_iframe" class="hover:text-teal-100">Full MSA Chart</a>
          </span>
        </div>

        <div class="main-chart mb15">
          <iframe
            :src="investingchart"
            frameborder="0"
            name="chart_iframe"
            id="chart_iframe"
            width="100%"
            height="100%"
            class="z-0"
          ></iframe>
        </div>
      </div>
    </div>

    <!-- Binance Chart View -->
    <div v-if="isBinanceChartVisible"  class="w-full flex flex-row">
         <div class="content_left">
        <keep-alive>
          <BinanceTradePanel />
 
        </keep-alive>
      </div>
        <div class="content_right binance-chart-container">
  <div class="text-white mb-4">
  <div class="flex gap-4 items-center mb-2">
    <label for="crypto" class="text-gray-300">Select Crypto:</label>
    <select v-model="selectedCrypto" id="crypto" class="p-2 bg-black border border-gray-600 rounded">
      <option v-for="sym in binanceSymbols" :key="sym" :value="sym">{{ sym }}</option>
    </select>

    <!-- 🔄 Toggle Tabs -->
    <div class="flex gap-2 ml-6">
      <button
        @click="binanceTab = 'chart'"
        :class="['px-3 py-1 rounded', binanceTab === 'chart' ? 'bg-blue-600' : 'bg-gray-700']"
      >
        📈 Chart
      </button>
      <button
        @click="binanceTab = 'history'"
        :class="['px-3 py-1 rounded', binanceTab === 'history' ? 'bg-blue-600' : 'bg-gray-700']"
      >
        📜 Trade History
      </button>
    </div>
  </div>

  <!-- Conditional Render -->
   <transition name="fade">
  <div v-if="binanceTab === 'chart'">
    <iframe
      :src="binanceChartUrl"
      frameborder="0"
      width="100%"
      height="800"
      class="rounded-lg border border-gray-700"
    ></iframe>
  </div>

  <div v-else>
    <BinanceTradeHistory />
  </div>
  </transition>
</div>
        </div>

    </div>

  </div>
</template>


<style scoped>
#productBar {
  justify-content: space-between;
}

.main-chart {
  position: relative;
  width: 100%;
  border: 1px solid red;
}

#chart_iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 90vh;
  border: none;
}

.content_left {
  width: 400px;
}

.content_right {
  width: calc(100% - 400px);
}

#watchingProduct {
  color: yellow;
  font-size: 2em;
}

.watching_btn {
  background-color: black;
  width: 50px;
  text-align: center;
}

.watchlistPanel {
  position: fixed;
  right: 0;
  width: 20rem;
  min-width: 300px;
  background-color: black;
  height: 100vh;
  border: 1px solid white;
  z-index: 9999;
  top: 0;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
