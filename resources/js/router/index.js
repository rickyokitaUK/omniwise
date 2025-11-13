import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'

const routes = [
  { path: '/login', name: 'login', component: Login },
  {
    path: '/home',
    name: 'home',
    component: () => import('../components/Home.vue'), // optional page
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
