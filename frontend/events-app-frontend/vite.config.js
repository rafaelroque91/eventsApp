// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      // String shorthand: '/api' -> 'http://localhost:3000/api'
      // Change target to your actual backend server address and port
      '/api': {
        target: 'http://localhost:50000', // <-- YOUR BACKEND ADDRESS
        changeOrigin: true, // Needed for virtual hosted sites
        // Optional: rewrite path if your backend doesn't expect /api prefix
        // rewrite: (path) => path.replace(/^\/api/, '')
      }
      // You can add more proxy rules if needed
      // '/auth': { ... }
    }
  }
})
