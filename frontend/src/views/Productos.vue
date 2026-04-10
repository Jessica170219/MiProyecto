<script setup>
import { ref, onMounted, computed } from 'vue'

const isCollapsed = ref(false)
const searchQuery = ref('')
const showAddModal = ref(false)
const showEditModal = ref(false)


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
    iva: 0
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
  <div class="min-h-screen bg-[#f8fafc] font-sans text-slate-700 flex">
    
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
        <router-link v-for="item in menuItems" :key="item.name" :to="item.path"
          class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/10 transition-all"
          :class="{'bg-white/20 border-l-4 border-white': item.name === 'Productos'}">
          <span class="text-xl">{{ item.icon }}</span>
          <span v-if="!isCollapsed" class="font-bold text-sm">{{ item.name }}</span>
        </router-link>
      </nav>
    </aside>

    <main :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" class="flex-1 p-4 md:p-8 pt-20 transition-all">
      
      <header class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-black text-[#0f172a]">Catálogo de Productos</h1>
          <p class="text-gray-400 font-medium text-xs uppercase tracking-widest">Tarifas 2026</p>
        </div>
        <button @click="showAddModal = true" class="bg-[#0f172a] text-white px-6 py-3 rounded-2xl font-black shadow-lg hover:scale-105 transition-all text-xs">
          + Nuevo Producto
        </button>
      </header>

      <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 flex items-center gap-3 border border-slate-200">
        <span class="text-slate-300">🔍</span>
        <input v-model="searchQuery" type="text" placeholder="Buscar por nombre o CN..." class="flex-1 outline-none text-sm font-bold" />
      </div>

      <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-400 text-[9px] uppercase font-black tracking-tighter">
              <tr>
                <th class="p-4 border-b">Producto</th>
                <th class="p-4 border-b">Grupo</th>
                <th class="p-4 border-b">Gama</th>
                <th class="p-4 border-b">CN</th>
                <th class="p-4 border-b ">PVL</th>
                <th class="p-4 border-b ">PVP</th>
                <th class="p-4 border-b">IVA</th>
                <th class="p-4 border-b bg-orange-50/50">Importe</th>
                <th class="p-4 border-b text-center">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="p in productosFiltrados" :key="p.id" class="hover:bg-slate-50 transition-colors">
                <td class="p-4">
                  <p class="font-black text-slate-900 text-sm">{{ p.nombre }}</p>
                </td>
                <td class="p-4">
                  <span class="text-[10px] font-black px-2 py-1 bg-slate-100 rounded-md text-slate-600 uppercase mr-1">{{ p.grupo }}</span>
                </td>
                <td class="p-4">
                  <span class="text-[10px] font-black px-2 py-1 bg-slate-100 rounded-md text-slate-600 uppercase mr-1">{{ p.gama }}</span>
                </td>
                <td class="p-4 font-mono text-xs font-bold text-[#109bc5]">{{ p.cn }}</td>
                
                <td class="p-4 text-xs font-bold text-slate-600 ">{{ formatMoneda(p.pvl) }}</td>
                <td class="p-4 text-xs font-bold text-slate-600">{{ formatMoneda(p.pvp) }}</td>
                <td class="p-4 text-xs font-bold text-slate-400">{{ p.iva }}%</td>
                <td class="p-4 text-xs font-bold  text-orange-600  bg-orange-50/20">{{ formatMoneda(p.importe) }}</td>
                <td class="p-4 text-center">
                <button @click="abrirEditar(producto)" class="bg-[#ff6900]/10 text-[#109bc5] p-2 rounded-lg hover:bg-[#109bc5] hover:text-white transition-all">
                  <i class="fas fa-edit"></i>
                </button>
              </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
    <!--Modal de editar-->
    <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div @click="showEditModal = false" class="absolute inset-0 bg-slate-500/60 backdrop-blur-sm"></div>
      <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg z-10 overflow-hidden relative border border-white/20">
        <div class="bg-[#ff964d] p-4 text-white flex justify-between items-center">
          <h3 class="font-black text-l">Editar Producto</h3>
          <button @click="showEditModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <form @submit.prevent="guardarCambios" class="p-6 space-y-4">
          <div>
            <label class="text-[10px] font-black  uppercase">Nombre</label>
            <input v-model="productoSeleccionado.nombre" type="text" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#ff964d]">
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="text-[10px] font-black  uppercase">PVL (€)</label>
              <input v-model.number="productoSeleccionado.pvl" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#ff964d]">
            </div>
            <div>
              <label class="text-[10px] font-black  uppercase">PVP (€)</label>
              <input v-model.number="productoSeleccionado.pvp" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#ff964d]">
            </div>
             <div>
              <label class="text-[10px] font-black  uppercase">IMPORTE(€)</label>
              <input v-model.number="productoSeleccionado.importe" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#ff964d]">
            </div>
          </div>
          <button type="submit" class="w-full bg-[#ff964d] text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[1.02] transition-all">Guardar Cambios</button>
        </form>
      </div>
    </div>
    <!--Modal de añadir producto-->
    <div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div @click="showAddModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg z-10 overflow-hidden relative border border-white/20">
        <div class="bg-[#0f172a] p-6 text-white flex justify-between items-center">
          <h3 class="font-black text-xl">Nuevo Producto</h3>
          <button @click="showAddModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <form @submit.prevent="agregarProducto" class="p-6 space-y-4">
          <div class="grid grid-cols-3 gap-3">
             <div class="col-span-2">
                <label class="text-[10px] font-black  uppercase">Nombre</label>
                <input v-model="nuevoProducto.nombre" type="text" required class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold">
             </div>
             <div>
                <label class="text-[10px] font-black  uppercase">CN</label>
                <input v-model="nuevoProducto.cn" type="text" required class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold">
             </div>
             <div>
              <label class="text-[10px] font-black  uppercase">PVL (€)</label>
              <input v-model.number="nuevoProducto.pvl" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold">
            </div>
            <div>   
              <label class="text-[10px] font-black  uppercase">PVP (€)</label>
              <input v-model.number="nuevoProducto.pvp" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold ">
            </div>
             <div>
                <label class="text-[10px] font-black uppercase">Importe (€)</label>
                <input v-model.number="nuevoProducto.importe" type="number" step="0.01" class="w-full bg-slate-50 border-none p-3 rounded-xl text-sm font-bold">
             </div>
          </div>
          <button type="submit" class="w-full bg-[#0f172a] text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[1.02] transition-all">Crear Producto</button>
        </form>
      </div>
    </div>
  </div>
</template>