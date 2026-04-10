<script setup>
import { ref } from 'vue'

// Variables reactivas para el formulario
const email = ref('')
const password = ref('')
const cargando = ref(false)

const handleLogin = async () => {
  cargando.value = true
  // Aquí conectamos 'login.php con el backend para validar las credenciales'
  try {
    const response = await fetch('http://localhost/MIPROYECTO/api/login.php', {
      method: 'POST',
      body: JSON.stringify({ email: email.value, password: password.value })
    })
    const data = await response.json()
    
    if (data.success) {
      localStorage.setItem('auth_user', JSON.stringify(data.user));
      window.location.href = '/dashboard'; //vinculado a traves de main.js
    } else {
      alert('Credenciales incorrectas')
    }
  } catch (error) {
    console.error("Error en el login:", error)
  } finally {
    cargando.value = false
  }
}
</script>

<template>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#E3F2FD] to-[#BBDEFB] p-4">
    
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <img src="../img/logoS.png" alt="SEID Farmacia" class="mx-auto max-h-[60px]">
      </div>

      <div class="bg-white rounded-2xl shadow-2xl border-t-4 border-[#005B96] overflow-hidden">
        
        <div class="p-8">
          <div class="text-center mb-8">
            <button @click="$router.push('/registro')" class="group border border-[#005B96] text-[#005B96] hover:bg-[#005B96] hover:text-white px-6 py-2 rounded-lg transition-all duration-300">
              <span class="flex items-center font-semibold">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Usuario
              </span>
            </button>
          </div>

          <form @submit.prevent="handleLogin">
            <div class="mb-6">
              <label class="block font-bold text-slate-700 mb-2">Email</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#005B96]">
                  <i class="fas fa-user"></i>
                </span>
                <input 
                  v-model="email"
                  type="email" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005B96] outline-none transition-all"
                  placeholder="Introduce tu email"
                  required
                >
              </div>
            </div>

            <div class="mb-8">
              <div class="flex justify-between items-center mb-2">
                <label class="font-bold text-slate-700">Contraseña</label>
                <a href="#" class="text-sm text-[#005B96] hover:underline">¿Olvidaste tu contraseña?</a>
              </div>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#005B96]">
                  <i class="fas fa-lock"></i>
                </span>
                <input 
                  v-model="password"
                  type="password" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005B96] outline-none transition-all"
                  placeholder="Introduce tu contraseña"
                  required
                >
              </div>
            </div>

            <button 
              type="submit" 
              :disabled="cargando"
              class="w-full bg-[#005B96] hover:bg-[#003D6B] text-white font-bold py-3 rounded-lg shadow-lg transform active:scale-95 transition-all flex justify-center items-center"
            >
              <i v-if="!cargando" class="fas fa-sign-in-alt mr-2"></i>
              {{ cargando ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>