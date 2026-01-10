<script setup>
import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { useStockStore } from '@/js/stores/useStockStore'

// Components
import tradeView from '../Trade/TradeView.vue'
import autoTradeView from '../Trade/AutoTradeView.vue'
import orderlist from '../Trade/OrderList.vue'
import accPropertiesView from '../User/UserAssetView.vue'
import accountDetailList from '../User/AccountDetailList.vue'

// Icons
import icon01 from '@/assets/images/omnitrader_icon_01.png'
import icon01On from '@/assets/images/omnitrader_icon_01_on.png'
import icon02 from '@/assets/images/omnitrader_icon_02.png'
import icon02On from '@/assets/images/omnitrader_icon_02_on.png'
import icon03 from '@/assets/images/omnitrader_icon_03.png'
import icon03On from '@/assets/images/omnitrader_icon_03_on.png'
import icon04 from '@/assets/images/omnitrader_icon_04.png'
import icon04On from '@/assets/images/omnitrader_icon_04_on.png'
import icon05 from '@/assets/images/omnitrader_icon_05.png'
import icon05On from '@/assets/images/omnitrader_icon_05_on.png'

const activeTab = ref('trade')
const showDropdown = ref(false)
const stockStore = useStockStore()

const tabIcons = {
  trade: { on: icon01On, off: icon01 },
  systemTrade: { on: icon02On, off: icon02 },
  orderlist: { on: icon03On, off: icon03 },
  accountoverview: { on: icon04On, off: icon04 },
  accountmanagement: { on: icon05On, off: icon05 },
}

function handleTabSelect(tab) {
  activeTab.value = tab.paneName
}

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
}

function logout() {
  alert('Logged out') // or emit event
}

function selectStock(item) {
  stockStore.setStock(item)
}
</script>

<template>
  <div id="home" class="flex flex-col min-h-screen bg-black text-white font-arial">
    <!-- Header -->
    <header
      class="flex items-center justify-between h-16 px-4 border-b border-gray-700 bg-black z-50"
    >
      <!-- Left: Logo + Title -->
      <div class="flex items-center space-x-3">
        <img src="@/assets/images/logo.jpg" class="h-10 w-10" />
        <span class="hidden lg:inline-block font-bold text-lg">Omni Wise</span>
      </div>

      <!-- Middle: Tabs -->
      <div class="flex-1 flex justify-center  items-center overflow-visible">
       <el-tabs v-model="activeTab" @tab-click="handleTabSelect">
  <el-tab-pane name="trade">
    <template #label>
      <img
        class="homeTabItemIcon"
        :src="activeTab === 'trade' ? tabIcons.trade.on : tabIcons.trade.off"
      /> Trade
    </template>
  </el-tab-pane>

  <el-tab-pane name="systemTrade">
    <template #label>
      <img
        class="homeTabItemIcon"
        :src="activeTab === 'systemTrade' ? tabIcons.systemTrade.on : tabIcons.systemTrade.off"
      /> SystemTrade
    </template>
  </el-tab-pane>

  <el-tab-pane name="orderlist">
    <template #label>
      <img
        class="homeTabItemIcon"
        :src="activeTab === 'orderlist' ? tabIcons.orderlist.on : tabIcons.orderlist.off"
      /> OrderList
    </template>
  </el-tab-pane>

  <el-tab-pane name="accountoverview">
    <template #label>
      <img
        class="homeTabItemIcon"
        :src="activeTab === 'accountoverview' ? tabIcons.accountoverview.on : tabIcons.accountoverview.off"
      /> Account
    </template>
  </el-tab-pane>

  <el-tab-pane name="accountmanagement">
    <template #label>
      <img
        class="homeTabItemIcon"
        :src="activeTab === 'accountmanagement' ? tabIcons.accountmanagement.on : tabIcons.accountmanagement.off"
      /> Management
    </template>
  </el-tab-pane>
</el-tabs>
      </div>

      <!-- Right: Icons + User Menu -->
      <div class="flex items-center space-x-4">
        <font-awesome-icon :icon="['fas', 'circle-question']" />
        <font-awesome-icon :icon="['fas', 'bell']" />

        <div class="relative">
          <button
            id="btn_login"
            @click="toggleDropdown"
            :class="[
              'px-3 py-2 rounded-full border-2 flex items-center',
              showDropdown ? 'border-white' : 'border-transparent'
            ]"
          >
            <font-awesome-icon :icon="['fas', 'user']" />
          </button>
          <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-48 bg-gray-900 border border-gray-700 text-white rounded-md shadow-lg"
          >
            <a href="#" class="block px-4 py-2 hover:bg-gray-700">帳戶設定</a>
            <a href="#" class="block px-4 py-2 hover:bg-gray-700">系統設定</a>
            <a href="#" class="block px-4 py-2 hover:bg-gray-700">語言</a>
            <a href="#" class="block px-4 py-2 hover:bg-gray-700" @click="logout">登出</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex flex-1 overflow-hidden bg-black">
      <tradeView v-if="activeTab === 'trade'" class="flex-1" />
      <autoTradeView v-else-if="activeTab === 'systemTrade'" class="flex-1" />
      <orderlist v-else-if="activeTab === 'orderlist'" class="flex-1" />
      <accPropertiesView v-else-if="activeTab === 'accountoverview'" class="flex-1" />
      <accountDetailList v-else-if="activeTab === 'accountmanagement'" class="flex-1" />
    </main>
  </div>
</template>


<style scoped>
.homeTabItemIcon {
  height: 40px;
}

#btn_login {
  transition: all 0.2s ease;
}

#btn_login:hover {
  border-color: #fff;
}

/* Optional: prevent overflow in tabs */
.el-tabs__header {
  margin: 0 !important;
  background-color: black;
}
</style>
