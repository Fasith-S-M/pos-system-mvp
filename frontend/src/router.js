import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from './views/LoginPage.vue'
import PosPage from './views/PosPage.vue'

const routes = [
  { path: '/', component: LoginPage },
  { path: '/pos', component: PosPage }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
