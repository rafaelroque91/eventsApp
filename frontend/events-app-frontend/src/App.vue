<!-- src/App.vue -->
<template>
  <div id="app-container" class="container mt-4">
    <h1>Event Management</h1>
    <!-- EventList component needs a ref if we want to call its methods -->
    <EventList ref="eventListRef" />
    <!-- Listen for the event emitted by the form -->
    <AddEventForm @event-added="handleEventAdded" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import EventList from './components/EventList.vue'; // Import .vue file
import AddEventForm from './components/AddEventForm.vue'; // Import .vue file

// Ref to access the EventList component instance
const eventListRef = ref(null);

// Method to handle the event emitted by AddEventForm
const handleEventAdded = () => {
  // Check if the ref is available and has the method before calling
  if (eventListRef.value && typeof eventListRef.value.loadEvents === 'function') {
    console.log("Refreshing event list after add...");
    eventListRef.value.loadEvents(); // Call the exposed method
  } else {
    console.warn("Could not find event list component or loadEvents method to refresh.");
  }
};
</script>

<style>
/* Add global styles or import a CSS framework like Bootstrap */
/* If using Bootstrap, install it: npm install bootstrap */
/* Then import it in main.js: import 'bootstrap/dist/css/bootstrap.min.css'; */
#app-container {
  max-width: 960px;
  margin: auto;
}
</style>
