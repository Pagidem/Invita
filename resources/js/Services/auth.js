import api from './axios';

const auth = {
    async login(email, password) {
        const response = await api.post('/login', {
            email,
            password,
        });

        const token = response.data.token;

        if (token) {
            localStorage.setItem('token', token);
        }

        return response.data;
    },

    async profile() {
        const response = await api.get('/profile');
        return response.data;
    },

    async logout() {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('Error enviando petición de logout al servidor:', error);
        } finally {
            // Siempre borra el token localmente aunque el servidor falle
            localStorage.removeItem('token');
        }
    },

    isAuthenticated() {
        return !!localStorage.getItem('token');
    },
};

export default auth;