<template>
  <div class="modal fade" :class="{ 'show': show, 'd-block': show }" tabindex="-1" aria-hidden="true" @click.self="closeModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ modalTitle }}</h5>
          <button type="button" class="btn-close" @click="closeModal" aria-label="Close" :disabled="isLoading"></button>
        </div>
        <div class="modal-body">
          <div v-if="isLoading" class="text-center my-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">{{ isCreating ? 'Saving...' : 'Loading details...' }}</p>
          </div>

          <div v-if="errorMessage && !isLoading" class="alert alert-danger" role="alert">
            {{ errorMessage }}
          </div>

          <form v-if="!isLoading" @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label for="eventTitle" class="form-label">Title</label>
              <input
                  type="text"
                  class="form-control"
                  id="eventTitle"
                  v-model="editableEvent.title"
                  :disabled="!isCreating"
                  required
              >
            </div>
            <div class="mb-3">
              <label for="eventDescription" class="form-label">Description</label>
              <textarea
                  class="form-control"
                  id="eventDescription"
                  rows="3"
                  v-model="editableEvent.description"
                  :disabled="!isCreating"
              ></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="eventStartDate" class="form-label">Start Date</label>
                <input
                    type="date"
                    class="form-control"
                    id="eventStartDate"
                    v-model="editableEvent.startDate"
                    :disabled="!isCreating"
                    required
                >
              </div>
              <div class="col-md-6 mb-3">
                <label for="eventEndDate" class="form-label">End Date</label>
                <input
                    type="date"
                    class="form-control"
                    id="eventEndDate"
                    v-model="editableEvent.endDate"
                    :disabled="!isCreating"
                    required
                >
              </div>
            </div>
            <div class="modal-footer pb-0"> <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Close</button>
              <button v-if="isCreating" type="submit" class="btn btn-primary" :disabled="isLoading">
                {{ isLoading ? 'Saving...' : 'Save Event' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div v-if="show" class="modal-backdrop fade show"></div>
</template>

<script setup>
import { ref, watch, computed, defineProps, defineEmits, reactive, nextTick } from 'vue';
import EventService from '@/services/EventService';

const props = defineProps({
  show: Boolean,
  eventId: String,
  mode: { // 'view' or 'create'
    type: String,
    default: 'view'
  }
});

const emit = defineEmits(['close', 'event-saved']);

const isLoading = ref(false);
const errorMessage = ref('');

// Estado reativo para os dados do formulário/visualização
const editableEvent = reactive({
  id: null,
  title: '',
  description: '',
  startDate: '',
  endDate: '',
  teste: '123123' // Valor fixo conforme payload de exemplo POST
});

// Determina se o modal está em modo de criação ou visualização
const isCreating = computed(() => props.mode === 'create');
const modalTitle = computed(() => isCreating.value ? 'Create New Event' : 'Event Details');

// Função para limpar o formulário
const resetForm = () => {
  // Não resetar 'teste' se for um valor fixo necessário
  editableEvent.id = null;
  editableEvent.title = '';
  editableEvent.description = '';
  editableEvent.startDate = '';
  editableEvent.endDate = '';
  errorMessage.value = '';
};

// Função para buscar detalhes do evento (View Mode)
const fetchEventDetails = async () => {
  if (!isCreating.value && props.eventId && props.show) {
    isLoading.value = true;
    errorMessage.value = '';
    resetForm();
    try {
      const response = await EventService.getEventById(props.eventId);
      // A API de detalhe pode retornar o evento diretamente ou dentro de 'data'
      const eventData = response.data.data || response.data; // Ajuste conforme necessário

      editableEvent.id = eventData.id;
      editableEvent.title = eventData.title || '';
      editableEvent.description = eventData.description || '';

      // Formata datas YYYY-MM-DD para input date
      editableEvent.startDate = eventData.startDate && !eventData.startDate.startsWith('0001')
          ? eventData.startDate.split('T')[0] // Assume YYYY-MM-DD ou ISO
          : '';
      editableEvent.endDate = eventData.endDate && !eventData.endDate.startsWith('0001')
          ? eventData.endDate.split('T')[0] // Assume YYYY-MM-DD ou ISO
          : '';

    } catch (error) {
      console.error('Error fetching event details:', error);
      errorMessage.value = 'Failed to load event details. ' + (error.response?.data?.message || error.message);
    } finally {
      isLoading.value = false;
    }
  }
};

// Observa mudanças em 'show' para buscar dados ou resetar
watch(() => props.show, (newValue) => {
  if (newValue) {
    // nextTick garante que o DOM do modal esteja potencialmente pronto
    nextTick(() => {
      if (isCreating.value) {
        resetForm();
      } else {
        fetchEventDetails();
      }
    });
  } else {
    // Limpa ao fechar externamente (pode ser redundante se resetForm já for chamado)
    // resetForm();
  }
}, { immediate: true }); // 'immediate' pode causar chamadas iniciais, ajuste se necessário

// Handler para submit (Create Mode)
const handleSubmit = async () => {
  if (!isCreating.value) return;

  isLoading.value = true;
  errorMessage.value = '';

  // Prepara payload
  const eventPayload = {
    title: editableEvent.title,
    description: editableEvent.description,
    startDate: editableEvent.startDate, // Já deve estar YYYY-MM-DD
    endDate: editableEvent.endDate,     // Já deve estar YYYY-MM-DD
    teste: editableEvent.teste // Inclui o campo 'teste'
  };

  try {
    await EventService.createEvent(eventPayload);
    emit('event-saved'); // Avisa o App.vue que salvou
    // closeModal(); // O App.vue agora controla o fechamento no 'event-saved'
  } catch (error) {
    console.error('Error creating event:', error);
    // Tenta pegar a mensagem de erro da resposta da API
    let apiErrorMsg = 'An unknown error occurred.';
    if (error.response && error.response.data) {
      if (typeof error.response.data === 'string') {
        apiErrorMsg = error.response.data;
      } else if (error.response.data.message) {
        apiErrorMsg = error.response.data.message;
      } else if (error.response.data.errors) {
        // Se houver um objeto de erros (comum em validação Laravel/Symfony)
        apiErrorMsg = Object.values(error.response.data.errors).flat().join(' ');
      } else if (error.response.data.title) { // Outro formato possível
        apiErrorMsg = error.response.data.title;
      }
    } else {
      apiErrorMsg = error.message;
    }
    errorMessage.value = `Failed to save event: ${apiErrorMsg}`;
  } finally {
    isLoading.value = false;
  }
};

// Função para fechar o modal (emitindo evento)
const closeModal = () => {
  if (!isLoading.value) {
    emit('close');
  }
};

</script>

<style scoped>
/* Estilo para garantir que o modal seja exibido corretamente com v-if/v-show */
.modal.d-block {
  display: block;
}
/* Adiciona um fundo escuro quando o modal está ativo */
.modal-backdrop.show {
  opacity: 0.5;
}
/* Remove o padding duplo no footer se os botões estiverem dentro do form */
.modal-footer.pb-0 {
  padding-bottom: 0 !important;
}
.modal-body {
  max-height: 70vh; /* Limita altura do corpo e permite scroll se necessário */
  overflow-y: auto;
}

</style>