<template>

    <div
        class="mesa-card"
        :style="{
            borderLeftColor: borderColor
        }"
        @click="$emit('click', mesa)"
    >

        <div class="mesa-top">

            <h3>
                Color {{ String(mesa.color).padStart(2,'0') }}
            </h3>

            <span
                class="estado-pill"
                :class="estadoClass"
            >
                {{ mesa.estado }}
            </span>

        </div>

        <div
            class="ocupacion"
            :class="colorClass"
        >
            {{ mesa.invitados_actuales }}/10
        </div>

        <p class="descripcion">
            Invitados asignados
        </p>

        <div class="mesa-footer">

            <button
                type="button"
                class="guests-trigger"
                @click.stop.prevent="handleGuestClick"
                aria-label="Ver invitados asignados a la mesa"
            >
                👥 {{ mesa.invitados_actuales }} invitados
            </button>

            <span>
                Mesa #{{ mesa.numero }}
            </span>

        </div>

    </div>

</template>

<script setup>

import { computed } from 'vue';

const emit = defineEmits(['click', 'view-guests']);

const props = defineProps({
    mesa: {
        type: Object,
        required: true,
    },
});

const handleGuestClick = () => {
    emit('view-guests', props.mesa);
};

const colorClass = computed(() => {

    switch (props.mesa.estado) {

        case 'llena':
            return 'rojo';

        case 'libre':
            return 'amarillo';

        default:
            return 'verde';
    }
});

const toneClass = computed(() => {

    switch (props.mesa.estado) {

        case 'llena':
            return 'alta';

        case 'libre':
            return 'media';

        default:
            return 'disponible';
    }
});

const estadoClass = computed(() => {

    switch (props.mesa.estado) {

        case 'llena':
            return 'llena';

        case 'libre':
            return 'libre';

        default:
            return 'vacia';
    }
});

const borderColor = computed(() => {

    const color = String(
        props.mesa.color || ''
    ).toLowerCase();

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
        beige: '#d6c7a1',
    };

    return colores[color] || '#67b77d';
});

</script>
<style scoped>

.mesa-card {
    background: #fff;
    border-radius: 18px;
    padding: 16px;

    border-left: 5px solid #67b77d;

    box-shadow:
        0 8px 18px rgba(0,0,0,.06);

    transition: .2s ease;
}

.mesa-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 14px 24px rgba(0,0,0,.10);
}


.mesa-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.mesa-top h3 {
    margin: 0;

    font-size: 1rem;
    font-weight: 700;

    color: #244338;
}

.estado-pill {

    padding: 5px 12px;

    border-radius: 999px;

    font-size: .70rem;

    font-weight: 700;

    text-transform: uppercase;
}

.estado-pill.ocupada {

    background: #edf7ef;
    color: #2c8a4b;
}

.estado-pill.cobrar {

    background: #fff0f0;
    color: #c24b4b;
}

.ocupacion {

    text-align: center;

    font-size: 2.3rem;

    font-weight: 800;

    line-height: 1;

    margin-bottom: 8px;
}

.ocupacion.verde {
    color: #2c8a4b;
}

.ocupacion.amarillo {
    color: #b58408;
}

.ocupacion.rojo {
    color: #c24b4b;
}

.descripcion {

    text-align: center;

    font-size: .75rem;

    text-transform: uppercase;

    letter-spacing: .12em;

    color: #7b8f86;

    margin-bottom: 18px;
}

.mesa-footer {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding-top: 14px;

    border-top:
        1px solid rgba(0,0,0,.08);

    font-size: .80rem;

    color: #61766d;

    font-weight: 600;
}

.guests-trigger {
    appearance: none;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;
    font: inherit;
    color: inherit;
    cursor: pointer;
    font-weight: 700;
    text-align: left;
    transition: opacity 0.2s ease;
}

.guests-trigger:hover,
.guests-trigger:focus-visible {
    opacity: 0.8;
}

.mesa-card {
    cursor: pointer;
}

.mesa-card:hover {
    transform: translateY(-3px);
}

/*
|--------------------------------------------------------------------------
| ESTADOS
|--------------------------------------------------------------------------
*/

.estado-pill.vacia {
    background: #edf7ef;
    color: #2c8a4b;
}

.estado-pill.libre {
    background: #fff7df;
    color: #b58408;
}

.estado-pill.llena {
    background: #ffe4e4;
    color: #c24b4b;
}

/*
|--------------------------------------------------------------------------
| TABLET
|--------------------------------------------------------------------------
*/

@media (max-width: 991px) {

    .mesa-card {
        padding: 14px;
    }

    .ocupacion {
        font-size: 2rem;
    }
}

/*
|--------------------------------------------------------------------------
| CELULAR
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .ocupacion {
        font-size: 1.8rem;
    }

    .mesa-footer {
        font-size: .72rem;
    }
}

</style>