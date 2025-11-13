<template>
  <div>

    <div id="cbbcproductBar" class="clear-both z-0 w-full block flex flex-row justify-space-between relative w-1/2">
         <el-tabs type="card" v-model="activeCBBCProductTab" @tab-click="handleCBBCProductTabSelect" class="float-left w-1/2 block flex">
            <el-tab-pane label="全部牛熊" name="all" class="selected"></el-tab-pane>
            <el-tab-pane label="牛證" name="bull"></el-tab-pane>
            <el-tab-pane label="熊證" name="bear"></el-tab-pane>
            <el-tab-pane label="認購" name="call"></el-tab-pane>
            <el-tab-pane label="認沽" name="put"></el-tab-pane>
            <el-tab-pane label="界內證" name="hkadr"></el-tab-pane>
         </el-tabs>

         
        
         <div class="lang float-right w-1/4 text-center block flex">
            <div class="search-button-container" :class="{active : searchPanelVisible }">
              <button @click="toggleSearchPanel" style="width:120px;" >
                {{ searchPanelVisible ? '隱藏搜尋 ▴' : '進階搜尋 ▾' }}
              </button>
            </div>
            <div class="ml-8">
            <label>
                <input type="radio" value="en" v-model="language"> English
            </label>
            <label>
                <input type="radio" value="zh" v-model="language"> 中文
            </label>
          </div>
        </div>

    </div>



    <!-- 搜尋面板 -->
    <div v-if="searchPanelVisible" class="search-panel">
      <cbbcSearchOption @search="handleSearch"></cbbcSearchOption>
    </div>

    <table v-if="stocks.length">
      <thead>
        <tr>
          <th class="text-center">{{ headers[language].code }}</th>
          <th class="text-left">{{ headers[language].name }}</th>
          <th class="text-left">{{ headers[language].latestPrice }}</th>
          <th class="text-right">{{ headers[language].change }}</th>
          <th class="text-right">{{ headers[language].change_percent }}</th>
          <th class="text-right">{{ headers[language].volume }}</th>
          <th class="text-right">{{ headers[language].turnover }}</th>
          <th class="text-right">{{ headers[language].leverage_times }}</th>
          <th class="text-right">{{ headers[language].recall_price }}</th>
          <th class="text-right">{{ headers[language].exercise_price }}</th>
          <th class="text-center">{{ headers[language].upper_limit_price }}</th>
          <th class="text-left">{{ headers[language].lower_limit_price }}</th>
          <th class="text-right">{{ headers[language].last_trading_day }}</th>
          <th class="text-right">{{ headers[language].outstanding_ratio }}</th>
          <th class="text-right">{{ headers[language].outstanding_volume }}</th>
          <th class="text-right">{{ headers[language].status }}</th>
          <th class="text-right">{{ headers[language].premium }}</th>
          <th class="text-right">{{ headers[language].sensitivity }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="stock in stocks" :key="stock.code" @click="handleRowClick(stock)">
          <td class="text-left">{{ stock.code }}</td>
          <td class="text-left">{{ stock.name }}</td>
          <td :class="getPriceChangeClass(stock.change)" class="text-right">{{ stock.latest_price }}</td>
          <td :class="getPriceChangeClass(stock.change)" class="text-right">{{ stock.change }}</td>
          <td :class="getPriceChangeClass(stock.change)" class="text-right">{{ stock.change_percent }}</td>
          <td class="text-right">{{ formatNumber(stock.volume) }}</td>
          <td class="text-right">{{ formatNumber(stock.turnover) }}</td>
          <td class="text-right">{{ formatNumber(stock.leverage_times) }}</td>
          <td class="text-right">{{ formatNumber(stock.recall_price) }}</td>
          <td class="text-right">{{ formatNumber(stock.exercise_price) }}</td>
          <td class="text-right">{{ formatNumber(stock.upper_limit_price) }}</td>
          <td class="text-right">{{ formatNumber(stock.lower_limit_price) }}</td>
          <td class="text-right">{{ formatNumber(stock.last_trading_day) }}</td>
          <td class="text-right">{{ formatNumber(stock.outstanding_ratio) }}</td>
          <td class="text-right">{{ formatNumber(stock.outstanding_volume) }}</td>
          <td class="text-right">{{ formatNumber(stock.status) }}</td>
          <td class="text-right">{{ formatNumber(stock.premium) }}</td>
          <td class="text-right">{{ formatNumber(stock.sensitivity) }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else>Loading data...</p>
  </div>
</template>

<script>
import axios from 'axios';
import {API_BASE} from '../config.js';
import cbbcSearchOption from './searchCBBCOptions.vue';

export default {
  name: 'dataCBBCTable',
  components: {
    cbbcSearchOption,
  },
  data() {
    return {
      language: 'zh', // Default language is Chinese
      stocks: [], // Array to hold the data
      searchPanelVisible: false, // 控制搜尋面板的顯示與隱藏
      headers: {
        en: {
          id: 'ID',
          code: 'Code',
          name: 'Name',
          latestPrice: 'Latest Price',
          change: 'Price Change',
          change_percent: 'Percentage Change',
          volume: 'Trading Volume',
          turnover: 'Trading Turnover',
          leverage_times: 'Leverage Times',
          recall_price: 'Recall Price',
          exercise_price: 'Exercise Price',
          upper_limit_price: 'Upper Limit Price',
          lower_limit_price: 'Lower Limit Price',
          last_trading_day: "Last trading day",
          outstanding_ratio: "Outstanding Ratio",
          outstanding_volume: "Outstanding Volume",
          status: "Status",
          premium: "Premium",
          sensitivity: "Sensitivity"
          
        },
        zh: {
          id: '序號',
          code: "代碼",
          name: "名稱",
          latestPrice: "最新價",
          change: "漲跌額",
          change_percent: "漲跌幅",
          volume: "成交量",
          turnover: "成交額",
          leverage_times: "槓桿比率(倍)",
          recall_price: "收回價",
          exercise_price: "行使價",
          upper_limit_price: "上限價",
          lower_limit_price: "下限價",
          last_trading_day: "最後交易日",
          outstanding_ratio: "街貨比",
          outstanding_volume: "街貨量",
          status: "狀態",
          premium: "溢價",
          sensitivity: "敏感度"
        }
      },
      activeCBBCProductTab : "all"
    };
  },
  created() {
    // Fetch data when the component is created
    this.fetchCBBCData();
  },
  methods: {
    async fetchCBBCData() {
      try {
        const url = `${API_BASE}/api/getCbbcTableJsonSim.php`;
        const response = await axios.get(url, {
          headers: {
            'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4'
          }
        });

        if (response.status !== 200) {
          throw new Error('Network response was not ok.');
        }

        const data = response.data;
        this.stocks = data.datas || []; // Assuming JSON structure is { datas: [...] }
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    },
    getPriceChangeClass(priceChange) {
      const numericPriceChange = parseFloat(priceChange);
      if (numericPriceChange > 0) {
        return 'text-green';
      } else if (numericPriceChange < 0) {
        return 'text-red';
      } else {
        return 'text-grey';
      }
    },
    handleRowClick(stock) {
      this.$emit('row-selected', stock); // Emit event with row data
      // Trigger event to update the stock in marketinfo.vue
      this.$emit('update-stock', stock);
    },
    formatNumber(value) {
      const num = parseFloat(value);
      if (isNaN(num)) return value;

      const formattedNum = num.toFixed(2).replace(/\.?0+$/, ''); // Remove trailing zeroes
      
      if (this.language === 'en') {
        if (num >= 1e9) {
          return (num / 1e9).toFixed(2).replace(/\.?0+$/, '') + 'B';
        } else if (num >= 1e6) {
          return (num / 1e6).toFixed(2).replace(/\.?0+$/, '') + 'M';
        } else if (num >= 1e3) {
          return (num / 1e3).toFixed(2).replace(/\.?0+$/, '') + 'K';
        } else {
          return formattedNum;
        }
      } else if (this.language === 'zh') {
        if (num >= 1e8) {
          return (num / 1e8).toFixed(2).replace(/\.?0+$/, '') + '億';
        } else if (num >= 1e4) {
          return (num / 1e4).toFixed(2).replace(/\.?0+$/, '') + '萬';
        } else {
          return formattedNum;
        }
      }
    },
    handleCBBCProductTabSelect(tab){
        this.activeCBBCProductTab = tab.name;
        console.log("tab changed? " + this.activeCBBCProductTab);
        marketinfoView.methods.changeOnShow((tab.name === "default"));
    },
    toggleSearchPanel() {
      this.searchPanelVisible = !this.searchPanelVisible;
    },
    handleSearch(filters) {
      // 在這裡處理來自 cbbcSearchOption 的搜尋過濾器
      console.log('搜尋條件：', filters);
      // 可以根據 filters 更新表格數據
    }
  }
};
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;

}

th, td {
  padding: 8px;
  color:#f2f2f2;
}

tr:nth-child(odd){
  background-color: #131722;
}

tr:nth-child(even){
  background-color: #1f2638;
}

tr:hover{
  background-color: #414d6b;
  cursor: pointer;
}



th {
  background-color: #f2f2f2;
  color:#1f1f1f;
}

.lang{
    color: #f2f2f2;
}

label {
  margin-right: 10px;
}

.text-red {
    color: rgb(185 28 28);
}

.text-green {
  color: green;
}

.text-grey {
  color: grey;
}

.search-button-container{
  color:#FFFFFF;
  padding: 5px;
  border: 1px solid #FFFFFF;
  border-radius: 3px;
}

.search-button-container:hover, .search-button-container.active{
  background-color: #414d6b;
}
</style>
