<template>
  <div class="login-container">
    <div class="glass login-box">
      <div class="logo">
        <div class="logo-icon"></div>
        <h1>Nexus POS</h1>
      </div>
      <p class="subtitle">Sign in to access the system</p>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input 
            type="email" 
            v-model="email" 
            class="input-field" 
            placeholder="admin@pos.com"
            required
          />
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input 
            type="password" 
            v-model="password" 
            class="input-field" 
            placeholder="••••••••"
            required
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="btn btn-primary login-btn" :disabled="loading">
          <span v-if="loading">Signing in...</span>
          <span v-else>Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('admin@pos.com')
const password = ref('password')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  try {
    loading.value = true
    error.value = ''
    
    // Use the backend API
    const response = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      password: password.value
    })

    if (response.data.success) {
      router.push('/pos')
    } else {
      error.value = 'Invalid credentials'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to connect to server'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-box {
  width: 100%;
  max-width: 420px;
  padding: 2.5rem;
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.logo-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, var(--primary), #a855f7);
  border-radius: 8px;
}

h1 {
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.subtitle {
  color: var(--text-muted);
  margin-bottom: 2rem;
  font-size: 0.95rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-muted);
}

.login-btn {
  width: 100%;
  margin-top: 1rem;
}

.error-message {
  color: var(--danger);
  font-size: 0.9rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 8px;
  margin-bottom: 1rem;
  text-align: center;
}
</style>
