<template>
    <div id="user-asset" class="p-4 w-full">
      <div class="flex justify-between">
        <!-- Left section: Asset overview and trend -->
        <div class="w-1/2">
          <!-- Asset Overview -->
          <div class="bg-gray-900 p-4 rounded-lg mb-4">
            <div class="flex justify-between items-center">
              <div>
                <div class="text-white text-lg mb-2">資產總覽</div>
             
                <el-radio-group v-model="assetType" size="small" class="text-white">
                    <el-radio-button label="幣種"></el-radio-button>
                    <el-radio-button label="持倉"></el-radio-button>
                </el-radio-group>
              </div>
              
              <div class="text-white  font-bold">可用資金 : <span class="text-4xl">282.65</span></div>
              <div class="text-white  font-bold">總資產 : <span class="text-4xl">23018.2</span></div>
            </div>
            <div class=" flex justify-center items-center">
              <div id="propertiesChartView" class="w-full h-full"></div>
            </div>
            <div class="text-white text-center">港元 100.00%</div>
          </div>
          <!-- Trend and Flow -->
          <div id="trendchart" class="bg-gray-900 p-4 rounded-lg">
            <div class="text-white text-lg mb-4">收益率走勢</div>
            <div id="trendtab" class="flex flex-row mb-2"><ul style="display: inline-block; list-style: none;   margin: 0 auto;">
              <li>今日</li>
              <li>近5日</li>
              <li>近1月</li>
              <li>年初至今</li>
              <li class="active">近1年</li>
              <li>近2年</li>
            </ul></div>
            <div class=" justify-between items-center mb-4 text-center" style="margin: 0 auto;">
              <date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="至"
                start-placeholder="開始日期"
                end-placeholder="結束日期"
                format="yyyy/MM/dd"
                value-format="yyyy-MM-dd"
                class="text-white"
              ></date-picker>
            </div>
            <div class="h-40">
              <canvas id="profitTrendChart" width="400" height="200"></canvas>
              
            </div>
          </div>
        </div>
        <!-- Right section: Account summary and actions -->
        <div class="w-1/2 pl-4">
          <!-- Account Summary -->
          <div class="bg-gray-900 p-4 rounded-lg mb-4">
            <div class="text-white text-lg mb-2">港股拓展</div>
            <div>資產淨值 (港元)</div>
            <div class="text-white text-4xl font-bold">282.65</div>

            <!-- Grid layout for two columns -->
    <div class="grid grid-cols-2 gap-4">
      
      <!-- First Column -->
      <div class="space-y-4 mr-12">
        <div class="flex justify-between text-white">
          <div>現金</div>
          <div>282.65</div>
        </div>
        <div class="flex justify-between text-white">
          <div>證券市值</div>
          <div>0.00</div>
        </div>
        <div class="flex justify-between text-white">
          <div>最大購買力</div>
          <div>565.30 <span class="text-blue-400">提升</span></div>
        </div>
        <div class="flex justify-between text-white">
          <div>多頭市值</div>
          <div>0.00</div>
        </div>
        <div class="flex justify-between text-white">
          <div>風控狀態</div>
          <div class="text-green-400">安全</div>
        </div>
        <div class="flex justify-between text-white">
          <div>槓桿倍數</div>
          <div>0.00</div>
        </div>
      </div>

      <!-- Second Column -->
      <div class="space-y-4 ml-12">
        <div class="flex justify-between text-white">
          <div>今日盈虧</div>
          <div>0.00 0.00%</div>
        </div>
        <div class="flex justify-between text-white">
          <div>持倉盈虧</div>
          <div>0.00</div>
        </div>
      
        <div class="flex justify-between text-white">
          <div>沽空購買力</div>
          <div>471.08</div>
        </div>
        <div class="flex justify-between text-white">
          <div>空頭市值</div>
          <div>0.00</div>
        </div>
        <div class="flex justify-between text-white">
          <div>剩餘流動性</div>
          <div>282.65</div>
        </div>
      </div>

    </div>

    <div class="text-blue-400 text-right ">
      <a href="#">更多...</a>
    </div>
          
            
          </div>
          <!-- Actions -->
          <div class="bg-gray-900 px-5 py-3 rounded-lg">
            <div class="flex justify-between items-center text-white ">
              <div class="flex flex-col items-center">
                <button class=" p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset01.png" />
                </button>
                <div>存入資金</div>
              </div>
              <div class="flex flex-col items-center">
                <button class=" p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset02.png" />
                </button>
                <div>提取資金</div>
              </div>
              <div class="flex flex-col items-center">
                <button class=" p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset03.png" />
                </button>
                <div>貨幣兌換</div>
              </div>
              <div class="flex flex-col items-center">
                <button class=" p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset04.png" />
                </button>
                <div>資金調撥</div>
              </div>
              <div class="flex flex-col items-center">
                <button class="p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset05.png" />
                </button>
                <div>新股認購</div>
              </div>
              <div class="flex flex-col items-center">
                <button class=" p-2 rounded-full ">
                  <img class="assetTapItemIcon" src="@/assets/images/asset06.png" />
                </button>
                <div>我的結單</div>
              </div>
            </div>
          </div>

          <div class="bg-gray-900 p-4 rounded-lg mt-4" id="statements">
          <div class="text-white text-lg mb-4">現金流分析</div>
          <select id="statement-type" v-model="chartType" @change="updateChart">
            <option value="daily">Daily</option>
            <option value="monthly">Monthly</option>
          </select>
          <div class="chart-container">
            <canvas id="statement-chart" style="height:300px;"></canvas>
          </div>
        </div>


        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
    import { ElRadioButton, ElRadioGroup, ElDatePicker } from 'element-plus';
        import 'element-plus/dist/index.css';

  import { propertiseChart } from '@/js/common.js'; // Import propertiseChart
  import { Chart } from 'chart.js';
  
  export default {
    name: 'userAssetView',
    components: {
      ElRadioButton,
      ElRadioGroup,
      ElDatePicker,
    },
 
    setup() {
      const assetType = ref('幣種');
      const dateRange = ref([new Date('2023-07-01'), new Date('2024-07-31')]);
      const showDropdown = ref(false);
      const propertiesChart = new propertiseChart(); // Initialize propertiseChart
  
      const toggleDropdown = () => {
        showDropdown.value = !showDropdown.value;
      };
  
      const handleOutsideClick = (event) => {
        const dropdown = document.querySelector('.relative');
        if (dropdown && !dropdown.contains(event.target)) {
          showDropdown.value = false;
          document.removeEventListener('click', handleOutsideClick);
        }
      };
  
      const renderCharts = () => {
        // Initialize data for the properties chart
        const chartData = [
          { x: '牛熊', value: 1500 },
          { x: '期貨', value: 25000 },
          { x: '可用資金', value: 282.65 },
        ];
        propertiesChart.initChart(chartData, "#1a1a1a");
        propertiesChart.show(); // Render the properties chart
      };

      const createCashTrendChart = () => {
        const ctx = document.getElementById('statement-chart').getContext('2d');

        const data = {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], // X-axis labels for monthly
          datasets: [{
            label: 'Account Balance',
            data: [10000, 12000, 11000, 13000, 14500, 14000], // Y-axis data for monthly
            borderColor: 'rgba(54, 162, 235, 1)', // Line color
            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Fill under the line
            borderWidth: 2, // Line thickness
            fill: false, // Don't fill under the line
            tension: 0.2, // Line tension (smoothness)
            pointRadius: 3, // Point size
            pointBackgroundColor: 'rgba(54, 162, 235, 1)' // Point color
          }]
        };

        const options = {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: false,
              max: 15000,
              min: 10000,
              ticks: {
                stepSize: 1000
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                color: 'rgba(255, 255, 255, 0.8)'
              }
            }
          }
        };

        this.chartInstance = new Chart(ctx, {
          type: 'line',
          data: data,
          options: options,
        });
    };

     const updateCashTrendChart = () => {
        if (this.chartType === 'daily') {
          this.chartInstance.data.labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; // Example daily labels
          this.chartInstance.data.datasets[0].data = [10000, 10200, 10400, 10300, 10500, 10700, 10600]; // Example daily data
        } else if (this.chartType === 'monthly') {
          this.chartInstance.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']; // Monthly labels
          this.chartInstance.data.datasets[0].data = [10000, 12000, 11000, 13000, 14500, 14000]; // Monthly data
        }
        this.chartInstance.update();
      };
  

      const renderProfitTrendChart = () => {
      const ctx = document.getElementById('profitTrendChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [
            '2023/7', '2023/8', '2023/9', '2023/10', '2023/11', '2023/12', 
            '2024/1', '2024/2', '2024/3', '2024/4', '2024/5', '2024/6', '2024/7', '2024/8'
          ],
          datasets: [{
            label: '收益率 (%)',
            data: [2500, 3000, 1800, 2200, 2900, 3300, 4000, 3500, 4200, 4500, 4800, 5000, 4700, 4100],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: '收益率 (%)'
              }
            },
            x: {
              title: {
                display: true,
                text: '月份'
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label: function(tooltipItem) {
                  return tooltipItem.raw + '%';
                }
              }
            }
          }
        }
      });
    };

  
      onMounted(() => {
        renderCharts();
        renderProfitTrendChart();
        createCashTrendChart();

        document.addEventListener('click', handleOutsideClick);

      });
  
      return {
        assetType,
        dateRange,
        showDropdown,
        toggleDropdown,
      };
    },
  };
  </script>
<style scoped>
    #user-asset {
    background-color: #000000;
    height: 100%;
    color: #ffffff;
    }
    .el-tabs__item {
    color: #cccccc;
    }
    .el-tabs__header {
    margin: 0 !important;
    }
    .bg-gray-900 {
    background-color: #1a1a1a;
    }
    .el-radio-group .el-radio-button__inner {
    color: white;
    }
    .el-radio-group .el-radio-button.is-active .el-radio-button__inner {
    background-color: #409eff;
    color: white;
    }
    .assetTapItemIcon {
      height: 47px;
      padding-bottom: 7px;
    }

    #propertiesChartView{
      height: 20rem;
    }

    #trendchart{
      height : 30rem;
    }

    #trendtab ul li{
        display: inline-block; 
        padding-left: 15px;
        padding-right: 15px;
    }

    #trendtab .active{
      color: #ff8000;
      font-weight: 800;
    }
    

    .chart-container {
      height: 380px;
      width: 100%;
    }
</style>
