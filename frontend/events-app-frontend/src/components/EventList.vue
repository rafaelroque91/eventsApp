<template>
  <div>
    <h1 class="mb-4">Events List</h1>

    <div class="card mb-4">
      <div class="card-header">Filters</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="titleFilter" class="form-label">Title</label>
            <input type="text" class="form-control" id="titleFilter" v-model="filters.title" @input="applyFiltersDebounced" :disabled="isLoading">
          </div>
          <div class="col-md-6">
            <label for="descFilter" class="form-label">Description</label>
            <input type="text" class="form-control" id="descFilter" v-model="filters.description" @input="applyFiltersDebounced" :disabled="isLoading">
          </div>
          <div class="col-md-6">
            <label for="startDateFilter" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="startDateFilter" v-model="filters.startDate" @change="applyFilters" :disabled="isLoading">
          </div>
          <div class="col-md-6">
            <label for="endDateFilter" class="form-label">End Date</label>
            <input type="date" class="form-control" id="endDateFilter" v-model="filters.endDate" @change="applyFilters" :disabled="isLoading">
          </div>
          <div class="col-12 d-flex justify-content-end align-items-center">
            <!-- Filter Loading Indicator -->
            <div v-if="isLoading" class="me-3 text-primary">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span class="ms-1">Applying filters...</span>
            </div>
            <button class="btn btn-secondary" @click="resetFilters" :disabled="isLoading">Reset Filters</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Initial Loading Indicator (only shows when list is empty and loading) -->
    <div v-if="isLoading && events.length === 0" class="text-center mt-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p>Loading events...</p>
    </div>
    <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <!-- Event List -->
    <div v-else-if="events.length > 0">
      <!-- Optional: Add subtle opacity while filtering -->
      <div class="list-group" :class="{ 'opacity-75': isLoading }">
        <div v-for="event in events" :key="event.id" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <div class="flex-grow-1 me-3">
            <h5 class="mb-1">{{ event.title }}</h5>
            <p class="mb-1 text-muted">{{ event.description || 'No description' }}</p>
            <small>
              {{ formatDate(event.startDate) }} - {{ formatDate(event.endDate) }}
            </small>
          </div>
          <button class="btn btn-outline-primary btn-sm flex-shrink-0" @click="emitOpenViewModal(event.id)" :disabled="isLoading">
            View Details
          </button>
        </div>
      </div>

      <!-- Load More Button -->
      <div class="text-center mt-4 mb-4">
        <button
            v-if="canLoadMore"
            class="btn btn-primary"
            @click="loadMoreEvents"
            :disabled="isLoadingMore || isLoading"
        >
          <span v-if="isLoadingMore" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          {{ isLoadingMore ? 'Loading...' : 'Load More' }}
        </button>
        <p v-else-if="!isLoading && !isLoadingMore && events.length > 0" class="text-muted">No more events to load.</p>
      </div>

    </div>
    <!-- No Events Found Message -->
    <div v-else-if="!isLoading && events.length === 0" class="text-center mt-5 text-muted">
      <p>No events found matching your criteria.</p>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, defineEmits } from 'vue';
import EventService from '@/services/EventService';
import { debounce } from 'lodash-es'; // Importar debounce

const emit = defineEmits(['open-view-modal']);

const events = ref([]); // Array para armazenar os eventos
const isLoading = ref(false); // Loading for initial load AND filtering
const isLoadingMore = ref(false); // Loading specific for "Load More"
const errorMessage = ref('');
const currentPage = ref(1);
const lastPage = ref(1);

// Filtros reativos
const filters = reactive({
  title: '',
  description: '',
  startDate: '',
  endDate: ''
});

// Computed property para verificar se pode carregar mais
const canLoadMore = computed(() => currentPage.value < lastPage.value);

// --- START: UPDATED fetchEvents FUNCTION ---
// Função para buscar eventos
const fetchEvents = async (page = 1, loadMore = false) => {
  // Set appropriate loading state
  if (!loadMore) {
    isLoading.value = true; // Use isLoading for filtering/initial load
  } else {
    isLoadingMore.value = true; // Use isLoadingMore for pagination
  }
  errorMessage.value = '';

  // Base parameters always include the page
  const params = { page };

  // Create an object to hold the actual filter values
  const filterData = {};
  for (const key in filters) {
    if (filters[key]) { // Only include filters that have a value
      filterData[key] = filters[key];
    }
  }

  // If there are any active filters, add them under the 'filter' key
  if (Object.keys(filterData).length > 0) {
    params.filter = filterData; // Nest the filters: { page: 1, filter: { title: '...', ... } }
  }

  console.log("Sending params:", params); // Optional: Log to verify structure

  try {
    // Pass the modified params object to the service
    // Axios (or similar) will serialize params.filter correctly
    const response = await EventService.getEvents(params);

    if (response.data && response.data.data && response.data.meta && response.data.meta.page) {
      const newEvents = response.data.data;
      const pageInfo = response.data.meta.page;

      if (loadMore) {
        events.value = [...events.value, ...newEvents];
      } else {
        events.value = newEvents; // Replace with new results (filtering/initial)
      }

      console.log('events', events.value);
      currentPage.value = pageInfo.current_page;
      lastPage.value = pageInfo.last_page;
    } else {
      console.warn("API response structure might be different:", response.data);
      if (!loadMore) events.value = []; // Clear if it's an initial/filter search
      errorMessage.value = "Received unexpected data structure from server.";
    }

  } catch (error) {
    console.error('Error fetching events:', error);
    errorMessage.value = 'Failed to load events. ' + (error.response?.data?.message || error.message);
    if (!loadMore) events.value = []; // Clear list on error during initial/filter search
  } finally {
    // Reset appropriate loading state
    if (!loadMore) {
      isLoading.value = false;
    } else {
      isLoadingMore.value = false;
    }
  }
};
// --- END: UPDATED fetchEvents FUNCTION ---


// Função para carregar mais eventos
const loadMoreEvents = () => {
  if (canLoadMore.value && !isLoading.value && !isLoadingMore.value) { // Prevent double loading
    fetchEvents(currentPage.value + 1, true);
  }
};

// Função para aplicar filtros (chamada diretamente ou via debounce)
const applyFilters = () => {
  if (isLoading.value || isLoadingMore.value) return; // Prevent applying filters while already loading
  currentPage.value = 1; // Reseta a página ao aplicar filtros
  fetchEvents(1, false); // Busca a primeira página com os novos filtros
}

// Versão com Debounce para campos de texto
const applyFiltersDebounced = debounce(applyFilters, 500); // Espera 500ms após parar de digitar

// Função para resetar filtros
const resetFilters = () => {
  if (isLoading.value || isLoadingMore.value) return; // Prevent reset while loading
  filters.title = '';
  filters.description = '';
  filters.startDate = '';
  filters.endDate = '';
  applyFilters(); // Aplica os filtros (vazios) para recarregar
}


// Função para formatar datas
const formatDate = (dateString) => {
  if (!dateString || dateString.startsWith('0001-01-01')) {
    return 'N/A';
  }
  try {
    const date = new Date(dateString);
    // Adiciona verificação se a data vem só como YYYY-MM-DD (sem HORA/TIMEZONE)
    // Nestes casos, new Date() pode interpretar como UTC 00:00, causando erro de 1 dia
    // Solução: Usar UTC para evitar problemas de timezone na conversão simples
    const year = date.getUTCFullYear();
    const month = (date.getUTCMonth() + 1).toString().padStart(2, '0');
    const day = date.getUTCDate().toString().padStart(2, '0');

    if (isNaN(year)) return 'Invalid Date'; // Verifica se a conversão foi válida

    // Retorna no formato local, mas baseado nos componentes UTC extraídos
    const displayDate = new Date(Date.UTC(year, date.getUTCMonth(), day));
    return displayDate.toLocaleDateString(undefined, {
      year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' // Garante consistência
    });

  } catch (e) {
    console.error("Error formatting date:", dateString, e);
    return 'Invalid Date';
  }
};

// Emitir evento para App.vue abrir o modal de detalhes
const emitOpenViewModal = (eventId) => {
  if (isLoading.value) return; // Prevent opening modal while list is refreshing
  emit('open-view-modal', eventId);
}

// Busca os eventos iniciais quando o componente é montado
onMounted(() => {
  fetchEvents(1);
});
</script>

<style scoped>
/* Estilos específicos se Bootstrap não for suficiente */
.list-group-item h5 {
  color: #0d6efd; /* Azul primário do Bootstrap */
}
.card-header {
  font-weight: bold;
}

/* Style for list opacity during filtering */
.opacity-75 {
  opacity: 0.75;
  transition: opacity 0.15s ease-in-out;
}
</style>