import { createRouter, createWebHistory } from 'vue-router';

import Login from '../Components/Login.vue';
import Dashboard from '../Components/Dashboard.vue';
import AppLayout from '../Components/AppLayout.vue';

const routes = [
    {
        path: '/',
        redirect: '/login',
    },

    // Rutas públicas
    {
        path: '/login',
        name: 'login',
        component: Login,
    },

    {
        path: '/rsvp/:token',
        name: 'rsvp',
        component: () => import('../../views/RspvView.vue'),
    },

    // Rutas protegidas con Layout
    {
        path: '/',
        component: AppLayout,
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: Dashboard,
            },
            {
                path: 'guests',
                name: 'guests',
                component: () => import('../Components/Guests.vue'),
            },
        ],
    },

    {
        path: '/:pathMatch(.*)*',
        redirect: '/login',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        return { name: 'login' };
    }

    if (to.name === 'login' && token) {
        return { name: 'dashboard' };
    }

    return true;
});

router.afterEach((to) => {
    const baseTitle = 'Invita';

    const routeTitles = {
        dashboard: 'Dashboard',
        guests: 'Invitados',
        rsvp: 'Confirmar asistencia',
        login: 'Iniciar sesión',
    };

    const pageTitle = routeTitles[to.name] || '';

    document.title = pageTitle
        ? `${pageTitle} - ${baseTitle}`
        : baseTitle;
});

export default router;