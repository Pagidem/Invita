<template>
    <div class="container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow">

                    <div class="card-body">

                        <h2 class="text-center mb-4">
                            Iniciar sesión
                        </h2>

                        <div
                            v-if="error"
                            class="alert alert-danger"
                        >
                            {{ error }}
                        </div>

                        <form @submit.prevent="login">

                            <div class="mb-3">

                                <label class="form-label">
                                    Correo electrónico
                                </label>

                                <input
                                    v-model="ema"
                                    type="email"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Contraseña
                                </label>

                                <input
                                    v-model="pas"
                                    type="password"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="loading"
                            >
                                {{ loading ? 'Ingresando...' : 'Ingresar' }}
                            </button>

                            

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue';

import { useRouter } from 'vue-router';
import auth from '../Services/auth.js';

const router = useRouter();

const ema = ref('');
const pas = ref('');
const loading = ref(false);
const error = ref('');


const login = async () => {

    error.value = '';
    loading.value = true;

    try {
        const response = await auth.login(
            ema.value,
            pas.value
        );

        console.log('Login correcto :', response);

        const profile = await auth.profile();

        console.log('Perfil del usuario :', profile);

        router.push({ name: 'dashboard' });

    } catch (err) {
        console.error('Error al iniciar sesión', err);

        if (err.response?.status === 422) {
            error.value = 'Correo electrónico o contraseña incorrectos.';
        } else {
            error.value = 'Error al iniciar sesión. Por favor, inténtalo de nuevo más tarde.';
        }


    } finally {
        loading.value = false;
    }


};



</script>