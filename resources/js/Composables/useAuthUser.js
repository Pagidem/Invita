// Estado global reactivo del usuario autenticado
import { ref } from 'vue';
import auth from '../Services/auth.js';
import router from '../router/index.js';

export const authUser = ref(null);

export function setAuthUser(user) {
    authUser.value = user;
}

export function clearAuthUser() {
    authUser.value = null;
}

export function useAuthUser() {
    return { authUser, setAuthUser, clearAuthUser };
}

// Función para inicializar el usuario (se llama desde Login tras login exitoso)
export async function initAuthUser() {
    try {
        const profile = await auth.profile();
        setAuthUser(profile);
        console.log('Perfil del usuario cargado:', profile);
        return profile;
    } catch (err) {
        console.error('Error al obtener el perfil del usuario', err);
        clearAuthUser();
        router.push({ name: 'login' });
        throw err;
    }
}