<script setup>
import { ref, onMounted } from 'vue'

// isCollapsed controla el ancho en Desktop/Tablet
const isCollapsed = ref(false) 
// isMobileMenuOpen controla si el menú aparece en móviles (iPhone)
const isMobileMenuOpen = ref(false)

const stats = ref({
  clientes: 0,
  pedidos: 0,
  objetivos: 0,
  visitas: 0,
  gastos: 0
})

const pedidosRecientes = ref([])

//Funcion para cargar datos del dashboard

const loadDashboardData = async () => {
  try {
    const response = await  fetch('http://localhost/MiProyecto/api/get_dashboard_stats.php')
    const data = await response.json()
    stats.value = data.stats
    pedidosRecientes.value = data.pedidosRecientes
  } catch (error) {
    console.error('Error al cargar datos del dashboard:', error)
  }
}

// MENU PRINCIPAL (SIDEBAR)
const menuItems = [
  { name: 'Dashboard', icon: '📊', path: '/dashboard' },
  { name: 'Clientes', icon: '👥', path: '/clientes' },
  { name: 'Pedidos', icon: '📦',path: '/pedidos' },
  { name: 'Objetivos', icon: '🎯', path: '/objetivos' },
  { name: 'Visitas', icon: '📍', path: '/visitas' },
  { name: 'Gastos', icon: '💸', path: '/gastos' },
  {name :' Productos', icon: '🛒', path: '/productos' },
]
</script>





<template>
  <div class="min-h-screen bg-[#f0f4f7] font-sans text-slate-700">
    
    <aside 
      :class="[
        isCollapsed ? 'md:w-20' : 'md:w-64',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
      ]"
      class="fixed top-0 left-0 h-full bg-[#109bc5] text-white transition-all duration-300 ease-in-out z-50 flex flex-col shadow-2xl"
    >
      <button 
        @click="isCollapsed = !isCollapsed"
        class="hidden md:flex absolute -right-3 top-10 bg-[#ff6900] w-6 h-6 rounded-full items-center justify-center border-2 border-white text-[10px] shadow-lg hover:scale-110 transition-transform"
      >
        <span :class="isCollapsed ? 'text-xs' : 'text-xs'">
          {{ isCollapsed ? '→' : '←' }}
        </span>
      </button>

      <div class="p-6 flex items-center gap-3 overflow-hidden whitespace-nowrap">
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center font-black"><img src='../img/icono.png'></div>
        <span v-if="!isCollapsed" class="font-black text-xl tracking-tighter uppercase transition-opacity">CRM</span>
      </div>

     <nav class="flex-1 px-3 space-y-1">
        <router-link 
          v-for="item in menuItems" 
          :key="item.name" 
          :to="item.path || '#'"
          class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/10 transition-all group relative"
          active-class="bg-white/20 shadow-sm"
        >
          <span class="text-xl">{{ item.icon }}</span>
          
          <span v-if="!isCollapsed" class="font-bold text-sm">{{ item.name }}</span>
          
          <div v-if="isCollapsed" class="absolute left-16 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-[60]">
            {{ item.name }}
          </div>
        </router-link>
      </nav>

      <button @click="isMobileMenuOpen = false" class="md:hidden p-4 text-center text-white/50 text-xs">
        Cerrar Menú
      </button>
    </aside>

    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden"></div>

    <main 
      :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" 
      class="transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8"
    >
      <header class="flex justify-between items-center mb-6 md:mb-10 fixed md:relative top-0 left-0 w-full p-4 md:p-0 bg-[#f0f4f7] md:bg-transparent z-30">
        <div class="flex items-center gap-3">
          <button @click="isMobileMenuOpen = true" class="md:hidden w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-[#109bc5]">
             ☰
          </button>
          <h1 class="text-xl md:text-2xl font-black text-slate-800">Panel Principal</h1>
        </div>
        <router-link 
          to="/pedidos?add=true" 
          class="bg-[#ff6900] text-white px-4 py-2 rounded-xl font-bold shadow-lg text-xs hover:bg-[#e55e00] transition-colors inline-block"
        >
          + <span class="hidden sm:inline">Añadir Pedido</span>
        </router-link>
      </header>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-6 mb-8">
        <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#109bc5]">
          <span class="text-xl block mb-1">👥</span>
          <p class="text-gray-400 text-[9px] font-bold uppercase">Clientes</p>
          <h3 class="text-xl font-black text-slate-800">{{ stats.clientes }}</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#ff6900]">
          <span class="text-xl block mb-1">📦</span>
          <p class="text-gray-400 text-[9px] font-bold uppercase">Pedidos</p>
          <h3 class="text-xl font-black text-slate-800">{{ stats.pedidos }}</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#fcb900]">
          <span class="text-xl block mb-1">🎯</span>
          <p class="text-gray-400 text-[9px] font-bold uppercase">Objetivos</p>
          <h3 class="text-xl font-black text-slate-800">{{ stats.objetivos }}%</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#00d084]">
          <span class="text-xl block mb-1">📍</span>
          <p class="text-gray-400 text-[9px] font-bold uppercase">Visitas</p>
          <h3 class="text-xl font-black text-slate-800">{{ stats.visitas }}</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#9b51e0] col-span-2 md:col-span-1">
          <span class="text-xl block mb-1">💸</span>
          <p class="text-gray-400 text-[9px] font-bold uppercase">Gastos</p>
          <h3 class="text-xl font-black text-slate-800">{{ stats.gastos }}€</h3>
        </div>
      </div>

      <div class="bg-white rounded-3xl p-5 md:p-8 shadow-sm border border-gray-100 overflow-hidden">
        <h3 class="font-black text-lg text-slate-800 mb-6 flex items-center gap-2">
          <span class="w-1.5 h-6 bg-[#d65799] rounded-full"></span>
          Últimos Pedidos
        </h3>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-50">
                <th class="pb-4">Ref</th>
                <th class="pb-4">Cliente</th>
                <th class="pb-4 text-right">Monto</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="pedido in pedidosRecientes" :key="pedido.id" class="hover:bg-gray-50/50">
                <td class="py-4 text-[11px] font-bold text-gray-400">{{ pedido.id }}</td>
                <td class="py-4 text-sm font-bold text-slate-700">{{ pedido.cliente }}</td>
                <td class="py-4 text-right font-black text-slate-800">{{ pedido.total }}€</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>