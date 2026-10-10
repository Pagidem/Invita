<template>
    <div class="card section-card shadow-sm">
        <div class="card-body section-body">
            <div class="header-row mb-1">
                <div class="title-block">
                    <div class="kicker-row">
                        <p class="section-kicker">SECCION DE MESAS</p>
                        <button
                            type="button"
                            class="refresh-btn"
                            @click="refreshMesas"
                            title="Actualizar mesas"
                            aria-label="Actualizar mesas"
                        >
                            <span aria-hidden="true">↻</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="guests-loading">
                <div class="loading-spinner-card">
                    <div class="loading-spinner-ring"></div>
                    <p class="loading-text">Cargando mesas</p>
                    <p class="loading-subtext">Sincronizando ocupación...</p>
                </div>
            </div>

            <div v-else class="dashmesa-container">
                <div class="mesas-scroll">
                    <div class="mesas-grid">
                        <BoxMesa
                            v-for="mesa in filteredMesas"
                            :key="mesa.id"
                            :mesa="mesa"
                            @click="openMesa(mesa)"
                            @view-guests="openMesaGuests(mesa)"
                        />
                    </div>
                </div>
            </div>

            <ModalGuests
                :show="showGuestsModal"
                :mesa="selectedMesa"
                :guests="guests"
                :mode="modalMode"
                @close="showGuestsModal = false"
                @assign="assignGuestsToMesa"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../Services/axios.js';
import BoxMesa from './mesas/BoxMesa.vue';
import ModalGuests from './mesas/ModalGuests.vue';

const loading = ref(false);
const search = ref('');

const mesas = ref([]);

const showGuestsModal = ref(false);
const selectedMesa = ref(null);
const guests = ref([]);
const modalMode = ref('assign');

const filteredMesas = computed(() => {

    const term =
        search.value.trim().toLowerCase();

    if (!term) {
        return mesas.value;
    }

    return mesas.value.filter((mesa) => {

        const numero =
            String(mesa.numero);

        const estado =
            mesa.estado.toLowerCase();

        const invitados =
            String(mesa.invitados_actuales);

        return (
            numero.includes(term) ||
            estado.includes(term) ||
            invitados.includes(term)
        );
    });
});

const loadMesas = async () => {

    loading.value = true;

    try {

        const response = await api.get('/mesas');

        mesas.value = response.data.data;

    } catch (error) {

        console.error(
            'Error al cargar mesas:',
            error
        );

    } finally {

        loading.value = false;
    }
};

const loadGuests = async () => {

    try {

        const response =
            await api.get('/guests?per_page=1000')

        guests.value =
            response.data.data ?? []

    } catch (error) {

        console.error(
            'Error cargando invitados',
            error
        )
    }
}

const openMesa = async (mesa) => {

    selectedMesa.value = mesa
    modalMode.value = 'assign'

    await loadGuests()

    showGuestsModal.value = true
}

const openMesaGuests = async (mesa) => {
    selectedMesa.value = mesa
    modalMode.value = 'view'

    try {
        const response = await api.get('/guests/by-mesa', {
            params: {
                mesa_id: mesa.id,
            },
        })

        guests.value = response.data.data ?? []
        showGuestsModal.value = true
    } catch (error) {
        console.error('Error al cargar invitados de la mesa:', error)
    }
}

const refreshMesas = async () => {
    if (loading.value) return;
    await loadMesas();
};

const assignGuestsToMesa = async (
    payload
) => {

    try {

        await api.post(
            `/mesas/${payload.mesa.id}/assign-guests`,
            {
                guest_ids:
                    payload.guests
            }
        )

        await loadMesas()

        showGuestsModal.value = false

    } catch (error) {

        alert(
            error?.response?.data?.message ??
            'Error al asignar invitados.'
        )
    }
}

onMounted(() => {
    loadMesas();
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
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.title-block {
    min-width: 0;
}

.kicker-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
}

.refresh-btn {
    width: 32px;
    height: 32px;
    border: 1px solid rgba(122, 155, 128, 0.3);
    background: rgba(255, 255, 255, 0.72);
    color: #496659;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
    flex-shrink: 0;
}

.refresh-btn:hover {
    background: rgba(122, 155, 128, 0.08);
    border-color: rgba(122, 155, 128, 0.6);
}

.refresh-btn:active {
    transform: scale(0.96);
}

.section-kicker {
    margin: 0;
    color: #6a8675;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    line-height: 1.2;
}

.dashmesa-container {
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.mesas-scroll {
    overflow: visible;
}

.mesas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    align-items: stretch;
    width: 100%;
}

.guests-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    min-height: 220px;
}

.loading-spinner-card {
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(120, 147, 128, 0.15);
    border-radius: 16px;
    padding: 30px 36px;
    text-align: center;
    box-shadow: 0 12px 28px rgba(95, 120, 104, 0.1);
}

.loading-spinner-ring {
    width: 48px;
    height: 48px;
    margin: 0 auto 16px;
    border: 3.5px solid rgba(122, 155, 128, 0.18);
    border-top-color: #7a9d88;
    border-radius: 50%;
    animation: loadingSpin 1s linear infinite;
}

.loading-text {
    margin: 0 0 6px;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d463e;
    letter-spacing: 0.02em;
}

.loading-subtext {
    margin: 0;
    font-size: 0.85rem;
    color: #6d8878;
    font-weight: 500;
}

@keyframes loadingSpin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 991.98px) {
    .section-body {
        padding: 16px 14px;
    }

    .header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .kicker-row {
        gap: 6px;
    }

    .refresh-btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        font-size: 0.95rem;
    }

    .mesas-scroll {
        max-height: calc(100vh - 260px);
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px;
    }
}
</style>

