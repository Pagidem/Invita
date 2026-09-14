<template>
    <div class="page-shell">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6">
                    <div class="invitation-card">
                        <div class="invitation-header">
                            <p class="eyebrow">Te invitamos a celebrar</p>
                            <h1>Tarjeta de Confirmación</h1>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div v-if="loading" class="text-center py-4">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Cargando invitación...</span>
                                </div>
                                <p class="mb-0 text-muted">Cargando invitación...</p>
                            </div>

                            <div v-else>
                                <div class="text-center mb-4">
                                    <p class="guest-label">{{ guestName ? 'Querido/a' : 'Cargando...' }}</p>
                                    <h2 class="guest-name">{{ guestName || 'Buscando invitado...' }}</h2>
                                </div>

                                <div class="event-details compact-events">
                                    <div class="event-item compact-item">
                                        <span class="label">Fecha y hora</span>
                                        <strong>19 oct 2026 · 7:00 PM</strong>
                                    </div>
                                    <div class="event-item compact-item location-item">
                                        <span class="label">Lugar</span>
                                        <div class="location-inline">
                                            <strong>Salón La Toscana</strong>
                                            <a
                                                class="gps-link"
                                                :href="mapsUrl"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="Abrir ubicación en Google Maps"
                                                title="Abrir ubicación en Google Maps"
                                            >
                                                📍
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 mb-4">
                                    <label class="form-label fw-semibold text-dark">¿Podrás acompañarnos?</label>
                                    <div class="btn-group w-100" role="group" aria-label="Respuesta de asistencia">
                                        <button
                                            type="button"
                                            class="btn option-btn"
                                            :class="form.confirmation_status === 'confirmed' ? 'btn-primary selected' : 'btn-outline-primary'"
                                            :disabled="saving"
                                            @click="setConfirmation('confirmed')"
                                        >
                                            Sí, asistiré
                                        </button>
                                        <button
                                            type="button"
                                            class="btn option-btn"
                                            :class="form.confirmation_status === 'declined' ? 'btn-danger selected' : 'btn-outline-danger'"
                                            :disabled="saving"
                                            @click="setConfirmation('declined')"
                                        >
                                            No podré asistir
                                        </button>
                                    </div>
                                </div>

                                <div v-if="form.confirmation_status === 'confirmed'" class="mb-4">
                                    <label class="form-label fw-semibold text-dark">Cantidad de acompañantes</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="10"
                                        class="form-control form-control-lg rounded-3"
                                        v-model.number="form.companions"
                                    >
                                </div>

                                <div class="d-grid gap-2">
                                    <button
                                        v-if="hasExistingResponse"
                                        class="btn btn-outline-secondary btn-lg rounded-3"
                                        :disabled="saving"
                                        @click="setConfirmation('declined')"
                                    >
                                        Cancelar asistencia
                                    </button>
                                </div>

                                <div v-if="success" class="alert alert-success mt-3 mb-0 rounded-3">
                                    ¡Gracias! Tu respuesta quedó registrada correctamente.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/Services/axios.js'

const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const success = ref(false)

const guest = ref({})

const guestName = computed(() => {
    const first = guest.value?.first_name ?? ''
    const last = guest.value?.last_name ?? ''
    return [first, last].filter(Boolean).join(' ').trim()
})

const form = ref({
    confirmation_status: null,
    companions: 0,
})

const mapsUrl = 'https://maps.app.goo.gl/bZmqPkuyeVG86XAv8'

const hasExistingResponse = computed(() => {
    const status = guest.value?.confirmation_status ?? guest.value?.confirmacion ?? null
    return ['confirmed', 'confirmado', 'declined', 'cancelado'].includes(String(status).toLowerCase())
})

const normalizeStatus = (status) => {
    if (!status) return null

    const value = String(status).toLowerCase()

    if (['confirmed', 'confirmado'].includes(value)) return 'confirmed'
    if (['declined', 'cancelado'].includes(value)) return 'declined'
    if (['pending', 'pendiente'].includes(value)) return null

    return null
}

const loadGuest = async () => {
    try {
        const response = await api.get(`/rsvp/${route.params.token}`)
        const guestData = response.data.data ?? response.data

        guest.value = guestData
        form.value.confirmation_status = normalizeStatus(guestData.confirmation_status ?? guestData.confirmacion)
        form.value.companions = guestData.companions ?? 0
    } catch (error) {
        console.error(error)
    } finally {
        loading.value = false
    }
}

const setConfirmation = async (status) => {
    if (!status) return

    form.value.confirmation_status = status

    if (status === 'confirmed') {
        form.value.companions = Number(form.value.companions ?? 0)
    } else {
        form.value.companions = 0
    }

    await saveConfirmation(status)
}

const saveConfirmation = async (status = form.value.confirmation_status) => {
    if (!status) return

    saving.value = true
    success.value = false

    try {
        const payload = {
            confirmation_status: status,
            companions: status === 'confirmed' ? Number(form.value.companions ?? 0) : 0,
        }

        await api.post(`/rsvp/${route.params.token}`, payload)
        guest.value.confirmation_status = status
        success.value = true
    } catch (error) {
        console.error(error)
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    loadGuest()
})
</script>

<style scoped>
.page-shell {
    min-height: 10vh;
    background:
        radial-gradient(circle at top, rgba(214, 152, 217, 0.35), transparent 35%),
        linear-gradient(135deg, #fffafc 0%, #f9f4ff 45%, #f3f8ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.invitation-card {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(145, 107, 172, 0.15);
    border-radius: 28px;
    box-shadow: 0 25px 60px rgba(87, 56, 104, 0.15);
    overflow: hidden;
}

.invitation-header {
    background: linear-gradient(135deg, #7b3db5 0%, #d91f8d 100%);
    color: white;
    text-align: center;
    padding: 1.2rem 1rem 1rem;
}

.eyebrow {
    margin: 0 0 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.15rem;
    font-size: 0.65rem;
    opacity: 0.88;
}

.invitation-header h1 {
    margin: 0;
    font-size: clamp(1.35rem, 2.2vw, 1.8rem);
    font-weight: 600;
}

.guest-label {
    margin: 0 0 0.5rem;
    color: #7a5c8d;
    text-transform: uppercase;
    letter-spacing: 0.12rem;
    font-size: 0.72rem;
    font-weight: 700;
}

.guest-name {
    margin: 0;
    font-size: clamp(1.9rem, 3vw, 2.5rem);
    font-weight: 700;
    color: #2d1b3d;
}

.event-details {
    display: grid;
    gap: 0.6rem;
    padding: 0.9rem 1rem;
    background: linear-gradient(180deg, #fffafd 0%, #f8f5ff 100%);
    border: 1px solid rgba(125, 94, 163, 0.12);
    border-radius: 18px;
}

.event-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.8rem;
    padding: 0.45rem 0.15rem;
    border-bottom: 1px dashed rgba(120, 92, 167, 0.2);
}

.event-item:last-child {
    border-bottom: none;
}

.compact-item {
    font-size: 0.92rem;
}

.label {
    color: #7d6a8d;
    font-size: 0.82rem;
}

.event-item strong {
    color: #2f2437;
    font-weight: 600;
    text-align: right;
    line-height: 1.2;
}

.location-item {
    gap: 0.5rem;
}

.location-inline {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.45rem;
    min-width: 0;
    flex: 1;
}

.location-inline strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    max-width: 100%;
}

.gps-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 50%;
    background: rgba(123, 61, 181, 0.1);
    color: #7b3db5;
    text-decoration: none;
    font-size: 1rem;
    flex-shrink: 0;
    margin-left: 0.1rem;
}

.gps-btn {
    font-weight: 600;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #7b3db5, #d91f8d);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #6b35a2, #c7117d);
}

.option-btn {
    transition: all 0.2s ease;
    font-weight: 600;
}

.option-btn.selected {
    box-shadow: inset 0 0 0 2px rgba(255,255,255,0.55);
}

@media (max-width: 576px) {
    .event-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .location-inline {
        width: 100%;
        justify-content: space-between;
    }

    .event-item strong {
        text-align: left;
    }
}
</style>
