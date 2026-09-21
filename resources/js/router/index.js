import { createRouter, createWebHistory } from 'vue-router';

import Login from '../Components/Login.vue';
import Dashboard from '../Components/Dashboard.vue';

const routes = [
    {
        path: '/',
        redirect: '/login', // Redirige automáticamente la raíz al Login
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/guests',
        name: 'guests',
        component: () => import('../Components/Guests.vue'),
        meta : {
            requiresAuth: true,
        },
    },
    {
        path: '/rsvp/:token',
        name: 'rsvp',
        component: () => import('../../views/RspvView.vue'),
    },
    {
        path: '/:pathMatch(.*)*', // Redirige cualquier URL inexistente al Login
        redirect: '/login',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const token = localStorage.getItem('token');

    // 1. Si la ruta requiere autenticación y NO hay token -> ir a Login
    if (to.meta.requiresAuth && !token) {
        return { name: 'login' };
    } 
    
    // 2. Si el usuario intenta entrar a Login pero YA tiene un token -> mandar a Dashboard
    if (to.name === 'login' && token) {
        return { name: 'dashboard' };
    }

    // 3. Continuar la navegación normal
    return true;
});

// Actualizar título de la pestaña según la ruta
router.afterEach((to) => {
    const baseTitle = 'Invita';
    const routeTitles = {
        
        dashboard: 'Dashboard',
        guests: 'Invitados',
        rsvp: 'Confirmar asistencia',
    };
    
    const pageTitle = routeTitles[to.name] || '';
    document.title = pageTitle ? `${pageTitle} - ${baseTitle}` : baseTitle;
});

export default router;