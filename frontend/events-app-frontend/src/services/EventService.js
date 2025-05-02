// src/services/EventService.js
import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'http://localhost:50000/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json', // É bom incluir o Accept
    }
});

export default {
    // --- Listar Eventos com Filtros e Paginação ---
    getEvents(params = {}) {
        // params pode conter: page, title, description, startDate, endDate
        console.log('Fetching events with params:', params);
        return apiClient.get('/events', { params });
    },

    // --- Buscar Evento por ID ---
    getEventById(eventId) {
        console.log(`Workspaceing event with ID: ${eventId}`);
        return apiClient.get(`/events/${eventId}`);
    },

    // --- Criar Evento ---
    createEvent(eventData) {
        console.log('Creating event with data:', eventData);
        // Payload conforme o último exemplo fornecido
        const payload = {
            title: eventData.title,
            description: eventData.description,
            startDate: eventData.startDate, // Espera-se YYYY-MM-DD
            endDate: eventData.endDate,     // Espera-se YYYY-MM-DD
            teste: "123123" // Incluído conforme o exemplo de payload POST
        };
        return apiClient.post('/events', payload);
    }
}