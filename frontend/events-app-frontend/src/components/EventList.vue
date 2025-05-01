<template>
    <div>
        <h2>Events</h2>
        <div v-if="loading" class="text-center">
            <!-- ... spinner ... -->
        </div>
        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>
        <div v-else-if="events.length === 0" class="alert alert-info">
            No events found.
        </div>
        <ul v-else class="list-group">
            <li v-for="event in events" :key="event.id" class="list-group-item ...">
            <div>
                <h5>{{ event.title }}</h5>
                <small>
                    {{ formatDate(event.startDate) }} - {{ formatDate(event.endDate) }}
                </small>
            </div>
            <!-- Consider using Vue Router for navigation instead of raw href -->
            <a :href="'#'" @click.prevent="viewDetails(event.id)" class="btn btn-sm btn-outline-primary">View Details</a>
    </li>
</ul>
</div>
</template>

<script setup>
    // Use <script setup> for Composition API sugar
    import { ref, onMounted } from 'vue';
    import { fetchEvents } from '../services/api.js'; // Correct path

    const events = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const loadEvents = async () => {
        loading.value = true;
        error.value = null;
        try {
        events.value = await fetchEvents();
    } catch (err) {
        error.value = 'Failed to load events. Please try again.';
        // Error details logged by interceptor in api.js
    } finally {
        loading.value = false;
    }
    };

    const formatDate = (dateString) => {
        // ... (same formatting logic) ...
        if (!dateString) return 'N/A';
        try {
        return new Date(dateString).toLocaleString();
    } catch (e) {
        console.error("Error formatting date:", dateString, e);
        return 'Invalid Date';
    }
    };

    const viewDetails = (eventId) => {
        // TODO: Implement navigation, perhaps using Vue Router
        console.log("Navigate to details for event:", eventId);
        alert(`Implement navigation to details for event ID: ${eventId}`);
    }

    // Expose method for parent/refresh (if needed via ref)
    defineExpose({
        loadEvents
    });

    onMounted(loadEvents);

    // No explicit return needed with <script setup>, everything defined is exposed
    </script>

    <style scoped>
        /* Add component-specific styles here if needed */
        .list-group-item {
        /* Example style */
        margin-bottom: 10px;
    }
    </style>
