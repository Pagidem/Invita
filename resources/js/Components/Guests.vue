<template>
    <AppLayout>
        <div class="card shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <h1 class="h3 mb-1">
                            Invitados
                        </h1>

                        <p class="text-muted mb-0">
                            Gestión de invitados de la boda.
                        </p>
                    </div>

                    <button class="btn btn-primary">
                        Nuevo invitado
                    </button>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Telefono</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="guest in guests" :key="guest.id">
                                <td>
                                 {{ guest.id }}
                                </td>

                                <td>
                                 {{ guest.first_name }}
                                </td>

                                <td>
                                 {{ guest.last_name }}
                                </td>

                                <td>
                                 {{ guest.phone }}
                                </td>

                                
                            </tr>
                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </AppLayout>
    
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from './AppLayout.vue';
import api from '../Services/axios.js';

const guests = ref([]);

const loadGuest = async () => {
    try {
        const response = await api.get('/guests');
        guests.value = response.data.data;
    } catch (err) {
        console.error('Error al cargar los invitados:', err);
    }
};

onMounted(() => {
    loadGuest();
});



</script>