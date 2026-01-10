<template>
  <div>

    <div id="stockproductBar" class="clear-both z-0 w-full block flex flex-row justify-space-between relative">
         <el-tabs type="card" v-model="activeStockProductTab" @tab-click="handleStockProductTabSelect" class="float-left w-1/2 block flex">
            <el-tab-pane label="全部港股" name="all" class="selected"></el-tab-pane>
            <el-tab-pane label="香港主板" name="hkmain"></el-tab-pane>
            <el-tab-pane label="創業版" name="inno"></el-tab-pane>
            <el-tab-pane label="恒生指數" name="hsi"></el-tab-pane>
            <el-tab-pane label="港股ADR" name="hkadr"></el-tab-pane>
         </el-tabs>
        
         <div class="lang float-right w-1/2 text-right block flex">
            <label>
                <input type="radio" value="en" v-model="language"> English
            </label>
            <label>
                <input type="radio" value="zh" v-model="language"> 中文
            </label>
        </div>

    </div>

    <table v-if="stocks.length">
      <thead>
        <tr>
          <th class="text-center">{{ headers[language].id }}</th>
          <th class="text-left">{{ headers[language].code }}</th>
          <th class="text-left">{{ headers[language].name }}</th>
          <th class="text-right">{{ headers[language].latestPrice }}</th>
          <th class="text-right">{{ headers[language].priceChange }}</th>
          <th class="text-right">{{ headers[language].percentageChange }}</th>
          <th class="text-right">{{ headers[language].tradingVolume }}</th>
          <th class="text-right">{{ headers[language].tradingAmount }}</th>
          <th class="text-right">{{ headers[language].marketCap }}</th>
          <th class="text-right">{{ headers[language].peRatio }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="stock in stocks" :key="stock.code" @click="handleRowClick(stock)">
          <td class="text-center">{{ stock.id }}</td>
          <td class="text-left">{{ stock.code }}</td>
          <td class="text-left">{{ stock.name }}</td>
          <td :class="getPriceChangeClass(stock.price_change)" class="text-right">{{ stock.latest_price }}</td>
          <td :class="getPriceChangeClass(stock.price_change)" class="text-right">{{ stock.price_change }}</td>
          <td :class="getPriceChangeClass(stock.price_change)" class="text-right">{{ stock.percentage_change }}</td>
          <td class="text-right">{{ formatNumber(stock.trading_volume) }}</td>
          <td class="text-right">{{ formatNumber(stock.trading_amount) }}</td>
          <td class="text-right">{{ formatNumber(stock.market_cap) }}</td>
          <td class="text-right">{{ formatNumber(stock.pe_ratio) }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else>Loading data...</p>
  </div>
</template>

<script>
import axios from 'axios';
import {API_BASE} from '../../config/config.js';

export default {
  name: 'dataStockTable',
  data() {
    return {
      language: 'zh', // Default language is Chinese
      stocks: [], // Array to hold the data
      headers: {
        en: {
          id: 'ID',
          code: 'Code',
          name: 'Name',
          latestPrice: 'Latest Price',
          priceChange: 'Price Change',
          percentageChange: 'Percentage Change',
          tradingVolume: 'Trading Volume',
          tradingAmount: 'Trading Amount',
          marketCap: 'Market Cap',
          peRatio: 'P.E.'
        },
        zh: {
          id: '序號',
          code: '代碼',
          name: '名稱',
          latestPrice: '最新價',
          priceChange: '漲跌額',
          percentageChange: '漲跌幅',
          tradingVolume: '成交股數',
          tradingAmount: '成交額',
          marketCap: '總市值',
          peRatio: '市盈率'
        }
      },
      activeStockProductTab : "all"
    };
  },
  created() {
    // Fetch data when the component is created
    this.fetchStockData();
  },
  methods: {
    async fetchStockData() {
      try {
        const url = `${API_BASE}/api/getStockTableJsonSim.php`;
        const response = await axios.get(url, {
          headers: {
            'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4',
            'Content-Type': 'application/json'
          },
          withCredentials: true
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
    handleStockProductTabSelect(tab){
        this.activeStockProductTab = tab.name;
        console.log("tab changed? " + this.activeStockProductTab);
        marketinfoView.methods.changeOnShow((tab.name === "default"));
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

th {
  background-color: #f2f2f2;
  color:#1f1f1f;
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
</style>
