import axios from 'axios';

const apiClient = axios.create({
    headers: {
        'Content-Type': 'application/json',
    }
});

export default {
    getEvents(page = 1) {
        console.log(`Get events for page: ${page}`);
        return apiClient.get('api/v1/events', { params: { page} });
    },

    getEventById(eventId) {
        console.log(`Get event with ID: ${eventId}`);
        return apiClient.get(`api/v1/events/${eventId}`);
    },

    createEvent(eventData) {
        console.log('Creating event with data:', eventData);
        const payload = {
            title: eventData.title,
            description: eventData.description,
            startDate: eventData.startDate,
            endDate: eventData.endDate,
        };
        return apiClient.post('api/v1/events', payload);
    }
}