<template>
  <div>
    <h1 class="mb-4">Events List</h1>

    <div class="card mb-4">
      <!-- Updated Card Header -->
      <div class="card-header">Filters & Sorting</div>
      <div class="card-body">
        <!-- Filter Row -->
        <div class="row g-3 mb-3">
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
        </div>

        <div class="row g-3 align-items-end mb-3">
          <div class="col-md-6">
            <label for="sortFieldSelect" class="form-label">Sort By</label>
            <select id="sortFieldSelect" class="form-select" v-model="sortField" @change="applySorting" :disabled="isLoading">
              <option value="">-- Default Order --</option>
              <option value="title">Title</option>
              <option value="description">Description</option>
              <option value="startDate">Start Date</option>
              <option value="endDate">End Date</option>
            </select>
          </div>
        </div>

        <!-- Reset Button Row -->
        <div class="row">
          <div class="col-12 d-flex justify-content-end align-items-center">
            <!-- Filter/Sort Loading Indicator -->
            <div v-if="isLoading" class="me-3 text-primary">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span class="ms-1">Applying changes...</span>
            </div>
            <button class="btn btn-secondary" @click="resetFiltersAndSort" :disabled="isLoading">Reset All</button>
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
      <!-- Optional: Add subtle opacity while filtering/sorting -->
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
import { debounce } from 'lodash-es';

const emit = defineEmits(['open-view-modal']);

const events = ref([]);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const errorMessage = ref('');
const currentPage = ref(1);
const lastPage = ref(1);

// Filters
const filters = reactive({
  title: '',
  description: '',
  startDate: '',
  endDate: ''
});

// --- NEW: Sorting State (Simplified) ---
const sortField = ref(''); // e.g., 'title', 'startDate', '' for none

// Computed
const canLoadMore = computed(() => currentPage.value < lastPage.value);

// Fetch Events Function (Includes Nested Filters and Sorting)
const fetchEvents = async (page = 1, loadMore = false) => {
  if (!loadMore) {
    isLoading.value = true;
  } else {
    isLoadingMore.value = true;
  }
  errorMessage.value = '';

  const params = { page };

  // Add Filters (Nested)
  const filterData = {};
  for (const key in filters) {
    if (filters[key]) {
      filterData[key] = filters[key];
    }
  }
  if (Object.keys(filterData).length > 0) {
    params.filter = filterData;
  }

  if (sortField.value) {
    params.orderBy = sortField.value; // Just the field name
  }

  console.log("Sending params:", params); // Log for debugging

  try {
    const response = await EventService.getEvents(params);
    if (response.data && response.data.data && response.data.meta && response.data.meta.page) {
      const newEvents = response.data.data;
      const pageInfo = response.data.meta.page;

      if (loadMore) {
        events.value = [...events.value, ...newEvents];
      } else {
        // Replace events on initial load, filter, or sort change
        events.value = newEvents;
      }
      currentPage.value = pageInfo.current_page;
      lastPage.value = pageInfo.last_page;
    } else {
      console.warn("API response structure might be different:", response.data);
      if (!loadMore) events.value = [];
      errorMessage.value = "Received unexpected data structure from server.";
    }
  } catch (error) {
    console.error('Error fetching events:', error);
    errorMessage.value = 'Failed to load events. ' + (error.response?.data?.message || error.message);
    if (!loadMore) events.value = [];
  } finally {
    if (!loadMore) {
      isLoading.value = false;
    } else {
      isLoadingMore.value = false;
    }
  }
};

// Load More Events
const loadMoreEvents = () => {
  if (canLoadMore.value && !isLoading.value && !isLoadingMore.value) {
    fetchEvents(currentPage.value + 1, true);
  }
};

// --- NEW: Apply Sorting ---
// Function to apply sorting (called when sort field changes)
const applySorting = () => {
  if (isLoading.value || isLoadingMore.value) return; // Prevent re-sorting while loading
  currentPage.value = 1; // Reset to first page when sorting changes
  fetchEvents(1, false); // Fetch page 1 with new sort order
}

// Apply Filters (Directly or Debounced)
// This function now implicitly handles the current sort order via fetchEvents
const applyFilters = () => {
  if (isLoading.value || isLoadingMore.value) return;
  currentPage.value = 1; // Reset page when filters change
  fetchEvents(1, false); // Fetch page 1 with current filters and sort
}

// Debounced version for text inputs
const applyFiltersDebounced = debounce(applyFilters, 500);

// --- UPDATED: Reset Filters and Sorting ---
// Function to reset filters AND sorting
const resetFiltersAndSort = () => {
  if (isLoading.value || isLoadingMore.value) return; // Prevent reset while loading

  // Reset Filters
  filters.title = '';
  filters.description = '';
  filters.startDate = '';
  filters.endDate = '';

  // Reset Sorting Field
  sortField.value = '';

  // Apply changes (will fetch with no filters and default/no sort)
  applyFilters(); // applyFilters calls fetchEvents which now respects the cleared sortField
}


// Format Date Function
const formatDate = (dateString) => {
  if (!dateString || dateString.startsWith('0001-01-01')) {
    return 'N/A';
  }
  try {
    const date = new Date(dateString);
    // Use UTC methods to avoid timezone issues with date-only strings
    const year = date.getUTCFullYear();
    const month = (date.getUTCMonth() + 1).toString().padStart(2, '0');
    const day = date.getUTCDate().toString().padStart(2, '0');

    if (isNaN(year)) return 'Invalid Date'; // Check validity

    // Create a new Date object using UTC values for consistent display formatting
    const displayDate = new Date(Date.UTC(year, date.getUTCMonth(), day));
    return displayDate.toLocaleDateString(undefined, {
      year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' // Specify UTC for formatting consistency
    });

  } catch (e) {
    console.error("Error formatting date:", dateString, e);
    return 'Invalid Date';
  }
};

// Emit Event for Modal
const emitOpenViewModal = (eventId) => {
  if (isLoading.value) return; // Prevent opening modal while list is refreshing
  emit('open-view-modal', eventId);
}

// Initial Fetch on Mount
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

/* Style for list opacity during filtering/sorting */
.opacity-75 {
  opacity: 0.75;
  transition: opacity 0.15s ease-in-out;
}
</style>