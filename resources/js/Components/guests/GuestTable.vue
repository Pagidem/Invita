<template>

<table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
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
                                    <div style="position: relative;">
                                        <button
                                            class="btn btn-sm btn-outline-success"
                                            @click="toggleShare(guest.id)"
                                            title="Opciones de compartir"
                                        >
                                            Compartir
                                        </button>

                                        <div v-if="openShareId === guest.id" class="share-popover p-2 shadow-sm bg-white rounded mt-1">
                                            <button class="btn btn-sm btn-link d-block text-start" @click="shareInvitation(guest)">Copiar WhatsApp</button>
                                            <button class="btn btn-sm btn-link d-block text-start" @click="openWhatsApp(guest)">Abrir WhatsApp</button>
                                        </div>
                                    </div>
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

import { toRefs, ref } from 'vue'
import api from '@/Services/axios.js'

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

const openShareId = ref(null)

const toggleShare = (id) => {
    openShareId.value = openShareId.value === id ? null : id
}

const shareInvitation = async (guest) => {
    if (!guest) {
        return
    }

    const guestName = guest.first_name || 'invitado'

    // Construir URL de la página de confirmación usando el token
    const baseUrl = window.location.origin
    const token = guest.confirmation_token || guest.confirmationToken || ''
    let rsvpUrl = token ? `${baseUrl}/rsvp/${token}` : baseUrl

    const ensureAbsolute = (u) => {
        if (!u) return u
        if (/^https?:\/\//i.test(u)) return u
        return `${baseUrl}${u.startsWith('/') ? '' : '/'}${u}`
    }

    rsvpUrl = ensureAbsolute(rsvpUrl)

    // Intentar crear short link en el backend
    try {
        const resp = await api.post('/short-link', {
            guest_id: guest.id,
            confirmation_token: token,
        })

        if (resp?.data?.short_url) {
            rsvpUrl = resp.data.short_url
        }
    } catch (e) {
        // no crítico: usar URL completa si falla
    }

    // Usar texto plano (sin markdown) para evitar problemas de linkificación
    const message = `Hola ${guestName}!\n\n` +
        `Te invitamos a confirmar tu asistencia a nuestra celebración.\n\n` +
        `Puedes ver y responder tu invitación aquí:\n${rsvpUrl}\n\n` +
        `Confirmación de asistencia:\n` +
        `- Si asistirás, confirma tu respuesta\n` +
        `- Si no podrás acompañarnos, también puedes responder\n\n` +
        `Gracias por acompañarnos y por compartir este momento con nosotros.`

    console.debug('Invitation message:', message)

    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(message)
            alert('Mensaje copiado al portapapeles. Ahora puedes pegarlo en WhatsApp.');
            openShareId.value = null
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
        openShareId.value = null
    } catch (error) {
        console.error('No se pudo copiar el texto para WhatsApp:', error)
    }
}

const openWhatsApp = async (guest) => {
    if (!guest) return

    const guestName = guest.first_name || 'invitado'
    const baseUrl = window.location.origin
    const token = guest.confirmation_token || guest.confirmationToken || ''
    let rsvpUrl = token ? `${baseUrl}/rsvp/${token}` : baseUrl

    try {
        const resp = await api.post('/short-link', {
            guest_id: guest.id,
            confirmation_token: token,
        })

        if (resp?.data?.short_url) {
            rsvpUrl = resp.data.short_url
        }
    } catch (e) {
        // ignore and use full URL
    }

    const plainMessage = `¡Hola ${guestName}!\n\n` +
        `Te invitamos a confirmar tu asistencia a nuestra celebración.\n\n` +
        `Abre tu invitación aquí:\n${rsvpUrl}\n\n` +
        `¡Gracias por acompañarnos!`

    const waLink = `https://wa.me/?text=${encodeURIComponent(plainMessage)}`
    window.open(waLink, '_blank')
    openShareId.value = null
}

</script>