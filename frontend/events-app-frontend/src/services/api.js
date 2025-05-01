import axios from 'axios';

const api = axios.create({
    // Optional: Add baseURL if all requests share a common prefix
    // baseURL: '/api/v1'
});

api.interceptors.response.use(
    response => response.data, // Return data directly on success
    error => {
        console.error('API Error:', error.response || error.message);
        // Provide user feedback (e.g., using a toast library or simple alert)

        // TODO: Remove later - only for development
        alert(`Error: ${error.response?.data?.error?.message || error.message}`);
        return Promise.reject(error); // Propagate the error
    }
);

// Use full paths now, or configure baseURL above
export const fetchEvents = () => api.get('/api/v1/events');
export const addEvent = (eventData) => api.post('/api/v1/events', eventData);

// Export the instance if needed elsewhere, though usually functions are preferred
// export default api;