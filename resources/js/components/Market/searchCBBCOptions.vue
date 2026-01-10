<template>
  <div class="search-container">
  
    <div class="title">牛熊證搜尋</div>

    <div class="search-filters row-md text-sm">
      <div class=" flex flex-row justify-space-between w-full  filter-group ">
          <div class="col-12 col-md-6 col-lg-3 underlying-container mt-md-0 ">
            <label>發行商:</label>
            <select v-model="issuer" class="w-2/3">
              <option value="所有">所有</option>
              <option value="issuer1">發行商1</option>
              <option value="issuer2">發行商2</option>
              <!-- 其他發行商選項 -->
            </select>
          </div>

          <div class="col-6 col-md-3  col-lg-3  w-1/4">
            <div class="btn-group btn-group-toggle col-12 p-0 " data-toggle="buttons"
              id='type-group'>
              <label class="btn btn-outline-secondary">
                <input type="radio" name="wtype" value='CALL' id="option1"> <span
                  class="d-none d-lg-inline">認</span>購
              </label>
              <label class="btn btn-outline-secondary">
                <input type="radio" name="wtype" value='PUT' id="option2"> <span
                  class="d-none d-lg-inline">認</span>沽
              </label>
              <label class="btn btn-outline-secondary active">
                <input type="radio" name="wtype" value='BULL' id="option3" checked> 牛<span
                  class="d-none d-lg-inline">證</span>
              </label>
              <label class="btn btn-outline-secondary ">
                <input type="radio" name="wtype" value='BEAR' id="option4" > 熊<span
                  class="d-none d-lg-inline">證</span>
              </label>
            </div>
			   </div>

         <div class="w-1/4 col-12 col-md-6 col-lg-5 underlying-container order-md-2 mt-md-0">
            <div class="form-control custom-selector underlying underlying_selector line-md-2 line-lg-1 inputed  filter-group" data-custom-modal='underlying'>             
              <label class="cust-input__title">相關資產:</label>
              <select v-model="asset" class="w-2/3 cust-input__value">
                <option value="asset0">恒生指數</option>
                <option value="asset1">發行商1</option>
                <option value="asset2">發行商2</option>
                <!-- 其他發行商選項 -->
              </select>
            </div>
          </div>


          <div class="col-auto col-auto  d-none d-xl-block flex flex-row">
            <button @click="resetFilters" style="width:150px;"><font-awesome-icon :icon="['fas', 'magnifying-glass']" /> 搜尋</button>
            <button @click="resetFilters" style="background-color:blueviolet;" class="pl-2"><font-awesome-icon :icon="['fas', 'rotate-right']" /></button>

          </div>
          
        
      </div>
 
    
      <div class="row-md mt-md-1r flex flex-row justify-space-between w-full  filter-group " style="padding-top:3px;">
        <div class="col-12 col-md-6 col-lg-4">
					<div class="input-group mb-2 flex flex-row">
						<div class="input-group-prepend">
							<div class="input-group-text line-height">收回價</div>
						</div>
						<input type="text" id="PriceFrom" name="PriceFrom" class="form-control price-input col-lg-4"  value="">
						<div class="input-group-prepend">
							<div class="input-group-text line-height ml-2">至</div>
						</div>
						<input type="text" id="PriceTo" name="PriceTo" class="form-control price-input col-lg-4" value="">
					</div>
				</div>
      

        <div class="filter-group col-6 col-md-3 col-lg-3 mt-md-0 ">
          <label style="width:80px">到期日:</label>
          <select v-model="expiryDate" class="w-full">
            <option value="所有">所有</option>
            <option value="2024-08-31">2024-08-31</option>
            <option value="2024-09-30">2024-09-30</option>
            <!-- 其他到期日選項 -->
          </select>
        </div>

        <div class="filter-group col-6 col-md-3 col-lg-3  mt-md-0">
          <label style="width:120px">買賣差價:</label>
          <select v-model="bidAskSpread" class="w-full">
            <option value="所有">所有</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <!-- 其他差價選項 -->
          </select>
        </div>

        <div class="filter-group col-6  col-md-6 col-lg-3 mt-md-2 mt-lg-0">
          <label style="width:180px">換股比率(%):</label>
          <select v-model="conversionRatio" class="w-full">
            <option value="所有">所有</option>
            <option value="50">50%</option>
            <option value="100">100%</option>
            <!-- 其他換股比率選項 -->
          </select>
        </div>

        <div class="filter-group col-12 col-lg-3 col-md-6 mt-md-2 mt-lg-0">
          
          <label style="width:120px">街貨比率:</label>
          <input type="text" v-model="stockRatio" placeholder="低於" class="w-2/3"/>
        </div>

      </div>


      <div class="w-full clear-both">

<div class="float-left">
  <div class="index-info">
  <span class="index-label">恒生指數價格:</span>
  <span class="index-value">
    17,174.06 <span :class="indexChangeClass">▲ 62.41 (0.36%)</span>
  </span>
</div>


<div class="results ">
  搜尋結果: 共{{ totalResults }}個牛熊證 | 即將上市
</div>
</div>




<div class="actions float-right">
  <button @click="printResults">列印</button>
  <button @click="downloadResults">下載</button>
</div>
</div>

    </div>





  </div>
</template>

<script>
export default {
  data() {
    return {
      issuer: '所有',
      expiryDate: '所有',
      bidAskSpread: '所有',
      conversionRatio: '所有',
      stockRatio: '',
      asset: 'asset0',
      totalResults: 833,
      searchResults: [
        // 模擬的數據，實際應由API或資料庫獲取
        {
          id: '001',
          issuer: '發行商1',
          strikePrice: '20000',
          exercisePrice: '18000',
          expiryDate: '2024-09-30',
          leverage: '10',
          currentPrice: '0.250',
          changePercent: '+1.20%',
          bidAskSpread: '1',
          conversionRatio: '50%',
          turnover: '150',
          premium: '10%',
          outstandingVolume: '200',
          stockRatio: '30%',
        },
        // 更多數據...
      ],
    };
  },
  computed: {
    indexChangeClass: function indexChangeClass() {
        // 動態決定漲跌的顏色
        if (this.searchResults.length > 0 && this.searchResults[0].changePercent) {
          return this.searchResults[0].changePercent.startsWith('+') ? 'up' : 'down';
        }
        return ''; // 或者返回一個默認的 class，比如 'neutral'
      }
  },
  methods: {
    resetFilters() {
      this.issuer = '所有';
      this.expiryDate = '所有';
      this.bidAskSpread = '所有';
      this.conversionRatio = '所有';
      this.stockRatio = '';
    },
    printResults() {
      window.print();
    },
    downloadResults() {
      // 實現下載功能
      console.log('下載結果');
    }
  }
};
</script>

<style scoped>
.search-container {
  color:#FFF;
  padding: 20px;
  background-color: #131722;
  height:100%;
}

.breadcrumb {
  font-size: 14px;
  color: #666;
}

.title {
  font-size: 24px;
  font-weight: bold;
  margin: 10px 0;
}

.search-filters {
  display: flex;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.index-info {
  font-size: 18px;
  margin-bottom: 20px;
}

.index-label {
  font-weight: bold;
}

.index-value {
  color: green;
  font-weight: bold;
}

.up {
  color: green;
}

.down {
  color: red;
}

.results {
  font-size: 16px;
  margin-bottom: 20px;
}

.results-table {
  width: 100%;
  border-collapse: collapse;
}

.results-table th, .results-table td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}

.text-xs{
  font-size: 0.75rem; /* 12px */
  line-height: 1rem; /* 16px */
}

.actions {
  margin-top: 20px;
}

button {
  padding: 10px 20px;
  margin-right: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}

.line-height{
  line-height:30px;
}

select, input{
  background-color: #1f2638;
  padding-top:8px;
  padding-left: 15px;
  padding-right: 15px;
  padding-bottom:8px;
}

label{
  margin-right:10px;
}

.input-group-text{
  margin-right:10px;
}

.row::after {
        content: "";
        clear: both;
        display: table;
    }
    [class*="col-"] {
        float: left;
       /* border: 1px solid red; */
    }
    .col-1 {width: 8.33%;}
    .col-2 {width: 16.66%;}
    .col-3 {width: 25%;}
    .col-4 {width: 33.33%;}
    .col-5 {width: 41.66%;}
    .col-6 {width: 50%;}
    .col-7 {width: 58.33%;}
    .col-8 {width: 66.66%;}
    .col-9 {width: 75%;}
    .col-10 {width: 83.33%;}
    .col-11 {width: 91.66%;}
    .col-12 {width: 100%;}

    * {
      box-sizing: border-box;
    }

    .container {
  margin-right: auto;
  margin-left: auto;
  padding-left: 15px;
  padding-right: 15px;
}
@media (min-width: 768px) {
  .container {
    width: 750px;
  }
}
@media (min-width: 992px) {
  .container {
    width: 970px;
  }
}
@media (min-width: 1200px) {
  .container {
    width: 1170px;
  }
}
.container-fluid {
  margin-right: auto;
  margin-left: auto;
  padding-left: 15px;
  padding-right: 15px;
}
.row {
  margin-left: -15px;
  margin-right: -15px;
}
.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12 {
  position: relative;
  min-height: 1px;
  padding-left: 15px;
  padding-right: 15px;
}
.col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12 {
  float: left;
}
.col-xs-12 { width: 100%; }
.col-xs-11 { width: 91.66666667%; }
.col-xs-10 { width: 83.33333333%; }
.col-xs-9 { width: 75%; }
.col-xs-8 { width: 66.66666667%; }
.col-xs-7 { width: 58.33333333%; }
.col-xs-6 { width: 50%; }
.col-xs-5 { width: 41.66666667%; }
.col-xs-4 { width: 33.33333333%; }
.col-xs-3 { width: 25%; }
.col-xs-2 { width: 16.66666667%; }
.col-xs-1 { width: 8.33333333%; }

.col-xs-pull-12 { right: 100%; }
.col-xs-pull-11 { right: 91.66666667%; }
.col-xs-pull-10 { right: 83.33333333%; }
.col-xs-pull-9 { right: 75%; }
.col-xs-pull-8 { right: 66.66666667%; }
.col-xs-pull-7 { right: 58.33333333%; }
.col-xs-pull-6 { right: 50%; }
.col-xs-pull-5 { right: 41.66666667%; }
.col-xs-pull-4 { right: 33.33333333%; }
.col-xs-pull-3 { right: 25%; }
.col-xs-pull-2 { right: 16.66666667%; }
.col-xs-pull-1 { right: 8.33333333%; }
.col-xs-pull-0 { right: auto; }

.col-xs-push-12 { left: 100%; }
.col-xs-push-11 { left: 91.66666667%; }
.col-xs-push-10 { left: 83.33333333%; }
.col-xs-push-9 { left: 75%; }
.col-xs-push-8 { left: 66.66666667%; }
.col-xs-push-7 { left: 58.33333333%; }
.col-xs-push-6 { left: 50%; }
.col-xs-push-5 { left: 41.66666667%; }
.col-xs-push-4 { left: 33.33333333%; }
.col-xs-push-3 { left: 25%; }
.col-xs-push-2 { left: 16.66666667%; }
.col-xs-push-1 { left: 8.33333333%; }
.col-xs-push-0 { left: auto; }

.col-xs-offset-12 { margin-left: 100%; }
.col-xs-offset-11 { margin-left: 91.66666667%; }
.col-xs-offset-10 { margin-left: 83.33333333%; }
.col-xs-offset-9 { margin-left: 75%; }
.col-xs-offset-8 { margin-left: 66.66666667%; }
.col-xs-offset-7 { margin-left: 58.33333333%; }
.col-xs-offset-6 { margin-left: 50%; }
.col-xs-offset-5 { margin-left: 41.66666667%; }
.col-xs-offset-4 { margin-left: 33.33333333%; }
.col-xs-offset-3 { margin-left: 25%; }
.col-xs-offset-2 { margin-left: 16.66666667%; }
.col-xs-offset-1 { margin-left: 8.33333333%; }
.col-xs-offset-0 { margin-left: 0%; }

@media (min-width: 768px) {
  .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
    float: left;
  }
  .col-sm-12 { width: 100%; }
  .col-sm-11 { width: 91.66666667%; }
  .col-sm-10 { width: 83.33333333%; }
  .col-sm-9 { width: 75%; }
  .col-sm-8 { width: 66.66666667%; }
  .col-sm-7 { width: 58.33333333%; }
  .col-sm-6 { width: 50%; }
  .col-sm-5 { width: 41.66666667%; }
  .col-sm-4 { width: 33.33333333%; }
  .col-sm-3 { width: 25%; }
  .col-sm-2 { width: 16.66666667%; }
  .col-sm-1 { width: 8.33333333%; }

  .col-sm-pull-12 { right: 100%; }
  .col-sm-pull-11 { right: 91.66666667%; }
  .col-sm-pull-10 { right: 83.33333333%; }
  .col-sm-pull-9 { right: 75%; }
  .col-sm-pull-8 { right: 66.66666667%; }
  .col-sm-pull-7 { right: 58.33333333%; }
  .col-sm-pull-6 { right: 50%; }
  .col-sm-pull-5 { right: 41.66666667%; }
  .col-sm-pull-4 { right: 33.33333333%; }
  .col-sm-pull-3 { right: 25%; }
  .col-sm-pull-2 { right: 16.66666667%; }
  .col-sm-pull-1 { right: 8.33333333%; }
  .col-sm-pull-0 { right: auto; }

  .col-sm-push-12 { left: 100%; }
  .col-sm-push-11 { left: 91.66666667%; }
  .col-sm-push-10 { left: 83.33333333%; }
  .col-sm-push-9 { left: 75%; }
  .col-sm-push-8 { left: 66.66666667%; }
  .col-sm-push-7 { left: 58.33333333%; }
  .col-sm-push-6 { left: 50%; }
  .col-sm-push-5 { left: 41.66666667%; }
  .col-sm-push-4 { left: 33.33333333%; }
  .col-sm-push-3 { left: 25%; }
  .col-sm-push-2 { left: 16.66666667%; }
  .col-sm-push-1 { left: 8.33333333%; }
  .col-sm-push-0 { left: auto; }

  .col-sm-offset-12 { margin-left: 100%; }
  .col-sm-offset-11 { margin-left: 91.66666667%; }
  .col-sm-offset-10 { margin-left: 83.33333333%; }
  .col-sm-offset-9 { margin-left: 75%; }
  .col-sm-offset-8 { margin-left: 66.66666667%; }
  .col-sm-offset-7 { margin-left: 58.33333333%; }
  .col-sm-offset-6 { margin-left: 50%; }
  .col-sm-offset-5 { margin-left: 41.66666667%; }
  .col-sm-offset-4 { margin-left: 33.33333333%; }
  .col-sm-offset-3 { margin-left: 25%; }
  .col-sm-offset-2 { margin-left: 16.66666667%; }
  .col-sm-offset-1 { margin-left: 8.33333333%; }
  .col-sm-offset-0 { margin-left: 0%; }

}

@media (min-width: 992px) {
 
  .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
    float: left;
  }
 
  .col-md-12 { width: 100%; }
  .col-md-11 { width: 91.66666667%; }
  .col-md-10 { width: 83.33333333%; }
  .col-md-9 { width: 75%; }
  .col-md-8 { width: 66.66666667%; }
  .col-md-7 { width: 58.33333333%; }
  .col-md-6 { width: 50%; }
  .col-md-5 { width: 41.66666667%; }
  .col-md-4 { width: 33.33333333%; }
  .col-md-3 { width: 25%; }
  .col-md-2 { width: 16.66666667%; }
  .col-md-1 { width: 8.33333333%; }

  .col-md-pull-12 { right: 100%; }
  .col-md-pull-11 { right: 91.66666667%; }
  .col-md-pull-10 { right: 83.33333333%; }
  .col-md-pull-9 { right: 75%; }
  .col-md-pull-8 { right: 66.66666667%; }
  .col-md-pull-7 { right: 58.33333333%; }
  .col-md-pull-6 { right: 50%; }
  .col-md-pull-5 { right: 41.66666667%; }
  .col-md-pull-4 { right: 33.33333333%; }
  .col-md-pull-3 { right: 25%; }
  .col-md-pull-2 { right: 16.66666667%; }
  .col-md-pull-1 { right: 8.33333333%; }
  .col-md-pull-0 { right: auto; }

  .col-md-push-12 { left: 100%; }
  .col-md-push-11 { left: 91.66666667%; }
  .col-md-push-10 { left: 83.33333333%; }
  .col-md-push-9 { left: 75%; }
  .col-md-push-8 { left: 66.66666667%; }
  .col-md-push-7 { left: 58.33333333%; }
  .col-md-push-6 { left: 50%; }
  .col-md-push-5 { left: 41.66666667%; }
  .col-md-push-4 { left: 33.33333333%; }
  .col-md-push-3 { left: 25%; }
  .col-md-push-2 { left: 16.66666667%; }
  .col-md-push-1 { left: 8.33333333%; }
  .col-md-push-0 { left: auto; }

  .col-md-offset-12 { margin-left: 100%; }
  .col-md-offset-11 { margin-left: 91.66666667%; }
  .col-md-offset-10 { margin-left: 83.33333333%; }
  .col-md-offset-9 { margin-left: 75%; }
  .col-md-offset-8 { margin-left: 66.66666667%; }
  .col-md-offset-7 { margin-left: 58.33333333%; }
  .col-md-offset-6 { margin-left: 50%; }
  .col-md-offset-5 { margin-left: 41.66666667%; }
  .col-md-offset-4 { margin-left: 33.33333333%; }
  .col-md-offset-3 { margin-left: 25%; }
  .col-md-offset-2 { margin-left: 16.66666667%; }
  .col-md-offset-1 { margin-left: 8.33333333%; }
  .col-md-offset-0 { margin-left: 0%; }

}

@media (min-width: 1200px) {
  .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12 {
    float: left;
  }
  .col-lg-12 { width: 100%; }
  .col-lg-11 { width: 91.66666667%; }
  .col-lg-10 { width: 83.33333333%; }
  .col-lg-9 { width: 75%; }
  .col-lg-8 { width: 66.66666667%; }
  .col-lg-7 { width: 58.33333333%; }
  .col-lg-6 { width: 50%; }
  .col-lg-5 { width: 41.66666667%; }
  .col-lg-4 { width: 33.33333333%; }
  .col-lg-3 { width: 25%; }
  .col-lg-2 { width: 16.66666667%; }
  .col-lg-1 { width: 8.33333333%; }

  .col-lg-pull-12 { right: 100%; }
  .col-lg-pull-11 { right: 91.66666667%; }
  .col-lg-pull-10 { right: 83.33333333%; }
  .col-lg-pull-9 { right: 75%; }
  .col-lg-pull-8 { right: 66.66666667%; }
  .col-lg-pull-7 { right: 58.33333333%; }
  .col-lg-pull-6 { right: 50%; }
  .col-lg-pull-5 { right: 41.66666667%; }
  .col-lg-pull-4 { right: 33.33333333%; }
  .col-lg-pull-3 { right: 25%; }
  .col-lg-pull-2 { right: 16.66666667%; }
  .col-lg-pull-1 { right: 8.33333333%; }
  .col-lg-pull-0 { right: auto; }

  .col-lg-push-12 { left: 100%; }
  .col-lg-push-11 { left: 91.66666667%; }
  .col-lg-push-10 { left: 83.33333333%; }
  .col-lg-push-9 { left: 75%; }
  .col-lg-push-8 { left: 66.66666667%; }
  .col-lg-push-7 { left: 58.33333333%; }
  .col-lg-push-6 { left: 50%; }
  .col-lg-push-5 { left: 41.66666667%; }
  .col-lg-push-4 { left: 33.33333333%; }
  .col-lg-push-3 { left: 25%; }
  .col-lg-push-2 { left: 16.66666667%; }
  .col-lg-push-1 { left: 8.33333333%; }
  .col-lg-push-0 { left: auto; }

  .col-lg-offset-12 { margin-left: 100%; }
  .col-lg-offset-11 { margin-left: 91.66666667%; }
  .col-lg-offset-10 { margin-left: 83.33333333%; }
  .col-lg-offset-9 { margin-left: 75%; }
  .col-lg-offset-8 { margin-left: 66.66666667%; }
  .col-lg-offset-7 { margin-left: 58.33333333%; }
  .col-lg-offset-6 { margin-left: 50%; }
  .col-lg-offset-5 { margin-left: 41.66666667%; }
  .col-lg-offset-4 { margin-left: 33.33333333%; }
  .col-lg-offset-3 { margin-left: 25%; }
  .col-lg-offset-2 { margin-left: 16.66666667%; }
  .col-lg-offset-1 { margin-left: 8.33333333%; }
  .col-lg-offset-0 { margin-left: 0%; }

}


.clearfix:before,
.clearfix:after,
.container:before,
.container:after,
.container-fluid:before,
.container-fluid:after,
.row:before,
.row:after {
  content: " ";
  display: table;
}
.clearfix:after,
.container:after,
.container-fluid:after,
.row:after {
  clear: both;
}
.center-block {
  display: block;
  margin-left: auto;
  margin-right: auto;
}
.pull-right {
  float: right !important;
}
.pull-left {
  float: left !important;
}
.hide {
  display: none !important;
}
.show {
  display: block !important;
}
.invisible {
  visibility: hidden;
}
.text-hide {
  font: 0/0 a;
  color: transparent;
  text-shadow: none;
  background-color: transparent;
  border: 0;
}
.hidden {
  display: none !important;
}
.affix {
  position: fixed;
}
</style>
