<template>
    <AppLayout>
        <div class="card section-card shadow-sm">

            <div class="card-body section-body">

                <div class="header-row mb-1">

                    <div class="title-block">
                        <p class="section-kicker">Panel Lista de Invitados</p>
                    </div>

                </div>

                <div class="search-row mb-1">
                    <input
                            v-model="search"
                            class="form-control search-input"
                            type="text"
                            placeholder="Buscar invitado..."
                            @input="debounceLoadGuests"
                        >
                </div>

                
                <div v-if="loading" class="text-center py-3">
                    Cargando invitados...
                </div>

                <div v-else>
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
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from './AppLayout.vue';
import api from '../Services/axios.js';
import GuestTable from './guests/GuestTable.vue';
import GuestModal from './guests/GuestModal.vue';

const route = useRoute();

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

const openCreateFromQuery = () => {
    if (route.query.create === '1') {
        openCreateModal();
    }
};

watch(
    () => route.query.create,
    (value) => {
        if (value === '1') {
            openCreateModal();
        }
    }
);

onMounted(() => {
    loadGuest();
    openCreateFromQuery();
});



</script>

<style scoped>
.section-card {
    border: 1px solid rgba(123, 156, 137, 0.18);
    border-radius: 20px;
    box-shadow: 0 12px 26px rgba(91, 120, 101, 0.08);
    background: rgba(255, 255, 255, 0.72);
    overflow: hidden;
}

.section-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.header-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
}

.title-block {
    min-width: 0;
}

.section-kicker {
    margin: 0 0 4px;
    color: #6a8675;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.page-title {
    margin: 0;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(1.8rem, 2.2vw, 2.6rem);
    line-height: 1.1;
    color: #2f473f;
}

.subtitle {
    margin-top: 6px;
    color: #627b6e;
    font-size: 0.95rem;
}

.search-row {
    margin-top: 0.1rem;
    margin-bottom: 0.35rem;
    position: sticky;
    top: 0;
    z-index: 5;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
}

.search-wrap {
    padding: 0;
}

.search-input {
    border-radius: 10px;
    border: 1px solid rgba(130, 156, 136, 0.28);
    background: rgba(255, 255, 255, 0.76);
    padding: 0.6rem 0.8rem;
    font-size: 0.9rem;
    color: #2c3d35;
    box-shadow: inset 0 1px 2px rgba(93, 115, 100, 0.04);
    min-height: 40px;
}

.search-input:focus {
    border-color: rgba(122, 155, 128, 0.8);
    box-shadow: 0 0 0 0.2rem rgba(122, 155, 128, 0.16);
}

@media (max-width: 991.98px) {
    .section-body {
        padding: 16px 14px;
    }

    .header-row {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .page-title {
        font-size: 1.8rem;
    }

    .subtitle {
        font-size: 0.82rem;
    }

}</style>
