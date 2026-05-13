<script setup>
import { ref, onMounted , computed } from 'vue';
import { useRoute } from 'vue-router';

//Estados de interfaz
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)
const searchQuery = ref('')
const route = useRoute()





//Datos del pedido
const pedidos = ref([])
const clientes = ref([])
const productosDisponibles = ref([])



//Función para cargar los pedidos desde la API
const fetchPedidos = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/get_pedidos.php');
    const data = await response.json();
    pedidos.value = data.pedidos;
  } catch (error) {
    console.error('Error fetching pedidos:', error);
  }
  
}

//Funcion para cargar los clientes desde la API
const fetchClientes = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/get_clients.php');
    const data = await response.json();
    clientes.value = data.clients;
  } catch (error) {
    console.error('Error fetching clientes:', error);
  }
}

//Funcion para cargar los productos disponibles desde la API
const fetchProductos = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/get_productos.php');
    const data = await response.json();
    productosDisponibles.value = data.productos;
  } catch (error) {
    console.error('Error fetching productos:', error);
  }
}



// =======Filtrado de pedidos según búsqueda=======
const pedidosFiltrados = computed(() => {
  if (!Array.isArray(pedidos.value)) return []
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return pedidos.value
  return pedidos.value.filter(pedido => {
    return (
      pedido.fecha.toLowerCase().includes(query) ||
      pedido.farmacia?.toLowerCase().includes(query) ||
      pedido.total.toString().toLowerCase().includes(query)
  )
  })
})



//=======Modal añadir pedido =========
const showAddModal = ref(false)
const nuevoPedido = ref({
  fecha: new Date().toISOString().split('T')[0], // Fecha actual
  cliente_id: '',
  lineas: []
})

const busquedaFarmacia = ref('')

const farmaciasList = computed(() => {
  if (!busquedaFarmacia.value) return clientes.value
  const query = busquedaFarmacia.value.toLowerCase()
  return clientes.value.filter(c=> c.farmacia.toLowerCase().includes(query))
})

const onSelectFarmacia = () => {
  const seleccionado = clientes.value.find(c => c.farmacia === busquedaFarmacia.value)
  nuevoPedido.value.cliente_id = seleccionado ? seleccionado.id : ''
}

const filtrarProductos = (busqueda) => {
  if (!busqueda) return productosDisponibles.value
  const query = busqueda.toLowerCase()
  return productosDisponibles.value.filter(p => p.nombre.toLowerCase().includes(query))
}


const agregarLinea = () => {
  nuevoPedido.value.lineas.push({
    producto_nombre: '',
    nombre: '',
    pvl: 0,
    cantidad: 1,
    dto: 0,
    total: 0, 
    categoria: '',
    productoBusqueda: ''
  })
}

const onProductoSeleccionado = (index, nombreProducto) => {
  const producto = productosDisponibles.value.find(p => p.nombre === nombreProducto)
  if (producto) {
    const linea = nuevoPedido.value.lineas[index]
    linea.producto_nombre = producto.nombre
    linea.nombre = producto.nombre
    linea.pvl = parseFloat(producto.pvl)
    linea.categoria = producto.categoria
    linea.productoBusqueda = producto.nombre
    recalcularLinea(index)
  } else if( nombreProducto === '') {
    const linea = nuevoPedido.value.lineas[index]
    linea.producto_nombre = ''
    linea.nombre = ''
    linea.pvl = 0
    linea.categoria = ''
    recalcularLinea(index)
  }
}

const recalcularLinea = (index) => {
  const linea = nuevoPedido.value.lineas[index]
  const importeBase = linea.cantidad * linea.pvl
  const descuento = importeBase * (linea.dto / 100)
  linea.total = importeBase - descuento
}

//Eliminar linea
const eliminarLinea = (index) => {
  nuevoPedido.value.lineas.splice(index, 1)
}

//Ttoal del pedido 
const totalPedidoGeneral = computed(() => {
  return nuevoPedido.value.lineas.reduce((sum, linea) => sum + linea.total, 0)
})

//Totales por categoría
const totalFarma = computed(() => {
  return nuevoPedido.value.lineas
    .filter(l => l.categoria === 'FARMA')
    .reduce((sum,l) =>sum +(l.total || 0), 0)
})

const totalMedica = computed(() => {
  return nuevoPedido.value.lineas
    .filter(l => l.categoria === 'MEDICA')
    .reduce((sum,l) =>sum +(l.total || 0), 0)
})

const totalNadie = computed(() => {
  return nuevoPedido.value.lineas
    .filter(l => l.categoria === 'NADIE')
    .reduce((sum,l) =>sum +(l.total || 0), 0)
})

const totalAmbos = computed(() => {
  return nuevoPedido.value.lineas
    .filter(l => l.categoria === 'AMBOS')
    .reduce((sum,l) =>sum +(l.total || 0), 0)
})


//Abrir modal 
const abrirAñadir = () => {
  nuevoPedido.value = {
    fecha: new Date().toISOString().split('T')[0],
    cliente_id: '',
    lineas: []
  }
  busquedaFarmacia.value = ''
  agregarLinea() // Añadir una línea vacía por defecto
  showAddModal.value = true
}

//Guardar pedido completo con lineas 
const guardarNuevoPedido = async () => {

  if (!nuevoPedido.value.cliente_id) {
    alert("Por favor, selecciona una farmacia para el pedido.")
    return
  }

  if (nuevoPedido.value.lineas.length === 0) {
    alert("Por favor, añade al menos un producto válido al pedido.")
    return
  }

  if (nuevoPedido.value.lineas.some(l => !l.producto_nombre || l.cantidad <= 0)) {
    alert("Por favor, asegúrate de que todas las líneas del pedido tengan un producto seleccionado y una cantidad válida.")
    return
  }

  try {
    const payload = {
      fecha: nuevoPedido.value.fecha,
      cliente_id: nuevoPedido.value.cliente_id,
      total: totalPedidoGeneral.value,
      lineas: nuevoPedido.value.lineas.map(l => ({
        producto_nombre: l.producto_nombre,
        cantidad: l.cantidad,
        dto: l.dto,
        precio_unitario: l.pvl,
        total: l.total,
        categoria: l.categoria
      }))
    }

    const response = await fetch('http://localhost/MiProyecto/api/add_pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const result = await response.json()
    if (result.success) {
      alert("Pedido añadido correctamente")
      showAddModal.value = false
      await fetchPedidos() // Refrescar la lista de pedidos
      await fetchTotalesCategorias() // Refrescar totales por categoría
    } else {
      alert("Error al añadir el pedido: " + result.message)
    }
  } catch (error) {
    console.error('Error al guardar el pedido:', error)
    alert("Error al guardar el pedido. Por favor, inténtalo de nuevo.")
  }
}

// ============ ELIMINAR PEDIDO ==============
const eliminarPedido = async (id) => {
  if (!confirm("¿Estás seguro de que deseas eliminar este pedido?")) return

  try {
    const response = await fetch('http://localhost/MiProyecto/api/delete_pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    const result = await response.json()
    if (result.success) {
      alert('Pedido eliminado')
      await fetchPedidos()
      await fetchTotalesCategorias()
    } else {
      alert('Error: ' + result.message)
    }
  } catch (error) {
    console.error('Error al eliminar:', error)
    alert('Error de conexión')
  }
}

//============VIsualizaciones del modal de detalle del pedido =============
const showDetailModal = ref(false)
const pedidoSeleccionado = ref(null)
const lineasPedido = ref([])

const verDetallePedido = async (pedido) => {
  pedidoSeleccionado.value = pedido
  try {
    const response = await fetch(`http://localhost/MiProyecto/api/get_pedido_lineas.php?id=${pedido.id}`)
    const data = await response.json()
    lineasPedido.value = data.lineas
    showDetailModal.value = true
  } catch (error) {
    console.error('Error al cargar detalle:', error)
    alert('Error al cargar detalle del pedido')
  }
}

//Funcion de cerrar el modal de detalle
const cerrarDetalle = () => {
  showDetailModal.value = false
  pedidoSeleccionado.value = null
  lineasPedido.value = []
}
      

//===============  TARJETAS DE CATEGORIAS ===============
const totalesCategorias = ref({
  FARMA: 0, 
  MEDICA: 0, 
  AMBOS: 0, 
  NADIE: 0
})

const fetchTotalesCategorias = async () => {
  try {
    const response = await fetch('http://localhost/MiProyecto/api/get_totales_categorias.php')
    const data = await response.json()
    totalesCategorias.value = data.totales
  } catch (error) {
    console.error('Error al cargar totales por categoría:', error)
  }
}



//Llamadas a funciones al montar el componente
onMounted(() => {
  fetchPedidos();
  fetchClientes();
  fetchProductos();
  fetchTotalesCategorias();


  // Traspaso desde Dashboard cuando pulsamos el boton de añadir pedido: Si la URL tiene ?add=true, se abre el modal de añadir pedido automáticamente
  if (route.query.add === 'true') {
    abrirAñadir()
    // Limpia el query de la URL para que no se reabra al recargar la página
    const newUrl = window.location.pathname
    window.history.replaceState({}, '', newUrl)
  }
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
  <div class="min-h-screen bg-[#f0f4f7] font-sans text-slate-700 flex">
    
    <!-- ======================== SIDEBAR ======================== -->
    <aside 
      :class="[isCollapsed ? 'md:w-20' : 'md:w-64', isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']"
      class="fixed top-0 left-0 h-full bg-[#109bc5] text-white transition-all duration-300 z-50 flex flex-col shadow-2xl"
    >
      <button @click="isCollapsed = !isCollapsed" class="hidden md:flex absolute -right-3 top-10 bg-[#ff6900] w-6 h-6 rounded-full items-center justify-center border-2 border-white text-[10px] shadow-lg">
        <span>{{ isCollapsed ? '→' : '←' }}</span>
      </button>

      <div class="p-6 flex items-center gap-3 overflow-hidden">
        <div class="min-w-[40px] h-10 bg-white/20 rounded-xl flex items-center justify-center font-black">
          <img src='../img/icono.png' alt="logo">
        </div>
        <span v-if="!isCollapsed" class="font-black text-xl tracking-tighter uppercase">CRM</span>
      </div>

      <nav class="flex-1 px-3 space-y-1">
        <router-link 
          v-for="item in menuItems" 
          :key="item.name" 
          :to="item.path"
          class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/10 transition-all group relative"
          :class="{'justify-center': isCollapsed, 'bg-white/20': item.name === 'Pedidos'}"
        >
          <span class="text-xl">{{ item.icon }}</span>
          <span v-if="!isCollapsed" class="font-bold text-sm">{{ item.name }}</span>
        </router-link>
      </nav>
    </aside>

    <!-- ================== CONTENIDO PRINCIPAL =========== -->
    <main :class="isCollapsed ? 'md:ml-20' : 'md:ml-64'" class="flex-1 transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8">
      
      <!-- Cabecera -->
      <header class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-black text-[#9b51e0]">Pedidos</h1>
          <p class="text-gray-400 font-medium text-xs md:text-sm">Gestión de pedidos de farmacias</p>
        </div>
        <button @click="abrirAñadir" class="bg-[#00d084] text-white px-4 py-2 md:px-6 md:py-3 rounded-xl md:rounded-2xl font-bold shadow-lg shadow-green-500/20 hover:scale-105 transition-all text-xs">
          <i class="fas fa-plus mr-2"></i> Añadir Pedido
        </button>
      </header>

      <!--Tarjetas categorias-->
      <!-- Tarjetas de totales por categoría -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <!-- Tarjeta FARMA -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-4 shadow-lg text-white">
          <p class="text-xs font-bold opacity-80">💊 FARMA</p>
          <p class="text-2xl font-black mt-1">{{ totalesCategorias.FARMA.toFixed(2) }} €</p>
          <p class="text-[10px] opacity-70 mt-1">Total mes actual</p>
        </div>
        
        <!-- Tarjeta MÉDICA -->
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl p-4 shadow-lg text-white">
          <p class="text-xs font-bold opacity-80">🏥 MÉDICA</p>
          <p class="text-2xl font-black mt-1">{{ totalesCategorias.MEDICA.toFixed(2) }} €</p>
          <p class="text-[10px] opacity-70 mt-1">Total mes actual</p>
        </div>
        
        <!-- Tarjeta AMBOS -->
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-2xl p-4 shadow-lg text-white">
          <p class="text-xs font-bold opacity-80">🔄 AMBOS</p>
          <p class="text-2xl font-black mt-1">{{ totalesCategorias.AMBOS.toFixed(2) }} €</p>
          <p class="text-[10px] opacity-70 mt-1">Total mes actual</p>
        </div>
        
        <!-- Tarjeta NADIE (VACIA) -->
        <div class="bg-gradient-to-br from-gray-500 to-gray-700 rounded-2xl p-4 shadow-lg text-white">
          <p class="text-xs font-bold opacity-80">❌ NADIE</p>
          <p class="text-2xl font-black mt-1">{{ totalesCategorias.NADIE.toFixed(2) }} €</p>
          <p class="text-[10px] opacity-70 mt-1">Total mes actual</p>
        </div>
      </div>

      <!-- Buscador -->
      <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 flex items-center gap-3 border border-gray-100">
        <span class="text-gray-300">🔍</span>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Buscar por fecha, farmacia o importe..." 
          class="flex-1 outline-none text-sm font-medium"
        />
      </div>

      <!-- TABLA (escritorio) -->
      <div class="hidden md:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-50/50 text-gray-400 text-[10px] uppercase tracking-[0.2em]">
            <tr>
              <th class="p-6">Fecha</th>
              <th class="p-6">Farmacia</th>
              <th class="p-6">Importe (€)</th>
              <th class="p-6 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="pedido in pedidosFiltrados" :key="pedido.id" class="hover:bg-gray-50/50 transition-colors">
              
              <td class="p-6 text-xs font-bold text-slate-800">{{ pedido.fecha }}</td>
              <td class="p-6">
                <p class="font-black text-slate-800 text-sm">{{ pedido.farmacia }}</p>
              </td>
              <td class="p-6 text-xs font-bold text-[#00d084]">{{ Number(pedido.total).toFixed(2) }} €</td>
              <td class="p-6 text-right"> 
                <div class="flex gap-2 justify-end">
                  <button @click="verDetallePedido(pedido)" class="bg-blue-100 text-blue-600 p-2 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                   👁️
                  </button>
                  <button @click="eliminarPedido(pedido.id)" class="bg-red-100 text-red-500 p-2 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                    🗑️
                  </button>
                </div>

              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- VISTA MÓVIL (grid de tarjetas) -->
      <div class="md:hidden grid grid-cols-2 gap-3">
        <div v-for="pedido in pedidosFiltrados" :key="pedido.id" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div>
            <div class="flex justify-between items-start mb-2">
        
              <span class="text-[#109bc5]">📦</span>
            </div>
            <h3 class="font-black text-slate-800 text-sm leading-tight mb-1">{{ pedido.farmacia }}</h3>
            <p class="text-[10px] text-gray-400">{{ pedido.fecha }}</p>
            <p class="text-sm font-black text-[#00d084] mt-1">{{ Number(pedido.total).toFixed(2) }} €</p>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-50 flex justify-end gap-2">
            <button @click="verDetallePedido(pedido)" class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
              👁️
            </button>
            <button @click="eliminarPedido(pedido.id)" class="w-8 h-8 bg-red-100 text-red-500 rounded-lg flex items-center justify-center">
               🗑️
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Botón menú móvil -->
    <button @click="isMobileMenuOpen = true" class="md:hidden fixed top-4 left-4 w-10 h-10 bg-white rounded-xl shadow-lg flex items-center justify-center text-[#109bc5] z-40">
      ☰
    </button>

    <!-- ==================== MODAL AÑADIR PEDIDO ==================== -->
    <div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
      <div @click="showAddModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl z-10 overflow-hidden transform transition-all my-8">
        <div class="bg-[#d65799] p-6 text-white flex justify-between items-center">
          <h3 class="font-black text-xl">Nuevo Pedido</h3>
          <button @click="showAddModal = false" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        
        <form @submit.prevent="guardarNuevoPedido" class="p-6 space-y-6">
          <!-- Datos generales: Fecha y Farmacia con datalist -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Fecha *</label>
              <input v-model="nuevoPedido.fecha" type="date" required class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl">
            </div>
            <div>
              <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Farmacia *</label>
              <input 
                v-model="busquedaFarmacia" 
                list="farmaciasList" 
                placeholder="Escribe para buscar farmacia..." 
                @change="onSelectFarmacia"
                class="w-full bg-gray-50 border border-gray-100 p-3 rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#d65799] outline-none"
              >
              <datalist id="farmaciasList">
                <option v-for="cliente in farmaciasList" :key="cliente.id" :value="cliente.farmacia"></option>
              </datalist>
            </div>
          </div>

          <!-- Tabla de productos con datalist por fila -->
          <div>
            <div class="flex justify-between items-center mb-3">
              <label class="text-[10px] font-black text-gray-400 uppercase">Productos del pedido</label>
              <button type="button" @click="agregarLinea" class="text-xs bg-[#d65799] text-white px-3 py-1 rounded-full hover:bg-[#b8407a] transition-all">+ Añadir producto</button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm border border-gray-100 rounded-xl">
                <thead class="bg-gray-50 text-gray-400 text-[9px] uppercase">
                  <tr>
                    <th class="p-2">Producto</th>
                    <th class="p-2">PVL (€)</th>
                    <th class="p-2">Cantidad</th>
                    <th class="p-2">Dto %</th>
                    <th class="p-2">Total (€)</th>
                    <th class="p-2"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(linea, idx) in nuevoPedido.lineas" :key="idx" class="border-b border-gray-100">
                    <td class="p-2 min-w-[180px] md:min-w-0">
                      <!-- Input con datalist único por fila -->
                      <input 
                        v-model="linea.productoBusqueda" 
                        :list="'productosList_' + idx" 
                        placeholder="Buscar producto..." 
                        @change="onProductoSeleccionado(idx, $event.target.value)"
                        class="w-full bg-gray-50 border border-gray-100 p-2 rounded-lg text-xs"
                      >
                      <datalist :id="'productosList_' + idx">
                        <option v-for="prod in filtrarProductos(linea.productoBusqueda)" :key="prod.id" :value="prod.nombre"></option>
                      </datalist>
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.01" v-model="linea.pvl" readonly class="w-20 bg-gray-100 p-2 rounded-lg text-xs text-center">
                    </td>
                    <td class="p-2">
                      <input type="number" min="0" step="1" v-model="linea.cantidad" @input="recalcularLinea(idx)" class="w-20 bg-gray-50 border border-gray-100 p-2 rounded-lg text-xs text-center">
                    </td>
                    <td class="p-2">
                      <input type="number" min="0" max="100" step="1" v-model="linea.dto" @input="recalcularLinea(idx)" class="w-20 bg-gray-50 border border-gray-100 p-2 rounded-lg text-xs text-center">
                    </td>
                    <td class="p-2">
                      <span class="font-bold text-[#d65799]">{{ linea.total.toFixed(2) }} €</span>
                    </td>
                    <td class="p-2">
                      <button type="button" @click="eliminarLinea(idx)" class="text-red-400 hover:text-red-600">✖</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totales y resumen por categorías -->
          <div class="bg-gray-50 p-4 rounded-2xl space-y-2">
            <div class="flex justify-between items-center">
              <span class="font-bold text-sm">Total Pedido:</span>
              <span class="text-xl font-black text-[#d65799]">{{ totalPedidoGeneral.toFixed(2) }} €</span>
            </div>
            <div class="border-t border-gray-200 pt-2">
              <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Resumen por categorías</p>
              <div class="grid grid-cols-4 gap-2 text-center">
                <div class="bg-white p-2 rounded-lg shadow-sm">
                  <p class="text-[9px] text-gray-400">Farma</p>
                  <p class="font-bold text-[#d65799]">{{ totalFarma.toFixed(2) }} €</p>
                </div>
                <div class="bg-white p-2 rounded-lg shadow-sm">
                  <p class="text-[9px] text-gray-400">Médica</p>
                  <p class="font-bold text-[#d65799]">{{ totalMedica.toFixed(2) }} €</p>
                </div>
                <div class="bg-white p-2 rounded-lg shadow-sm">
                  <p class="text-[9px] text-gray-400">Ambos</p>
                  <p class="font-bold text-[#d65799]">{{ totalAmbos.toFixed(2) }} €</p>
                </div>
                <div class="bg-white p-2 rounded-lg shadow-sm">
                  <p class="text-[9px] text-gray-400">Nadie</p>
                  <p class="font-bold text-[#d65799]">{{ totalNadie.toFixed(2) }} €</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="showAddModal = false" class="flex-1 py-3 font-bold text-gray-400 hover:text-gray-600">Cancelar</button>
            <button type="submit" class="flex-1 bg-[#d65799] text-white py-3 rounded-2xl font-black shadow-lg shadow-pink-500/30 hover:scale-105 transition-all">Guardar Pedido</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL DETALLE PEDIDO ==================== -->
    <div v-if="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
  <div @click="cerrarDetalle" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden transform transition-all my-8">
    <div class="bg-[#109bc5] p-6 text-white flex justify-between items-center">
      <h3 class="font-black text-xl">Detalle del Pedido </h3>
      <button @click="cerrarDetalle" class="text-white/50 hover:text-white text-2xl">&times;</button>
    </div>
    
    <div class="p-6 space-y-6">
      <!-- Datos generales -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-2xl">
        <div>
          <p class="text-[10px] font-black text-gray-400 uppercase">Fecha</p>
          <p class="font-bold text-slate-800">{{ pedidoSeleccionado?.fecha }}</p>
        </div>
        <div>
          <p class="text-[10px] font-black text-gray-400 uppercase">Farmacia</p>
          <p class="font-bold text-slate-800">{{ pedidoSeleccionado?.farmacia }}</p>
        </div>
        <div>
          <p class="text-[10px] font-black text-gray-400 uppercase">Total</p>
          <p class="font-black text-[#00d084] text-xl">{{ Number(pedidoSeleccionado?.total).toFixed(2) }} €</p>
        </div>
      </div>

      <!-- Líneas del pedido -->
      <div>
  <h4 class="font-black text-sm text-gray-500 uppercase mb-3">Líneas del pedido</h4>
  <div class="overflow-x-auto">
    <table class="w-full text-sm border border-gray-100 rounded-xl">
      <thead class="bg-gray-50 text-gray-400 text-[9px] uppercase">
        <tr>
          <th class="p-3 text-left min-w-[200px]">Producto</th>   <!-- Ancho mínimo para descripciones largas -->
          <th class="p-3 text-center">Cantidad</th>
          <th class="p-3 text-center">PVL (€)</th>
          <th class="p-3 text-center">Dto %</th>
          <th class="p-3 text-center">Total (€)</th>
          <th class="p-3 text-center">Categoría</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="(linea, idx) in lineasPedido" :key="idx">
          <!-- Celda de producto con texto envolvente y ancho automático -->
          <td class="p-3 align-top break-words whitespace-normal">
            {{ linea.producto_nombre || linea.nombre || 'Producto' }}
          </td>
          <td class="p-3 text-center">{{ linea.cantidad }}</td>
          <td class="p-3 text-center">{{ Number(linea.precio_unitario).toFixed(2) }}</td>
          <td class="p-3 text-center">{{ linea.dto }}%</td>
          <td class="p-3 text-center font-bold text-[#d65799]">{{ Number(linea.total).toFixed(2) }} €</td>
          <td class="p-3 text-center">
            <span class="px-2 py-1 rounded-full text-[10px] font-black inline-block" 
                  :class="{
                    'bg-purple-100 text-purple-700': linea.categoria === 'FARMA',
                    'bg-green-100 text-green-700': linea.categoria === 'MEDICA',
                    'bg-orange-100 text-orange-700': linea.categoria === 'AMBOS',
                    'bg-gray-100 text-gray-500': linea.categoria === 'NADIE'
                  }">
              {{ linea.categoria || '—' }}
            </span>
           </td>
         </tr>
        <tr v-if="lineasPedido.length === 0">
          <td colspan="6" class="p-6 text-center text-gray-400">No hay líneas en este pedido</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

      <div class="flex justify-end pt-4">
        <button @click="cerrarDetalle" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  </div>
</template>