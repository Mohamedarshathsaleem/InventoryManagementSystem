<template>
  <div class="register-container">
    <h2>Register</h2>
    <form @submit.prevent="register">
      <div>
        <label>Username:</label>
        <input v-model="form.username" required />
      </div>
      <div>
        <label>Password:</label>
        <input v-model="form.password" type="password" required />
      </div>
      <div>
        <label>Email:</label>
        <input v-model="form.email" type="email" required />
      </div>
      <div>
        <label>Full Name:</label>
        <input v-model="form.full_name" required />
      </div>
      <div>
        <label>Role:</label>
        <select v-model="form.role" required>
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button type="submit">Register</button>
    </form>
    <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success">{{ successMessage }}</div>
    <router-link to="/">Back to Login</router-link>
  </div>
</template>

<script>
export default {
  name: 'MyRegister',
  data() {
    return {
      form: {
        username: '',
        password: '',
        email: '',
        role: 'staff',
        full_name: ''
      },
      errorMessage: '',
      successMessage: ''
    };
  },
  methods: {
    async register() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const res = await fetch('http://localhost:8000/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        const data = await res.json();
        if (res.ok && data.message) {
          this.successMessage = data.message + ' You can now log in.';
          this.form = { username: '', password: '', email: '', role: 'staff', full_name: '' };
        } else {
          this.errorMessage = data.error || 'Registration failed.';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    }
  }
};
</script>

<style scoped>
.register-container {
  max-width: 400px;
  margin: 30px auto;
  padding: 30px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
h2 {
  text-align: center;
  color: #007bff;
  margin-bottom: 20px;
}
form > div {
  margin-bottom: 15px;
}
label {
  display: block;
  margin-bottom: 5px;
  color: #333;
}
input, select {
  width: 100%;
  padding: 8px;
  border: 1px solid #bbb;
  border-radius: 4px;
}
button {
  width: 100%;
  padding: 10px;
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}
button:hover {
  background: #0056b3;
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