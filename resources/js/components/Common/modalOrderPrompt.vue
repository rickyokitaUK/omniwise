<template>
  <div v-if="visible" class="popup-overlay">
    <div class="popup-box">
      <div class="popup-header">
        <i class="fas fa-exclamation-circle"></i>
        <span>訂單明細</span>
      </div>
      <div class="popup-body">
        <div class="order-detail">
          <div>賬戶</div>
          <div>香港期貨模擬賬戶</div>
        </div>
        <div class="order-detail">
          <div>訂單類型</div>
          <div>限價單</div>
        </div>
        <div class="order-detail">
          <div>方向</div>
          <div class="green-text">模擬買入</div>
        </div>
        <div class="order-detail">
          <div>代碼</div>
          <div>MHI2408.HK 小恒指2408</div>
        </div>
        <div class="order-detail">
          <div>價格</div>
          <div>17141.00</div>
        </div>
        <div class="order-detail">
          <div>數量</div>
          <div>1</div>
        </div>
        <div class="order-detail">
          <div>初始保證金</div>
          <div>10,590.00 HKD</div>
        </div>
        <div class="checkbox-container">
          <input type="checkbox" id="add-to-watchlist" v-model="addToWatchlist">
          <label for="add-to-watchlist">添加至自選</label>
        </div>
      </div>
      <div class="popup-footer">
        <button @click="cancel" class="cancel-button">取消</button>
        <button @click="confirm" class="confirm-button">確認</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      visible: true,
      addToWatchlist: true,
    };
  },
  methods: {
    confirm() {
      // Handle the confirm action here
  //卜    this.visible = false;
      this.$emit('confirm'); // Emit the confirm event
    },
    cancel() {
      // Handle the cancel action here
  //    this.visible = false;

      this.$emit('cancel'); // Emit the cancel event
    }
  }
};
</script>

<style scoped>
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index:9999;
}

.popup-box {
  background-color: #2c2c2c;
  color: #f2f2f2;
  padding: 20px;
  border-radius: 8px;
  width: 300px;
}

.popup-header {
  display: flex;
  align-items: center;
  font-size: 18px;
  margin-bottom: 15px;
}

.popup-header i {
  margin-right: 8px;
  color: #ffa500;
}

.order-detail {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.green-text {
  color: green;
}

.checkbox-container {
  display: flex;
  align-items: center;
  margin: 10px 0;
}

.popup-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.cancel-button, .confirm-button {
  padding: 10px 15px;
  border: none;
  cursor: pointer;
  margin-left: 10px;
}

.cancel-button {
  background-color: #757575;
  color: #f2f2f2;
}

.confirm-button {
  background-color: #ff8c00;
  color: #f2f2f2;
}
</style>
