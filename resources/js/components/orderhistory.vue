<template>
  <div class="trade-history">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="交易歷史" name="trade-history">
        <div class="history-table">
          <el-table stripe :data="tradeHistoryData" :header-cell-style="{ background: '#1c1d22', color: '#FFF' }" :cell-style="{ background: '#1c1d22', color: '#FFF'}">
          <el-table-column prop="time" label="時間">
            <template slot-scope="scope">
              <span v-html="scope.row.time"></span>
            </template>
          </el-table-column>
          <el-table-column prop="type" label="類型"></el-table-column>
          <el-table-column prop="status" label="狀態"></el-table-column>
          <el-table-column prop="market" label="市場"></el-table-column>
          <el-table-column prop="lev" label="槓桿"></el-table-column>
          <el-table-column prop="pnl" label="收益">
            <template slot-scope="scope">
              <span :class="{
                'positive-pnl': scope.row.pnl > 0, 
                'negative-pnl': scope.row.pnl < 0,
                'neutral-pnl': scope.row.pnl === 0 || scope.row.pnl === null || scope.row.pnl === undefined
              }">
                {{ scope.row.pnl !== null && scope.row.pnl !== undefined ? (scope.row.pnl > 0 ? '+' + scope.row.pnl.toFixed(2) + '%' : scope.row.pnl.toFixed(2) + '%') : '-' }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="amount" label="數量"></el-table-column>
          <el-table-column prop="unit_price" label="單價"></el-table-column>
          <el-table-column prop="transaction" label="交易">
            <template slot-scope="scope">
              <a :href="scope.row.transaction_url" target="_blank">Etherscan</a>
            </template>
          </el-table-column>

          </el-table>
        </div>
      </el-tab-pane>
      <el-tab-pane label="資金歷史" name="funding-history"></el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeTab: "trade-history",
      tradeHistoryData: [
      {
          time: "2024-04-16 15:09:45 <br/> 1 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "3.84X",
          pnl: 4.5,
          amount: "47.6479 ETH",
          unit_price: "249.80 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-11 14:23:15 <br/> 5 天前",
          type: "LONG",
          status: "開倉",
          market: "MHI",
          lev: "3.96X",
          pnl: null,
          amount: "47.6473 ETH",
          unit_price: "246.99 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-10 11:37:20 <br/> 6 天前",
          type: "LONG",
          status: "開倉",
          market: "MHI",
          lev: "3.05X",
          pnl: null,
          amount: "525.7708 ETH",
          unit_price: "227.83 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-09 16:01:55 <br/> 7 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "3.34X",
          pnl: 21.83,
          amount: "121.2078 ETH",
          unit_price: "204.97 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-09 12:47:30 <br/> 7 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "3.40X",
          pnl: 18.49,
          amount: "70.0000 ETH",
          unit_price: "203.29 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-09 11:15:10 <br/> 7 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "3.26X",
          pnl: 25.86,
          amount: "60.0000 ETH",
          unit_price: "207.00 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-09 11:02:00 <br/> 7 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "3.26X",
          pnl: 26.08,
          amount: "100.0000 ETH",
          unit_price: "207.08 DAI",
          transaction_url: "https://etherscan.io/"
      },
      {
          time: "2024-04-09 10:45:35 <br/> 7 天前",
          type: "LONG",
          status: "平倉",
          market: "MHI",
          lev: "2.41X",
          pnl: 72.28,
          amount: "169.1878 ETH",
          unit_price: "211.00 DAI",
          transaction_url: "https://etherscan.io/"
      }
  ]
    };
  }
};
</script>

<style scoped>
.trade-history {
  background-color: #1c1d22;
  color: #ffffff;
  padding: 20px;
}

.history-table {
  margin-top: 20px;
}

.el-tabs__item {
  color: #9e9e9e;
}

.el-tabs__item.is-active {
  color: #ffffff;
}

.el-table th, .el-table td {
  background-color: #2b2b2b;
  color: #ffffff;
}

.positive-pnl {
  color: #21c566;
}

.negative-pnl {
  color: #ff4d4f;
}

.el-table td{
 cursor: pointer;
}

a {
  color: #4ca0ff;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
</style>
