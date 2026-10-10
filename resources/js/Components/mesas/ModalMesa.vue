<template>
    <div
        v-if="show"
        class="modal-overlay"
        @click.self="$emit('close')"
    >
        <div class="modal-card">

            <div class="modal-header">

                <div>
                    <h4>Asignar mesa</h4>

                    <p v-if="guest">
                        {{ guest.full_name }}
                    </p>
                </div>

                <button
                    class="close-btn"
                    @click="$emit('close')"
                >
                    ✕
                </button>

            </div>

            <div class="mesa-grid">

                <button
                    v-for="mesa in mesas"
                    :key="mesa.id"
                    class="mesa-circle"
                    :style="{
                        backgroundColor: getMesaColor(mesa.color),
                        color: getTextColor(mesa.color)
                    }"
                    @click="$emit('select', mesa)"
                >
                    {{ mesa.numero }}
                </button>

            </div>

        </div>
    </div>
</template>

<script setup>

defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    guest: {
        type: Object,
        default: null,
    },

    mesas: {
        type: Array,
        default: () => [],
    },
});

const getMesaColor = (color) => {

    const colores = {

        rojo: '#dc3545',
        azul: '#0d6efd',
        verde: '#198754',
        amarillo: '#ffc107',
        morado: '#6f42c1',
        naranja: '#fd7e14',
        celeste: '#0dcaf0',
        rosa: '#d63384',
        gris: '#6c757d',
        dorado: '#d4a017',
        turquesa: '#20c997',
        lila: '#a78bfa',
        marrón: '#8b5e3c',
        marron: '#8b5e3c',
        beige: '#d6c7a1',
    }

    return colores[
        String(color).toLowerCase()
    ] || '#6c757d'
}

const getTextColor = (color) => {

    const claros = [
        'amarillo',
        'dorado',
        'beige',
        'celeste',
        'lila'
    ]

    return claros.includes(
        String(color).toLowerCase()
    )
        ? '#222'
        : '#fff'
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
    max-width: 500px;

    background: white;

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 20px 40px rgba(0,0,0,.15);
}

.modal-header {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    margin-bottom: 24px;
}

.modal-header h4 {

    margin: 0;

    font-weight: 700;
}

.modal-header p {

    margin: 4px 0 0;

    color: #6b7280;
}

.close-btn {

    border: none;

    background: transparent;

    font-size: 1.2rem;

    cursor: pointer;
}

.mesa-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

    justify-items: center;
}

.mesa-circle {

    width: 90px;
    height: 90px;

    border-radius: 50%;

    border: none;

    cursor: pointer;

    font-size: 1.4rem;

    font-weight: 800;

    transition: .2s;
}

.mesa-circle:hover {

    transform: scale(1.08);
}

@media (max-width: 576px) {

    .mesa-circle {

        width: 75px;
        height: 75px;

        font-size: 1.1rem;
    }
}

</style>