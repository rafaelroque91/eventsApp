<template>
  <div class="mt-4 p-4 border rounded">
    <h2>Add New Event</h2>

    <div v-if="submitMessage" :class="['alert', success ? 'alert-success' : 'alert-danger']" role="alert">
      {{ submitMessage }}
    </div>

    <form @submit.prevent="submitForm" novalidate>
      <div class="mb-3">
        <label for="eventTitle" class="form-label">Event Title <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="eventTitle"
            v-model.trim="form.eventTitle"
            required
            :disabled="submitting"
            aria-required="true">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea
            class="form-control"
            id="description"
            rows="3"
            v-model.trim="form.description"
            required
            :disabled="submitting"
            aria-required="true"
        ></textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="startDate" class="form-label">Start Date<span class="text-danger">*</span></label>
          <input
              type="date"
              class="form-control"
              id="startDate"
              v-model="form.startDate"
              required
              :disabled="submitting"
              aria-required="true"
          >
        </div>
        <div class="col-md-6">
          <label for="endDate" class="form-label">End Date<span class="text-danger">*</span></label>
          <input
              type="date"
              class="form-control"
              id="endDate"
              v-model="form.endDate"
              required
              :disabled="submitting"
              :min="form.startDate"
              aria-required="true"
          >
        </div>
        <div v-if="dateError" class="col-12 text-danger mt-2">{{ dateError }}</div>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="submitting || !isFormValid">
        <span v-if="submitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        {{ submitting ? 'Adding...' : 'Add Event' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { addEvent } from '../services/api.js';


console.log('emit 2');


const form = ref({
  eventTitle: '',
  description: '',
  startDate: '',
  endDate: ''
});


const submitting = ref(false);
const submitMessage = ref('');
const success = ref(false);
const dateError = ref('');


const isFormValid = computed(() => {
  return form.value.eventTitle !== '' &&
      form.value.description !== '' &&
      form.value.startDate !== '' &&
      form.value.endDate !== '' &&
      !dateError.value;
});


watch([() => form.value.startDate, () => form.value.endDate], ([start, end]) => {
  dateError.value = '';
  if (start && end) {
    const startDate = new Date(start);
    const endDate = new Date(end);
    if (!isNaN(startDate) && !isNaN(endDate) && endDate < startDate) {
      dateError.value = 'End date cannot be before the start date.';
    }
  }
}, { immediate: false });


const submitForm = async () => {

  if (!isFormValid.value) {
    submitMessage.value = 'Please fill in all required fields correctly.';
    success.value = false;
    return;
  }

  submitting.value = true;
  submitMessage.value = '';
  success.value = false;

  try {
    const payload = { ...form.value };

    const newEvent = await addEvent(payload);

    submitMessage.value = `Event "${newEvent.eventTitle || 'New Event'}" added successfully!`;
    success.value = true;

    resetForm();

    // Need to uncomment defineEmits and this line to actually emit
    // emit('event-added', newEvent);

  } catch (err) {
    submitMessage.value = `Failed to add event. ${err.response?.data?.error?.message || 'Please check console or try again.'}`;
    success.value = false;
  } finally {
    submitting.value = false;
  }
};

const resetForm = () => {
  form.value.eventTitle = '';
  form.value.description = '';
  form.value.startDate = '';
  form.value.endDate = '';
  dateError.value = '';
};

</script>

<style scoped>
.form-label {
  font-weight: bold;
}

.text-danger {
  font-size: 0.875em;
}
</style>