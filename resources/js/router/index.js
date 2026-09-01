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
        path: '/:pathMatch(.*)*', // Redirige cualquier URL inexistente al Login
        redirect: '/login',
    },
    {
        path: '/guests',
        name: 'guests',
        component: () => import('../Components/Guests.vue'),
        meta : {
            requiresAuth: true,
        },
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

export default router;