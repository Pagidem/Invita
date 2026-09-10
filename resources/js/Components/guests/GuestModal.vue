<template>
    <div>

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ isEditing ? 'Editar Invitado' : 'Nuevo Invitado' }}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        @click="close"
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
                        class="btn btn-secondary"
                        @click="close"
                    >
                        Cancelar
                    </button>

                    <button
                        class="btn btn-primary"
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
    form: { type: Object, default: () => ({ ci: '', first_name: '', last_name: '', phone: '', email: '', invitations: 1, notes: '' }) },
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