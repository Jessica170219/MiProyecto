
<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// ========== UI ==========
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)
const showModalObjetivos = ref(false)

// ========== Datos ==========
const anio = ref(new Date().getFullYear())
const trimestre = ref(Math.ceil((new Date().getMonth() + 1) / 3)) // trimestre actual
const objetivos = ref({
  facturacion: 0,
  intima: 0,
  artiseid:0,
  menopausia: 0,
  furaderm: 0,
  gastro: 0,
  copromo: 0
})
const objetivosTemp = ref({ ...objetivos.value })
const progreso = ref({
  facturacion: 0,
  distribuciones: {
    intima: 0,
    artiseid:0,
    menopausia: 0,
    furaderm: 0,
    gastro: 0,
    copromo: 0
  }
})

const categoriasDistribucion = [
  { key: 'intima', nombre: 'Íntima', icono: '💖', umbral: 12, colorBorde: 'border-[#d65799]', colorBarra: 'bg-[#d65799]' },
  { key: 'artiseid', nombre: 'Artiseid', icono: '🏃', umbral: 12, colorBorde: 'border-[#d65799]', colorBarra: 'bg-[#d65799]' },
  { key: 'furaderm', nombre: 'Furaderm', icono: '🧴', umbral: 12, colorBorde: 'border-[#00d084]', colorBarra: 'bg-[#00d084]' },
  { key: 'menopausia', nombre: 'Menopausia', icono: '🌺', umbral: 6, colorBorde: 'border-[#9b51e0]', colorBarra: 'bg-[#9b51e0]' },
  { key: 'gastro', nombre: 'Gastro', icono: '🍽️', umbral: 15, colorBorde: 'border-[#83a846]', colorBarra: 'bg-[#83a846]' },
  { key: 'copromo', nombre: 'Copromo', icono: '🤝', umbral: 1, colorBorde: 'border-[#ff6900]', colorBarra: 'bg-[#ff6900]' }
   
]

const colorBordes = {
  intima: 'border-[#d65799]',
  menopausia: 'border-[#9b51e0]',
  furaderm: 'border-[#00d084]',
  gastro: 'border-[#83a846]',
  copromo: 'border-[#fcb900]'
}
const colorBarra = {
  intima: 'bg-[#d65799]',
  menopausia: 'bg-[#9b51e0]',
  furaderm: 'bg-[#00d084]',
  gastro: 'bg-[#83a846]',
  copromo: 'bg-[#fcb900]'
}

const porcentajeFacturacion = computed(() => {
  if (!objetivos.value.facturacion) return 0
  return Math.min((progreso.value.facturacion / objetivos.value.facturacion) * 100, 100)
})

const porcentajeDistribucion = (cat) => {
   // Validación total para evitar errores
  if (!progreso.value || typeof progreso.value !== 'object') return 0
  if (!progreso.value.distribuciones || typeof progreso.value.distribuciones !== 'object') return 0
  const objetivo = objetivos.value?.[cat] || 0
  const conseguido = progreso.value.distribuciones[cat] || 0
  if (objetivo === 0) return 0
  const porcentaje = (conseguido / objetivo) * 100
  return Math.min(porcentaje, 100)
}

// ========== API Calls ==========
const API_BASE = 'http://localhost/MiProyecto/api'


//Funcion de cargar objetivos desde la API
const cargarObjetivos = async () => {
  try {
    const res = await fetch(`${API_BASE}/get_objetivos.php?anio=${anio.value}&trimestre=${trimestre.value}`)
    const data = await res.json()
    
    if (data.success) { 
      objetivos.value = data.objetivos
      objetivosTemp.value = { ...data.objetivos }
     }
    
  } catch (error) {
    console.error('Error cargando objetivos:', error)
  }
}


const cargarProgreso = async () => {
  try {
    const res = await fetch(`${API_BASE}/get_progreso_trimestre.php?anio=${anio.value}&trimestre=${trimestre.value}`)
    const data = await res.json()
    if (data.success) {
     progreso.value = {
        facturacion: data.facturacion ?? 0,
        distribuciones: data.distribuciones ?? {
          intima: 0,
          artiseid:0,
          menopausia: 0,
          furaderm: 0,
          gastro: 0,
          copromo: 0
        }
      }
    } else {
      // En caso de error, mantener estructura por defecto
      progreso.value = {
        facturacion: 0,
        distribuciones: {
          intima: 0,
          artiseid:0, 
          menopausia: 0,
          furaderm: 0,
          gastro: 0,
          copromo: 0
        }
      }
    }
  } catch (error) {
    console.error('Error cargando progreso:', error)
  }
}

//Funcion de guardar objetivos
const guardarObjetivos = async () => {
  try {
    const res = await fetch(`${API_BASE}/save_objetivos.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        anio: anio.value,
        trimestre: trimestre.value,
        objetivos: objetivosTemp.value
      })
    })
    const data = await res.json()
    if (data.success) {
      objetivos.value = { ...objetivosTemp.value }
      showModalObjetivos.value = false
      cargarProgreso() // para refrescar barras
    } else {
      alert('Error al guardar objetivos')
    }
  } catch (error) {
    console.error(error)
    alert('Error de red')
  }
}

const cargarDatos = async () => {
  await Promise.all([cargarObjetivos(), cargarProgreso()])
 
}

const abrirModalObjetivos = () => {
  objetivosTemp.value = { ...objetivos.value }
  showModalObjetivos.value = true
}

onMounted(() => {
  cargarDatos()
})

// Watcher para cuando cambie año o trimestre
watch([anio, trimestre], () => {
  cargarDatos()
})

const menuItems = [
  { name: 'Dashboard', icon: '📊', path: '/dashboard' },
  { name: 'Clientes', icon: '👥', path: '/clientes' },
  { name: 'Pedidos', icon: '📦', path: '/pedidos' },
  { name: 'Objetivos', icon: '🎯', path: '/objetivos' },
  { name: 'Visitas', icon: '📍', path: '/visitas' },
  { name: 'Gastos', icon: '💸', path: '/gastos' },
  { name: 'Productos', icon: '🛒', path: '/productos' }
]
</script>

<style scoped>
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>


<template>
  <div class="min-h-screen bg-[#f0f4f7] font-sans text-slate-700">
    <!-- Menú lateral  -->
    <aside 
      :class="[isCollapsed ? 'md:w-20' : 'md:w-64', isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']"
      class="fixed top-0 left-0 h-full bg-[#109bc5] text-white transition-all duration-300 ease-in-out z-50 flex flex-col shadow-2xl"
    >
      <button @click="isCollapsed = !isCollapsed" class="hidden md:flex absolute -right-3 top-10 bg-[#ff6900] w-6 h-6 rounded-full items-center justify-center border-2 border-white text-[10px] shadow-lg hover:scale-110 transition-transform">
        <span>{{ isCollapsed ? '→' : '←' }}</span>
      </button>
      <div class="p-6 flex items-center gap-3 overflow-hidden whitespace-nowrap">
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center font-black">
          <img src="../img/icono.png" alt="Logo" class="w-6 h-6">
        </div>
        <span v-if="!isCollapsed" class="font-black text-xl tracking-tighter uppercase">CRM</span>
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
      <button @click="isMobileMenuOpen = false" class="md:hidden p-4 text-center text-white/50 text-xs">Cerrar Menú</button>
    </aside>

    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden"></div>

    <main :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" class="transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8">
      <!-- Header fijo móvil -->
      <header class="flex justify-between items-center mb-6 md:mb-10 fixed md:relative top-0 left-0 w-full p-4 md:p-0 bg-[#f0f4f7] md:bg-transparent z-30 flex-wrap gap-2">
        <div class="flex items-center gap-3">
          <button @click="isMobileMenuOpen = true" class="md:hidden w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-[#109bc5]">☰</button>
          <h1 class="text-xl md:text-2xl font-black text-slate-800">Objetivos Trimestrales</h1>
        </div>
        <div class="flex gap-2 items-center flex-wrap">
          <!-- Selector año y trimestre -->
          <div class="flex gap-2 bg-white p-1 rounded-xl shadow-sm">
            <input type="number" v-model="anio" @change="cargarDatos" min="2020" max="2030" class="w-24 px-3 py-1 rounded-lg border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#109bc5]" />
            <select v-model="trimestre" @change="cargarDatos" class="px-3 py-1 rounded-lg border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
              <option :value="1">Trimestre 1 (Ene-Mar)</option>
              <option :value="2">Trimestre 2 (Abr-Jun)</option>
              <option :value="3">Trimestre 3 (Jul-Sep)</option>
              <option :value="4">Trimestre 4 (Oct-Dic)</option>
            </select>
          </div>
          <button @click="abrirModalObjetivos" class="bg-[#ff6900] text-white px-4 py-2 rounded-xl font-bold shadow-lg text-xs hover:bg-[#e55e00] transition-colors flex items-center gap-1">
            ⚙️ <span class="hidden sm:inline">Ajustar Objetivos</span>
          </button>
        </div>
      </header>

      <!-- Grid de tarjetas (responsive) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-14 md:mt-0">
        <!-- Facturación -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8 border-[#109bc5]">
          <div class="p-5">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-400 text-xs font-bold uppercase">💰 Facturación</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ (progreso.facturacion ?? 0).toFixed(2) }} €</h3>
                <p class="text-xs text-gray-500 mt-1">Objetivo: {{ objetivos.facturacion }} €</p>
              </div>
              <span class="text-3xl">📊</span>
            </div>
            <div class="mt-4">
              <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                <div :style="{ width: porcentajeFacturacion + '%' }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-[#109bc5]"></div>
              </div>
              <p class="text-right text-xs text-gray-500 mt-1">
                {{ porcentajeFacturacion.toFixed(0) }}% completado
              </p>
            </div>
          </div>
        </div>

        <!-- Tarjeta genérica para distribución -->
        <div v-for="cat in categoriasDistribucion" :key="cat.key" class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8" :class="colorBordes[cat.key]">
          <div class="p-5">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-400 text-xs font-bold uppercase">{{ cat.nombre }}</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ (progreso.distribuciones?.[cat.key] ?? 0) }} / {{ objetivos[cat.key] ?? 0 }}</h3>
               <p class="text-xs text-gray-500 mt-1">Mínimo {{ cat.umbral }} unidades por farmacia</p>
              </div>
              <span class="text-3xl">{{ cat.icono }}</span>
            </div>
            <div class="mt-4">
              <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                <div :style="{ width: porcentajeDistribucion(cat.key) + '%' }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center" :class="colorBarra[cat.key]"></div>
              </div>
              <p class="text-right text-xs text-gray-500 mt-1">
                {{ porcentajeDistribucion(cat.key).toFixed(0) }}% completado
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal ajustar objetivos -->
    <div v-if="showModalObjetivos" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showModalObjetivos = false">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-4">🎯 Ajustar Objetivos</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">💰 Facturación (€)</label>
            <input v-model.number="objetivosTemp.facturacion" type="number" step="100" min="0" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div v-for="cat in categoriasDistribucion" :key="cat.key">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ cat.nombre }} </label>
            <input v-model.number="objetivosTemp[cat.key]" type="number" step="1" min="0" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="guardarObjetivos" class="flex-1 bg-[#ff6900] text-white font-bold py-3 rounded-xl hover:bg-[#e55e00] transition">Guardar</button>
          <button @click="showModalObjetivos = false" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>
