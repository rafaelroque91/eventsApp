<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <router-link class="navbar-brand" to="/">EventApp</router-link>
      <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link class="nav-link" active-class="active" aria-current="page" to="/">See Events</router-link>
          </li>
          <li class="nav-item">
            <button class="nav-link btn btn-link" @click="emitOpenCreateModal">Create Event</button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['open-create-modal']);

const emitOpenCreateModal = () => {
  emit('open-create-modal');
};
</script>

<style scoped>
.btn-link.nav-link {
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
}
.btn-link.nav-link:hover,
.btn-link.nav-link.active {
  color: rgba(255, 255, 255, 0.75);
}

.btn-link {
  border: none;
  background: none;
  padding: 0.5rem 1rem;
  vertical-align: baseline;
}
</style>