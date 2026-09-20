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
                                <div v-if="!responseSaved" class="form-shell">
                                    <div v-if="errorMessage" class="alert alert-danger mb-0 rounded-3 small">
                                        {{ errorMessage }}
                                    </div>

                                    <div class="text-center intro-block">
                                        <p class="guest-label">{{ guestName ? 'Querido/a' : 'Cargando...' }}</p>
                                        <h2 class="guest-name">{{ guestName || 'Buscando invitado...' }}</h2>
                                    </div>

                                    <div class="event-details">
                                        <div class="event-item">
                                            <span class="label">Fecha</span>
                                            <strong>19 oct 2026</strong>
                                        </div>
                                        <div class="event-item">
                                            <span class="label">Hora</span>
                                            <strong>7:00 PM</strong>
                                        </div>
                                        <div class="event-item location-item">
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

                                    <div class="choice-panel">
                                        <label class="section-label">¿Podrás acompañarnos?</label>
                                        <div class="response-group" role="group" aria-label="Respuesta de asistencia">
                                            <button
                                                type="button"
                                                class="option-btn"
                                                :class="form.confirmation_status === 'confirmed' ? 'selected confirmed' : 'outlined confirmed'"
                                                :disabled="saving"
                                                @click="selectConfirmation('confirmed')"
                                            >
                                                Sí, asistiré
                                            </button>
                                            <button
                                                type="button"
                                                class="option-btn"
                                                :class="form.confirmation_status === 'declined' ? 'selected declined' : 'outlined declined'"
                                                :disabled="saving"
                                                @click="selectConfirmation('declined')"
                                            >
                                                No podré asistir
                                            </button>
                                        </div>
                                    </div>

                                    <div class="companions-box" :class="{ 'is-disabled': form.confirmation_status !== 'confirmed' }">
                                        <div class="companions-header">
                                            <label class="section-label">Cantidad de acompañantes</label>
                                            <span class="mini-badge">{{ form.confirmation_status === 'confirmed' ? 'Activa' : 'En espera' }}</span>
                                        </div>
                                        <input
                                            type="number"
                                            min="0"
                                            max="10"
                                            class="companions-input"
                                            :value="form.companions"
                                            :disabled="form.confirmation_status !== 'confirmed' || saving"
                                            @input="updateCompanions($event.target.value)"
                                        >
                                        <small class="helper-text">
                                            {{ form.confirmation_status === 'confirmed'
                                                ? 'Máximo 2 personas.'
                                                : 'Selecciona “Sí, asistiré” para indicar cuántos acompañarán.' }}
                                        </small>
                                    </div>

                                    <button
                                        v-if="form.confirmation_status"
                                        class="register-btn"
                                        :disabled="saving"
                                        @click="registerResponse"
                                    >
                                        {{ saving ? 'Registrando...' : 'Registrar respuesta' }}
                                    </button>
                                </div>

                                <div v-else class="response-result-card" :class="responseCardClass">
                                    <div class="response-icon">{{ responseSavedStatus === 'confirmed' ? '🎉' : '💙' }}</div>
                                    <p class="response-kicker">
                                        {{ responseSavedStatus === 'confirmed' ? '¡Qué alegría!' : 'Sentimos mucho' }}
                                    </p>
                                    <h3>
                                        {{ responseSavedStatus === 'confirmed'
                                            ? 'Nos alegra mucho que puedas acompañarnos'
                                            : 'Lamentamos no poder compartir este momento contigo' }}
                                    </h3>
                                    <p class="response-message">
                                        {{ responseSavedStatus === 'confirmed'
                                            ? `Gracias por acompañarnos con ${form.companions || 1} ${form.companions === 1 ? 'persona' : 'personas'}. Esperamos celebrarlo contigo con mucho amor.`
                                            : 'Te extrañaremos mucho, pero valoramos tu cariño y te queremos cerca aunque sea desde el corazón.' }}
                                    </p>

                                    <button class="exit-btn" type="button" @click="closeInvitation">
                                        Cerrar
                                    </button>
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
const errorMessage = ref('')
const responseSaved = ref(false)
const responseSavedStatus = ref(null)

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

const responseCardClass = computed(() => ({
    'is-confirmed': responseSavedStatus.value === 'confirmed',
    'is-declined': responseSavedStatus.value === 'declined',
}))

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
    errorMessage.value = ''

    try {
        const payload = {
            confirmation_status: status,
            companions: status === 'confirmed' ? Number(form.value.companions ?? 0) : 0,
        }

        await api.post(`/rsvp/${route.params.token}`, payload)
        guest.value.confirmation_status = status
        responseSaved.value = true
        responseSavedStatus.value = status
        success.value = true
    } catch (error) {
        console.error(error)
        errorMessage.value = 'No pudimos registrar tu respuesta. Inténtalo de nuevo.'
    } finally {
        saving.value = false
    }
}

const closeInvitation = () => {
    if (window.opener) {
        try {
            window.close()
            return
        } catch (error) {
            console.warn('No se pudo cerrar la ventana abierta por script.', error)
        }
    }

    if (window.history.length > 1) {
        window.history.back()
        return
    }

    window.location.href = 'about:blank'
}

onMounted(() => {
    loadGuest()
})
</script>

<style scoped>
.page-shell {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(146, 179, 152, 0.16), transparent 26%),
        radial-gradient(circle at bottom right, rgba(175, 190, 168, 0.14), transparent 28%),
        linear-gradient(135deg, #f7f4ef 0%, #ecf3ee 45%, #f3f7f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 12px;
}

.invitation-card {
    width: min(100%, 430px);
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid rgba(122, 148, 123, 0.16);
    border-radius: 26px;
    box-shadow: 0 22px 48px rgba(81, 101, 82, 0.12);
    overflow: hidden;
}

.invitation-header {
    background: linear-gradient(135deg, #8da88b 0%, #728f73 42%, #b7c7b3 100%);
    color: #fff;
    text-align: center;
    padding: 1rem 0.9rem 0.9rem;
}

.eyebrow {
    margin: 0 0 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.12rem;
    font-size: 0.62rem;
    opacity: 0.92;
    font-weight: 700;
}

.invitation-header h1 {
    margin: 0;
    font-size: clamp(1.35rem, 5vw, 1.7rem);
    font-weight: 700;
    font-family: Georgia, 'Times New Roman', serif;
    letter-spacing: -0.04em;
}

.card-body {
    padding: 1rem !important;
}

.form-shell {
    display: grid;
    gap: 0.9rem;
}

.intro-block {
    margin-top: 0.2rem;
}

.guest-label {
    margin: 0 0 0.35rem;
    color: #6f8579;
    text-transform: uppercase;
    letter-spacing: 0.12rem;
    font-size: 0.68rem;
    font-weight: 700;
}

.guest-name {
    margin: 0;
    font-size: clamp(2rem, 7vw, 2.5rem);
    line-height: 1.1;
    color: #243b35;
    font-family: Georgia, 'Times New Roman', serif;
    letter-spacing: -0.05em;
}

.event-details {
    display: grid;
    gap: 0.4rem;
    padding: 0.8rem 0.9rem;
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(248,245,239,0.95), rgba(240,245,240,0.95));
    border: 1px solid rgba(120, 147, 128, 0.18);
}

.event-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.6rem;
    padding: 0.32rem 0;
    border-bottom: 1px dashed rgba(107, 122, 110, 0.2);
}

.event-item:last-child {
    border-bottom: none;
}

.label {
    color: #687d73;
    font-size: 0.76rem;
    font-weight: 700;
}

.event-item strong {
    color: #2d463f;
    font-size: 0.9rem;
    font-weight: 700;
    text-align: right;
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
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 50%;
    background: rgba(122, 155, 128, 0.12);
    color: #406257;
    text-decoration: none;
    font-size: 1rem;
    flex-shrink: 0;
}

.choice-panel {
    display: grid;
    gap: 0.65rem;
    padding: 0.2rem 0;
}

.section-label {
    margin: 0;
    color: #2f473f;
    font-size: 0.88rem;
    font-weight: 700;
}

.response-group {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.6rem;
}

.option-btn {
    min-height: 50px;
    border-radius: 14px;
    border: 1px solid transparent;
    font-weight: 700;
    font-size: 0.92rem;
    transition: all 0.2s ease;
}

.option-btn.selected {
    box-shadow: 0 12px 20px rgba(91, 121, 96, 0.12);
    transform: translateY(-1px);
}

.option-btn.confirmed {
    background: #dfeee1;
    border-color: rgba(120, 164, 126, 0.32);
    color: #2d5a3d;
}

.option-btn.declined {
    background: #f3e9eb;
    border-color: rgba(164, 117, 130, 0.25);
    color: #7a4a52;
}

.option-btn.outlined {
    background: transparent;
    color: #4d6259;
    border-color: rgba(117, 147, 120, 0.2);
}

.companions-box {
    display: grid;
    gap: 0.55rem;
    padding: 0.9rem 0.9rem 0.7rem;
    border-radius: 16px;
    background: rgba(244, 243, 236, 0.9);
    border: 1px solid rgba(121, 145, 126, 0.18);
}

.companions-box.is-disabled {
    opacity: 0.72;
}

.companions-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.mini-badge {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08rem;
    padding: 0.28rem 0.5rem;
    border-radius: 999px;
    background: rgba(127, 163, 132, 0.12);
    color: #4c6d59;
}

.companions-input {
    width: 100%;
    min-height: 46px;
    border: 1px solid rgba(120, 147, 128, 0.24);
    border-radius: 12px;
    background: rgba(255,255,255,0.9);
    color: #1f2f2c;
    font-size: 1.05rem;
    padding: 0.7rem 0.8rem;
    outline: none;
}

.companions-input:focus {
    border-color: rgba(107, 140, 111, 0.6);
    box-shadow: 0 0 0 4px rgba(128, 169, 133, 0.15);
}

.helper-text {
    display: block;
    margin: 0;
    color: #6a7d72;
    font-size: 0.74rem;
    line-height: 1.4;
}

.register-btn {
    min-height: 52px;
    border: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, #95b79a 0%, #6d9070 100%);
    color: #fff;
    font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow: 0 14px 24px rgba(111, 147, 112, 0.2);
}

.response-result-card {
    display: grid;
    gap: 0.8rem;
    padding: 1.4rem 1rem;
    border-radius: 20px;
    text-align: center;
    background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(243,248,243,0.94));
    border: 1px solid rgba(120, 147, 128, 0.2);
    box-shadow: 0 14px 30px rgba(88, 108, 88, 0.08);
}

.response-result-card.is-confirmed {
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(234,245,235,0.96));
    border-color: rgba(120, 164, 126, 0.33);
}

.response-result-card.is-declined {
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(245,239,240,0.96));
    border-color: rgba(164, 117, 130, 0.28);
}

.response-icon {
    font-size: 2.8rem;
    line-height: 1;
}

.response-kicker {
    margin: 0;
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.12rem;
    text-transform: uppercase;
    color: #617d6d;
}

.response-result-card h3 {
    margin: 0;
    color: #2b433d;
    font-size: clamp(1.45rem, 5vw, 1.9rem);
    line-height: 1.25;
    font-family: Georgia, 'Times New Roman', serif;
}

.response-message {
    margin: 0;
    color: #4e665c;
    line-height: 1.7;
    font-size: 0.94rem;
}

.exit-btn {
    margin-top: 0.3rem;
    border: 0;
    border-radius: 12px;
    min-height: 46px;
    background: rgba(103, 120, 108, 0.12);
    color: #365145;
    font-weight: 700;
}

@media (max-width: 576px) {
    .page-shell {
        padding: 10px 8px;
    }

    .invitation-card {
        width: min(100%, 390px);
    }

    .card-body {
        padding: 0.9rem !important;
    }

    .response-group {
        gap: 0.5rem;
    }

    .option-btn {
        min-height: 46px;
        font-size: 0.86rem;
    }

    .register-btn {
        min-height: 48px;
    }
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
