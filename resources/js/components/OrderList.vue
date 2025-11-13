
<script>
    import { API_BASE } from '@/js/config.js'
    import axios from 'axios'
    import {CommonFunc} from '../../../public/js/common.js';
    import TradeHistory from './orderhistory.vue'; // Import the TradeHistory component

    const defaultprofileList = [
          { title: "--default-- (0/0/0)", value: "0,0,0", key: 0 },
          { title: "Profile1 (30/30/90)", value: "30,30,90", key: 1 },
          { title: "Profile2 (10/10/80)", value: "10,10,80", key: 2 },
          { title: "Profile3 (20/20/70)", value: "20,20,70", key: 3 },
        ];


    export default {
      name: "orderlist",
      props: ["marketinfo"],
      components:{
        TradeHistory, // Register the TradeHistory component
      },
      data: function() {
        return {
          activeTab : "open-orders", // activeTab: "all",
          timeraccount: null,
          orderDataList: [],
          activeStatus: [],
          selectedProfile: [],
          profileList: defaultprofileList,
          dates: ["2018-09-15", "2018-09-20"],
          commonfunc: new CommonFunc(),
          language: 'zh', // Default language,
          translations:
            {
              "en": {
                "open_orders": "Open Orders",
                "closed_orders": "Closed Orders",
                "order_history": "Order History",
                "trade_history": "Trade History",
                "date_time": "Date/Time",
                "product": "Product",
                "position_qty": "Position/Qty",
                "cost": "Cost",
                "profile": "Profile",
                "cut_gain": "Cut Gain",
                "cut_loss": "Cut Loss",
                "trailing_stop": "Trailing Stop",
                "pl": "P/L",
                "amount": "Amount",
                "action": "Action",
                "no_data": "No data",
                "add": "Add",
                "delete": "Delete",
                "edit": "Edit",
                "save": "Save",
                "cancel": "Cancel",
                "transaction_no" : "Trans. No."
              },
              "zh": {
                "open_orders": "未平倉合約 (2)",
                "closed_orders": "已完成合約",
                "order_history": "訂單歷史",
                "trade_history": "交易歷史",
                "date_time": "日期/時間",
                "product": "產品",
                "position_qty": "價位/數量",
                "cost": "成本",
                "profile": "簡介",
                "cut_gain": "止盈",
                "cut_loss": "止損",
                "trailing_stop": "追蹤止損",
                "pl": "盈虧",
                "amount": "金額",
                "action": "操作",
                "no_data": "沒有數據",
                "add": "添加",
                "delete": "刪除",
                "edit": "編輯",
                "save": "保存",
                "cancel": "取消",
                "transaction_no" : "交易編號"
              }
            }
          
        }
      },
      computed: {
        // Computed property to get the current language texts
        t() {
          return this.translations[this.language];
        }
      },
      mounted: function () {
        this.refreshOrderList();

        // this.timeraccount = setInterval(() => {
        //   //Object.keys(this.accountList).forEach(userid => {
        //     this.refreshOrderList();
        //   //});
        // }, 20000);
      },
      methods: {
        handleTabSelect(tab, event){
              // console.log(tab, event);
          this.activeTab = tab.name;
          console.log("tab changed? " + this.activeTab);
          this.refreshOrderList();
        },
        switchLanguage(lang) {
          this.language = lang;
        },
        getOrderPositionText(position, commit_avg_price, order_count) {
          return (
            '<span class="sup ' + position + '">' + position + "</span> " + commit_avg_price + "<br/>" + order_count
          );
        },
        handleOptFilterSelect(tab, event) {
          console.log(tab, event);
        },
        refreshOrderList() {
      //   console.log("Refresh Order List Handler -- BEGIN --");

          var orderdata = {
            action: "get_order_record",
            userid: "rickyDev",
            status: 1,
            date_fr: "2020-09-27",
          };

          this.orderDataList = [];

          // var url = host + "/v1/orders"
          var url = `${API_BASE}/api/getPositionsSim.php`
            // http.post("api/WiseSyncController.php", orderdata , function (response) {
            axios.get(url, {
              headers: {
                'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4'
              }
            }).then(function(response) {
                try {
                  var result = response.data;	
                  console.log('refreshOrderList result ' + result);
                  
                  if(typeof result == 'object'){
                    if(result.datas.length > 0){
                      console.log('refreshOrderList result get, should do layout update');
                      
                      
                      result.datas.forEach(row => {
                        	// console.log(row);
                          var date = this.commonfunc.formatDate(row.orderTime, true)  
                          var time = this.commonfunc.formatTime(row.orderTime)
                          var cost = row.orderPrice * row.quantity;
                          var commit_avg_price = 0;
                          var cut_gain = 0;
                          var cut_loss = 0;
                          var trailing_stop = 0;
                          var reslist = {
                              id: row.id,
                              stockCode : row.stockCode,
                              product: row.product,
                              date: `${date}\n${time}` , //this.datetimeFormat(row.opentime),
                              position: row.position,
                              commit_avg_price: row.commitAvgPrice,
                              qty: row.quantity,
                              cost: row.cost,
                              profitThreshold: cut_gain,
                              lossThreshold: cut_loss,
                              trailingStop: trailing_stop,
                          };
                        //   if (
                        //     !this.activeStatus ||
                        //     typeof this.activeStatus[row.id] == "undefined"
                        //   ) {
                        //     this.activeStatus[row.id] = false;
                        //   }

                        //   if (
                        //     !this.selectedProfile ||
                        //     typeof this.selectedProfile[row.id] == "undefined"
                        //   ) {
                        //     this.selectedProfile[row.id] = "0,0,0";
                        //   }

                          this.orderDataList.push(reslist);
                          // this.orderDataList[row_id] = reslist;
                      });
                      	console.log("--orderDataList LIST-- \n" );
                        console.log(this.orderDataList);                      
                        //       var orderResult = response.data.order_result;

                    }else{
                      console.log('refreshOrderList  no result ');
                    }
                  }
                  
                }catch(err) {
                  console.log('refreshOrderList  data parse err , ' + err);
                  //  this.alertify = true; 
                }
                
              }.bind(this),function(){
                  console.log('refreshOrderList network error');
                  //  this.alertify = true;
              });
          // console.log("Refresh Order List Handler -- END --");
        },
        datetimeFormat(datetime) {
          var datetimestr = datetime.split(" ");

          return (
            datetimestr[0].replace(/-/g, "/") +
            " <span class='sub time'>" +
            datetimestr[1] +
            "</span>"
          );
        },
        changeAction(id, action) {
          switch (action) {
              case "saveProfile":
                var row = this.orderDataList.find(x => x.id === id)
                var data = {};
                data.action = "order_setting";
                data.host = server;
                data.port = port;
                data.userid = userid;
                data.orderId = row.profitThreshold;
                data.maxLoss = row.lossThreshold;
                data.trailingStop = row.trailingStop;
                break;
              case "changeProfile":
                var profile = this.selectedProfile[id].split(",");
                var row = this.orderDataList.find(x => x.id === id)
                row.profitThreshold = profile[0];
                row.lossThreshold = profile[1];
                row.trailingStop = profile[2];
                this.activeStatus[id] = true;
                break;
              case "editProfile":
                if (!this.activeStatus[id]) {
                  this.activeStatus[id] = true;
                } else {
                  this.activeStatus[id] = false;
                }
                break;
              case "addProfileList":
                break;
              case "delProfileList":
                break;
            }
            this.$forceUpdate();
        },
      },
    };
</script>
<template>
  <div id="order_list">
   
    <!-- <v-row>
      <v-col cols="3" sm="6">
        <v-text-field
          v-model="dates"
          label="Date range"
          prepend-icon="mdi-calendar"
          readonly
        ></v-text-field>
        model: {{ dates }}
      </v-col>
      <v-col cols="12" sm="6" class="absolute hidden">
        <v-date-picker v-model="dates" range></v-date-picker>
      </v-col>
    </v-row> -->
    <div class="flex flex-grow flex-col w-full">

      <div class="clear-both nav nav-pills">
            <el-tabs type="card" v-model="activeTab" @tab-click="handleTabSelect">
              <el-tab-pane :label="t.open_orders" name="open-orders"></el-tab-pane>
              <el-tab-pane :label="t.closed_orders" name="stop-orders"></el-tab-pane>
              <el-tab-pane :label="t.order_history" name="order-history"></el-tab-pane>
              <el-tab-pane :label="t.trade_history" name="trade-history"></el-tab-pane>
              
            </el-tabs>            

      </div>
            
      <div class="lang text-right block flex flex-row">
              <label>
                  <input type="radio" value="en" v-model="language"> English
              </label>
              <label>
                  <input type="radio" value="zh" v-model="language"> 中文
              </label>
      </div>
      
      <div class="market-history market-order mt15 w-full">
        
        <div class="tab-content h-full overflow-y-auto">
          <div v-if="activeTab === 'open-orders'" class="tab-pane fade show active" id="open-orders" role="tabpanel">
            <ul class="d-flex justify-content-between market-order-item">
              <li style="width:120px;">{{ t.transaction_no }}</li>
              <li>{{ t.date_time }}</li>
              <li style="width:120px;">{{ t.product }}</li>
              <li class="text-center">{{ t.position_qty }}</li>
              <li class="w-3em">{{ t.cost }}</li>
              <li class="w-13em">{{ t.profile }}</li>
              <li class="w-6em">{{ t.cut_gain }}</li>
              <li class="w-6em">{{ t.cut_loss }}</li>
              <li class="w-6em">{{ t.trailing_stop }}</li>
              <li class="w-5em">{{ t.pl }}</li>
              <li class="w-7em">{{ t.amount }}</li>
              <li class="w-13em">{{ t.action }}</li>
            </ul>
            <span class="no-data" v-if="orderDataList.length <= 0">
              <i class="icon ion-md-document"></i>
              {{ t.no_data }}
            </span>
            <div class="order-data" v-if="orderDataList.length > 0">
              <ul
                class="d-flex justify-content-between order-row hover:bg-grey-100"
                v-for="item in orderDataList"
                :key="item.id"
              >
                <li class="px-3" style="width:120px;">{{ item.id }}</li>
                <li class="px-3">{{ item.date }}</li>
                <li style="width:120px;">{{ item.stockCode }} {{ item.product }}</li>
                <li v-html="getOrderPositionText(item.position, item.commit_avg_price, item.qty)" class="text-right"></li>
                <li class="w-3em text-center">{{ item.cost }}</li>
                <li class="w-13em">
                  <select
                    v-model="selectedProfile[item.id]"
                    class="selectfield border border-gray-100 rounded-sm"
                    @change="changeAction(item.id, 'changeProfile')"
                    :disabled="activeStatus[item.id] ? false : true"
                  >
                    <option v-for="option in profileList" :key="option.key" :value="option.value">
                      {{ option.title }}
                    </option>
                  </select>
                  <button
                    class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"
                    @click="changeAction(item.id, 'addProfileList')"
                  >
                    {{ t.add }}
                  </button>
                  <button
                    class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"
                    @click="changeAction(item.id, 'delProfileList')"
                  >
                    {{ t.delete }}
                  </button>
                </li>
                <li class="w-6em">
                  <button class="px-2" @click="decrement(item, 'profitThreshold')" :disabled="!activeStatus[item.id]">-</button>
                  <input
                    type="text"
                    class="textfield rounded-sm"
                    v-model="item.profitThreshold"
                    :disabled="!activeStatus[item.id]"
                  />
                  <button class="px-2" @click="increment(item, 'profitThreshold')" :disabled="!activeStatus[item.id]">+</button>
                </li>
                <li class="w-6em">
                  <button class="px-2" @click="decrement(item, 'lossThreshold')" :disabled="!activeStatus[item.id]">-</button>
                  <input
                    type="text"
                    class="textfield rounded-sm"
                    v-model="item.lossThreshold"
                    :disabled="!activeStatus[item.id]"
                  />
                  <button class="px-2" @click="increment(item, 'lossThreshold')" :disabled="!activeStatus[item.id]">+</button>
                </li>
                <li class="w-6em">
                  <button class="px-2" @click="decrement(item, 'trailingStop')" :disabled="!activeStatus[item.id]">-</button>
                  <input
                    type="text"
                    class="textfield rounded-sm"
                    v-model="item.trailingStop"
                    :disabled="!activeStatus[item.id]"
                  />
                  <button class="px-2" @click="increment(item, 'trailingStop')" :disabled="!activeStatus[item.id]">+</button>
                </li>
                <li class="w-5em">-</li>
                <li class="w-7em">-</li>
                <li class="w-13em">
                  <button class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm" @click="changeAction(item.id, 'editProfile')">
                    {{ activeStatus[item.id] ? "Cancel" : "Edit" }}
                  </button>
                  <button
                    class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"
                    @click="changeAction(item.id, 'saveProfile')"
                    v-show="activeStatus[item.id]"
                  >
                    {{ t.save }}
                  </button>
                </li>
              </ul>
            </div>
          </div>
           
          <div v-if="activeTab === 'stop-orders'"  class="tab-pane fade" id="stop-orders" role="tabpanel"></div>
          <div v-if="activeTab === 'order-history'" class="tab-pane fade" id="order-history" role="tabpanel">
            <trade-history></trade-history> <!-- Include Trade History Component -->
          </div>
          <div v-if="activeTab === 'trade-history'" class="tab-pane fade" id="trade-history" role="tabpanel">
            <trade-history></trade-history> <!-- Include Trade History Component -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>

.lang{
  position: absolute;
  right:0;
}
.lang label{
  padding-right: 50px;
}

.row {
  width: 100%;
}

.optfilter ul li {
  display: inline-block;
}

.tab-content ul li {
  width: 8em;
}
.tab-content ul li.w-3em {
  width: 3em !important;
}
.tab-content ul li.w-5em {
  width: 5em !important;
}
.tab-content ul li.w-7em {
  width: 7em !important;
}
.tab-content ul li.w-13em {
  width: 13em !important;
}
#order_list {
  width: 100%;
  font-size: 14px;
  line-height: 1.5;
  color: #ffffff;
  font-weight: 400;
  height: 500px;
}

.textfield {
  width: 30px;
  text-align: center;
  color: #000000;
  background:#FFFFFF;
}

.textfield:disabled {
  color: #ffffff;
  background:#121212;
}

.selectfield {
  background: #121212;
  color: #ffffff;
}

@media only screen and (min-width: 1400px) {
  .textfield {
    width: 60px;
  }
}

.order-row {
  border-bottom: 1px solid #333333;
  padding-top: 3px;
  cursor: pointer;
}

.market-order {
  border-top: 0;
  min-height: 392px;
  border: 1px solid #e0e3eb;
  border-radius: 2px;
}

.market-history .nav {
  background: #f5f9fc;
}

.market-history .nav-link.active {
  color: #007bff;
  background: transparent;
}

.market-history .nav-link {
  color: #4a4a4a;
  padding: 10px 13px;
}
.nav-pills .nav-link {
  border-radius: 0.25rem;
}

.market-order-item {
  color: #758696;
  padding: 8px 15px;
}

.d-flex {
  display: -ms-flexbox !important;
  display: flex !important;
}

ul,
ol {
  margin: 0;
  padding: 0;
  list-style: none;
}

.no-data {
  position: absolute;
  left: 40%;
  top: 48%;
  text-align: center;
  color: #b9c2ca;
}

.no-data i {
  font-size: 100px;
  display: block;
  line-height: 0px;
  color: #dce1e5;
}

.sub.time {
  font-size: 0.8em;
}
.sup.LONG {
  color: #008aff;
  font-weight: bold;
}
.sup.SHORT {
  color: #ff6c00;
  font-weight: bold;
}
</style>
