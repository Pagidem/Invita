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

                    <button 
                    class="btn btn-primary"
                    @click="openCreateModal"
                    >
                        Nuevo invitado
                    </button>

                    


                </div>

                <div class="col-md-12">
                    <input
                    v-model="search"
                    class="form-control"
                     type="text"
                    placeholder="Buscar invitado..."
                    @input="debounceLoadGuests"
                    >
                </div>

                
                <div v-if="loading" class="text-center py-3">
                    Cargando invitados...
                </div>

                <div v-else class="table-responsive">

                   

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

                    <div class="d-flex justify-content-center mt-4">

                        <button
                            class="btn btn-outline-primary me-2"
                            :disabled="currentPage <= 1"
                            @click="changePage(currentPage - 1)"
                        >
                            Anterior
                        </button>

                        <span class="align-self-center">
                            Página {{ currentPage }} de {{ lastPage }}
                        </span>

                        <button
                            class="btn btn-outline-primary ms-2"
                            :disabled="currentPage >= lastPage"
                            @click="changePage(currentPage + 1)"
                        >
                            Siguiente
                        </button>

                    </div>

                    

                </div>

            </div>

        </div>
    </AppLayout>


    <div
        v-if="showModal"
        class="modal fade show"
        style="display:block;background:rgba(0,0,0,.5)"
    >
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Nuevo Invitado
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        @click="showModal = false"
                    >
                    </button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Cédula / CI
                        </label>

                        <input
                            v-model="form.ci"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nombre
                        </label>

                        <input
                            v-model="form.first_name"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Apellido
                        </label>

                        <input
                            v-model="form.last_name"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Teléfono
                        </label>

                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Invitaciones
                        </label>

                        <input
                            v-model="form.invitations"
                            type="number"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Notas
                        </label>

                        <input
                            v-model="form.notes"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        @click="showModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        class="btn btn-primary"
                        @click="saveGuest"
                    >
                        Guardar
                    </button>

                </div>

            </div>
        </div>
</div>


    
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from './AppLayout.vue';
import api from '../Services/axios.js';

const guests = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);

const search = ref('');
const loading = ref(false);

const loadGuest = async () => {

    loading.value = true;

    try {

        console.debug('Calling /guests with', { search: search.value, page: currentPage.value });
        const response = await api.get('/guests', {
            params : {
                search: search.value,
                page : currentPage.value
            }
        });

        console.debug('Received response for /guests', response.data);
        guests.value = response.data.data || [];
        const meta = response.data.meta || {};
        currentPage.value = Number(meta.current_page) || 1;
        lastPage.value = Number(meta.last_page) || 1;
        console.debug('Pagination:', { currentPage: currentPage.value, lastPage: lastPage.value, meta });

    } catch (err) {
        console.error('Error al cargar los invitados:', err);
    } finally {
        loading.value = false;
    }
};

let timeout = null;

const changePage = (page) => {
    let p = Number(page) || 1;
    if (p < 1) p = 1;
    if (lastPage.value && p > lastPage.value) p = lastPage.value;
    currentPage.value = p;
    loadGuest();
}

const debounceLoadGuests = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        currentPage.value = 1;
        loadGuest();
    }, 200); // Ajusta el tiempo de espera según tus necesidades
};

const showModal = ref (false);

const form = ref({
    ci: '',
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    invitations: 1,
    notes: '',
});


const openCreateModal = () => {
    form.value = {
        ci: '',
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        invitations: 1,
        notes: '',
    }

    showModal.value = true;
};

const saveGuest = async () => {
    try {
        await api.post('/guests', {
            ...form.value,
            invitations: Number(form.value.invitations || 1),
        });

        alert("Invitado guardado exitosamente.");

        showModal.value = false;
        await loadGuest();


    } catch (err) {
        console.error('Error saving guest:', err);
        const serverMessage = err?.response?.data?.message;
        const validationErrors = err?.response?.data?.errors;

        if (validationErrors) {
            const firstError = Object.values(validationErrors)[0]?.[0];
            alert(firstError || 'Hay errores en los datos del invitado.');
            return;
        }

        alert(serverMessage || 'Error al guardar el invitado. Por favor, inténtalo de nuevo.');
    }
};



onMounted(() => {
    loadGuest();
});



</script>