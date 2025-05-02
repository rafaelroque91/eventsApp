import { createRouter, createWebHistory } from 'vue-router'
import EventList from '../components/EventList.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'EventList',
            component: EventList
        },
    ]
})

export default router