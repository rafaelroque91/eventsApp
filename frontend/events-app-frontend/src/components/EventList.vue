<template>
  <div>
    <h1 class="mb-4">Events List</h1>

    <div class="card mb-4">
      <div class="card-header">Filters</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="titleFilter" class="form-label">Title</label>
            <input type="text" class="form-control" id="titleFilter" v-model="filters.title" @input="applyFiltersDebounced">
          </div>
          <div class="col-md-6">
            <label for="descFilter" class="form-label">Description</label>
            <input type="text" class="form-control" id="descFilter" v-model="filters.description" @input="applyFiltersDebounced">
          </div>
          <div class="col-md-6">
            <label for="startDateFilter" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="startDateFilter" v-model="filters.startDate" @change="applyFilters">
          </div>
          <div class="col-md-6">
            <label for="endDateFilter" class="form-label">End Date</label>
            <input type="date" class="form-control" id="endDateFilter" v-model="filters.endDate" @change="applyFilters">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-secondary" @click="resetFilters">Reset Filters</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading && events.length === 0" class="text-center mt-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p>Loading events...</p>
    </div>
    <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <div v-else-if="events.length > 0">
      <div class="list-group">
        <div v-for="event in events" :key="event.id" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <div class="flex-grow-1 me-3">
            <h5 class="mb-1">{{ event.title }}</h5>
            <p class="mb-1 text-muted">{{ event.description || 'No description' }}</p>
            <small>
              {{ formatDate(event.startDate) }} - {{ formatDate(event.endDate) }}
            </small>
          </div>
          <button class="btn btn-outline-primary btn-sm flex-shrink-0" @click="emitOpenViewModal(event.id)">
            View Details
          </button>
        </div>
      </div>

      <div class="text-center mt-4 mb-4">
        <button
            v-if="canLoadMore"
            class="btn btn-primary"
            @click="loadMoreEvents"
            :disabled="isLoadingMore"
        >
          <span v-if="isLoadingMore" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          {{ isLoadingMore ? 'Loading...' : 'Load More' }}
        </button>
        <p v-else-if="!isLoading && events.length > 0" class="text-muted">No more events to load.</p>
      </div>

    </div>
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
const isLoading = ref(false);
const isLoadingMore = ref(false); // Loading específico para "Load More"
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

// Função para buscar eventos
const fetchEvents = async (page = 1, loadMore = false) => {
  if (!loadMore) {
    isLoading.value = true; // Loading principal para nova busca/filtro
  } else {
    isLoadingMore.value = true; // Loading específico para "load more"
  }
  errorMessage.value = '';

  // Prepara parâmetros removendo filtros vazios
  const params = { page };
  for (const key in filters) {
    if (filters[key]) {
      params[key] = filters[key];
    }
  }

  try {
    const response = await EventService.getEvents(params);
    if (response.data && response.data.data && response.data.meta && response.data.meta.page) {
      const newEvents = response.data.data;
      const pageInfo = response.data.meta.page;

      if (loadMore) {
        events.value = [...events.value, ...newEvents]; // Adiciona aos existentes
      } else {
        events.value = newEvents; // Substitui pelos novos resultados
      }
      currentPage.value = pageInfo.current_page;
      lastPage.value = pageInfo.last_page;
    } else {
      console.warn("API response structure might be different:", response.data);
      if (!loadMore) events.value = []; // Limpa se for busca inicial/filtro
      errorMessage.value = "Received unexpected data structure from server.";
    }

  } catch (error) {
    console.error('Error fetching events:', error);
    errorMessage.value = 'Failed to load events. ' + (error.response?.data?.message || error.message);
    if (!loadMore) events.value = []; // Limpa a lista em caso de erro na busca inicial/filtro
  } finally {
    if (!loadMore) {
      isLoading.value = false;
    } else {
      isLoadingMore.value = false;
    }
  }
};

// Função para carregar mais eventos
const loadMoreEvents = () => {
  if (canLoadMore.value) {
    fetchEvents(currentPage.value + 1, true);
  }
};

// Função para aplicar filtros (chamada diretamente ou via debounce)
const applyFilters = () => {
  currentPage.value = 1; // Reseta a página ao aplicar filtros
  fetchEvents(1, false); // Busca a primeira página com os novos filtros
}

// Versão com Debounce para campos de texto
// Instalar lodash-es: npm install lodash-es
const applyFiltersDebounced = debounce(applyFilters, 500); // Espera 500ms após parar de digitar

// Função para resetar filtros
const resetFilters = () => {
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
</style>