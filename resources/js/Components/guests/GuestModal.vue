<template>
    <div>

                <div class="modal-header">
                    <div class="header-content">
                        <span class="header-badge">Invitación</span>
                        <h5 class="modal-title">
                            {{ isEditing ? 'Editar Invitado' : 'Nuevo Invitado' }}
                        </h5>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        @click="close"
                        aria-label="Cerrar"
                    >
                    </button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Cédula / CI
                        </label>

                        <input
                            :value="form.ci"
                            @input="e => updateField('ci', e.target.value)"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nombre
                        </label>

                        <input
                            :value="form.first_name"
                            @input="e => updateField('first_name', e.target.value)"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Apellido
                        </label>

                        <input
                            :value="form.last_name"
                            @input="e => updateField('last_name', e.target.value)"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>

                        <input
                            :value="form.email"
                            @input="e => updateField('email', e.target.value)"
                            type="email"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Teléfono
                        </label>

                        <input
                            :value="form.phone"
                            @input="e => updateField('phone', e.target.value)"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Invitaciones
                        </label>

                        <input
                            :value="form.invitations"
                            @input="e => updateField('invitations', e.target.value)"
                            type="number"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Confirmación de asistencia
                        </label>

                        <select
                            :value="form.confirmacion || 'pendiente'"
                            @change="e => updateField('confirmacion', e.target.value)"
                            class="form-select"
                        >
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Notas
                        </label>

                        <input
                            :value="form.notes"
                            @input="e => updateField('notes', e.target.value)"
                            type="text"
                            class="form-control"
                        >
                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-cancel"
                        @click="close"
                    >
                        Cancelar
                    </button>

                    <button
                        class="btn btn-save"
                        @click="onSave"
                    >
                        Guardar
                    </button>

                </div>

    </div>
</template>

<script setup>
import { toRef } from 'vue'

const props = defineProps({
    showModal: { type: Boolean, default: false },
    form: { type: Object, default: () => ({ ci: '', first_name: '', last_name: '', phone: '', email: '', invitations: 1, confirmacion: 'pendiente', notes: '' }) },
    isEditing: { type: Boolean, default: false },
})

const emit = defineEmits(['update:showModal', 'update:form', 'save-guest'])

const form = toRef(props, 'form')

const close = () => {
    emit('update:showModal', false)
}

const updateField = (key, value) => {
    emit('update:form', {
        ...form.value,
        [key]: value,
    })
}

const onSave = () => {
    emit('save-guest')
}

</script>

<style scoped>
:global(.modal-content) {
    border: 1px solid rgba(124, 154, 134, 0.22);
    border-radius: 22px;
    overflow: hidden;
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,244,236,0.98));
    box-shadow: 0 24px 60px rgba(76, 101, 85, 0.18);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.15rem 0.9rem;
    border-bottom: 1px solid rgba(133, 163, 141, 0.18);
    background: linear-gradient(135deg, rgba(160, 185, 167, 0.12), rgba(240, 220, 180, 0.12));
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.header-badge {
    display: inline-flex;
    align-self: flex-start;
    background: rgba(122, 155, 128, 0.12);
    color: #597563;
    border: 1px solid rgba(122, 155, 128, 0.2);
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.modal-title {
    margin: 0;
    color: #2e473f;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(1.4rem, 2vw, 1.8rem);
    letter-spacing: -0.04em;
}

.btn-close {
    background: rgba(122, 155, 128, 0.1);
    border: 1px solid rgba(122, 155, 128, 0.18);
    border-radius: 50%;
    padding: 0.6rem;
    opacity: 1;
    box-shadow: none;
}

.btn-close:hover {
    background: rgba(122, 155, 128, 0.14);
}

.modal-body {
    padding: 1.15rem 1.15rem 0.8rem;
    background: rgba(255,255,255,0.35);
}

.form-label {
    margin-bottom: 0.45rem;
    color: #4d645d;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.form-control,
.form-select {
    border: 1px solid rgba(134, 159, 146, 0.25);
    background: rgba(255, 255, 255, 0.82);
    color: #2b413d;
    border-radius: 12px;
    min-height: 42px;
    box-shadow: inset 0 1px 2px rgba(97, 119, 106, 0.04);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: rgba(122, 155, 128, 0.8);
    box-shadow: 0 0 0 0.2rem rgba(122, 155, 128, 0.15);
    background: #fff;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 0.8rem 1.15rem 1rem;
    border-top: 1px solid rgba(133, 163, 141, 0.18);
    background: rgba(249, 245, 236, 0.7);
}

.btn-cancel,
.btn-save {
    border-radius: 12px;
    padding: 0.62rem 1.1rem;
    font-weight: 700;
    border: 1px solid transparent;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-cancel {
    background: rgba(122, 155, 128, 0.08);
    border-color: rgba(122, 155, 128, 0.18);
    color: #415d51;
}

.btn-cancel:hover {
    background: rgba(122, 155, 128, 0.12);
}

.btn-save {
    background: linear-gradient(135deg, #a9c0ac 0%, #7f9d88 100%);
    color: #fff;
    box-shadow: 0 10px 18px rgba(115, 146, 125, 0.18);
}

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 20px rgba(115, 146, 125, 0.22);
    color: #fff;
}

@media (max-width: 767.98px) {
    :global(.modal-dialog) {
        margin: 1rem auto;
        max-width: calc(100% - 1rem);
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding-left: 0.9rem;
        padding-right: 0.9rem;
    }

    .modal-footer {
        flex-direction: column-reverse;
    }

    .btn-cancel,
    .btn-save {
        width: 100%;
    }
}
</style>