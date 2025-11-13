
<template>
<div id="orderpanel" class="   text-l text-white" >
	<div id="panelbound" class="w-full">
		<div id="fieldContainer" class="flex flex-row flex-wrap justify-space-around">
			<!-- <div >代號 : <input v-model="symbol" value="" class="rounded-sm" readonly/></div> -->
			<div class="panelField flex flex-row" > 
				<div class="fieldName">類型</div>
				<div class="fieldVal flex flex-row">
					<select id="orderType" name="orderType" v-model="orderType" class="rounded-sm w-full">
						<option value="limit">限價</option>
						<option value="market">市價</option>
						<option value="stop">停損</option>
						<option value="stoplimit">停損限價</option>
						<option value="tradestopM">追蹤停損$</option>
						<option value="tradestopP">追蹤停損%</option>
					</select>
				</div>
			</div>
			<div class="panelField flex flex-row" v-if="orderType=='limit'"> 
				<div class="fieldName">限價</div> 
				<div class="fieldVal flex flex-row">
					<input class="priceChangeBtn" type="button" @click="priceDown" name="-" value="-">
					<input v-model="price" placeholder="> 0" class="rounded-sm inputField"/>
					<input class="priceChangeBtn" type="button" @click="priceUp" name="+" value="+">
				</div>
			</div>
			<div class="panelField flex flex-row" v-if="orderType=='stop' || orderType=='stoplimit'"> 
				<div class="fieldName">停損價格</div>  
				<div class="fieldVal flex flex-row">
					<input class="priceChangeBtn"  type="button" @click="stopPriceDown" name="-" value="-">
					<input v-model="stopPrice" placeholder="> 0" class="rounded-sm inputField"/>
					<input class="priceChangeBtn" type="button" @click="stopPriceUp" name="+" value="+">
				</div>
			</div>
			<div class="panelField flex flex-row" > 
				<div class="fieldName">股數</div>
				<div class="fieldVal flex flex-row">
					<input class="priceChangeBtn"  type="button" @click="qtyDown" name="-" value="-">
					<input v-model="qty" placeholder="e.g:00001" class="rounded-sm"/>
					<input class="priceChangeBtn" type="button" @click="qtyUp" name="+" value="+">
				</div>
			</div>
			<div class="panelField flex flex-row" > 
				<div class="fieldName">金額</div>
				<div class="fieldVal flex flex-row">
					<div  class="rounded-sm w-full">{{totalcost}}</div>
				</div>
			</div>
			<div class="panelField flex flex-row" > 
				<div class="fieldName">期限</div>
				<div class="fieldVal flex flex-row">
					<input type="radio" name="todayValid" value="todayValid" v-model="duration"> <label for="todayValid">當日有效</label>
					<input type="radio" name="validUntilcancel" value="validUntilcancel" v-model="duration"> <label for="validUntilcancel">撤單前有效</label>
				</div>
			</div>
			<div class="panelField flex flex-row" > 
				<div class="fieldName">盤前盤後</div>
				<div class="fieldVal flex flex-row">
					<input type="radio" name="allow" value="yes" v-model="tradinghourOnly"> <label for="yes">允許</label> 
					<input type="radio" name="notAllow" value="no" v-model="tradinghourOnly"> <label for="no">不允許</label>
				</div>
			</div>
		</div>
		<div id="panelProductOnhands" class="w-full flex flex-col">
			<div class="w-full flex flex-row">
				<div class="flex flex-row ">可買 <span>${{maxOnHold - stockOnHand}}</span> </div>
				<div class="flex flex-row ">持倉可賣 <span>${{stockOnHand}}</span> </div>
			</div>
			<div class="w-full flex flex-row">
				<div class="flex flex-row ">最大 <span>${{maxOnHold}}</span> </div>
				<div class="flex flex-row ">可沽空 <span>${{stockOnHand}}</span> </div>
			</div>
		</div>
		<div id="panelOrderBtns" class="w-full flex flex-row">
			<input type="button" value="買入" @click="openOrderPrompt('buy')"  class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"/>
			<input type="button" value="賣出" @click="openOrderPrompt('sell')" class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"/>
			<!-- <input type="button" value="加入觀察" @click="PlaceNewOrder('watch')" class="px-2 bg-white text-black hover:bg-gray-300 rounded-sm"/> -->
		</div>
	</div>

	<!-- Modal Component -->
	<modal-order-prompt v-if="isModalVisible" @confirm="handleConfirm" @cancel="handleCancel" />
</div>
</template>
<style scoped>


#orderpanel > #panelbound{
    height: fit-content;
    margin: 0;
    padding: 5px 5px;
	background-color: rgb(41, 41, 41);
}

#fieldContainer,#panelProductOnhands{
	justify-content: space-between;
	align-content: left;
}

/* button  */
#panelOrderBtns{
	justify-content: space-around;
	align-content: left;
	margin:  10px 0;
}
#panelOrderBtns > input{
	width: 35%;
	height: 30px;
	color: white;
	font-weight: 700;
	font-size: 100%;
	cursor: pointer;
}
#panelOrderBtns > input:nth-child(1) {
	background-color: rgb(71, 255, 71);
}	
#panelOrderBtns > input:nth-child(2) {
	background-color: rgb(255, 53, 84);
}

/* fields  */
#fieldContainer > div{
	width : 90%;
	position: relative;
	margin-bottom: 10px;
}
#fieldContainer > div > input , .panelField > .fieldVal > select , .panelField > .fieldVal > input {
	color: gray;
	background-color: rgb(41, 41, 41);
}



.fieldVal > .priceChangeBtn{
	width: 21px;
    height: 21px;
    margin: 0 9px;
	background-color: rgb(61, 61, 61) !important;
}


.panelField > .fieldName{
	width: 70px;
}

.panelField > .fieldVal{
	width: calc(100% - 70px);
	justify-content: space-between;
}


/*  Product Onhands */
#panelProductOnhands > div{
	justify-content: space-around;
	background-color: black;
}
#panelProductOnhands > div > div{
    width: 36%;
	justify-content: space-around;
	color: gray;
}
#panelProductOnhands > div > div > span{
	color: rgb(123, 123, 251);
}

/* Add your existing styles */
#orderpanel > #panelbound {
  height: fit-content;
  margin: 0;
  padding: 5px 5px;
  background-color: rgb(41, 41, 41);
}

/* Add other styles as per your code */
/* ... */

/* Modal dimming effect */
.popup-overlay {
  background-color: rgba(0, 0, 0, 0.9); /* Dimming effect */
}

</style>

<script >
import modalOrderPrompt from './modalOrderPrompt.vue'; // Import the modal component

//For testing

export default {
		name : 'orderpanel',
		components: {
			modalOrderPrompt, // Register the modal component
		},
		data: function() {			
			return {
				symbol:	"",
				qty:100,
				price:0,
				stopPrice:0,
				orderType:"limit",
				action:"",
				totalcost : 0,
				duration:"todayValid",
				tradinghourOnly: "no",
				maxOnHold:23018.2,
				stockOnHand:0,
				stockCanSell:0,
				isModalVisible: false, // State to control modal visibility
			}
		},		
		created: function () {

			console.log(' orderpanel created -1');

		},
		mounted() {
			console.log("orderpanel loaded");
			this.changeTotalCost();
		},
		watch: {
        	on_seach_stockCode: function(val, oldVal) {				
				if(val.length > 0){
					// list out the simular stock code
					
				}
			}
		},
		beforeDestroy() {
		  
		},
		destroyed: function () {
			
		},
		
		methods:{
			openOrderPrompt(action) {
				this.isModalVisible = true; // Show the modal
				// Set the action type (buy/sell) if needed
			},
			handleConfirm() {
				this.isModalVisible = false; // Hide the modal on confirm
				// Handle the confirm action
				
			},
			handleCancel() {
				this.isModalVisible = false; // Hide the modal on cancel
				// Handle the cancel action
				
			},
			
			SetOrderPanel(n_price, n_baseQty){
				this.price = n_price;
				this.changeTotalCost();
			},
			/// =-===-=-=-=-=-

			priceUp(){
				this.price += 100;
				this.changeTotalCost();	
			},
			priceDown(){
				this.price = Math.max(0,this.price - 100);
				this.changeTotalCost();	
			},
			stopPriceUp(){
				this.stopPrice  += 100;
				this.changeTotalCost();	
			},
			stopPriceDown(){
				this.stopPrice  = Math.max(0,this.stopPriceDown - 100);
				this.changeTotalCost();	
			},
			qtyUp(){
				this.qty += 100;
				this.changeTotalCost();	
			},
			qtyDown(){
				this.qty = Math.max(0,this.qty - 100);
				this.changeTotalCost();	
			},
			changeTotalCost(){
				if(this.orderType ==='limit'){
					this.totalcost = this.price * this.qty;
				}else if(this.orderType ==='stop' || this.orderType ==='stoplimit'){
					this.totalcost = this.stopPrice  * this.qty;
				}else{

				}
				
			},
			
			/// =-===-=-=-=-=-
	
			AddToWatch(data) {
				
			},

			PlaceNewOrder(action){
				// TODO : make order
			},
			openModal() {
				this.isModalVisible = true;
			},
			closeModal() {
				this.isModalVisible = false;
			},
			// Other existing methods like changeTotalCost, priceUp, priceDown, etc.
			// ...


			
			
		}
}
</script>