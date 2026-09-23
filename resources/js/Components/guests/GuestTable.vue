<template>

    <div class="guest-table-container">

        <!-- SOLO ESTA ZONA TIENE SCROLL -->
        <div class="table-scroll-inner">
            <table class="table table-hover align-middle mb-0 guest-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Confirmación</th>
                        <th>Compartir</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="guest in guests" :key="guest.id">

                        <td>
                            {{ guest.id }}
                        </td>

                        <td>
                            {{ guest.first_name }} {{ guest.last_name }}
                        </td>

                        <td>
                            {{ guest.phone }}
                        </td>

                        <td>
                            <select
                                class="form-select form-select-sm"
                                :class="statusSelectClass(guest.confirmacion)"
                                :value="normalizeStatus(guest.confirmacion)"
                                @change="updateConfirmation(guest, $event.target.value)"
                            >
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </td>

                        <td>
                            <button
                                class="btn btn-sm btn-outline-success"
                                @click="openWhatsApp(guest)"
                                title="Compartir por WhatsApp"
                            >
                                Compartir
                            </button>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap align-items-center">

                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="editGuest(guest)"
                                >
                                    Editar
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteGuest(guest)"
                                >
                                    Eliminar
                                </button>

                            </div>
                        </td>

                    </tr>
                </tbody>

            </table>
        </div>


        <!-- PAGINACIÓN TOTALMENTE FUERA DEL SCROLL -->
        <div class="pagination-wrap">

            <button
                class="btn btn-outline-primary page-btn"
                :disabled="currentPage <= 1"
                @click="changePage(currentPage - 1)"
            >
                Anterior
            </button>

            <span class="page-indicator">
                Página {{ currentPage }} de {{ lastPage }}
            </span>

            <button
                class="btn btn-outline-primary page-btn"
                :disabled="currentPage >= lastPage"
                @click="changePage(currentPage + 1)"
            >
                Siguiente
            </button>

        </div>

    </div>

</template>


<script setup>

import { toRefs, ref } from 'vue'
import api from '@/Services/axios.js'

const props = defineProps({
    guests: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
})

const { guests, currentPage, lastPage } = toRefs(props)

const emit = defineEmits([
    'edit-guest',
    'delete-guest',
    'update-confirmacion',
    'change-page'
])


const statusMap = {
    pendiente: {
        label: 'Pendiente',
        className: 'border-warning text-warning'
    },

    confirmado: {
        label: 'Confirmado',
        className: 'border-success text-success'
    },

    cancelado: {
        label: 'Cancelado',
        className: 'border-danger text-danger'
    },
}


const normalizeStatus = (status) =>
    statusMap[status] ? status : 'pendiente'


const statusLabel = (status) =>
    statusMap[normalizeStatus(status)]?.label || 'Pendiente'


const statusSelectClass = (status) =>
    statusMap[normalizeStatus(status)]?.className ||
    'border-warning text-warning'


const editGuest = (guest) => {
    emit('edit-guest', guest)
}


const deleteGuest = (guest) => {
    emit('delete-guest', guest)
}


const updateConfirmation = (guest, value) => {
    emit('update-confirmacion', {
        ...guest,
        confirmacion: value,
    })
}


const changePage = (page) => {
    emit('change-page', page)
}


const openWhatsApp = async (guest) => {

    if (!guest) return


    const guestName =
        guest.first_name || 'invitado'


    const baseUrl = `{window.location.origin}/api`
        


    const token =
        guest.confirmation_token ||
        guest.confirmationToken ||
        ''


    let rsvpUrl =
        token
            ? `${baseUrl}/rsvp/${token}`
            : baseUrl


    try {

        const resp = await api.post('/short-link', {
            guest_id: guest.id,
            confirmation_token: token,
        })

        if (resp?.data?.short_url) {
            rsvpUrl = resp.data.short_url
        }

    } catch (e) {
        // Usar URL completa si falla
    }


    const plainMessage =
        `¡Hola ${guestName}!\n\n` +
        `Te invitamos a confirmar tu asistencia a nuestra celebración.\n\n` +
        `Abre tu invitación aquí:\n${rsvpUrl}\n\n` +
        `¡Gracias por acompañarnos!`

    const guestPhone =
        guest.phone || ''

    const cleanedPhone =
        guestPhone.toString().replace(/\D/g, '')

    const waLink = cleanedPhone
        ? `https://wa.me/${cleanedPhone}?text=${encodeURIComponent(plainMessage)}`
        : `https://wa.me/?text=${encodeURIComponent(plainMessage)}`


    window.open(waLink, '_blank')
}

</script>


<style scoped>

.guest-table-container {
    width: 100%;
    min-width: 0;

    /*
     * IMPORTANTE:
     * No poner overflow aquí.
     *
     * La paginación queda fuera de cualquier
     * contenedor con overflow.
     */
}


/*
 * =========================================================
 * ÁREA SCROLLEABLE
 * =========================================================
 */

.table-scroll-inner {

    width: 100%;

    /*
     * El scroll pertenece EXCLUSIVAMENTE a esta zona.
     */
    overflow-x: auto;
    overflow-y: auto;

    /*
     * Altura máxima de la tabla.
     */
    max-height: calc(100vh - 280px);

    /*
     * Permite que el contenedor se desplace
     * correctamente en dispositivos táctiles.
     */
    -webkit-overflow-scrolling: touch;

    /*
     * Evita que un ancho grande de la tabla
     * provoque overflow en toda la página.
     */
    min-width: 0;

    /*
     * Separación visual.
     */
    padding-right: 2px;
}


/*
 * =========================================================
 * TABLA
 * =========================================================
 */

.guest-table {

    border-collapse: separate;
    border-spacing: 0;

    font-size: 0.85rem;

    /*
     * La tabla puede ser más ancha que el móvil.
     * El scroll horizontal pertenece al padre.
     */
    width: max-content;
    min-width: 100%;
}


.guest-table th,
.guest-table td {

    padding: 0.55rem 0.4rem;

    vertical-align: middle;
}


.guest-table thead th {

    color: #49615a;

    font-size: 0.68rem;

    letter-spacing: 0.05em;

    text-transform: uppercase;

    font-weight: 700;

    background: rgba(151, 178, 160, 0.08);

    /*
     * Mantiene el encabezado visible
     * mientras se hace scroll vertical.
     */
    position: sticky;
    top: 0;
    z-index: 2;
}


/*
 * =========================================================
 * SELECT
 * =========================================================
 */

.form-select {

    min-width: 110px;

    border-radius: 10px;

    border-color:
        rgba(128, 153, 137, 0.25);

    font-size: 0.72rem;

    padding: 0.28rem 0.5rem;
}


/*
 * =========================================================
 * SHARE POPOVER
 * =========================================================
 */

.share-container {
    position: relative;
}


.share-popover {

    position: absolute;

    top: 100%;
    left: 0;

    z-index: 20;

    min-width: 180px;

    white-space: nowrap;
}


/*
 * =========================================================
 * PAGINACIÓN
 *
 * IMPORTANTE:
 * Está fuera de .table-scroll-inner.
 * =========================================================
 */

.pagination-wrap {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 10px;

    /*
     * Separación respecto a la tabla.
     */
    margin-top: 16px;

    /*
     * La paginación NO tiene overflow.
     */
    overflow: visible;

    /*
     * Evita que quede pegada al contenido
     * cuando la tabla termina.
     */
    padding: 4px 0 12px;
}


.page-btn {

    border-radius: 10px;

    border-color:
        rgba(120, 150, 127, 0.4);

    color: #39584a;

    white-space: nowrap;

    flex: 0 0 auto;
}


.page-btn:hover:not(:disabled) {

    background:
        rgba(120, 150, 127, 0.12);

    border-color:
        rgba(120, 150, 127, 0.5);
}


.page-indicator {

    color: #536d64;

    font-size: 0.85rem;

    white-space: nowrap;

    flex: 0 0 auto;
}


/*
 * =========================================================
 * MÓVILES
 * =========================================================
 */

@media (max-width: 767.98px) {

    /*
     * En móvil dejamos espacio suficiente para
     * la paginación y evitamos que el scroll de la
     * tabla ocupe prácticamente todo el viewport.
     */
    .table-scroll-inner {

        max-height: calc(100dvh - 300px);

        /*
         * Fallback para navegadores antiguos.
         */
        max-height: calc(100vh - 300px);

        overscroll-behavior: contain;

        /*
         * Scroll táctil nativo.
         */
        -webkit-overflow-scrolling: touch;
    }


    .guest-table {

        font-size: 0.72rem;

        /*
         * Ancho mínimo para conservar
         * la estructura de las columnas.
         */
        min-width: 660px;

        width: 660px;
    }


    .guest-table th,
    .guest-table td {

        padding:
            0.45rem
            0.28rem;
    }


    .guest-table thead th {

        font-size: 0.62rem;
    }


    .form-select {

        min-width: 90px;

        padding:
            0.22rem
            0.4rem;

        font-size: 0.7rem;
    }


    /*
     * PAGINACIÓN
     *
     * Nunca entra dentro del scroll de la tabla.
     */

    .pagination-wrap {

        display: flex;

        flex-wrap: nowrap;

        align-items: center;

        justify-content: center;

        gap: 6px;

        width: 100%;

        min-width: 0;

        margin-top: 14px;

        padding:
            4px
            0
            16px;

        /*
         * Permite que los botones sigan siendo
         * accesibles aunque el viewport sea pequeño.
         */
        overflow: visible;
    }


    .page-btn {

        flex: 0 0 auto;

        padding:
            0.4rem
            0.65rem;

        font-size: 0.72rem;
    }


    .page-indicator {

        flex: 0 0 auto;

        text-align: center;

        font-size: 0.72rem;
    }
}


/*
 * =========================================================
 * MÓVILES MUY PEQUEÑOS
 * =========================================================
 */

@media (max-width: 380px) {

    .pagination-wrap {

        gap: 4px;
    }


    .page-btn {

        padding:
            0.35rem
            0.5rem;

        font-size: 0.68rem;
    }


    .page-indicator {

        font-size: 0.68rem;
    }
}

</style>