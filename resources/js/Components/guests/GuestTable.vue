<template>

<table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Telefono</th>
                                <th>Confirmación</th>
                                <th>Acciones</th>
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
                                        class="btn btn-sm btn-outline-success me-2"
                                        @click="shareInvitation(guest)"
                                        title="Copiar mensaje para WhatsApp"
                                    >
                                        Copiar WhatsApp
                                    </button>

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

</template>

<script setup>

import { toRefs } from 'vue'

const props = defineProps({
    guests: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
})

const { guests, currentPage, lastPage } = toRefs(props)

const emit = defineEmits(['edit-guest', 'delete-guest', 'update-confirmacion', 'change-page'])

const statusMap = {
    pendiente: { label: 'Pendiente', className: 'border-warning text-warning' },
    confirmado: { label: 'Confirmado', className: 'border-success text-success' },
    cancelado: { label: 'Cancelado', className: 'border-danger text-danger' },
}

const normalizeStatus = (status) => statusMap[status] ? status : 'pendiente'

const statusLabel = (status) => statusMap[normalizeStatus(status)]?.label || 'Pendiente'
const statusSelectClass = (status) => statusMap[normalizeStatus(status)]?.className || 'border-warning text-warning'

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

const shareInvitation = async (guest) => {
    if (!guest) {
        return
    }

    const guestName = guest.first_name || 'invitado'
    const message = `*¡Hola ${guestName}! 👋*\n\n` +
        `Te invitamos a confirmar tu asistencia a nuestra celebración.\n\n` +
        `*Confirmación de asistencia*\n` +
        `• Si asistirás, confirma tu respuesta\n` +
        `• Si no podrás acompañarnos, también puedes responder\n\n` +
        `_Gracias por acompañarnos y por compartir este momento con nosotros._`

    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(message)
            alert('Mensaje copiado al portapapeles. Ahora puedes pegarlo en WhatsApp.');
            return
        }

        const textArea = document.createElement('textarea')
        textArea.value = message
        textArea.style.position = 'fixed'
        textArea.style.opacity = '0'
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
    } catch (error) {
        console.error('No se pudo copiar el texto para WhatsApp:', error)
    }
}

</script>