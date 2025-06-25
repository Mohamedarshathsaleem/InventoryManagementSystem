<template>
  <div class="dashboard-container">
    <h2>Product Management Dashboard</h2>
    <button @click="$router.push('/login')" class="logout-btn">Logout</button>

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
              <button @click="editProduct(product)" class="edit-btn">Edit</button>
              <button @click="deleteProduct(product.id)" class="delete-btn">Delete</button>
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
          <input v-model.number="newProduct.price" id="product-price" type="number" required placeholder="Enter price" />
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
  </div>
</template>

<script>
export default {
  name: 'MyDashboard',
  data() {
    return {
      products: [],
      newProduct: { name: '', price: 0, stock: 0 },
      errorMessage: '',
      successMessage: ''
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
        const res = await fetch('http://localhost:8000/api/products', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(this.newProduct)
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = data.message;
          this.newProduct = { name: '', price: 0, stock: 0 };
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to add product';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    async deleteProduct(id) {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch(`http://localhost:8000/api/products/${id}`, {
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
      }
    },
    editProduct(product) {
      // Implement edit functionality if needed
      console.log('Edit product:', product);
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

.edit-btn, .delete-btn, .add-btn {
  padding: 6px 12px;
  margin-right: 5px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.edit-btn {
  background-color: #007bff;
  color: #fff;
}

.delete-btn {
  background-color: #dc3545;
  color: #fff;
}

.add-btn {
  background-color: #28a745;
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
</style>