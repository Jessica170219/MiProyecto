<script setup>
import { ref, onMounted } from 'vue'

// Estados de Interfaz
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)

// Datos
const visitas = ref([])
const farmaciasList = ref([])
const isLoading = ref(true)

// Filtros
const filtros = ref({
  farmacia: '',
  desde: '',
  hasta: ''
})

// Formulario Nueva Visita
const nuevaVisita = ref({
  fecha: new Date().toISOString().split('T')[0],
  farmacia: '',
  observacion: ''
})
//Funcion para obtener la lista de visitas desde el backend con filtros
const fetchVisitas = async () => {
  isLoading.value = true
  const query = new URLSearchParams(filtros.value).toString()
  try {
    const response = await fetch(`http://localhost/MiProyecto/api/get_visitas.php?${query}`)
    const data = await response.json()
    visitas.value = data.visitas || []
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isLoading.value = false
  }
}
//Funcion para obtener la lista de farmacias desde el backend
const fetchFarmacias = async () => {
  const response = await fetch('http://localhost/MiProyecto/api/get_clients.php')
  const data = await response.json()
  // Usamos data.clients porque así viene de tu PHP según vimos antes
  farmaciasList.value = data.clients || []
}

//Funcion para guardar una nueva visita en el backend
const guardarVisita = async () => {
  if (!nuevaVisita.value.farmacia) return alert("Selecciona una farmacia")
  
  try {
    const response = await fetch('http://localhost/MiProyecto/api/agregar_visita.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(nuevaVisita.value)
    })
    const data = await response.json()
    if (data.success) {
      nuevaVisita.value = { 
        fecha: new Date().toISOString().split('T')[0], 
        farmacia: '', 
        observacion: '' 
      }
      fetchVisitas()
    }
  } catch (error) { console.error(error) }
}

onMounted(() => {
  fetchVisitas()
  fetchFarmacias()
})

const menuItems = [
  { name: 'Dashboard', icon: '📊', path: '/dashboard' },
  { name: 'Clientes', icon: '👥', path: '/clientes' },
  { name: 'Pedidos', icon: '📦', path: '/pedidos' },
  { name: 'Objetivos', icon: '🎯', path: '/objetivos' },
  { name: 'Visitas', icon: '📍', path: '/visitas' },
  { name: 'Gastos', icon: '💸', path: '/gastos' },
  {name :' Productos', icon: '🛒', path: '/productos' },
]
</script>

<template>
  <div class="min-h-screen bg-[#f0f7f4] font-sans text-slate-700 flex">
    
    <aside 
      :class="[isCollapsed ? 'md:w-20' : 'md:w-64', isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']"
      class="fixed top-0 left-0 h-full bg-[#109bc5] text-white transition-all duration-300 z-50 flex flex-col shadow-2xl"
    >
      <button @click="isCollapsed = !isCollapsed" class="hidden md:flex absolute -right-3 top-10 bg-[#ff6900] w-6 h-6 rounded-full items-center justify-center border-2 border-white text-[10px] shadow-lg hover:scale-110 transition-transform">
        <i :class="isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
      </button>

      <div class="p-6 flex items-center gap-3">
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center">
          <img src='../img/icono.png' class="w-6">
        </div>
        <span v-if="!isCollapsed" class="font-black text-xl tracking-tighter uppercase">CRM</span>
      </div>

      <nav class="flex-1 px-3 space-y-1">
        <router-link v-for="item in menuItems" :key="item.name" :to="item.path || '#'"
          class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/10 transition-all group relative"
          :class="{'justify-center': isCollapsed, 'bg-white/20 border-l-4 border-white': item.name === 'Visitas'}"
        >
          <span class="text-xl">{{ item.icon }}</span>
          <span v-if="!isCollapsed" class="font-bold text-sm">{{ item.name }}</span>
          
          <div v-if="isCollapsed" class="absolute left-16 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-[60]">
            {{ item.name }}
          </div>
        </router-link>
      </nav>
    </aside>

    <main :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" class="flex-1 transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8">
      
      <header class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-black text-[#00d084]">Gestión de Visitas</h1>
          <p class="text-gray-400 font-medium text-xs md:text-sm">Registro y seguimiento comercial</p>
        </div>
        <div class="hidden sm:flex bg-white p-2 px-4 rounded-2xl shadow-sm border border-green-50 items-center gap-3">
            <span class="text-[10px] font-black text-green-400 uppercase tracking-widest">Actividad</span>
            <div class="flex -space-x-2">
                <div class="w-7 h-7 rounded-full border-2 border-white bg-green-100 flex items-center justify-center text-[10px] font-bold">📍</div>
                <div class="w-7 h-7 rounded-full border-2 border-white bg-green-200 flex items-center justify-center text-[10px] font-bold">📍</div>
            </div>
        </div>
      </header>

      <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-green-50 mb-8 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#00d084]/5 rounded-full"></div>
        
        <h3 class="font-black text-slate-800 mb-6 flex items-center gap-2">
            <span class="w-8 h-8 bg-green-50 text-[#00d084] rounded-xl flex items-center justify-center text-lg">✨</span>
            Registrar nueva visita
        </h3>
        
        <form @submit.prevent="guardarVisita" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end relative z-10">
          <div class="space-y-1">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2">Fecha</label>
            <input v-model="nuevaVisita.fecha" type="date" class="w-full bg-gray-50 border-none p-3 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#00d084] outline-none transition-all">
          </div>
          <div class="space-y-1">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2">Farmacia</label>
            <input v-model="nuevaVisita.farmacia" list="farmaciasList" placeholder="Buscar farmacia..." class="w-full bg-gray-50 border-none p-3 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#00d084] outline-none transition-all">
            <datalist id="farmaciasList">
              <option v-for="f in farmaciasList" :key="f.id" :value="f.farmacia"></option>
            </datalist>
          </div>
          <div class="space-y-1 lg:col-span-1">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2">Observación</label>
            <input v-model="nuevaVisita.observacion" type="text" placeholder="¿Qué se trató?" class="w-full bg-gray-50 border-none p-3 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#00d084] outline-none transition-all">
          </div>
          <button type="submit" class="bg-[#00d084] text-white p-3 rounded-2xl font-black shadow-lg shadow-green-500/20 hover:bg-[#00b372] transition-all flex items-center justify-center gap-2">
            <i class="fas fa-check-circle"></i> Guardar Visita
          </button>
        </form>
      </div>

      <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50/30 flex flex-wrap gap-4 items-center justify-between border-b border-gray-50">
            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-green-400">🔍</span>
                <input v-model="filtros.farmacia" @input="fetchVisitas" type="text" placeholder="Filtrar por farmacia..." class="outline-none text-xs font-bold w-48">
            </div>
            <div class="flex gap-2">
                <input v-model="filtros.desde" @change="fetchVisitas" type="date" class="text-[10px] font-black p-2 rounded-xl border-none bg-green-50 text-[#00d084] outline-none">
                <button @click="filtros={farmacia:'',desde:'',hasta:''}; fetchVisitas()" class="w-10 h-10 flex items-center justify-center bg-gray-100 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-400 transition-colors">
                    <i class="fas fa-sync-alt text-xs"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="text-gray-400 text-[9px] uppercase tracking-[0.3em]">
              <tr>
                <th class="p-8">Calendario</th>
                <th class="p-8">Farmacia / Cliente</th>
                <th class="p-8">Notas de la Visita</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="v in visitas" :key="v.id" class="group hover:bg-green-50/20 transition-colors">
                <td class="p-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white border-2 border-green-50 rounded-2xl flex flex-col items-center justify-center shadow-sm group-hover:border-[#00d084] transition-colors">
                            <span class="text-[9px] font-black text-green-400 uppercase">{{ new Date(v.fecha).toLocaleString('es-ES', {month: 'short'}) }}</span>
                            <span class="text-base font-black text-slate-800">{{ new Date(v.fecha).getDate() }}</span>
                        </div>
                        <span class="text-[11px] font-bold text-gray-300">{{ new Date(v.fecha).getFullYear() }}</span>
                    </div>
                </td>
                <td class="p-8">
                    <p class="font-black text-slate-800 text-sm mb-1">{{ v.farmacia }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-green-100 text-[#00d084] uppercase">Visita Completada</span>
                </td>
                <td class="p-8">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-transparent group-hover:border-green-100 group-hover:bg-white transition-all max-w-md">
                        <p class="text-xs text-gray-500 font-medium leading-relaxed italic">
                           "{{ v.observacion || 'Sin comentarios adicionales.' }}"
                        </p>
                    </div>
                </td>
              </tr>
              <tr v-if="visitas.length === 0 && !isLoading">
                <td colspan="3" class="p-24 text-center">
                    <div class="text-5xl mb-4 opacity-20">🍃</div>
                    <p class="text-gray-400 font-bold text-sm">No hay registros para mostrar</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden"></div>

    <button @click="isMobileMenuOpen = true" class="md:hidden fixed top-4 left-4 w-10 h-10 bg-white rounded-xl shadow-lg flex items-center justify-center text-[#00d084] z-40">
      <i class="fas fa-bars"></i>
    </button>
  </div>
</template>

<style scoped>
/* Estilo para los inputs de fecha en Chrome/Safari */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(48%) sepia(80%) saturate(2400%) hue-rotate(130deg) brightness(95%) contrast(101%);
}
</style>