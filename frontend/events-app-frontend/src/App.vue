<template>
  <header>
  </header>

  <main>
    <RouterView /> </main>

  <footer>
  </footer>
</template>

<script setup>
import { RouterView } from 'vue-router'
</script>

<style scoped>
main {
  padding-top: 20px;
}
</style>