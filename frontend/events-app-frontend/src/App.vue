<template>
  <div>
    <AppNavbar @open-create-modal="handleOpenCreateModal" />
    <main class="container mt-4">
      <RouterView :key="$route.fullPath" @open-view-modal="handleOpenViewModal" :open-create-modal-trigger="createModalTrigger" />
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
  // É bom resetar aqui também, embora o modal possa fazer internamente
  selectedEventId.value = null;
};

const handleEventSaved = () => {
  closeModal();
  // Aqui, idealmente, precisaríamos de uma forma de avisar a EventList
  // para recarregar. Uma solução simples é usar a key na RouterView
  // para forçar a remontagem, ou usar um event bus/Pinia/Vuex.
  // A solução com `:key="$route.fullPath"` já ajuda em navegações,
  // mas para refresh pós-save, pode precisar de algo mais.
  // Por enquanto, vamos confiar que o usuário verá a mudança
  // ou que a recarga da página/navegação atualize.
  // Uma solução mais avançada seria necessária para atualização em tempo real.
};

</script>

<style>
/* Adicionar estilos globais aqui se necessário, ou em main.css */
body {
  background-color: #f8f9fa; /* Um cinza claro para o fundo */
}
</style>