<template>
  <div class="dashboard">
    <header class="header">
      <h1>Product Dashboard</h1>
      <button @click="logout" class="logout-btn">
        <span>Logout</span>
      </button>
    </header>

    <div class="content">
      <!-- Add Product Section -->
      <div class="card add-section">
        <h2>Add New Product</h2>
        <form @submit.prevent="addProduct" class="form">
          <div class="input-group">
            <input v-model="newProduct.name" placeholder="Product name" required />
            <input v-model="newProduct.price" type="number" step="0.01" placeholder="Price" required />
            <input v-model.number="newProduct.stock" type="number" placeholder="Stock" required />
            <button type="submit" class="btn-primary">Add Product</button>
          </div>
        </form>
      </div>

      <!-- Products Table -->
      <div class="card">
        <h2>Products ({{ products.length }})</h2>
        <div class="table-container">
          <table v-if="products.length">
            <thead>
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td class="product-name">{{ product.name }}</td>
                <td class="price">RM{{ product.price }}</td>
                <td class="stock">{{ product.stock }}</td>
                <td class="actions">
                  <button @click="startEdit(product)" :disabled="userRole !== 'admin'" class="btn-edit">
                    Edit
                  </button>
                  <button @click="confirmDelete(product.id)" :disabled="userRole !== 'admin'" class="btn-delete">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty-state">
            <p>No products found</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div v-if="errorMessage" class="message error">{{ errorMessage }}</div>
    <div v-if="successMessage" class="message success">{{ successMessage }}</div>

    <!-- Delete Modal -->
    <div v-if="showDeleteConfirm" class="modal" @click.self="showDeleteConfirm = false">
      <div class="modal-content">
        <h3>Delete Product</h3>
        <p>Are you sure you want to delete this product?</p>
        <div class="modal-actions">
          <button @click="deleteProduct" class="btn-danger">Delete</button>
          <button @click="showDeleteConfirm = false" class="btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditPopup" class="modal" @click.self="cancelEdit">
      <div class="modal-content">
        <h3>Edit Product</h3>
        <form @submit.prevent="saveEdit" class="modal-form">
          <input v-model="editProduct.name" placeholder="Product name" required />
          <input v-model="editProduct.price" type="number" step="0.01" placeholder="Price" required />
          <input v-model.number="editProduct.stock" type="number" placeholder="Stock" required />
          <div class="modal-actions">
            <button type="submit" class="btn-primary">Save</button>
            <button type="button" @click="cancelEdit" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductDashboard',
  data() {
    return {
      products: [],
      newProduct: { name: '', price: '', stock: 0 },
      editProduct: { id: null, name: '', price: '', stock: 0 },
      errorMessage: '',
      successMessage: '',
      showDeleteConfirm: false,
      productToDelete: null,
      showEditPopup: false,
      userRole: JSON.parse(localStorage.getItem('user_info') || '{}').role || 'staff'
    };
  },
  methods: {
    async fetchProducts() {
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch('http://localhost:8000/api/products', {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (res.ok) {
          this.products = data;
        } else {
          this.showMessage(data.error || 'Failed to fetch products', 'error');
        }
      } catch {
        this.showMessage('Network error. Please try again.', 'error');
      }
    },
    async addProduct() {
      try {
        const token = localStorage.getItem('jwt_token');
        const payload = {
          name: this.newProduct.name,
          price: parseFloat(this.newProduct.price) || 0,
          stock: parseInt(this.newProduct.stock) || 0
        };
        const res = await fetch('http://localhost:8000/api/products', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
          this.showMessage(data.message, 'success');
          this.newProduct = { name: '', price: '', stock: 0 };
          this.fetchProducts();
        } else {
          this.showMessage(data.error || 'Failed to add product', 'error');
        }
      } catch {
        this.showMessage('Network error. Please try again.', 'error');
      }
    },
    async deleteProduct() {
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch(`http://localhost:8000/api/products/${this.productToDelete}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (res.ok) {
          this.showMessage(data.message, 'success');
          this.fetchProducts();
        } else {
          this.showMessage(data.error || 'Failed to delete product', 'error');
        }
      } catch {
        this.showMessage('Network error. Please try again.', 'error');
      } finally {
        this.showDeleteConfirm = false;
        this.productToDelete = null;
      }
    },
    async saveEdit() {
      try {
        const token = localStorage.getItem('jwt_token');
        const payload = {
          name: this.editProduct.name,
          price: parseFloat(this.editProduct.price) || 0,
          stock: parseInt(this.editProduct.stock) || 0
        };
        const res = await fetch(`http://localhost:8000/api/products/${this.editProduct.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
          this.showMessage(data.message, 'success');
          this.showEditPopup = false;
          this.fetchProducts();
        } else {
          this.showMessage(data.error || 'Failed to update product', 'error');
        }
      } catch {
        this.showMessage('Network error. Please try again.', 'error');
      }
    },
    confirmDelete(id) {
      this.productToDelete = id;
      this.showDeleteConfirm = true;
    },
    startEdit(product) {
      this.editProduct = { ...product };
      this.showEditPopup = true;
    },
    cancelEdit() {
      this.showEditPopup = false;
      this.editProduct = { id: null, name: '', price: '', stock: 0 };
    },
    logout() {
      localStorage.removeItem('jwt_token');
      localStorage.removeItem('user_info');
      this.$router.push('/');
    },
    showMessage(message, type) {
      this.errorMessage = '';
      this.successMessage = '';
      if (type === 'error') {
        this.errorMessage = message;
      } else {
        this.successMessage = message;
      }
      setTimeout(() => {
        this.errorMessage = '';
        this.successMessage = '';
      }, 3000);
    }
  },
  mounted() {
    this.fetchProducts();
  }
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header h1 {
  color: white;
  margin: 0;
  font-size: 2rem;
  font-weight: 600;
}

.logout-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 10px 20px;
  border-radius: 25px;
  cursor: pointer;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.content {
  max-width: 1000px;
  margin: 0 auto;
}

.card {
  background: white;
  border-radius: 15px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.card h2 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 1.4rem;
  font-weight: 600;
}

.add-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.add-section h2 {
  color: white;
}

.form .input-group {
  display: grid;
  grid-template-columns: 1fr 120px 100px auto;
  gap: 15px;
  align-items: center;
}

.form input {
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  font-size: 14px;
  transition: all 0.3s ease;
}

.form input:focus {
  outline: none;
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
}

th {
  background: #f8f9fa;
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #555;
  font-size: 14px;
}

td {
  padding: 16px;
  border-bottom: 1px solid #eee;
  text-align: left;
}

tr:hover {
  background: #f8f9fa;
}

.product-name {
  font-weight: 500;
  color: #333;
}

.price {
  font-weight: 600;
  color: #28a745;
}

.stock {
  color: #6c757d;
}

.actions {
  display: flex;
  gap: 8px;
}

/* Buttons */
.btn-primary, .btn-edit, .btn-delete, .btn-danger, .btn-secondary {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

.btn-edit {
  background: #007bff;
  color: white;
}

.btn-edit:hover:not(:disabled) {
  background: #0056b3;
}

.btn-delete, .btn-danger {
  background: #dc3545;
  color: white;
}

.btn-delete:hover:not(:disabled), .btn-danger:hover {
  background: #c82333;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Messages */
.message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 15px 20px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.message.error {
  background: #dc3545;
}

.message.success {
  background: #28a745;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 15px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-content h3 {
  margin: 0 0 20px 0;
  color: #333;
  text-align: center;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-form input {
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 20px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6c757d;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .dashboard {
    padding: 15px;
  }
  
  .header h1 {
    font-size: 1.5rem;
  }
  
  .form .input-group {
    grid-template-columns: 1fr;
  }
  
  .actions {
    flex-direction: column;
  }
}
</style>