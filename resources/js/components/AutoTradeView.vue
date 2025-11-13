<template> 
   <div id="autoTradeView" class="w-full">
      <div id="productBar" class="clear-both z-0 w-full">
         <el-tabs type="card" v-model="activeProductTab" @tab-click="handleProductTabSelect">
            <el-tab-pane label="期貨 Algo" name="future"></el-tab-pane>
            <el-tab-pane label="牛熊 Algo" name="cbbc"></el-tab-pane>
         </el-tabs>            
      </div>
      <div id="content_bound" class="w-full flex flex-row">
         <div class="content_left" >
            <algomarketinfo ref="algomarketinfo" :stockProps="selectedStock" :showGraph="isShowGraph"  :isDefaultOrderPanel="false"></algomarketinfo>
         </div>
         <div class="content_right" >
            <div class="flex flex-row">
            <span class="text-sm mx-2 text-green-500">
               <a class="hover:text-teal-100 charthref" :href="investingchart" target="chart_iframe"> Full Chart</a>
            </span>
            <span class="text-sm mx-2 text-green-500">
               <a class="hover:text-teal-100 charthref" :href="vwapchart"  target="chart_iframe"> Full MSA Chart</a>
            </span>
         </div>
         <div class="main-chart mb15">
            <iframe :src="investingchart" frameborder="0" name="chart_iframe" id="chart_iframe"  width="100%" height="100%" class="z-0"></iframe> 
         </div>

         <!-- TradingView Widget BEGIN -->
         <div class="tradingview-widget-container ">
            <div id="tradingview_e8053" class="w-full flex flex-grow" this.props="widget"></div>                        
         </div>
         <!-- TradingView Widget END -->
         
       </div>
   </div>
</div>
   
</template>

<style scoped>
   
   .tradingview-widget-container { 
      height : 500px;
   }

   .main-chart{
      position: relative;
      width: 100%;
      border: red solid 1px;
   }

   #chart_iframe{
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 90vh;
      border: none;
   }
   .content_left{
      width : calc(400px); 
   }
   .content_right{
		width : calc(100% - 400px); 
	}
</style>

<script>
   import algomarketinfoView from './MarketInfo.vue';

   // const investingchart = "https://tvc4.forexpros.com/init.php?family_prefix=tvc4&time=1479947567&domain_ID=55&lang_ID=55&timezone_ID=28&pair_ID=8984&interval=60&refresh=4&session=24x7&client=1&user=200995618&width=830&height=800&init_page=live-charts&m_pids=";
   //import {$serverurl} from '../../config.js';
   var $serverurl = "/omniserver";
   const investingchart = $serverurl + "/api/marketdataProxy.php";
   // const investingchart = "https://www.tradingview.com/chart/?symbol=HKEX%3AHSI1%21&theme=dark"
   const vwapchart =  "https://chart.altodock.com";   

   export default {
         name : 'autoTradeView',
         props: {
            widget : null 
         },
         data: function() {
            return {
               investingchart : investingchart,
               vwapchart : vwapchart,
               activeProductTab : "future",
               selectedStock: {}, // Initialize with an empty object
               isShowGraph: true
            }
         },
         components: {	
				'algomarketinfo' :  algomarketinfoView,
		   },
         mounted : function(){
            // Call a method to set showVWAPData to true on the algomarketinfo component
            if (this.$refs.algomarketinfo) {
               this.$refs.algomarketinfo.showVWAPData = true;
            }
         },
         methods : {
            handleProductTabSelect(tab, event){
               // console.log(tab, event);
               this.activeProductTab = tab.name;
               console.log("tab changed? " + this.activeProductTab);
               algomarketinfoView.methods.changeOnShow((tab.name === "default"));
			   },
            changeCode(code){
               console.log("chart view fetch new stock code => " + code);
               if(code.length > 0){
                  this.investingchart = investingchart + "?sym="+ parseInt(code);
               }else{
                  this.investingchart = investingchart;
               }
             //  marketinfoView.methods.pollingMarketData(code);
            }
         }
   }
   /*
   var widget =  */
						
</script>