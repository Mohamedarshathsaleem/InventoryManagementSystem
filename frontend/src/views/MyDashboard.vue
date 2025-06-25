<template>
  <div class="dashboard">
    <header>
      <h1>Product Management Dashboard</h1>
      <button @click="logout">Logout</button>
    </header>

    <main>
      <section>
        <h2>Products</h2>
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
              <td>
                <input v-if="editId === product.id" v-model="editProduct.name" />
                <span v-else>{{ product.name }}</span>
              </td>
              <td>
                <input v-if="editId === product.id" v-model="editProduct.price" type="number" />
                <span v-else>{{ product.price }}</span>
              </td>
              <td>
                <input v-if="editId === product.id" v-model="editProduct.stock" type="number" />
                <span v-else>{{ product.stock }}</span>
              </td>
              <td>
                <button v-if="editId === product.id" @click="updateProduct(product.id)">Save</button>
                <button v-if="editId === product.id" @click="cancelEdit">Cancel</button>
                <button v-else @click="startEdit(product)">Edit</button>
                <button @click="deleteProduct(product.id)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section>
        <h2>Add Product</h2>
        <form @submit.prevent="addProduct">
          <input v-model="newProduct.name" placeholder="Name" required />
          <input v-model="newProduct.price" type="number" placeholder="Price" required />
          <input v-model="newProduct.stock" type="number" placeholder="Stock" required />
          <button type="submit">Add</button>
        </form>
      </section>

      <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
      <div v-if="successMessage" class="success">{{ successMessage }}</div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'MyDashboard',
  data() {
    return {
      products: [],
      newProduct: { name: '', price: '', stock: '' },
      editId: null,
      editProduct: {},
      errorMessage: '',
      successMessage: ''
    };
  },
  created() {
    this.fetchProducts();
  },
  methods: {
    getAuthHeaders() {
      return {
        'Content-Type': 'application/json',
        Authorization: 'Bearer ' + localStorage.getItem('jwt_token')
      };
    },
    async fetchProducts() {
      try {
        const res = await fetch('http://localhost:8000/api/products', {
          headers: this.getAuthHeaders()
        });
        this.products = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load products.';
      }
    },
    async addProduct() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const res = await fetch('http://localhost:8000/api/products', {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: JSON.stringify(this.newProduct)
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = 'Product added!';
          this.newProduct = { name: '', price: '', stock: '' };
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to add product.';
        }
      } catch {
        this.errorMessage = 'Failed to add product.';
      }
    },
    startEdit(product) {
      this.editId = product.id;
      this.editProduct = { ...product };
    },
    cancelEdit() {
      this.editId = null;
      this.editProduct = {};
    },
    async updateProduct(id) {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const res = await fetch(`http://localhost:8000/api/products/${id}`, {
          method: 'PUT',
          headers: this.getAuthHeaders(),
          body: JSON.stringify(this.editProduct)
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = 'Product updated!';
          this.editId = null;
          this.editProduct = {};
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to update product.';
        }
      } catch {
        this.errorMessage = 'Failed to update product.';
      }
    },
    async deleteProduct(id) {
      this.errorMessage = '';
      this.successMessage = '';
      if (!confirm('Are you sure you want to delete this product?')) return;
      try {
        const res = await fetch(`http://localhost:8000/api/products/${id}`, {
          method: 'DELETE',
          headers: this.getAuthHeaders()
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = 'Product deleted!';
          this.fetchProducts();
        } else {
          this.errorMessage = data.error || 'Failed to delete product.';
        }
      } catch {
        this.errorMessage = 'Failed to delete product.';
      }
    },
    logout() {
      localStorage.removeItem('jwt_token');
      localStorage.removeItem('user_info');
      this.$router.push('/');
    }
  }
};
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 30px;
  background-color: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  color: #343a40;
}

header h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #007bff;
}

header button {
  padding: 8px 15px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s ease;
}

header button:hover {
  background-color: #c82333;
}

main {
  flex-grow: 1;
  padding: 30px;
  background-color: #f0f2f5;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
}

th {
  background: #007bff;
  color: #fff;
}

.error {
  color: #dc3545;
  margin-top: 10px;
}

.success {
  color: #28a745;
  margin-top: 10px;
}
</style>