
<template>
    <div class="min-vh-100 bg-light">

        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-primary shadow-sm">
            <div class="container-fluid">

                <span class="navbar-brand mb-0 h1">
                    Invita
                </span>

                <div class="d-flex align-items-center">

                    <span v-if="user" class="text-white">
                        {{ user.name }}
                    </span>

                    <button
                        class="btn btn-outline-light btn-sm ms-3"
                        @click="logout"
                    >
                        Cerrar sesión
                    </button>

                </div>

            </div>
        </nav>

        <!-- Contenido -->
        <main class="container-fluid py-4">

            <div class="row">

                <!-- Sidebar -->
                <aside class="col-md-3 col-lg-2 mb-4">

                    <div class="card shadow-sm">

                        <div class="list-group list-group-flush">

                            <RouterLink
                                to="/dashboard"
                                class="list-group-item list-group-item-action"
                                active-class="active"
                            >
                                Dashboard
                            </RouterLink>

                            <RouterLink
                                to="/guests"
                                class="list-group-item list-group-item-action"
                                active-class="active"
                            >
                                Invitados
                            </RouterLink>

                        </div>

                    </div>

                </aside>

                <!-- Contenido de la vista -->
                <section class="col-md-9 col-lg-10">

                    <slot />

                </section>

            </div>

        </main>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import auth from '../Services/auth.js';

const router = useRouter();
const user = ref(null);

onMounted(async () => {
    try {
        const profile = await auth.profile();
        user.value = profile;
        console.log('Perfil del usuario :', profile);
    } catch (err) {
        console.error('Error al obtener el perfil del usuario', err);
        router.push({ name: 'login' });
    }
});

const logout = async () => {
    try {
        await auth.logout();
        console.log('Sesión cerrada correctamente');
        router.push({ name: 'login' });
    } catch (err) {
        console.error('Error al cerrar sesión', err);
        localStorage.removeItem('token');
    }
};

</script>