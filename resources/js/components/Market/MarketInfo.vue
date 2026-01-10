
<script setup>
import { ref, computed, watch } from 'vue'
import { useMarketData } from '@/js/composables/useMarketData.js'
import { useFavoriteStocks } from '@/js/composables/useFavouriteStocks.js'
import orderpanel from '../Trade/OrderPanel.vue'
import algoorderpanel from '../Algo/AlgoOrderPanel.vue'
import { useStockStore } from '@/js/stores/useStockStore'

const props = defineProps({
  stockProps: Object,
  showGraph: Boolean,
  stockHistory: { type: Array, default: () => [] },
  isDefaultOrderPanel: Boolean,
})

const stockStore = useStockStore()
const stock = ref(props.stockProps)
const selectedSymbol = ref(props.stockHistory?.[0]?.code || '')
const showVWAPData = ref(props.showGraph)

// market data composable
const serverProfile = { userid: 'demoUser', sseMaster: 'http://localhost', marketController: 'marketdataController.php' }
const { marketinfo, fetchMarketData } = useMarketData(serverProfile, showVWAPData)

// favorites composable
const user = { id: 1 }
const { isFavStock, toggleFavorite, checkFavStockStatus } = useFavoriteStocks(user)

const priceColor = computed(() => (marketinfo.value.nominalNet >= 0 ? 'text-green' : 'text-red'))

const onSymbolChange = () => {
  fetchMarketData()
  checkFavStockStatus(selectedSymbol.value)
}
</script>


<template>
  <div id="marketheader" class="m-0 ml-4 text-sm text-white">
    <div id="productlist">
      <label>產品編號:</label>
      <select v-model="selectedSymbol" @change="onSymbolChange" class="rounded-sm bg-gray-700 p-1">
        <option v-for="item in stockHistory" :key="item.code" :value="item.code">
          {{ item.code }} {{ item.name }}
        </option>
      </select>
      <div class="btn-list"><font-awesome-icon icon="list" /></div>
    </div>

    <div id="productShortInfo" class="w-full border-b border-white mb-4">
      <div class="flex justify-between">
        <span class="text-orange-400 text-lg font-bold">{{ marketinfo.product }}</span>
        <span>{{ marketinfo.lastUpdatedDateTime }}</span>
      </div>
      <div>
        <span :class="priceColor">{{ marketinfo.nominal }}</span>
        <span :class="priceColor">{{ marketinfo.nominalNet }}</span>
      </div>
    </div>

    <!-- VWAP panel -->
    <div v-if="showVWAPData">
      <div class="flex flex-row">
        <div class="w-40">
          <div><b>VWAP</b> {{ marketinfo.vwap }}</div>
        </div>
      </div>
    </div>

    <div v-else>
      <!-- simplified stock info -->
      <div class="stock-widget">
        <div class="stock-header flex justify-between">
          <span>{{ stock.name }} ({{ stock.code }})</span>
          <font-awesome-icon
            :icon="isFavStock ? 'fa-solid fa-star' : 'fa-regular fa-star'"
            :class="isFavStock ? 'text-yellow-400' : 'text-white'"
            @click="toggleFavorite(stock)"
          />
        </div>
        <div class="stock-price text-lg mt-2">
          <span :class="priceColor">{{ stock.currentPrice }}</span>
          <span :class="priceColor">{{ stock.priceChange }}</span>
        </div>
      </div>
    </div>

    <orderpanel v-if="isDefaultOrderPanel" />
    <algoorderpanel v-else />
  </div>
</template>

<style scoped>
.text-green {
  color: #22c55e;
}
.text-red {
  color: #ef4444;
}
.btn-list {
  display: inline-block;
  padding: 3px;
  border: 1px solid #ccc;
  cursor: pointer;
}
.stock-widget {
  background-color: black;
  color: white;
  padding: 10px;
}
</style>
