<template>
  <div class="dashboard">
    <header>
      <h1>Welcome, {{ userDisplayName }} ({{ roleLabel }})</h1>
      <button @click="logout">Logout</button>
    </header>

    <nav>
      <ul>
        <li v-for="item in menu" :key="item.name">
          <router-link :to="item.route">{{ item.name }}</router-link>
        </li>
      </ul>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'MyDashboard',
  data() {
    return {
      currentUserInfo: null,
      menuItems: {
        admin: [
          { name: 'Manage Users', route: '/admin/manage-users' },
          { name: 'Inventory', route: '/admin/inventory' },
          { name: 'Reports', route: '/admin/reports' }
        ],
        staff: [
          { name: 'Inventory', route: '/staff/inventory' },
          { name: 'Orders', route: '/staff/orders' }
        ]
        // Add more roles and menus as needed
      }
    };
  },
  computed: {
    jwtToken() {
      return localStorage.getItem('jwt_token');
    },
    userRole() {
      return this.currentUserInfo ? this.currentUserInfo.role : 'guest';
    },
    userDisplayName() {
      if (this.currentUserInfo && this.currentUserInfo.full_name) {
        return this.currentUserInfo.full_name;
      } else if (this.currentUserInfo && this.currentUserInfo.username) {
        return this.currentUserInfo.username;
      }
      return 'Guest';
    },
    menu() {
      return this.menuItems[this.userRole] || [];
    },
    roleLabel() {
      return {
        admin: 'Admin',
        staff: 'Staff'
      }[this.userRole] || 'User';
    }
  },
  created() {
    this.loadUserInfo();
  },
  mounted() {
    if (!this.jwtToken || this.userRole === 'guest') {
      this.logout();
    }
  },
  methods: {
    loadUserInfo() {
      const userInfoString = localStorage.getItem('user_info');
      if (userInfoString) {
        try {
          this.currentUserInfo = JSON.parse(userInfoString);
        } catch (e) {
          this.currentUserInfo = null;
        }
      } else {
        this.currentUserInfo = null;
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

nav {
  background-color: #343a40;
  padding: 15px 30px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  gap: 25px;
}

nav li a {
  text-decoration: none;
  color: #ffffff;
  font-weight: bold;
  font-size: 1rem;
  padding: 5px 0;
  transition: color 0.2s ease, border-bottom 0.2s ease;
}

nav li a:hover {
  color: #007bff;
  border-bottom: 2px solid #007bff;
}

main {
  flex-grow: 1;
  padding: 30px;
  background-color: #f0f2f5;
}
</style>