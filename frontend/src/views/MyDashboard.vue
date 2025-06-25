<template>
  <div class="dashboard-container">
    <h2>Product Management Dashboard</h2>
    <button @click="logout" class="logout-btn">Logout</button>

    <div class="product-section">
      <h3>Products</h3>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>{{ product.name }}</td>
            <td>{{ product.price }}</td>
            <td>{{ product.stock }}</td>
            <td>
              <button @click="startEdit(product)" :disabled="userRole !== 'admin'" class="edit-btn">Edit</button>
              <button @click="confirmDelete(product.id)" :disabled="userRole !== 'admin'" class="delete-btn">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="add-product-section">
      <h3>Add Product</h3>
      <form @submit.prevent="addProduct" autocomplete="off">
        <div class="form-row">
          <label for="product-name">Name</label>
          <input v-model="newProduct.name" id="product-name" required placeholder="Enter product name" />
        </div>
        <div class="form-row">
          <label for="product-price">Price</label>
          <input v-model="newProduct.price" id="product-price" type="number" step="0.01" required placeholder="Enter price (e.g., 90.90)" />
        </div>
        <div class="form-row">
          <label for="product-stock">Stock</label>
          <input v-model.number="newProduct.stock" id="product-stock" type="number" required placeholder="Enter stock" />
        </div>
        <button type="submit" class="add-btn">Add</button>
      </form>
    </div>

    <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-msg">{{ successMessage }}</div>

    <!-- Delete Confirmation Popup -->
    <div v-if="showDeleteConfirm" class="modal">
      <div class="modal-content">
        <p>Are you sure you want to delete this product?</p>
        <button @click="deleteProduct" class="confirm-btn">Yes</button>
        <button @click="showDeleteConfirm = false" class="cancel-btn">No</button>
      </div>
    </div>

    <!-- Edit Popup -->
    <div v-if="showEditPopup" class="modal">
      <div class="modal-content">
        <h3>Edit Product</h3>
        <form @submit.prevent="saveEdit" autocomplete="off">
          <div class="form-row">
            <label for="edit-name">Name</label>
            <input v-model="editProduct.name" id="edit-name" required placeholder="Enter product name" />
          </div>
          <div class="form-row">
            <label for="edit-price">Price</label>
            <input v-model="editProduct.price" id="edit-price" type="number" step="0.01" required placeholder="Enter price (e.g., 90.90)" />
          </div>
          <div class="form-row">
            <label for="edit-stock">Stock</label>
            <input v-model.number="editProduct.stock" id="edit-stock" type="number" required placeholder="Enter stock" />
          </div>
          <div class="button-group">
            <button type="submit" class="save-btn">Save</button>
            <button type="button" @click="cancelEdit" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MyDashboard',
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
          this.errorMessage = data.error || 'Failed to fetch products';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    async addProduct() {
      this.errorMessage = '';
      this.successMessage = '';
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
          this.successMessage = data.message;
          this.newProduct = { name: '', price: '', stock: 0 };
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to add product';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    async deleteProduct() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch(`http://localhost:8000/api/products/${this.productToDelete}`, {
          method: 'DELETE',
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = data.message;
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to delete product';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      } finally {
        this.showDeleteConfirm = false;
        this.productToDelete = null;
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
    async saveEdit() {
      this.errorMessage = '';
      this.successMessage = '';
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
          this.successMessage = data.message;
          this.showEditPopup = false;
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to update product';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    cancelEdit() {
      this.showEditPopup = false;
      this.editProduct = { id: null, name: '', price: '', stock: 0 };
    },
    logout() {
      localStorage.removeItem('jwt_token');
      localStorage.removeItem('user_info');
      this.$router.push('api/auth/login');
    }
  },
  mounted() {
    this.fetchProducts();
  }
};
</script>

<style scoped>
.dashboard-container {
  max-width: 900px;
  margin: 40px auto;
  padding: 32px;
  background: #fff;
  box-shadow: 0 6px 24px rgba(0,0,0,0.10);
  border-radius: 12px;
  border: 1px solid #e3e3e3;
  position: relative;
}

h2 {
  text-align: center;
  color: #007bff;
  margin-bottom: 20px;
}

.logout-btn {
  float: right;
  padding: 8px 16px;
  background: #dc3545;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.product-section, .add-product-section {
  margin-top: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f8f9fa;
}

.edit-btn, .delete-btn, .add-btn, .save-btn, .cancel-btn, .confirm-btn {
  padding: 6px 12px;
  margin-right: 5px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.edit-btn {
  background-color: #007bff; /* Blue color for Edit button */
  color: #fff;
}

.edit-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.delete-btn {
  background-color: #dc3545;
  color: #fff;
}

.delete-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.add-btn {
  background-color: #28a745;
  color: #fff;
}

.save-btn {
  background-color: #28a745;
  color: #fff;
}

.cancel-btn {
  background-color: #6c757d;
  color: #fff;
}

.confirm-btn {
  background-color: #dc3545;
  color: #fff;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 15px;
}

label {
  font-weight: 500;
}

input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

input[type="number"] {
  -moz-appearance: textfield;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.error-msg, .success-msg {
  margin-top: 15px;
  padding: 10px;
  text-align: center;
  border-radius: 4px;
}

.error-msg {
  color: #dc3545;
  background: #fff0f2;
  border: 1px solid #f5c2c7;
}

.success-msg {
  color: #28a745;
  background: #eafaf1;
  border: 1px solid #b7e4c7;
}

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
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 5px;
  text-align: center;
  max-width: 400px;
  width: 90%;
}

.modal-content h3 {
  margin-bottom: 15px;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.button-group {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 15px;
}
</style>