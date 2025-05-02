// src/main.js
import { createApp } from 'vue'
import App from './App.vue'
import router from './router' // Importa o router

// import './assets/main.css' // Ou seu arquivo CSS principal

const app = createApp(App)

app.use(router) // Usa o router

app.mount('#app')