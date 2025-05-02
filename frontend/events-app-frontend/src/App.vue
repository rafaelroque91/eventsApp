<template>
  <div>
    <AppNavbar @open-create-modal="handleOpenCreateModal" />
    <main class="container mt-4">
      <RouterView
          :key="routerViewKey"
          @open-view-modal="handleOpenViewModal"
          :open-create-modal-trigger="createModalTrigger"
      />
    </main>

    <EventModal
        :show="isModalVisible"
        :event-id="selectedEventId"
        :mode="modalMode"
        @close="closeModal"
        @event-saved="handleEventSaved"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterView } from 'vue-router';
import AppNavbar from '@/components/AppNavbar.vue';
import EventModal from '@/components/EventModal.vue';

const isModalVisible = ref(false);
const selectedEventId = ref(null);
const modalMode = ref('view'); // 'view' or 'create'
const createModalTrigger = ref(0);

const routerViewKey = ref(0);

const handleOpenViewModal = (eventId) => {
  selectedEventId.value = eventId;
  modalMode.value = 'view';
  isModalVisible.value = true;
};

const handleOpenCreateModal = () => {
  selectedEventId.value = null;
  modalMode.value = 'create';
  isModalVisible.value = true;
  createModalTrigger.value++;
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedEventId.value = null;
};

const handleEventSaved = () => {
  closeModal();
  routerViewKey.value++;
  console.log('Event saved, forcing RouterView reload by incrementing key to:', routerViewKey.value); // Log para depuração
};

</script>

<style>
/* Adicionar estilos globais aqui se necessário, ou em main.css */
body {
  background-color: #f8f9fa; /* Um cinza claro para o fundo */
}
</style>