<script setup>
import { onMounted, ref } from 'vue'
import VueCookies from 'vue-cookies'

// You can use VueCookies directly via its functions
// or register it globally in main.js
const hoverServerSetting = ref(false)
const userid = ref('')
const host = ref('')
const port = ref('')
const ip = ref('')

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  userid.value = params.get('userid') || ''
  host.value = params.get('host') || ''
  port.value = params.get('port') || ''
  ip.value = params.get('host') || ''

  console.log(`USERID = ${userid.value} | host = ${host.value} | port = ${port.value}`)

  VueCookies.set('selectedAccount', JSON.stringify({ selectedAccount: userid.value }))
})
</script>


<template>
    <div>
        <div class="inline-block">
            <i class="text-2xl fas fa-server p-2 cursor-pointer hover:bg-gray-700"  @mouseover="hoverServerSetting = true"  @mouseleave="hoverServerSetting = false"></i>
            <div id="serverbox" class="bg-alphablack w-64 p-6 rounded-md border border-gray-800  absolute" v-show="hoverServerSetting" @mouseover="hoverServerSetting = true"  @mouseout="hoverServerSetting = false">
                <h1 class="text-left align-left"> Login setting </h1>
                <hr class="border my-1" />
                <form method="GET" >
                    <div class="py-1"><div class="w-10 inline-block">User</div> <input type="text" name="userid" class="pl-1 usertext w-40  inline-block bg-gray-900 border border-gray-400 rounded-sm" v-model="userid"  /></div>
                    <div class="py-0"><div class="w-10 inline-block">Host</div> <input type="text" name="host" class="pl-1 host w-40  inline-block bg-gray-900 border border-gray-400 rounded-sm"  v-model="host" /></div>
                    <div class="py-1"><div class="w-10 inline-block">Port</div> <input type="text" name="port" class="pl-1 port w-40  inline-block bg-gray-900 border border-gray-400 rounded-sm"  v-model="port" /></div>
                    <button type="submit" class=" bg-blue-500 text-sm py-1 px-4 rounded-sm">Submit</button>
                    <button type="submit" class=" bg-blue-500 text-sm py-1 px-4 rounded-sm">Logout</button>
                </form>
            </div>
        </diV>
        <div class="inline-block">
            <i class="text-2xl fas fa-volume-up pr-2 cursor-pointer hover:bg-gray-700"></i> 
        </div>
        <div class="inline-block">
           
        </div>
    </div>	
</template>
<style>
    #serverbox{ right: 1.5em;}

</style>
