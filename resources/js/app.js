
import "../css/app.css";

import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import App from './App.vue'
import router from './router'

// Font Awesome imports
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faUser, faChartLine, faSignOutAlt } from '@fortawesome/free-solid-svg-icons'
import axios from 'axios';

// Add icons to the library
library.add(faUser, faChartLine, faSignOutAlt)

const token = localStorage.getItem('auth_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
axios.defaults.headers.common['Accept'] = 'application/json';


const app = createApp(App)
const pinia = createPinia() 

app.use(pinia)
app.use(router)
app.use(ElementPlus)
app.mount('#app')
