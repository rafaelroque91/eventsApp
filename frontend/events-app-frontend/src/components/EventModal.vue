<template>
  <div v-if="show" class="modal-backdrop" @click.self="closeModal">
    <div class="modal-content">
      <h2>{{ isCreating ? 'Create New Event' : 'Event Details' }}</h2>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="title">Title:</label>
          <input type="text" id="title" v-model="editableEvent.title" :disabled="!isCreating" required>
        </div>
        <div class="form-group">
          <label for="description">Description:</label>
          <textarea id="description" v-model="editableEvent.description" :disabled="!isCreating"></textarea>
        </div>
        <div class="form-group">
          <label for="startDate">Start Date:</label>
          <input type="date" id="startDate" v-model="editableEvent.startDate" :disabled="!isCreating" required>
        </div>
        <div class="form-group">
          <label for="endDate">End Date:</label>
          <input type="date" id="endDate" v-model="editableEvent.endDate" :disabled="!isCreating" required>
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        <div v-if="isLoading" class="loading-message">
          Loading / Saving...
        </div>


        <div class="modal-actions">
          <button type="button" @click="closeModal" :disabled="isLoading">Close</button>
          <button v-if="isCreating" type="submit" :disabled="isLoading">
            {{ isLoading ? 'Saving...' : 'Save Event' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, defineProps, defineEmits, reactive } from 'vue';
import EventService from '@/services/EventService';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  eventId: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['close', 'event-saved']);

const isLoading = ref(false);
const errorMessage = ref('');

const editableEvent = reactive({
  id: null,
  title: '',
  description: '',
  startDate: '',
  endDate: ''
});

const isCreating = computed(() => !props.eventId);

const resetForm = () => {
  editableEvent.id = null;
  editableEvent.title = '';
  editableEvent.description = '';
  editableEvent.startDate = ''; // Ou new Date().toISOString().split('T')[0];
  editableEvent.endDate = '';   // Ou new Date().toISOString().split('T')[0];
  errorMessage.value = ''; // Limpa mensagens de erro
};
const fetchEventDetails = async () => {
  if (props.eventId && props.show) {
    isLoading.value = true;
    errorMessage.value = '';
    resetForm();
    try {
      const response = await EventService.getEventById(props.eventId);
      const eventData = response.data;

      editableEvent.id = eventData.id;
      editableEvent.title = eventData.title || ''; // Lida com null
      editableEvent.description = eventData.description || ''; // Lida com null

      editableEvent.startDate = eventData.startDate && !eventData.startDate.startsWith('0001')
          ? new Date(eventData.startDate).toISOString().split('T')[0]
          : '';
      editableEvent.endDate = eventData.endDate && !eventData.endDate.startsWith('0001')
          ? new Date(eventData.endDate).toISOString().split('T')[0]
          : '';

    } catch (error) {
      console.error('Error fetching event details:', error);
      errorMessage.value = 'Failed to load event details. ' + (error.response?.data?.message || error.message);
      // Talvez fechar o modal ou mostrar erro persistente
      // closeModal(); // Descomente se quiser fechar em caso de erro
    } finally {
      isLoading.value = false;
    }
  } else if (!props.eventId && props.show) {
    // Se está abrindo em modo de criação, reseta o formulário
    resetForm();
  }
};
watch(() => [props.show, props.eventId], () => {
  if (props.show) {
    if (isCreating.value) {
      resetForm();
    } else {
      fetchEventDetails();
    }
  } else {
    resetForm();
  }
}, { immediate: true });

const handleSubmit = async () => {
  if (!isCreating.value) return;

  isLoading.value = true;
  errorMessage.value = '';

  const eventPayload = {
    title: editableEvent.title,
    description: editableEvent.description,
    startDate: editableEvent.startDate,
    endDate: editableEvent.endDate,
  };

  try {
    await EventService.createEvent(eventPayload);
    emit('event-saved');
    closeModal();
  } catch (error) {
    console.error('Error creating event:', error);
    errorMessage.value = 'Failed to save event. ' + (error.response?.data?.message || error.response?.data?.title || error.message);
  } finally {
    isLoading.value = false;
  }
};

const closeModal = () => {
  if (!isLoading.value) {
    resetForm();
    emit('close');
  }
};

</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  min-width: 300px;
  max-width: 500px;
  width: 90%;
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input[type="text"],
.form-group textarea,
.form-group input[type="date"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.form-group input:disabled,
.form-group textarea:disabled {
  background-color: #f0f0f0;
  cursor: not-allowed;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.modal-actions button {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.modal-actions button[type="submit"] {
  background-color: #4CAF50;
  color: white;
}
.modal-actions button[type="submit"]:disabled {
  background-color: #aaa;
}

.modal-actions button[type="button"] {
  background-color: #f44336;
  color: white;
}
.modal-actions button[type="button"]:disabled {
  background-color: #aaa;
}

.error-message {
  color: #D8000C;
  background-color: #FFD2D2;
  border: 1px solid #D8000C;
  padding: 10px;
  border-radius: 4px;
  margin-top: 15px;
  margin-bottom: 15px;
  text-align: center;
  font-size: 0.9em;
}

.loading-message {
  text-align: center;
  padding: 10px;
  color: #555;
  font-style: italic;
}
</style>