import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    //proxy to all api calls to /api/v1 will be sent to the target
    proxy: {
      '/api/v1': {
        target: 'http://localhost:50000',
        changeOrigin: true,
      }
    }
  }
})
