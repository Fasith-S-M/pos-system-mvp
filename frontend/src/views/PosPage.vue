<template>
  <div class="pos-layout">
    <!-- Header -->
    <header class="glass pos-header">
      <div class="logo">
        <div class="logo-icon"></div>
        <h2>Nexus POS</h2>
      </div>
      <div class="user-info">
        <span>Cashier</span>
        <button @click="logout" class="btn logout-btn">Logout</button>
      </div>
    </header>

    <div class="pos-content">
      <!-- Product Grid -->
      <main class="products-section">
        <div v-if="loading" class="loading">Loading products...</div>
        <div v-else class="product-grid">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="glass product-card"
            @click="addToCart(product)"
            :class="{ 'out-of-stock': product.stock <= 0 }"
          >
            <div class="product-info">
              <h3>{{ product.name }}</h3>
              <p class="price">${{ parseFloat(product.price).toFixed(2) }}</p>
            </div>
            <div class="stock-badge" :class="product.stock > 10 ? 'high' : (product.stock > 0 ? 'low' : 'none')">
              {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of Stock' }}
            </div>
          </div>
        </div>
      </main>

      <!-- Cart Sidebar -->
      <aside class="glass cart-sidebar">
        <div class="cart-header">
          <h3>Current Order</h3>
        </div>

        <div class="cart-items">
          <div v-if="cart.length === 0" class="empty-cart">
            Cart is empty
          </div>
          <div v-else v-for="(item, index) in cart" :key="index" class="cart-item">
            <div class="item-details">
              <h4>{{ item.name }}</h4>
              <p>${{ parseFloat(item.price).toFixed(2) }}</p>
            </div>
            <div class="item-actions">
              <button @click="updateQuantity(index, -1)" class="qty-btn">-</button>
              <span class="qty">{{ item.quantity }}</span>
              <button @click="updateQuantity(index, 1)" class="qty-btn">+</button>
              <button @click="removeItem(index)" class="remove-btn">×</button>
            </div>
          </div>
        </div>

        <div class="cart-summary">
          <div class="summary-row">
            <span>Subtotal</span>
            <span>${{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>
          
          <button 
            @click="placeOrder" 
            class="btn btn-primary place-order-btn"
            :disabled="cart.length === 0 || placingOrder"
          >
            {{ placingOrder ? 'Processing...' : 'Place Order' }}
          </button>
        </div>
      </aside>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" class="toast" :class="toast.type">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const products = ref([])
const cart = ref([])
const loading = ref(true)
const placingOrder = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const fetchProducts = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/products')
    products.value = response.data
  } catch (err) {
    showToast('Failed to load products', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})

const addToCart = (product) => {
  if (product.stock <= 0) return

  const existingItem = cart.value.find(item => item.id === product.id)
  if (existingItem) {
    if (existingItem.quantity < product.stock) {
      existingItem.quantity++
    } else {
      showToast('Not enough stock', 'error')
    }
  } else {
    cart.value.push({
      ...product,
      quantity: 1
    })
  }
}

const updateQuantity = (index, delta) => {
  const item = cart.value[index]
  const product = products.value.find(p => p.id === item.id)
  
  const newQty = item.quantity + delta
  if (newQty > 0 && newQty <= product.stock) {
    item.quantity = newQty
  } else if (newQty === 0) {
    removeItem(index)
  }
}

const removeItem = (index) => {
  cart.value.splice(index, 1)
}

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const total = computed(() => subtotal.value)

const placeOrder = async () => {
  if (cart.value.length === 0) return
  
  try {
    placingOrder.value = true
    const response = await axios.post('http://localhost:8000/api/orders', {
      items: cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity
      }))
    })

    if (response.data.success) {
      showToast('Order placed successfully!', 'success')
      cart.value = []
      fetchProducts() // Refresh stock
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to place order', 'error')
  } finally {
    placingOrder.value = false
  }
}

const logout = () => {
  router.push('/')
}

const showToast = (message, type) => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}
</script>

<style scoped>
.pos-layout {
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  margin: 1rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  width: 24px;
  height: 24px;
  background: linear-gradient(135deg, var(--primary), #a855f7);
  border-radius: 6px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logout-btn {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-main);
  padding: 0.5rem 1rem;
}
.logout-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.pos-content {
  flex: 1;
  display: flex;
  gap: 1rem;
  padding: 0 1rem 1rem 1rem;
  overflow: hidden;
}

.products-section {
  flex: 1;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.product-card {
  padding: 1.5rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 140px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover:not(.out-of-stock) {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  border-color: rgba(99, 102, 241, 0.5);
}

.product-card.out-of-stock {
  opacity: 0.5;
  cursor: not-allowed;
}

.price {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary);
  margin-top: 0.5rem;
}

.stock-badge {
  font-size: 0.8rem;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  display: inline-block;
  margin-top: 1rem;
  width: fit-content;
}
.stock-badge.high { background: rgba(34, 197, 94, 0.1); color: var(--success); }
.stock-badge.low { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
.stock-badge.none { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

.cart-sidebar {
  width: 380px;
  display: flex;
  flex-direction: column;
}

.cart-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-cart {
  text-align: center;
  color: var(--text-muted);
  margin-top: 2rem;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  margin-bottom: 0.5rem;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: transparent;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qty-btn:hover { background: rgba(255, 255, 255, 0.1); }

.qty {
  min-width: 20px;
  text-align: center;
}

.remove-btn {
  background: transparent;
  border: none;
  color: var(--danger);
  font-size: 1.2rem;
  cursor: pointer;
  margin-left: 0.5rem;
}
.remove-btn:hover { color: var(--danger-hover); }

.cart-summary {
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  background: rgba(0, 0, 0, 0.2);
  border-radius: 0 0 16px 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: var(--text-muted);
}

.summary-row.total {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-main);
  margin: 1rem 0;
}

.place-order-btn {
  width: 100%;
  padding: 1rem;
  font-size: 1.1rem;
}

.place-order-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  padding: 1rem 2rem;
  border-radius: 8px;
  background: var(--bg-card);
  backdrop-filter: blur(10px);
  color: white;
  font-weight: 500;
  animation: slideUp 0.3s ease-out;
  z-index: 1000;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.toast.success { border-left: 4px solid var(--success); }
.toast.error { border-left: 4px solid var(--danger); }
</style>
