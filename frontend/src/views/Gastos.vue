<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'

// ==================== ESTADO UI ====================
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)
const showModalGasto = ref(false)
const showModalTopes = ref(false)

// ==================== DATOS PRINCIPALES ====================
const gastos = ref([])               // Todos los gastos (sin filtrar)
const mesSeleccionado = ref('')      // Formato YYYY-MM
const topeActual = reactive({        // Topes del mes seleccionado
  lavados: 0,
  gasolina: 0
})

// Copia temporal para el modal de topes
const topesTemp = reactive({
  mes: '',
  lavados: 0,
  gasolina: 0
})

// Nuevo gasto (formulario)
const nuevoGasto = reactive({
  categoria: 'lavados',
  concepto: '',
  importe: 0,
  fecha: new Date().toISOString().split('T')[0]
})

// ==================== COMPUTED: GASTOS FILTRADOS POR MES ====================
const gastosFiltrados = computed(() => {
  if (!mesSeleccionado.value) return []
  return gastos.value.filter(g => g.fecha.startsWith(mesSeleccionado.value))
})

// Gastos por categoría (solo del mes seleccionado)
// =======LAVADOS ========
const gastadoLavados = computed(() => {
  return gastosFiltrados.value
    .filter(g => g.categoria === 'lavados')
    .reduce((sum, g) => sum + Number(g.importe), 0)
})

const restanteLavados = computed(() => topeActual.lavados - gastadoLavados.value)

const porcentajeLavados = computed(() => {
  if (topeActual.lavados === 0) return 0
  return Math.min((gastadoLavados.value / topeActual.lavados) * 100, 100)
})

// =======GASOLINA ========
const gastadoGasolina = computed(() => {
  return gastosFiltrados.value
    .filter(g => g.categoria === 'gasolina')
    .reduce((sum, g) => sum + Number(g.importe), 0)
})

const restanteGasolina = computed(() => topeActual.gasolina - gastadoGasolina.value)

const porcentajeGasolina = computed(() => {
  if (topeActual.gasolina === 0) return 0
  return Math.min((gastadoGasolina.value / topeActual.gasolina) * 100, 100)
})

// =======COMIDA ========
const gastosComida = computed(() => {
  return gastosFiltrados.value.filter(g => g.categoria === 'comida')
  
})

const gastadoComida = computed(() => {
  return gastosComida.value.reduce((sum, g) => sum + Number(g.importe), 0)
})

const diasComida = computed(() => gastosComida.value.length)
const DIETA_DIARIA = 26.67
const subsidioComida = computed(() => diasComida.value * DIETA_DIARIA)
const diferenciaComida = computed(() => subsidioComida.value - gastadoComida.value)
const ganaciaComida = computed(() => diferenciaComida.value >= 0)

// ========CENA ========
const gastosCena = computed(() => {
  return gastosFiltrados.value.filter(g => g.categoria === 'cena')
  
})

const gastadoCena = computed(() => {
  return gastosCena.value.reduce((sum, g) => sum + Number(g.importe), 0)
})

const diasCena = computed(() => gastosCena.value.length)
const subsidioCena = computed(() => diasCena.value * DIETA_DIARIA)
const diferenciaCena = computed(() => subsidioCena.value - gastadoCena.value)
const ganaciaCena = computed(() => diferenciaCena.value >= 0)

const mostrarCena = computed(() => gastosCena.value.length > 0)





// ==================== API CALLS ====================
const API_BASE = 'http://localhost/MiProyecto/api'

// Obtener todos los gastos (sin filtro de mes, los filtramos luego en el front)
const fetchGastos = async () => {
  try {
    const response = await fetch(`${API_BASE}/get_gastos.php`)
    const data = await response.json()
    if (data.success) {
      gastos.value = data.gastos
    } else {
      console.error('Error al cargar gastos:', data.error)
    }
  } catch (error) {
    console.error('Error de red:', error)
  }
}

// Obtener topes para un mes específico
const fetchTopes = async (mes) => {
  if (!mes) return
  try {
    const response = await fetch(`${API_BASE}/get_topes.php?mes=${mes}`)
    const data = await response.json()
    if (data.success) {
      topeActual.lavados = data.topes.lavados || 0
      topeActual.gasolina = data.topes.gasolina || 0
    } else {
      // Si no hay topes configurados, poner ceros
      topeActual.lavados = 0
      topeActual.gasolina = 0
    }
  } catch (error) {
    console.error('Error cargando topes:', error)
  }
}

// Guardar topes para un mes
const saveTopes = async (mes, lavados, gasolina) => {
  try {
    const response = await fetch(`${API_BASE}/save_topes.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mes, lavados, gasolina })
    })
    const data = await response.json()
    if (!data.success) {
      throw new Error(data.error || 'Error al guardar topes')
    }
    return true
  } catch (error) {
    console.error('Error guardando topes:', error)
    alert('No se pudieron guardar los topes')
    return false
  }
}

// Añadir nuevo gasto
const addGasto = async (gasto) => {
  try {
    const response = await fetch(`${API_BASE}/add_gasto.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(gasto)
    })
    const data = await response.json()
   
    if (!data.success) {
      throw new Error(data.error || 'Error al guardar gasto')
    }
    return true
    
  } catch (error) {
    console.error('Error guardando gasto:', error)
    alert('No se pudo guardar el gasto')
    return null
  }
}

// Eliminar gasto
const deleteGasto = async (id) => {
  try {
    const response = await fetch(`${API_BASE}/delete_gasto.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    const data = await response.json()
    if (!data.success) {
      throw new Error(data.error || 'Error al eliminar gasto')
    }
    return true
  } catch (error) {
    console.error('Error eliminando gasto:', error)
    alert('No se pudo eliminar el gasto')
    return false
  }
}

// ==================== ACCIONES DEL USUARIO ====================
const cambiarMes = async () => {
  await fetchTopes(mesSeleccionado.value)
}

const abrirModalGasto = () => {
  nuevoGasto.categoria = 'lavados'
  nuevoGasto.concepto = ''
  nuevoGasto.importe = 0
  nuevoGasto.fecha = new Date().toISOString().split('T')[0]
  showModalGasto.value = true
}

const guardarGasto = async () => {
  
  if (!nuevoGasto.importe || nuevoGasto.importe <= 0) {
    alert('El importe debe ser mayor que 0')
    return
  }
  if (!nuevoGasto.fecha) {
    alert('Selecciona una fecha')
    return
  }

  const gastoParaEnviar = {
    categoria: nuevoGasto.categoria,
    concepto: nuevoGasto.concepto,
    importe: parseFloat(nuevoGasto.importe),
    fecha: nuevoGasto.fecha
  }

  const nuevoId = await addGasto(gastoParaEnviar)
 
  if (nuevoId) {
    // Recargar todos los gastos desde el servidor
    await fetchGastos()
    // Si el mes del gasto coincide con el seleccionado, se actualizarán los computados automáticamente
    showModalGasto.value = false
  }
}

const eliminarGasto = async (id) => {
  if (confirm('¿Eliminar este gasto?')) {
    const success = await deleteGasto(id)
    if (success) {
      await fetchGastos()
    }
  }
}

const abrirModalTopes = () => {
  // Cargar los topes actuales del mes seleccionado en el modal
  topesTemp.mes = mesSeleccionado.value
  topesTemp.lavados = topeActual.lavados
  topesTemp.gasolina = topeActual.gasolina
  showModalTopes.value = true
}

const guardarTopes = async () => {
  if (!topesTemp.mes) {
    alert('Selecciona un mes')
    return
  }
  if (topesTemp.lavados < 0 || topesTemp.gasolina < 0) {
    alert('Los topes no pueden ser negativos')
    return
  }

  const success = await saveTopes(topesTemp.mes, topesTemp.lavados, topesTemp.gasolina)
  if (success) {
    // Si el mes guardado es el mismo que el seleccionado, actualizar topeActual
    if (topesTemp.mes === mesSeleccionado.value) {
      topeActual.lavados = topesTemp.lavados
      topeActual.gasolina = topesTemp.gasolina
    }
    showModalTopes.value = false
  }
}

// ==================== INICIALIZACIÓN ====================
onMounted(async () => {
  // Establecer el mes actual como valor por defecto (YYYY-MM)
  const hoy = new Date()
  mesSeleccionado.value = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}`
  
  await fetchGastos()
  await fetchTopes(mesSeleccionado.value)
})

// ==================== MENÚ ITEMS ====================
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





<template>
  <div class="min-h-screen bg-[#f0f4f7] font-sans text-slate-700">
    <!-- ==================== ASIDE (MENÚ LATERAL) ==================== -->
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
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center font-black">
          <img src="../img/icono.png" alt="Logo" class="w-6 h-6">
        </div>
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

    <!-- Overlay móvil -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden"></div>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main 
      :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" 
      class="transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8"
    >
      <!-- Header fijo en móvil -->
      <header class="flex justify-between items-center mb-6 md:mb-10 fixed md:relative top-0 left-0 w-full p-4 md:p-0 bg-[#f0f4f7] md:bg-transparent z-30 flex-wrap gap-2">
        <div class="flex items-center gap-3">
          <button @click="isMobileMenuOpen = true" class="md:hidden w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-[#109bc5]">
            ☰
          </button>
          <h1 class="text-xl md:text-2xl font-black text-slate-800">Gestión de Gastos</h1>
        </div>
        <div class="flex gap-2 items-center flex-wrap">
          <!-- Selector de mes -->
          <input 
            type="month" 
            v-model="mesSeleccionado" 
            @change="cambiarMes"
            class="px-3 py-2 rounded-xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#109bc5]"
          />
          <button 
            @click="abrirModalGasto"
            class="bg-[#ff6900] text-white px-4 py-2 rounded-xl font-bold shadow-lg text-xs hover:bg-[#e55e00] transition-colors flex items-center gap-1"
          >
            + <span class="hidden sm:inline">Añadir Gasto</span>
          </button>
          <button 
            @click="abrirModalTopes"
            class="bg-[#109bc5] text-white px-4 py-2 rounded-xl font-bold shadow-lg text-xs hover:bg-[#0e7fa3] transition-colors flex items-center gap-1"
          >
            ⚙️ <span class="hidden sm:inline">Ajustar Topes</span>
          </button>
        </div>
      </header>

       <!-- ========== TARJETAS ========== -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-14 md:mt-0">
        <!-- Lavados (con tope) -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8 border-[#109bc5]">
          <div class="p-5">
            <div class="flex justify-between">
              <div><p class="text-gray-400 text-xs uppercase font-bold">Lavados</p><h3 class="text-2xl font-black">{{ gastadoLavados.toFixed(2) }}€</h3><p class="text-xs text-gray-500">de tope {{ topeActual.lavados }}€</p></div>
              <span class="text-3xl">🧼</span>
            </div>
            <div class="mt-3"><div class="h-2 bg-gray-200 rounded-full"><div class="h-2 bg-[#109bc5] rounded-full" :style="{ width: porcentajeLavados + '%' }"></div></div><p class="text-right text-xs mt-1">Restante: <span :class="restanteLavados<0?'text-red-500':''">{{ restanteLavados.toFixed(2) }}€</span></p></div>
          </div>
        </div>

        <!-- Gasolina (con tope) -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8 border-[#ff6900]">
          <div class="p-5">
            <div class="flex justify-between">
              <div><p class="text-gray-400 text-xs uppercase font-bold">Gasolina</p><h3 class="text-2xl font-black">{{ gastadoGasolina.toFixed(2) }}€</h3><p class="text-xs text-gray-500">de tope {{ topeActual.gasolina }}€</p></div>
              <span class="text-3xl">⛽</span>
            </div>
            <div class="mt-3"><div class="h-2 bg-gray-200 rounded-full"><div class="h-2 bg-[#ff6900] rounded-full" :style="{ width: porcentajeGasolina + '%' }"></div></div><p class="text-right text-xs mt-1">Restante: <span :class="restanteGasolina<0?'text-red-500':''">{{ restanteGasolina.toFixed(2) }}€</span></p></div>
          </div>
        </div>

        <!-- Comida (dieta) -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8 border-[#fcb900]">
          <div class="p-5">
            <div class="flex justify-between">
              <div>
                <p class="text-gray-400 text-xs uppercase font-bold">Comida</p>
                <h3 class="text-2xl font-black">{{ gastadoComida.toFixed(2) }}€</h3>
                <p class="text-xs text-gray-500">{{ diasComida }} días · Dieta {{ subsidioComida.toFixed(2) }}€</p>
              </div>
              <span class="text-3xl">🍔</span>
            </div>
            <div class="mt-3">
              <div class="flex justify-between text-sm font-bold">
                <span>Diferencia:</span>
                <span :class="gananciaComida ? 'text-red-600' : 'text-green-600'">
                  {{ diferenciaComida >= 0 ? '+' : '' }}{{ diferenciaComida.toFixed(2) }}€
                  ({{ gananciaComida ? 'pérdida' : 'ganancia' }})
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Cena (opcional, solo si hay gastos) -->
        <div v-if="mostrarCena" class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-8 border-[#d65799]">
          <div class="p-5">
            <div class="flex justify-between">
              <div>
                <p class="text-gray-400 text-xs uppercase font-bold">Cena</p>
                <h3 class="text-2xl font-black">{{ gastadoCena.toFixed(2) }}€</h3>
                <p class="text-xs text-gray-500">{{ diasCena }} días · Dieta {{ subsidioCena.toFixed(2) }}€</p>
              </div>
              <span class="text-3xl">🍽️</span>
            </div>
            <div class="mt-3">
              <div class="flex justify-between text-sm font-bold">
                <span>Diferencia:</span>
                <span :class="gananciaCena ? 'text-red-600' : 'text-green-600'">
                  {{ diferenciaCena >= 0 ? '+' : '' }}{{ diferenciaCena.toFixed(2) }}€
                  ({{ gananciaCena ? 'pérdida' : 'ganancia' }})
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABLA DE GASTOS DEL MES SELECCIONADO -->
       <div class="bg-white rounded-3xl p-5 md:p-8 shadow-sm border border-gray-100 overflow-hidden">
        <h3 class="font-black text-lg mb-6">Gastos de {{ mesSeleccionado }}</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-left min-w-[350px]">
            <thead><tr class="text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-50">
                <th class="pb-4 px-1 md:px-2">Fecha</th>
                <th class="pb-4 px-1 md:px-2">Categoría</th>
                <th class="pb-4 px-1 md:px-2">Concepto</th>
                <th class="pb-4 px-1 md:px-2">Importe</th>
                <th class="pb-4 px-1 md:px-2 text-center">Acción</th></tr>
            </thead>
            <tbody>
              <tr v-for="gasto in gastosFiltrados" :key="gasto.id" class="hover:bg-gray-50">
                <td class="py-4 text-xs font-mono text-gray-500">{{ new Date(gasto.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' }) }}</td>
                <td><span class="text-xs font-bold px-2 py-1 rounded-full" :class="{'bg-blue-100 text-blue-800': gasto.categoria==='lavados','bg-yellow-100 text-yellow-800': gasto.categoria==='comida','bg-orange-100 text-orange-800': gasto.categoria==='gasolina','bg-pink-100 text-pink-800': gasto.categoria==='cena'}">{{ gasto.categoria }}</span></td>
                <td class="text-sm font-bold">{{ gasto.concepto || '-' }}</td>
                <td class="text-center font-black">{{ Number(gasto.importe).toFixed(2) }}€</td>
                <td class="text-center"><button @click="eliminarGasto(gasto.id)" class="text-red-400">🗑️</button></td>
              </tr>
              <tr v-if="gastosFiltrados.length===0"><td colspan="5" class="py-8 text-center text-gray-400">No hay gastos este mes</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- ==================== MODAL AÑADIR GASTO ==================== -->
    <div v-if="showModalGasto" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showModalGasto = false">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-4">➕ Nuevo Gasto</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Categoría</label>
            <select v-model="nuevoGasto.categoria" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
              <option value="lavados">Lavados</option>
              <option value="comida">Comida</option>
              <option value="cena">Cena</option>
              <option value="gasolina">Gasolina</option>

            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Concepto</label>
            <input v-model="nuevoGasto.concepto" type="text" placeholder="Ej: Lavado de coche" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Importe (€)</label>
            <input v-model.number="nuevoGasto.importe" type="number" step="0.01" min="0" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha</label>
            <input v-model="nuevoGasto.fecha" type="date" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="guardarGasto" class="flex-1 bg-[#ff6900] text-white font-bold py-3 rounded-xl hover:bg-[#e55e00] transition">Guardar Gasto</button>
          <button @click="showModalGasto = false" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL AJUSTAR TOPES POR MES ==================== -->
    <div v-if="showModalTopes" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showModalTopes = false">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-4">⚙️ Ajustar Topes Mensuales</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mes</label>
            <input type="month" v-model="topesTemp.mes" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">🧼 Lavados (€)</label>
            <input v-model.number="topesTemp.lavados" type="number" step="10" min="0" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">⛽ Gasolina (€)</label>
            <input v-model.number="topesTemp.gasolina" type="number" step="10" min="0" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#ff6900]">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="guardarTopes" class="flex-1 bg-[#109bc5] text-white font-bold py-3 rounded-xl hover:bg-[#0e7fa3] transition">Guardar Topes</button>
          <button @click="showModalTopes = false" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

