import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'http://localhost:50000/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

export default {
    getEvents(params = {}) {
        console.log('Fetching events with params:', params);
        return apiClient.get('/events', { params });
    },

    getEventById(eventId) {
        console.log(`Workspaceing event with ID: ${eventId}`);
        return apiClient.get(`/events/${eventId}`);
    },

    createEvent(eventData) {
        console.log('Creating event with data:', eventData);
        const payload = {
            title: eventData.title,
            description: eventData.description,
            startDate: eventData.startDate,
            endDate: eventData.endDate,
        };
        return apiClient.post('/events', payload);
    }
}