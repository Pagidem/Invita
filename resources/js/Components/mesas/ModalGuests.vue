<template>
    <div
        v-if="show"
        class="modal-overlay"
        @click.self="$emit('close')"
    >
        <div class="modal-card">

            <div class="modal-header">

                <div>
                    <h3>
                        Mesa {{ mesa?.numero }}
                    </h3>

                    <p class="mesa-color">
                        {{ mesa?.color }}
                    </p>
                </div>

                <button
                    class="close-btn"
                    @click="$emit('close')"
                >
                    ✕
                </button>

            </div>

            <div class="mesa-stats">

                <div class="stat">
                    <span>Ocupación actual</span>
                    <strong>
                        {{ ocupacionActual }}/10
                    </strong>
                </div>

                <div class="stat">
                    <span>Disponible</span>
                    <strong>
                        {{ 10 - ocupacionActual }}
                    </strong>
                </div>

            </div>

            <div v-if="mode === 'assign'">
                <input
                    v-model="search"
                    type="text"
                    class="search-input"
                    placeholder="Buscar invitado..."
                >

                <div class="guests-list">

                    <label
                        v-for="guest in filteredGuests"
                        :key="guest.id"
                        class="guest-item"
                    >

                        <input
                            type="checkbox"
                            :value="guest.id"
                            v-model="selectedGuests"
                        >

                        <div class="guest-info">

                            <strong>
                                {{ guest.full_name }}
                            </strong>

                            <small>
                                {{ getOcupantes(guest) }}
                                ocupante(s)
                            </small>

                        </div>

                        <div class="guest-mesa">

                            {{
                                guest.mesa
                                    ? `Mesa ${guest.mesa.numero}`
                                    : 'Sin mesa'
                            }}

                        </div>

                    </label>

                </div>

                <div class="summary-box">

                    <div>
                        Seleccionados:
                        <strong>
                            {{ seleccionadosOcupacion }}
                        </strong>
                    </div>

                    <div>
                        Ocupación final:
                        <strong
                            :class="{
                                danger: ocupacionFinal > 10
                            }"
                        >
                            {{ ocupacionFinal }}/10
                        </strong>
                    </div>

                </div>

                <button
                    class="assign-btn"
                    :disabled="ocupacionFinal > 10"
                    @click="emitAssign"
                >
                    Asignar invitados
                </button>
            </div>

            <div v-else class="guests-list read-only-list">
                <template v-if="Array.isArray(filteredGuests) && filteredGuests.length">
                    <div
                        v-for="guest in filteredGuests"
                        :key="guest.id"
                        class="guest-item read-only-item"
                    >
                        <div class="guest-info">
                            <strong>
                                {{ guest?.full_name || 'Invitado sin nombre' }}
                            </strong>

                            <small>
                                {{ getOcupantes(guest) }}
                                ocupante(s)
                            </small>
                        </div>

                        <div class="guest-mesa">
                            {{ guest?.confirmacion || 'Pendiente' }}
                        </div>
                    </div>
                </template>

                <div v-else class="empty-state">
                    No hay invitados asignados a esta mesa.
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>

import {
    ref,
    computed,
    watch,
} from 'vue'

const props = defineProps({

    show: Boolean,

    mesa: {
        type: Object,
        default: null,
    },

    guests: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'assign',
        validator(value) {
            return ['assign', 'view'].includes(value)
        },
    },

})

const emit = defineEmits([
    'close',
    'assign',
])

const search = ref('')

const selectedGuests = ref([])

watch(
    () => props.mesa?.id,
    (mesaId) => {

        if (!mesaId) {

            selectedGuests.value = []

            return
        }

        selectedGuests.value =
            props.guests
                .filter(
                    guest =>
                        guest.mesa_id === mesaId
                )
                .map(
                    guest => guest.id
                )

        search.value = ''
    },
    {
        immediate: true,
    }
)

const getOcupantes = (guest) => {

    return 1 + (
        Number(guest.companions) || 0
    )
}

const ocupacionActual = computed(() => {

    return Number(
        props.mesa?.invitados_actuales ?? 0
    )
})

const filteredGuests = computed(() => {

    const list = Array.isArray(props.guests)
        ? props.guests
        : []

    const term = String(search.value ?? '')
        .trim()
        .toLowerCase()

    if (!term) {

        return list
    }

    return list.filter(
        (guest) =>
            String(guest?.full_name ?? '')
                .toLowerCase()
                .includes(term)
    )
})

const seleccionadosOcupacion =
computed(() => {

    return props.guests
        .filter(
            guest =>
                selectedGuests.value.includes(
                    guest.id
                )
        )
        .reduce(
            (total, guest) =>
                total + getOcupantes(guest),
            0
        )
})

const ocupacionFinal = computed(() => {

    return (
        ocupacionActual.value +
        seleccionadosOcupacion.value
    )
})

const emitAssign = () => {

    emit(
        'assign',
        {
            mesa: props.mesa,
            guests: selectedGuests.value,
        }
    )
}

</script>

<style scoped>

.modal-overlay {
    position: fixed;
    inset: 0;

    background: rgba(0,0,0,.45);

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 9999;

    padding: 20px;
}

.modal-card {

    width: 100%;
    max-width: 700px;

    background: white;

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 20px 40px rgba(0,0,0,.15);
}

.modal-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 16px;
}

.modal-header h3 {

    margin: 0;
}

.mesa-color {

    margin: 4px 0 0;

    color: #6b7280;
}

.close-btn {

    border: none;

    background: transparent;

    font-size: 1.2rem;

    cursor: pointer;
}

.mesa-stats {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 12px;

    margin-bottom: 16px;
}

.stat {

    padding: 12px;

    border-radius: 12px;

    background: #f8f9fa;

    text-align: center;
}

.search-input {

    width: 100%;

    margin-bottom: 16px;

    border: 1px solid #ddd;

    border-radius: 10px;

    padding: 10px 12px;
}

.guests-list {

    max-height: 350px;

    overflow-y: auto;
}

.guest-item {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px;

    border-radius: 12px;

    border: 1px solid #ececec;

    margin-bottom: 8px;

    cursor: pointer;
}

.guest-info {

    flex: 1;

    display: flex;

    flex-direction: column;
}

.guest-info small {

    color: #6b7280;
}

.guest-mesa {

    font-size: .85rem;

    color: #6b7280;
}

.summary-box {

    margin-top: 16px;

    padding-top: 16px;

    border-top: 1px solid #e5e7eb;

    display: flex;

    justify-content: space-between;
}

.assign-btn {

    width: 100%;

    margin-top: 16px;

    border: none;

    border-radius: 12px;

    padding: 12px;

    background: #198754;

    color: white;

    font-weight: 700;
}

.assign-btn:disabled {

    background: #adb5bd;

    cursor: not-allowed;
}

.danger {

    color: #dc3545;
}

</style>