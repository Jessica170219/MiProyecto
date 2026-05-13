<script setup>
import { ref, onMounted, computed } from 'vue'

// Estados de Interfaz
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)
const searchQuery = ref('')

//Datos de clientes 
const clientes = ref([])

const fetchClientes = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/get_clients.php')
    const data = await response.json()
    clientes.value = data.clients
  } catch (error) {
    console.error('Error al cargar clientes:', error)
  }
}

onMounted(() => {
  fetchClientes()
})

// Lógica de filtrado (Sustituye a la búsqueda de PHP en tiempo real)
const clientesFiltrados = computed(() => {
 if(!Array.isArray(clientes.value)) return []
    const query = searchQuery.value.toLowerCase().trim()
    if (!query) return clientes.value 
    return clientes.value.filter(cliente => {
      return (
        cliente.farmacia.toLowerCase().includes(query) ||
        cliente.provincia.toLowerCase().includes(query) ||
        cliente.municipio.toLowerCase().includes(query) ||
        cliente.codigo_postal.toLowerCase().includes(query)
      )
  })
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

//Control del modal de añadir cliente
const showAddModal = ref(false)
const nuevoCliente = ref({
  farmacia: '',
  direccion: '',
  municipio: '',
  provincia: '',
  codigo_postal: '',
  telefono: '',
  email: ''
})

//Funcion para abrir el modal de añadir cliente
const abrirAñadir= () => {
  nuevoCliente.value = {
    farmacia: '',
    direccion: '',
    municipio: '',
    provincia: '',
    codigo_postal: '',
    telefono: '',
    email: ''
  }
  showAddModal.value = true
}

//Funcion para guardar nuevo cliente, conexion con add_cliente.php
const guardarNuevoCliente = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/add_cliente.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(nuevoCliente.value)
    });

    const result = await response.json();

    if (result.success) {
      showAddModal.value = false;
      alert('¡Cliente añadido correctamente!');
      await fetchClientes(); 
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error al guardar:', error);
    alert('Error de conexión con el servidor');
  }
};


//Control del modal de editar cliente 
const showEditModal = ref(false)
const clienteEditando = ref({})

//Funcion de abrir modal con datos del cliente a editar
const abrirEditar = (cliente) => {
    clienteEditando.value = { ...cliente }
    showEditModal.value = true
}

//Funcion para guardar cambios , conexion con editar_cliente.php
const guardarCambios = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/editar_cliente.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(clienteEditando.value)
    });

    const result = await response.json();

    if (result.success) {
      // 1. Cerramos el modal
      showEditModal.value = false;
      
      // 2. Notificación visual (puedes usar un alert o un toast)
      alert('¡Actualizado correctamente!');
      
      // 3. Refrescamos la lista localmente para no tener que recargar la página
      await fetchClientes(); 
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error al guardar:', error);
    alert('Error de conexión con el servidor');
  }
};


//Modal de detalles 
const showDetailsModal = ref(false)
const detallesCliente = ref({})

const verDetalles = (cliente) => {
  detallesCliente.value = { ...cliente }
  showDetailsModal.value = true
}

</script>

<template>
  <div class="min-h-screen bg-[#f0f4f7] font-sans text-slate-700 flex">
    
    <aside 
      :class="[isCollapsed ? 'md:w-20' : 'md:w-64', isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']"
      class="fixed top-0 left-0 h-full bg-[#109bc5] text-white transition-all duration-300 z-50 flex flex-col shadow-2xl"
    >
      <button @click="isCollapsed = !isCollapsed" class="hidden md:flex absolute -right-3 top-10 bg-[#ff6900] w-6 h-6 rounded-full items-center justify-center border-2 border-white text-[10px] shadow-lg">
        <span :class="isCollapsed ? 'text-xs' : 'text-xs'">
          {{ isCollapsed ? '→' : '←' }}
        </span>
      </button>

      <div class="p-6 flex items-center gap-3 overflow-hidden">
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center font-black"><img src='../img/icono.png'></div>
        <span v-if="!isCollapsed" class="font-black text-xl tracking-tighter uppercase">CRM</span>
      </div>

      <nav class="flex-1 px-3 space-y-1">
        <router-link v-for="item in menuItems" :key="item.name" :to="item.path"
          class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/10 transition-all group relative"
          :class="{'justify-center': isCollapsed, 'bg-white/20': item.name === 'Clientes'}"
        >
          <span class="text-xl">{{ item.icon }}</span>
          <span v-if="!isCollapsed" class="font-bold text-sm">{{ item.name }}</span>
        </router-link>
      </nav>
    </aside>

    <main :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" class="flex-1 transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8">
      
      <header class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-black text-[#109bc5]">Clientes</h1>
          <p class="text-gray-400 font-medium text-xs md:text-sm">Gestión de farmacias</p>
        </div>
        <button @click="abrirAñadir" class="bg-[#00d084] text-white px-4 py-2 md:px-6 md:py-3 rounded-xl md:rounded-2xl font-bold shadow-lg shadow-green-500/20 hover:scale-105 transition-all text-xs">
          <i class="fas fa-plus mr-2"></i> Añadir Farmacia
        </button>
      </header>

      <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 flex items-center gap-3 border border-gray-100">
        <span class="text-gray-300">🔍</span>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Buscar por farmacia, provincia o CP..." 
          class="flex-1 outline-none text-sm font-medium"
        />
      </div>

      <div class="hidden md:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-50/50 text-gray-400 text-[10px] uppercase tracking-[0.2em]">
            <tr>
              <th class="p-6">ID</th>
              <th class="p-6">Farmacia</th>
              <th class="p-6">Dirección</th>
              <th class="p-6">Municipio</th>
              <th class="p-6">Provincia</th>
              <th class="p-6">Contacto</th>
              <th class="p-6 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="cliente in clientesFiltrados" :key="cliente.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="p-6 text-xs font-bold text-gray-400">#{{ cliente.id }}</td> 
              <td class="p-6"> 
                <p class="font-black text-slate-800 text-sm">{{ cliente.farmacia }}</p>
              </td>
              <td class="p-6 text-xs">
                <p class="font-bold text-slate-700">{{ cliente.direccion }}</p>
              </td>
              <td class="p-6 text-xs">
                <p class="font-bold text-slate-700">{{ cliente.municipio }} </p>
                <p class="text-gray-400">{{ cliente.codigo_postal }}</p>
              </td>
               <td class="p-6 text-xs">
                <p class="font-bold text-slate-700">{{ cliente.provincia }} </p>
               
              </td>
              <td class="p-6 text-xs">
                <p class="font-bold text-[#109bc5]">{{ cliente.telefono }}</p>
                <p class="text-gray-400">{{ cliente.email }}</p>
              </td>
              <td class="p-6 text-right">
                <button @click="abrirEditar(cliente)" class="bg-[#109bc5]/10 text-[#109bc5] p-2 rounded-lg hover:bg-[#109bc5] hover:text-white transition-all">
                  <i class="fas fa-edit"></i>
                </button>
                
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="md:hidden grid grid-cols-2 gap-3">
        <div v-for="cliente in clientesFiltrados" :key="cliente.id" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div>
            <div class="flex justify-between items-start mb-2">
              <span class="text-[9px] font-bold text-gray-300">#{{ cliente.id }}</span>
              <span class="text-[#109bc5]">🏥</span>
            </div>
            <h3 class="font-black text-slate-800 text-sm leading-tight mb-1">{{ cliente.farmacia }}</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ cliente.municipio }}</p>
          </div>
          
          <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center">
             <button @click="abrirEditar(cliente)" class="text-[10px] font-black text-[#109bc5] uppercase">Editar</button>
             <button @click="verDetalles(cliente)" class="text-[10px] font-black text-[#ff6900] uppercase">Detalles</button>
          </div>
        </div>
      </div>
    </main>

    <button @click="isMobileMenuOpen = true" class="md:hidden fixed top-4 left-4 w-10 h-10 bg-white rounded-xl shadow-lg flex items-center justify-center text-[#109bc5] z-40">
      ☰
    </button>


    <!-----------------------------MODAL DE EDITAR---------------------------------- -->
    <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div @click="showEditModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
      <div class="bg-[#109bc5] p-6 text-white flex justify-between items-center">
        <h3 class="font-black text-xl">Editar Farmacia</h3>
        <button @click="showEditModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
      </div>

      <form @submit.prevent="guardarCambios" class="p-8 space-y-4">
        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Nombre de la Farmacia</label>
          <input v-model="clienteEditando.farmacia" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Provincia</label>
            <input v-model="clienteEditando.provincia" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
          </div>
          <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">C. Postal</label>
            <input v-model="clienteEditando.codigo_postal" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Municipio</label>
          <input v-model="clienteEditando.municipio" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Teléfono</label>
            <input v-model="clienteEditando.telefono" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
          </div>
          <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Email</label>
            <input v-model="clienteEditando.email" type="email" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#109bc5] outline-none font-bold text-sm">
          </div>
        </div>

        <div class="flex gap-3 pt-4">
          <button type="button" @click="showEditModal = false" class="flex-1 py-3 font-bold text-gray-400 hover:text-gray-600 transition-colors">Cancelar</button>
          <button type="submit" class="flex-1 bg-[#ff6900] text-white py-3 rounded-2xl font-black shadow-lg shadow-orange-500/30 hover:scale-105 transition-all">
            Guardar Cambios
          </button>
        </div>
      </form>
    </div>
</div>

<!---------------------------MODAL DE AÑADIR CLIENTE---------------------------------------->
<div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
  <div @click="showAddModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all scale-100">
    <div class="bg-[#00d084] p-6 text-white flex justify-between items-center">
      <h3 class="font-black text-xl">Nueva Farmacia</h3>
      <button @click="showAddModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
    </div>

    <form @submit.prevent="guardarNuevoCliente" class="p-8 space-y-4">
      <div>
        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Nombre *</label>
        <input v-model="nuevoCliente.farmacia" type="text" required placeholder="Ej: Farmacia Central" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
      </div>

      <div>
        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Dirección</label>
        <input v-model="nuevoCliente.direccion" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Provincia</label>
          <input v-model="nuevoCliente.provincia" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
        </div>
        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Código Postal</label>
          <input v-model="nuevoCliente.codigo_postal" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Teléfono</label>
          <input v-model="nuevoCliente.telefono" type="text" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
        </div>
        <div>
          <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Email</label>
          <input v-model="nuevoCliente.email" type="email" class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl focus:ring-2 focus:ring-[#00d084] outline-none font-bold text-sm">
        </div>
      </div>

      <div class="flex gap-3 pt-4">
        <button type="button" @click="showAddModal = false" class="flex-1 py-3 font-bold text-gray-400 hover:text-gray-600">Cancelar</button>
        <button type="submit" class="flex-1 bg-[#00d084] text-white py-3 rounded-2xl font-black shadow-lg shadow-green-500/30 hover:scale-105 transition-all">
          Guardar Farmacia
        </button>
      </div>
    </form>
  </div>
</div>


<!------------------------------MODAL DE DETALLES---------------------------------------------->
<div v-if="showDetailsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
  <div @click="showDetailsModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm z-10 overflow-hidden transform transition-all">
    <div class="bg-[#ff6900] p-6 text-white flex justify-between items-center">
      <h3 class="font-black text-xl">Datos de contacto</h3>
      <button @click="showDetailsModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
    </div>
    <div class="p-6 space-y-4">
      <div>
        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Farmacia</p>
        <p class="font-bold text-slate-800">{{ detallesCliente.farmacia }}</p>
      </div>
      <div>
        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Teléfono</p>
        <a :href="'tel:' + detallesCliente.telefono" class="text-[#109bc5] font-bold hover:underline">
          {{ detallesCliente.telefono }}
        </a>
      </div>
      <div>
        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Email</p>
        <a :href="'mailto:' + detallesCliente.email" class="text-[#109bc5] font-bold hover:underline">
          {{ detallesCliente.email }}
        </a>
      </div>
    </div>
  </div>
</div>
  </div>
</template>