
<script>
    import axios from 'axios'
    import {CommonFunc} from '../../../public/js/common.js';
    import {API_BASE} from '@/js/config.js';

    const defaultprofileList = [
          { title: "--default-- (0/0/0)", value: "0,0,0", key: 0 },
          { title: "Profile1 (30/30/90)", value: "30,30,90", key: 1 },
          { title: "Profile2 (10/10/80)", value: "10,10,80", key: 2 },
          { title: "Profile3 (20/20/70)", value: "20,20,70", key: 3 },
        ];


    export default {
      name: "accountDetailList",
      props: ["marketinfo"],
      data: function() {
        return {
          activeTab : "refund-status", // activeTab: "all",
          timeraccount: null,
          dataList: [],
          activeStatus: [],
          selectedProfile: [],
          profileList: defaultprofileList,
          dates: ["2018-09-15", "2018-09-20"],
          commonfunc: new CommonFunc(),
          
          /// =-=- refund panel -=-=
          acctotalBalance : 1440.99,
          refundableBalance : 1440.99,
          refundAmt: 0,
        }
      },
      mounted: function () {
          if(this.activeTab == 'refund-status'){
            this.requestRefundStatusDatas();
          }else{
            this.requestStatementDatas();
          }
      },
      watch : {
        refundAmt:function(val){
          // console.log(" refundAmt changed => " + val);
            this.refundAmt = Math.max(Math.min(this.refundableBalance, val) , 0) ;
        }
      },
      methods: {
        handleTabSelect(tab, event){
              // console.log(tab, event);
          this.activeTab = tab.name;
          console.log("tab changed? " + this.activeTab);

          if(tab.name == 'refund-status'){
            this.requestRefundStatusDatas();
          }else{
            this.requestStatementDatas();
          }

        },
        getOrderPositionText(position, commit_avg_price, order_count) {
          return (
            '<span class="sup ' + position + '">' + position + "</span> " + commit_avg_price + "<br/>" + order_count
          );
        },
        handleOptFilterSelect(tab, event) {
          console.log(tab, event);
        },
        requestStatementDatas() {
      //   console.log("Refresh Order List Handler -- BEGIN --");

          var orderdata = {
            action: "get_order_record",
            userid: "rickyDev",
            status: 1,
            date_fr: "2020-09-27",
          };

          this.dataList = [];

          // var url = host + "/v1/orders"
          var url = `${API_BASE}/api/getPositionsSim.php`
          if(this.activeTab == "statement-d"){
            url = `${API_BASE}/api/getPositionsSim_daily.php`
          }

            // http.post("api/WiseSyncController.php", orderdata , function (response) {
            axios.get(url, {
              headers: {
                'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4'
              }
            }).then(function(response) {
                try {
                  var result = response.data;	
                  console.log(result);
                  
                  if(typeof result == 'object'){
                    if(result.datas.length > 0){
                      console.log('refresh accountDetailList result get, should do layout update');
                      
                      
                      result.datas.forEach(row => {
                            // console.log(row);
                          var date = this.commonfunc.formatDate(row.orderTime ,  (this.activeTab == "statement-d"))  
                          // var time = this.commonfunc.formatTime(row.orderTime);
                          var cost = row.orderPrice * row.quantitly;
                          var commit_avg_price = 0;
                          var cut_gain = 0;
                          var cut_loss = 0;
                          var trailing_stop = 0;
                          var reslist = {
                            id: row.id != null ? row.id : row.productID,
                            product: row.wise_id,
                            date: `${date}`, //this.datetimeFormat(row.opentime),
                            position: row.position,
                            commit_avg_price: commit_avg_price,
                            qty: row.quantitly,
                            cost: row.cost,
                            profitThreshold: cut_gain,
                            lossThreshold: cut_loss,
                            trailingStop: trailing_stop,
                            filelink: row.filelink
                          };
                        
                          this.dataList.push(reslist);
                          // this.dataList[row_id] = reslist;
                      });
                        console.log("--dataList LIST-- \n" + this.dataList);                      
                        //       var orderResult = response.data.order_result;

                    }else{
                      console.log('refresh accountDetailList  no result ');
                    }
                  }
                  
                }catch(err) {
                  console.log('refresh accountDetailList  data parse err , ' + err);
                  //  this.alertify = true; 
                }
                
              }.bind(this),function(){
                  console.log('refresh accountDetailList network error');
                  //  this.alertify = true;
              });
          // console.log("Refresh Order List Handler -- END --");
        },
        requestRefundStatusDatas() {
      //   console.log("Refresh Order List Handler -- BEGIN --");

          var orderdata = {
            action: "get_order_record",
            userid: "rickyDev",
            status: 1,
            date_fr: "2020-09-27",
          };

          this.dataList = [];

          // var url = host + "/v1/orders"
          var url =  `${API_BASE}/api/getFundingRecordsSim.php`

            // http.post("api/WiseSyncController.php", orderdata , function (response) {
            axios.get(url, {
              headers: {
                'X-Api-Key': 'PMAK-66340d92344bf70001c7fe69-4061338754c8b30eda6c5c8ad7dee7e7d4'
              }
            }).then(function(response) {
                try {
                  var result = response.data;	
                  console.log('refresh accountDetailList result ' + result);
                  
                  if(typeof result == 'object'){
                    if(result.datas.length > 0){
                      console.log('refresh accountDetailList result get, should do layout update');
                      
                      
                      result.datas.forEach(row => {
                            // console.log(row);
                          var reslist = {
                            id: row.id,
                            productID : row.productID,
                            stockCode : row.stockCode,
                            market : row.market,
                            currency : row.currency,
                            total_refund : row.total_refund,
                            orderTime : this.commonfunc.formatDate(row.orderTime) +"\n"+ this.commonfunc.formatTime(row.orderTime),
                            requestTime : this.commonfunc.formatDate(row.requestTime) + this.commonfunc.formatTime(row.requestTime),
                            wiseid : row.wiseid,
                            status : row.status,
                          };
                        
                          this.dataList.push(reslist);
                          // this.dataList[row_id] = reslist;
                      });
                        console.log("--dataList LIST-- \n" + this.dataList);                      
                        //       var orderResult = response.data.order_result;

                    }else{
                      console.log('refresh accountDetailList  no result ');
                    }
                  }
                  
                }catch(err) {
                  console.log('refresh accountDetailList  data parse err , ' + err);
                  //  this.alertify = true; 
                }
                
              }.bind(this),function(){
                  console.log('refresh accountDetailList network error');
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

          var row = this.dataList.find(x => x.id === id)

              var data = {};
              data.action = "order_setting";
              data.host = server;
              data.port = port;
              data.userid = userid;
              data.orderId = row.profitThreshold;
              data.maxLoss = row.lossThreshold;
              data.trailingStop = row.trailingStop;

              // this.$http.post('api/OmniController.php', data)
              // .then(function(response){

              //   try {
              //     var result = response.data;
              //     this.activeStatus[id] = false;
                            
              //   }catch(err) {
              //     console.log('order_setting no result ');
              //     this.alertify = true; 
              //   }
                  
              // },function(){
              //     console.log('order_setting network error');
              //     this.alertify = true;
              // });

              break;
            case "changeProfile":
      //		console.log("Change Profile ID = ", id);

          var profile = this.selectedProfile[id].split(",");

          var row = this.dataList.find(x => x.id === id)
          row.profitThreshold = profile[0];
          row.lossThreshold = profile[1];
          row.trailingStop = profile[2];

          this.activeStatus[id] = true;
          break;
            case "editProfile":
          console.log("Edit Profile ID = ", id);
          if (!this.activeStatus[id]) {
            // toggle
            this.activeStatus[id] = true;
          } else {
            // cancel - clear value - revert previous value
            this.activeStatus[id] = false;
          }
          console.log(this.activeStatus);
          break;
            case "addProfileList":
          var profileList = [];
          var storeprofile = this.$cookies.get('tradeProfile_'+userid);
          
          if (storeprofile != null){
            profileList = JSON.parse(storeprofile);
          }else{
            profileList = defaultprofileList;
          }
              break;
            case "delProfileList":
              break;
          }
          this.$forceUpdate();
        },

        forceFileDownload(response, title) {
          console.log(title)
          const url = window.URL.createObjectURL(new Blob([response.data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', title)
          document.body.appendChild(link)
          link.click()
        },

        downloadStatment(id){
            /// TODO : download the pdf file
            var _data;
            this.dataList.forEach(element => {
              if(element.id == id){
                _data = element;
              }
            });

            if(_data != null){
              var title = "monthlyBill.pdf";
              var url = _data.filelink; 
              if(url !=null && url.length > 0){
                axios({ method: 'get', url }) .then((response) => {
                    this.forceFileDownload(response, title)
                  }) .catch(() => console.log('error occured'))
              }
            }
        },

        viewStatment(id){
            /// TODO : download the pdf file
            var _data;
            this.dataList.forEach(element => {
              if(element.id == id){
                _data = element;
              }
            });

            if(_data != null){
              var url = _data.filelink;
              if(url !=null && url.length > 0){
                open(url , "_blank","width=1200,height=800");
              }else{
                alert("Sorry, the statement of "+_data.date + " is not yet ready. Please call our CS if have any problem of the statement issue. Thank!");
              }
            }
              
        },
        submitRefundRequest(){
            console.log("request to refund");
        }
        
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
    <div class="market-history market-order mt15 w-full flex flex-row h-1">
        <el-tabs :tab-position="'left'" class="h-full" v-model="activeTab" @tab-click="handleTabSelect">
            <el-tab-pane label="出金歷史" name="refund-status"></el-tab-pane>
            <el-tab-pane label="結單-月" name="statement-m"></el-tab-pane>
            <el-tab-pane label="結單-日(本月)" name="statement-d"></el-tab-pane>
        </el-tabs>  
        <div id="listView" class=" w-full tab-content h-full overflow-y-auto" >
          <!-- refund status table struct  -->
          <div class="tab-pane fade show active" id="refund-status" role="tabpanel" v-if="activeTab == 'refund-status'" >
            <ul class="d-flex justify-content-between market-order-item">
              <li class="px-3 text-center">Date/Time</li>
              <li class="w-7em text-center">Amount</li>
              <li class="w-7em text-center">Transaction code.</li>
              <li class="w-7em text-center">Status</li>
            </ul>
            <span class="no-data" v-if="dataList.length <= 0"> <i class="icon ion-md-document"></i> No data </span>
            <div class="order-data" v-if="dataList.length > 0">
              <ul class="d-flex justify-content-between order-row hover:bg-grey-100" v-for="item in dataList" :key="item.product" >
                <li class="px-3 text-center">{{ item.orderTime }}</li>
                <li class="w-7em text-center">{{ "$"+item.total_refund }}</li>
                <li class="w-7em text-center">{{ item.wiseid }}</li>
                <li class="w-7em text-center">{{ item.status }}</li>
              </ul>
            </div>
          </div>
          
          <!-- statement status table struct  -->
          <div class="tab-pane fade show active" id="refund-status" role="tabpanel" v-if="activeTab == 'statement-m' || activeTab == 'statement-d'" >
            <ul class="d-flex justify-content-between market-order-item">
              <li>Date</li>
              <li class="w-13em">Action</li>
            </ul>
            <span class="no-data" v-if="dataList.length <= 0"> <i class="icon ion-md-document"></i> No data </span>
            <div class="order-data" v-if="dataList.length > 0">
              <ul class="d-flex justify-content-between order-row hover:bg-grey-100" v-for="item in dataList" :key="item.product" >
                <li class="px-3">{{ item.date }}</li>
                <li class="w-13em">
                  <!-- <button class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm" @click="changeAction(item.id, 'saveProfile')"  v-show="item.url.length > 0" >  -->
                  
                    <!-- <button class="px-3 bg-white text-black hover:bg-gray-300 rounded-sm" @click="downloadStatment(item.id)"  > 
                      下載
                  </button> -->
                  <button class="px-3 bg-white text-black hover:bg-gray-300 rounded-sm" @click="viewStatment(item.id)"  > 
                      檢閱
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div id="refundPanel" class="w-full flex flex-col" >
            <h1>Refund panel </h1>
            <div class="full-row odd">
              <span class="fr_left">Account Balance:</span>
              <span class="fr_right">HKD {{parseFloat(this.acctotalBalance).toFixed(2)}}</span>
            </div>
            <div class="full-row odd">
              <span class="fr_left">Total Refundable Balance:</span>
              <span class="fr_right">HKD {{parseFloat(this.refundableBalance).toFixed(2)}}</span>
            </div>
            <div class="full-row odd">
              <span class="fr_left">Refund Amount:</span>
              <span class="fr_right"><input type="number" step="100" v-model="refundAmt" on-keydown="submitRefundRequest" class="rounded-sm text-black" ></span>
            </div>
            <div class="full-row odd">
              <span class="fr_left"></span>
              <span class="fr_right"><input type="submit" value="submit" name="submit" class="px-10 rounded-sm text-black hover:bg-gray-300" @click="submitRefundRequest"></span>
            </div>
        </div>
  </div>
</template>

<style>
.row {
  width: 100%;
}

.optfilter ul li {
  display: inline-block;
}

#refundPanel{
  justify-content: space-around;
  position: relative;
  height: 10rem;
}

#refundPanel > h1{
  font-style: italic;
  font-size: 30px;
}

#refundPanel > div{
  margin-left:  50px;
}

.fr_left{
    width:60%;
    display:table-cell;
    vertical-align: top;
    padding-left: 20px;
    padding-right: 4.5px;
    padding-top: 19px;
    padding-bottom: 19px;
}
.fr_right{
    width:40%;
    display:table-cell;
    text-align:right;
    vertical-align: top;
    padding-right: 20px;
    padding-left: 4.5px;
    padding-top: 19px;
    padding-bottom: 19px;
}

#listView{
  background-color: #130e0e;
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
