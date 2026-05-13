import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import Login from './views/Login.vue'
import Dashboard from './views/Dashboard.vue'
import Clientes from './views/Clients.vue'
import Visitas from './views/Visitas.vue'
import Productos from './views/Productos.vue'
import Gastos from './views/Gastos.vue'
import Pedidos from './views/Pedidos.vue'
import Objetivos from './views/Objetivos.vue'
import { createRouter, createWebHistory } from 'vue-router'

//Definimos las rutas de la aplicacion 
const routes = [
    { path: '/', component: Login },
    { path: '/dashboard', component: Dashboard }, 
    { path: '/clientes', component: Clientes }, 
    { path: '/visitas', component: Visitas },
    { path: '/productos', component: Productos }, 
    { path: '/gastos', component: Gastos },
    { path: '/pedidos', component: Pedidos },
    {path: '/objetivos', component: Objetivos }
]

//Creamos la instancia del router
const router = createRouter({
    history: createWebHistory(),
    routes
})

const app = createApp(App)
app.use(router)
app.mount('#app')
