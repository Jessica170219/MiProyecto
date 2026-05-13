<script setup>
import { ref, onMounted, computed } from 'vue'

const isCollapsed = ref(false)
const searchQuery = ref('')
const showAddModal = ref(false)
const showEditModal = ref(false)
const isMobileMenuOpen = ref(false)


const productos = ref([])
//Recoger datos del backend
const fetchProductos = async () => {
    try {
        const response = await fetch('http://localhost/MiProyecto/api/get_productos.php')
        const data = await response.json()
        productos.value = data.productos
    } catch (error) {
        console.error('Error al cargar productos:', error)
    }
}

onMounted(() => {
    fetchProductos()
})

const productoSeleccionado = ref({
    id: null,
    nombre: '',
    grupo: '',
    gama: '',
    cn: '',
    pvl: 0,
    pvp: 0,
    iva: 0, 
    categoria: '',
})

//Logica de busqueda y filtrado 
const productosFiltrados = computed(() => {
    if (!searchQuery.value) return productos.value
    return productos.value.filter(p => 
        p.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        p.cn.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        p.grupo.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        p.gama.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
const formatMoneda = (valor) => {
  if (valor === null || valor === undefined || isNaN(valor)) return '0,00 €';
  
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2
  }).format(valor);
}


//Funcion para abrir el modal de edición con los datos del producto seleccionado
const abrirEditar = (p) => {
    productoSeleccionado.value = { ...p }
    showEditModal.value = true
}

//Funcion de guardar cambios de edicion 
const guardarCambios = async () => {
    try {
        const response = await fetch('http://localhost/MiProyecto/api/update_producto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(productoSeleccionado.value)
        })
        const data = await response.json()
        if (data.success) {
            fetchProductos() // Refrescar lista después de guardar
            showEditModal.value = false
        } else {
            console.error('Error al guardar cambios:', data.message)
        }
    } catch (error) {
        console.error('Error al guardar cambios:', error)
    }
}
//Objeto para almacenar los datos del producto nuevo
const nuevoProducto = ref({
    nombre: '',
    grupo: '',
    gama: '',
    cn: '',
    pvl: 0,
    pvp: 0,
    iva: 0
})
//Funcion de añadir producto nuevo 
const agregarProducto = async () => {
    try {
        const response = await fetch('http://localhost/MiProyecto/api/add_producto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevoProducto.value)
        })
        const data = await response.json()
        if (data.success) {
            fetchProductos() // Refrescar lista después de añadir
            showAddModal.value = false
            // Limpiar formulario
            nuevoProducto.value = { nombre: '', grupo: '', gama: '', cn: '', pvl: 0, pvp: 0, iva: 0 }
        } else {
            console.error('Error al agregar producto:', data.message)
        }
    } catch (error) {
        console.error('Error al agregar producto:', error)
    }
}


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
          <div>
            <h1 class="text-xl md:text-2xl font-black text-slate-800">Catálogo de Productos</h1>
            <p class="text-gray-400 font-medium text-[10px] uppercase tracking-widest hidden md:block">Tarifas 2026</p>
          </div>
        </div>
        <button 
          @click="showAddModal = true"
          class="bg-[#ff6900] text-white px-4 py-2 rounded-xl font-bold shadow-lg text-xs hover:bg-[#e55e00] transition-colors flex items-center gap-1"
        >
          + <span class="hidden sm:inline">Nuevo Producto</span>
        </button>
      </header>

      <!-- Barra de búsqueda -->
      <div class="bg-white rounded-2xl shadow-sm p-3 mb-6 flex items-center gap-3 border border-gray-200 mt-14 md:mt-0">
        <span class="text-slate-300 text-lg">🔍</span>
        <input v-model="searchQuery" type="text" placeholder="Buscar por nombre, CN, grupo o gama..." class="flex-1 outline-none text-sm font-medium text-slate-700 bg-transparent" />
      </div>

      <!-- TABLA DE PRODUCTOS -->
      <div class="bg-white rounded-3xl p-5 md:p-8 shadow-sm border border-gray-100 overflow-hidden">
        <h3 class="font-black text-lg text-slate-800 mb-6 flex items-center gap-2">
          <span class="w-1.5 h-6 bg-[#d65799] rounded-full"></span>
          Listado de Productos
        </h3>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-50">
                <th class="pb-4">Producto</th>
                <th class="pb-4">Grupo</th>
                <th class="pb-4">Gama</th>
                <th class="pb-4">CN</th>
                <th class="pb-4">IVA</th>
                <th class="pb-4">PVL</th>
                <th class="pb-4">PVP</th>
                <th class="pb-4 text-center">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="p in productosFiltrados" :key="p.id" class="hover:bg-gray-50/50">
                <td class="py-4 pr-4">
                  <p class="font-black text-sm text-slate-800">{{ p.nombre }}</p>
                </td>
                <td class="py-4 pr-4">
                  <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-600 uppercase">{{ p.grupo || '-' }}</span>
                </td>
                <td class="py-4 pr-4">
                  <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-600 uppercase">{{ p.gama || '-' }}</span>
                </td>
                <td class="py-4 pr-4 font-mono text-xs font-bold text-[#109bc5]">{{ p.cn }}</td>
                <td class="py-4 pr-4 text-xs font-bold text-slate-500">{{ p.iva }}%</td>
                <td class="py-4 pr-4 text-right text-xs font-bold text-slate-600">{{ formatMoneda(p.pvl) }}</td>
                <td class="py-4 pr-4 text-right text-xs font-bold text-orange-600">{{ formatMoneda(p.pvp) }}</td>
                <td class="py-4 text-center">
                  <button @click="abrirEditar(p)" class="text-[#109bc5] hover:text-[#ff6900] transition-colors text-sm font-bold">
                    ✏️
                  </button>
                </td>
              </tr>
              <tr v-if="productosFiltrados.length === 0">
                <td colspan="8" class="py-8 text-center text-gray-400">No se encontraron productos. ¡Añade el primero!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- ==================== MODAL AÑADIR PRODUCTO ==================== -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showAddModal = false">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-4">➕ Nuevo Producto</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre</label>
            <input v-model="nuevoProducto.nombre" type="text" required class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Grupo</label>
              <input v-model="nuevoProducto.grupo" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gama</label>
              <input v-model="nuevoProducto.gama" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CN (Código Nacional)</label>
            <input v-model="nuevoProducto.cn" type="text" required class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PVL (€)</label>
              <input v-model.number="nuevoProducto.pvl" type="number" step="0.01" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PVP (€)</label>
              <input v-model.number="nuevoProducto.pvp" type="number" step="0.01" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">IVA (%)</label>
              <select v-model="nuevoProducto.iva" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
                <option value="4">4%</option>
                <option value="10">10%</option>
                <option value="21">21%</option>
              </select>
            </div>
          </div>
          <!-- Campo extra opcional si tu backend lo soporta -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Categoría</label>
              <input v-model="nuevoProducto.categoria" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Presentación</label>
              <input v-model="nuevoProducto.presentacion" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="agregarProducto" class="flex-1 bg-[#ff6900] text-white font-bold py-3 rounded-xl hover:bg-[#e55e00] transition">Crear Producto</button>
          <button @click="showAddModal = false" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL EDITAR PRODUCTO ==================== -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showEditModal = false">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-800 mb-4">✏️ Editar Producto</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre</label>
            <input v-model="productoSeleccionado.nombre" type="text" required class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Grupo</label>
              <input v-model="productoSeleccionado.grupo" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gama</label>
              <input v-model="productoSeleccionado.gama" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CN (Código Nacional)</label>
            <input v-model="productoSeleccionado.cn" type="text" required class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PVL (€)</label>
              <input v-model.number="productoSeleccionado.pvl" type="number" step="0.01" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PVP (€)</label>
              <input v-model.number="productoSeleccionado.pvp" type="number" step="0.01" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">IVA (%)</label>
              <select v-model="productoSeleccionado.iva" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
                <option value="4">4%</option>
                <option value="10">10%</option>
                <option value="21">21%</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Categoría</label>
              <input v-model="productoSeleccionado.categoria" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Presentación</label>
              <input v-model="productoSeleccionado.presentacion" type="text" class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#109bc5]">
            </div>
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="guardarCambios" class="flex-1 bg-[#ff6900] text-white font-bold py-3 rounded-xl hover:bg-[#e55e00] transition">Guardar Cambios</button>
          <button @click="showEditModal = false" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>