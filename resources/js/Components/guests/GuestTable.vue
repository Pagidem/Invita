<template>

<table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Telefono</th>
                                <th width="150">Acciones</th>
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
                                
                                    <button
                                        class="btn btn-sm btn-outline-primary me-2" 
                                        @click="editGuest(guest)"
                                    >
                                        Editar
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

const emit = defineEmits(['edit-guest', 'change-page'])

const editGuest = (guest) => {
    emit('edit-guest', guest)
}

const changePage = (page) => {
    emit('change-page', page)
}

</script>