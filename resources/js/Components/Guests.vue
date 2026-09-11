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
                    
                    <GuestTable
                            :guests="guests"
                            :current-page="currentPage"
                            :last-page="lastPage"
                            @edit-guest="editGuest"
                            @delete-guest="deleteGuest"
                            @update-confirmacion="updateConfirmation"
                            @change-page="changePage"
                        />
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

                <GuestModal
                    v-model:showModal="showModal"
                    v-model:form="form"
                    :is-editing="isEditing"
                    @save-guest="saveGuest"
                />

            </div>
        </div>
        
    </div>


    
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from './AppLayout.vue';
import api from '../Services/axios.js';
import GuestTable from './guests/GuestTable.vue';
import GuestModal from './guests/GuestModal.vue';

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
                page : currentPage.value,
                per_page: 15
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

const normalizeStatus = (value) => {
    const statusOrder = ['pendiente', 'confirmado', 'cancelado'];

    if (statusOrder.includes(value)) return value;
    if (value === true || value === 1) return 'confirmado';
    if (value === false || value === 0) return 'pendiente';
    return 'pendiente';
};

const form = ref({
    ci: '',
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    invitations: 1,
    confirmacion: 'pendiente',
    notes: '',
});

const isEditing = ref(false);
const editingGuestId = ref(null);


const editGuest = (guest) => {
    
    isEditing.value = true;
    editingGuestId.value = guest.id;

    form.value = {
        ...guest,
        confirmacion: normalizeStatus(guest.confirmacion),
    };

    showModal.value = true;
};

const openCreateModal = () => {

    isEditing.value = false;
    editingGuestId.value = null;

    form.value = {
        ci: '',
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        invitations: 1,
        confirmacion: 'pendiente',
        notes: '',
    }

    showModal.value = true;
};

const saveGuest = async () => {

    try {
        const payload = {
            ...form.value,
            confirmacion: normalizeStatus(form.value.confirmacion),
        };

        if (isEditing.value) {

            await api.put(
                `/guests/${editingGuestId.value}`,
                payload
            )

            alert('Datos actualizados!');

        } else {

            await api.post(
                '/guests',
                payload
            )

            alert('Invitado registrado!');
        }

        showModal.value = false

        await loadGuest()

    } catch (error) {

        console.error(error)

    }
}

const deleteGuest = async (guest) => {
    if (!guest?.id) return;

    const confirmed = window.confirm(`¿Deseas eliminar a ${guest.first_name} ${guest.last_name}?`);

    if (!confirmed) return;

    try {
        await api.delete(`/guests/${guest.id}`);
        alert('Invitado eliminado!');
        await loadGuest();
    } catch (error) {
        console.error('Error al eliminar el invitado:', error);
        alert('No se pudo eliminar el invitado.');
    }
};

const statusOrder = ['pendiente', 'confirmado', 'cancelado'];

const updateConfirmation = async (guest) => {
    if (!guest?.id) return;

    try {
        await api.put(`/guests/${guest.id}`, {
            ...guest,
            confirmacion: normalizeStatus(guest.confirmacion),
        });

        await loadGuest();
    } catch (error) {
        console.error('Error al actualizar la confirmación:', error);
        alert('No se pudo actualizar la confirmación.');
    }
};

onMounted(() => {
    loadGuest();
});



</script>