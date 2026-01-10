<template>
  <div id="WatchlistView" class="text-white">
    <header class="w-full p-1 flex justify-between items-center bg-gray-800">
      <span>自選清單</span>
      <div class="close-btn" @click="closeWatchlist">
        <font-awesome-icon :icon="['fas', 'times']" class="text-white absolute  mt-1 transform px-2 "/> 
      </div>
    </header>
    <div id="listHeader" class="w-full p-1 ">
      <div class="w-full flex flex-row bg-white text-gray-900">名稱<span>最新價</span></div>
    </div>
    <div v-if="watchlist.length" class="contentBound">
      <div v-for="item in watchlist" :key="item.stock_code" class="w-full flex flex-col listItem " @click="productItemOnClicked(item)">
        <div class="nameNnor w-full flex flex-row">
          {{ item.stock_name }} <span class="text-gray-600">{{ item.stock_code }}</span></div>
        <div class="curChanges w-full flex flex-row">
          <span class="current-price" :class="getPriceChangeClass(item.priceChange)">{{ item.currentPrice }}</span>
				  <span class="price-change" :class="getPriceChangeClass(item.priceChange)">{{ item.priceChange }}</span>
				  <span class="price-change" :class="getPriceChangeClass(item.priceChange)"> {{ item.priceChangePercent }}</span>
        </div>
      </div>
    </div>
    <div id="no-item" class="w-full h-full flex flex-col" v-else>
      <p>沒有相關項目</p>
    </div>
  </div>
</template>

<style>
/* Add your styles here */
.positive {
  color: green;
}
.negative {
  color: red;
}
#WatchlistView {
  overflow-y: hidden;
  overflow-x: hidden;
  min-height:100%;
  height: calc(100% - 10px);
}
header{
  background-color: rgb(69, 69, 69);
} 
#listHeader {
  border-bottom: 1px solid gray;
  background-color: white;
}
.contentBound {
  overflow-y: scroll;
  overflow-x: hidden;
  height: 100%;
}
.listItem {
  border-bottom: 1px solid white;
  padding : 0.5em;
  cursor: pointer;
}

.listItem:hover{
  background-color: rgb(23, 23, 23);
}

#listHeader > div, .listItem > div {
  justify-content: space-between;
}
.nameNnor {
  color: orange;
  font-size: 20px;
  font-weight: 500;
}
.curChanges {
  color: gray;
}
.nameNnor > span, .curChanges > span {
  font-size: 16px;
}
#no-item {
  justify-content: center;
  text-align: center;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.5em;
  cursor: pointer;
  right:5px;
  position: absolute;
  width: 30px;
  height:30px;
  
}

.close-btn:hover{
  background-color: #354956;
}
.text-red {
	color: rgb(185 28 28) !important;
}

.text-green {
  color: green !important;
}

.text-grey {
  color: grey !important;
}
</style>

<script>
import {API_BASE} from '../../config/config.js';
import axios from 'axios'; // Import axios for HTTP requests

const stockListDataDefault ={
      code: "00000",
			name: "未知股票",
      currentPrice: "0.00",
			priceChange: "0.000",
			priceChangePercent: "0.00%"
}


export default {
  name: 'watchlist',
  props: {
    fetchStocksData: {
      type: Function,
      required: true
    }
  },
  data() {
    return {
      watchlist: []
    };
  },
  created() {
    this.fetchWatchlist();
    this.$root.$on('update-watchlist', this.fetchWatchlist); // Listen for the update-watchlist event

  },
  beforeDestroy() {
    this.$root.$off('update-watchlist', this.fetchWatchlist); // Remove the event listener
  },
  methods: {
    async fetchWatchlist() {
      try {
        const url =  `${API_BASE}/api/getUserFavStocks.php`;
        const response = await axios.get(url, {
          headers: {
            'Content-Type': 'application/json'
          },
          params: {
            user_id: this.$store.state.user.id // Assuming user ID is stored in Vuex store
          },          
          withCredentials: true
        });
        if (response.data.success) {
             // Fetch additional stock data for each item in the watchlist
            this.watchlist = await Promise.all(response.data.watchlist.map(async item => {
              const stockData = await this.fetchStocksData({code : item.stock_code});
              if (!stockData || Object.keys(stockData).length === 0) {
                Object.assign(stockData, stockListDataDefault);
              }
              return {
                ...item,
                ...stockData
              };
            }));
           // console.log(this.watchlist);
        } else {
          console.error('Error fetching watchlist:', response.data.message);
        }
      } catch (error) {
        console.error('Error fetching watchlist:', error);
      }
    },
    productItemOnClicked(item) {
      console.log("Emitting update-stock-info with item:", item); // Add this line for debugging

     // this.$emit('row-selected', stock); // Emit event with row data
     this.$root.$emit('update-stock-info', item); // Trigger event to update the stock in marketinfo.vue
     this.$emit('product-code-change', item.stock_code);

    },
    closeWatchlist() {
      console.log("closeWatchList");
      this.$emit('close-watchlist');
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
		}
  }
  
};
</script>
