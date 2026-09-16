<template>
    <div class="page-shell">
        <div class="container py-1">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6">
                    <div class="invitation-card">
                        <div class="invitation-header">
                            <p class="eyebrow">Te invitamos a celebrar</p>
                            <h1>Tarjeta de Confirmación</h1>
                        </div>

                        <div class="card-body p-2 p-md-5">
                            <div v-if="loading" class="text-center py-4">
                                <div class="spinner-border text-primary mb-1" role="status">
                                    <span class="visually-hidden">Cargando invitación...</span>
                                </div>
                                <p class="mb-0 text-muted">Cargando invitación...</p>
                            </div>

                            <div v-else>
                                <div class="text-center mb-2">
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

                                <div class="mt-2 mb-2">
                                    <label class="form-label fw-semibold text-dark">¿Podrás acompañarnos?</label>
                                    <div class="btn-group w-100 response-group" role="group" aria-label="Respuesta de asistencia">
                                        <button
                                            type="button"
                                            class="btn option-btn"
                                            :class="form.confirmation_status === 'confirmed' ? 'btn-primary selected' : 'btn-outline-primary'"
                                            :disabled="saving"
                                            @click="selectConfirmation('confirmed')"
                                        >
                                            Sí, asistiré
                                        </button>
                                        <button
                                            type="button"
                                            class="btn option-btn"
                                            :class="form.confirmation_status === 'declined' ? 'btn-danger selected' : 'btn-outline-danger'"
                                            :disabled="saving"
                                            @click="selectConfirmation('declined')"
                                        >
                                            No podré asistir
                                        </button>
                                    </div>
                                </div>

                                <div v-if="form.confirmation_status === 'confirmed'" class="mb-2 companions-box">
                                    <label class="form-label fw-semibold text-dark">Cantidad de acompañantes</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="10"
                                        class="form-control form-control-lg rounded-3 companions-input"
                                        :value="form.companions"
                                        @input="updateCompanions($event.target.value)"
                                    >
                                    <small class="text-muted d-block mt-2">Máximo 10 personas.</small>
                                </div>

                                <div v-if="form.confirmation_status" class="d-grid gap-2 mt-1">
                                    <button
                                        class="btn btn-primary btn-lg rounded-3 register-btn"
                                        :disabled="saving"
                                        @click="registerResponse"
                                    >
                                        {{ saving ? 'Registrando...' : 'Registrar respuesta' }}
                                    </button>
                                </div>

                                <div v-if="hasExistingResponse && !form.confirmation_status" class="d-grid gap-2">
                                    <button
                                        class="btn btn-outline-secondary btn-lg rounded-3"
                                        :disabled="saving"
                                        @click="selectConfirmation('declined')"
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

const selectConfirmation = (status) => {
    if (!status) return

    form.value.confirmation_status = status

    if (status === 'confirmed') {
        form.value.companions = Number(form.value.companions ?? 0)
    } else {
        form.value.companions = 0
    }
}

const updateCompanions = (value) => {
    const numericValue = Number(value || 0)
    form.value.companions = Math.min(Math.max(numericValue, 0), 10)
}

const registerResponse = async () => {
    const status = form.value.confirmation_status
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
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(146, 179, 152, 0.18), transparent 28%),
        radial-gradient(circle at bottom right, rgba(184, 197, 171, 0.16), transparent 30%),
        linear-gradient(135deg, #f8f7f3 0%, #eef5ef 42%, #f3f7f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 12px;
}

.invitation-card {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(123, 155, 128, 0.18);
    border-radius: 28px;
    box-shadow: 0 24px 60px rgba(96, 120, 101, 0.12);
    overflow: hidden;
}

.invitation-header {
    background: linear-gradient(135deg, #7da07d 0%, #6d8f6d 42%, #a9bca1 100%);
    color: white;
    text-align: center;
    padding: 1.2rem 1rem 1rem;
}

.eyebrow {
    margin: 0 0 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.15rem;
    font-size: 0.65rem;
    opacity: 0.9;
    font-weight: 700;
}

.invitation-header h1 {
    margin: 0;
    font-size: clamp(1.35rem, 2.2vw, 1.8rem);
    font-weight: 600;
    font-family: Georgia, 'Times New Roman', serif;
    letter-spacing: -0.04em;
}

.guest-label {
    margin: 0 0 0.5rem;
    color: #637d6a;
    text-transform: uppercase;
    letter-spacing: 0.12rem;
    font-size: 0.72rem;
    font-weight: 700;
}

.guest-name {
    margin: 0;
    font-size: clamp(1.9rem, 3vw, 2.5rem);
    font-weight: 700;
    color: #2b433d;
    font-family: Georgia, 'Times New Roman', serif;
    letter-spacing: -0.06em;
}

.event-details {
    display: grid;
    gap: 0.6rem;
    padding: 0.9rem 1rem;
    background: linear-gradient(180deg, rgba(248, 245, 238, 0.95), rgba(239, 245, 239, 0.9));
    border: 1px solid rgba(120, 147, 128, 0.18);
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
    color: #6d867b;
    font-size: 0.82rem;
    font-weight: 600;
}

.event-item strong {
    color: #2f473f;
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
    background: rgba(122, 155, 128, 0.12);
    color: #406257;
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
    background: linear-gradient(135deg, #8db094 0%, #6f9370 100%);
    border: none;
    box-shadow: 0 10px 18px rgba(111, 147, 112, 0.2);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #84a98d 0%, #648666 100%);
}

.response-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.option-btn {
    transition: all 0.2s ease;
    font-weight: 600;
    min-height: 52px;
    border-radius: 12px !important;
}

.option-btn.selected {
    box-shadow: inset 0 0 0 2px rgba(255,255,255,0.55);
}

.register-btn {
    min-height: 52px;
    font-weight: 700;
    letter-spacing: 0.02em;
    border-radius: 14px;
}

.companions-box {
    padding: 1rem;
    border: 1px solid rgba(120, 147, 128, 0.18);
    border-radius: 16px;
    background: rgba(244, 243, 236, 0.82);
}

.companions-input {
    border: 1px solid rgba(120, 147, 128, 0.22);
    background: rgba(255,255,255,0.92);
}

@media (max-width: 576px) {
    .page-shell {
        padding: 12px 8px;
    }

    .container {
        padding-left: 0;
        padding-right: 0;
    }

    .invitation-header {
        padding: 1rem 0.8rem 0.9rem;
    }

    .invitation-header h1 {
        font-size: 1.7rem;
    }

    .card-body {
        padding: 1rem !important;
    }

    .guest-name {
        font-size: 2.1rem;
    }

    .event-details {
        padding: 0.8rem 0.8rem;
    }

    .event-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
    }

    .event-item strong {
        text-align: left;
        width: 100%;
    }

    .location-inline {
        width: 100%;
        justify-content: space-between;
    }

    .response-group {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .option-btn,
    .register-btn {
        min-height: 48px;
        font-size: 0.94rem;
    }

    .companions-box {
        padding: 0.8rem;
    }
}
</style>
