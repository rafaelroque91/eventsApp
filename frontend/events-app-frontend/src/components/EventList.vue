<template>
  <div class="event-list-container">
    <h1>Events</h1>

    <button @click="openCreateModal" class="create-button">Create New Event</button>

    <div v-if="isLoading" class="loading">Loading events...</div>
    <div v-if="errorMessage" class="error">{{ errorMessage }}</div>

    <ul v-if="events.length > 0" class="event-list">
      <li v-for="event in events" :key="event.id" class="event-item">
        <div class="event-info">
          <strong>{{ event.title }}</strong>
          <p v-if="event.description">{{ event.description }}</p>
          <small>
            Starts: {{ formatDate(event.startDate) }} | Ends: {{ formatDate(event.endDate) }}
          </small>
        </div>
        <button @click="openViewModal(event.id)" class="details-button">View Details</button>
      </li>
    </ul>
    <div v-else-if="!isLoading && events.length === 0 && !errorMessage" class="no-events">
      No events found.
    </div>

    <EventModal
        :show="isModalVisible"
        :event-id="selectedEventId"
        @close="closeModal"
        @event-saved="handleEventSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import EventService from '@/services/EventService';
import EventModal from '@/components/EventModal.vue';

const events = ref([]);
const isLoading = ref(false);
const errorMessage = ref('');
const isModalVisible = ref(false);
const selectedEventId = ref(null);

const fetchEvents = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  events.value = [];
  try {
    //todo add pagination
    const response = await EventService.getEvents(1);

    if (response.data.data && Array.isArray(response.data.data)) {
      events.value = response.data.data;
      console.log('response',response.data.data);
    } else {
      console.warn("Unexpected API response structure:", response.data);
      events.value = [];
    }

  } catch (error) {
    console.error('Error fetching events:', error);
    errorMessage.value = 'Failed to load events. ' + (error.response?.data?.message || error.message);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString || dateString.startsWith('0001-01-01')) {
    return 'N/A';
  }
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
      return 'Invalid Date';
    }
    return date.toLocaleDateString(undefined, {
      year: 'numeric', month: 'short', day: 'numeric'
    });
  } catch (e) {
    console.error("Error formatting date:", dateString, e);
    return 'Invalid Date';
  }
};


const openViewModal = (eventId) => {
  selectedEventId.value = eventId;
  isModalVisible.value = true;
};

const openCreateModal = () => {
  selectedEventId.value = null;
  isModalVisible.value = true;
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedEventId.value = null;
};

const handleEventSaved = () => {
  closeModal();
  fetchEvents();
};

onMounted(() => {
  fetchEvents();
});
</script>

<style scoped>
.event-list-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  font-family: sans-serif;
}

h1 {
  text-align: center;
  margin-bottom: 20px;
}

.create-button {
  display: block;
  margin: 0 auto 25px auto;
  padding: 10px 20px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
}
.create-button:hover {
  background-color: #218838;
}

.loading, .error, .no-events {
  text-align: center;
  padding: 20px;
  margin-top: 20px;
  border-radius: 5px;
}

.loading {
  color: #555;
}

.error {
  color: #D8000C;
  background-color: #FFD2D2;
  border: 1px solid #D8000C;
}
.no-events {
  color: #777;
  font-style: italic;
}


.event-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.event-item {
  background-color: #f9f9f9;
  border: 1px solid #eee;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.event-info {
  flex-grow: 1;
  margin-right: 15px; /* Espaço antes do botão */
}
.event-info strong {
  font-size: 1.1em;
  display: block;
  margin-bottom: 5px;
}
.event-info p {
  margin: 5px 0;
  color: #333;
  font-size: 0.95em;
}
.event-info small {
  color: #666;
  font-size: 0.85em;
}

.details-button {
  padding: 8px 12px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  white-space: nowrap;
}

.details-button:hover {
  background-color: #0056b3;
}
</style>