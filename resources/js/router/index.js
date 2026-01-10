import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/User/Login.vue'

const routes = [
  { path: '/login', name: 'login', component: Login },
  {
    path: '/home',
    name: 'home',
    component: () => import('../components/Pages/Home.vue'), // optional page
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
