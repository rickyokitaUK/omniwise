<template>
  <div>

    <div id="futureproductBar" class="clear-both z-0 w-full block flex flex-row justify-space-between relative">
         <el-tabs type="card" v-model="activeFutureProductTab" @tab-click="handleFutureProductTabSelect" class="float-left w-1/2 block flex">
            <el-tab-pane label="全部期貨" name="all" class="selected"></el-tab-pane>
            <el-tab-pane label="港期" name="hk"></el-tab-pane>
            <el-tab-pane label="美期" name="us"></el-tab-pane>
            <el-tab-pane label="新加坡" name="sg"></el-tab-pane>
            <el-tab-pane label="日本" name="jp"></el-tab-pane>
            <el-tab-pane label="馬來西亞" name="my"></el-tab-pane>
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
      <th class="text-right">{{ headers[language].latest_price }}</th>
      <th class="text-right">{{ headers[language].price_change }}</th>
      <th class="text-right">{{ headers[language].percentage_change }}</th>
      <th class="text-right">{{ headers[language].buy_price }}</th>
      <th class="text-right">{{ headers[language].sell_price }}</th>
      <th class="text-right">{{ headers[language].buy_volume }}</th>
      <th class="text-right">{{ headers[language].trade_volume }}</th>
      <th class="text-right">{{ headers[language].holding_volume }}</th>
      <th class="text-right">{{ headers[language].daily_change_volume }}</th>
      <th class="text-right">{{ headers[language].open_price }}</th>
      <th class="text-right">{{ headers[language].highest_price }}</th>
      <th class="text-right">{{ headers[language].lowest_price }}</th>
      <th class="text-right">{{ headers[language].last_trading_day }}</th>
      <th class="text-right">{{ headers[language].yesterday_close }}</th>
      <th class="text-right">{{ headers[language].yesterday_settle }}</th>
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
      <td class="text-right">{{ stock.buy_price }}</td>
      <td class="text-right">{{ stock.sell_price }}</td>
      <td class="text-right">{{ stock.buy_volume }}</td>
      <td class="text-right">{{ stock.trade_volume }}</td>
      <td class="text-right">{{ stock.holding_volume }}</td>
      <td class="text-right">{{ stock.daily_change_volume }}</td>
      <td class="text-right">{{ stock.open_price }}</td>
      <td class="text-right">{{ stock.highest_price }}</td>
      <td class="text-right">{{ stock.lowest_price }}</td>
      <td class="text-right">{{ stock.last_trading_day }}</td>
      <td class="text-right">{{ stock.yesterday_close }}</td>
      <td class="text-right">{{ stock.yesterday_settle }}</td>
    </tr>
  </tbody>
</table>


    <p v-else>Loading data...</p>
  </div>
</template>

<script>
import axios from 'axios';
import {API_BASE} from '../config.js';

export default {
  name: 'dataFutureTable',
  data() {
    return {
      language: 'zh', // Default language is Chinese
      stocks: [], // Array to hold the data
      headers: {
        en: {
          id: 'ID',
          code: 'Code',
          name: 'Name',
          latest_price: 'Latest Price',
          price_change: 'Price Change',
          percentage_change: 'Percentage Change',
          buy_price: 'Buy Price',
          sell_price: 'Sell Price',
          buy_volume: 'Buy Volume',
          trade_volume: 'Trade Volume',
          holding_volume: 'Holding Volume',
          daily_change_volume: 'Daily Change Volume',
          open_price: 'Open Price',
          highest_price: 'Highest Price',
          lowest_price: 'Lowest Price',
          last_trading_day: 'Last Trading Day',
          yesterday_close: 'Yesterday Close',
          yesterday_settle: 'Yesterday Settle'
        },
        zh: {
          id: '序號',
          code: '代碼',
          name: '名稱',
          latest_price: '最新價',
          price_change: '漲跌額',
          percentage_change: '漲跌幅',
          buy_price: '買入價',
          sell_price: '沽出價',
          buy_volume: '買量',
          trade_volume: '成交量',
          holding_volume: '持倉量',
          daily_change_volume: '日增倉',
          open_price: '今開',
          highest_price: '最高',
          lowest_price: '最低',
          last_trading_day: '最後交易日',
          yesterday_close: '昨結',
          yesterday_settle: '昨收'
        }
      },      
      activeFutureProductTab : "all"
    };
  },
  created() {
    // Fetch data when the component is created
    this.fetchFutureData();
  },
  methods: {
    async fetchFutureData() {
      try {
        const url = `${API_BASE}/api/getFutureTableJsonSim.php`;
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
    handleFutureProductTabSelect(tab){
        this.activeFutureProductTab = tab.name;
        console.log("tab changed? " + this.activeFutureProductTab);
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
</style>
