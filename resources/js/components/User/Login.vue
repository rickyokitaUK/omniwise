<script setup>
import axios from 'axios'
import { useCookies } from 'vue3-cookies'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const { cookies } = useCookies()

// API base URL always correct for production
const apiBase = `${window.location.origin}/api`

const input = ref({ username: '', password: '' })
const selectedTab = ref('live')
const errorMessage = ref('')
const token = ref(localStorage.getItem('auth_token') || null)

// If already logged in → redirect
onMounted(() => {
  if (token.value) {
    router.replace({ name: 'home' })
  }
})

// ---------------------------
// LOGIN FUNCTION
// ---------------------------
async function login() {
  errorMessage.value = ''

  if (!input.value.username || !input.value.password) {
    errorMessage.value = "Username / password can't be empty"
    return
  }

  try {
    // 1. Get CSRF cookie
    await fetch("/sanctum/csrf-cookie", {
      credentials: "include"
    })

    // 2. Login request
    const res = await fetch(`${apiBase}/login`, {
      method: 'POST',
      credentials: "include",         // REQUIRED
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': cookies.get('XSRF-TOKEN'),
      },
      body: JSON.stringify({
        username: input.value.username,
        password: input.value.password
      })
    })

    const text = await res.text()
    console.log('Login status:', res.status)
    console.log('Login response:', text)

    if (!res.ok) {
      errorMessage.value = `HTTP ${res.status}\n${text}`
      return
    }

    const data = JSON.parse(text)

    if (!data.token) {
      errorMessage.value = "Invalid login response"
      return
    }

    // 3. Store token
    localStorage.setItem('auth_token', data.token)
    cookies.set('auth_token', data.token, '7d')
    cookies.set('selected_mode', selectedTab.value, '7d')

    token.value = data.token

    // 4. Set axios default header
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

    // 5. Redirect
    router.replace({ name: 'home' })

  } catch (err) {
    console.error(err)
    errorMessage.value = `⚠️ Error: ${err.message}`
  }
}
</script>

<template>
  <div id="appbg" class="min-h-screen w-full bg-cover bg-center flex items-center justify-center">
    <!-- Login Box -->
    <div class="w-full max-w-md bg-[#131722] border border-gray-500 rounded-md p-10 shadow-xl">

       <!-- Logo + Title -->
      <div class="flex items-center mb-6">
        <img
          src="@/assets/images/logo.jpg"
          class="w-10 h-10 mr-3"
        />
        <h3 class="text-lg text-white">OmniWeb Trader v2.0</h3>
      </div>

      <h1 class="text-2xl text-white justify-center" style="margin-top: 10px;margin-bottom: 10px;">
        Welcome to OmniTrader Desktop
      </h1>

      <div class="text-sm text-white" style="margin-top: 20px;margin-bottom: 20px;">
        <ul class="flex">
          <li class="mr-4" style="display:inline;" :class="{ 'underline': selectedTab === 'live' }" @click="selectedTab = 'live'">Live Trading</li>
          <li class="mr-4" style="display:inline;" :class="{ 'underline': selectedTab === 'paper' }" @click="selectedTab = 'paper'">Paper Trading</li>
        </ul>
      </div>

      <input type="text" name="username" v-model="input.username" placeholder="Username"
        class="block border-solid border-gray-600 border px-2 py-2 my-2 rounded-sm w-full bg-black text-white"
        @keyup.enter="login" />

      <input type="password" name="password" v-model="input.password" placeholder="Password"
        @keyup.enter="login"
        class="block border-solid border-gray-600 border px-2 py-2 my-2 rounded-sm w-full bg-black text-white" />

      <div v-if="errorMessage" class="text-red-500 mt-2 text-left">{{ errorMessage }}</div>

      <div class="text-sm text-blue-500 text-right">Forget password</div>

      <button type="button" @click="login()" @keyup.enter="login()"
        class="block bg-blue-500 text-white rounded-sm px-3 py-1">Login</button>
    </div>
  </div>
</template>

<style scoped>
#appbg {
  background-image: url("@/assets/images/omniweb_bg.jpg");
  background-repeat: no-repeat;
  background-size: cover;
}

.underline {
  color: #478dc5;
}
</style>
