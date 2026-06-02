<script setup>
import { ref } from 'vue'
import {useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const cargando = ref(false)
const errorMsg = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)


const handleRegister = async () => {
    errorMsg.value = ''

    //Validaciones basicas 
    if (!email.value || !password.value || !confirmPassword.value) {
        errorMsg.value = 'Por favor, completa todos los campos.'
        return
    }

    if (password.value.length < 6) {
        errorMsg.value = 'La contraseña debe tener al menos 6 caracteres.'
        return
    }

    if (password.value !== confirmPassword.value) {
        errorMsg.value = 'Las contraseñas no coinciden.'
        return
    }

    cargando.value = true

    try {
        const response = await fetch('http://localhost/MiProyecto/api/registro_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
        })

        const data = await response.json()

        if (data.success) {
            //registro exitoso, redirigir a login
            router.push('/login')
        } else {
            errorMsg.value = data.message || 'Error al registrar. Intenta nuevamente.'
        }
    } catch (error) {
        console.error('Error en la solicitud de registro:', error)
        errorMsg.value = 'Error de conexión. Intenta nuevamente.'
    } finally {
        cargando.value = false

    }
}
</script>


<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#E3F2FD] to-[#BBDEFB] p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-6">
        <img src="../img/logoS.png" alt="SEID Farmacia" class="mx-auto max-h-[60px]">
      </div>

      <!-- Tarjeta de registro -->
      <div class="bg-white rounded-2xl shadow-2xl border-t-4 border-[#005B96] overflow-hidden">
        <div class="p-8">
          <!-- Título y enlace a login -->
          <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Crear cuenta</h2>
            <p class="text-sm text-gray-500 mt-1">Regístrate para acceder al CRM</p>
          </div>

          <!-- Formulario -->
          <form @submit.prevent="handleRegister">
            <!-- Campo Email -->
            <div class="mb-5">
              <label class="block font-bold text-slate-700 mb-2">Email</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#005B96]">
                  <i class="fas fa-envelope"></i>
                </span>
                <input 
                  v-model="email"
                  type="email" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005B96] outline-none transition-all"
                  placeholder="ejemplo@correo.com"
                  required
                >
              </div>
            </div>

            <!-- Campo Contraseña -->
            <div class="mb-5">
              <label class="block font-bold text-slate-700 mb-2">Contraseña</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#005B96]">
                  <i class="fas fa-lock"></i>
                </span>
                <input 
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005B96] outline-none transition-all"
                  placeholder="Mínimo 6 caracteres"
                  required
                >
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-[#005B96]"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Campo Confirmar Contraseña -->
            <div class="mb-5">
              <label class="block font-bold text-slate-700 mb-2">Confirmar contraseña</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#005B96]">
                  <i class="fas fa-check-circle"></i>
                </span>
                <input 
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005B96] outline-none transition-all"
                  placeholder="Repite la contraseña"
                  required
                >
                <button 
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-[#005B96]"
                >
                  <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Mensaje de error -->
            <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-200">
              {{ errorMsg }}
            </div>

            <!-- Botón de registro -->
            <button 
              type="submit" 
              :disabled="cargando"
              class="w-full bg-[#005B96] hover:bg-[#003D6B] text-white font-bold py-3 rounded-lg shadow-lg transform active:scale-95 transition-all flex justify-center items-center"
            >
              <i v-if="!cargando" class="fas fa-user-plus mr-2"></i>
              {{ cargando ? 'Registrando...' : 'Registrarse' }}
            </button>
          </form>

          <!-- Enlace a iniciar sesión -->
          <div class="text-center mt-6">
            <router-link to="/login" class="text-sm text-[#005B96] hover:underline">
              ¿Ya tienes cuenta? Inicia sesión aquí
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>