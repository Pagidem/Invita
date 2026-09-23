<template>
    <div class="login-shell">
        <div class="login-panel">
            <div class="brand-panel">
                <h1>Bienvenido a nuestra historia</h1>
                <p class="brand-text">
                    Ingresa tus datos para acceder al panel de administración de nuestra boda.
                </p>
                
            </div>

            <div class="form-panel">
                <div class="login-card">
                    <p class="card-kicker">Acceso al sistema</p>
                    <h2>Iniciar sesión</h2>

                    <div v-if="error" class="alert alert-danger custom-alert">
                        {{ error }}
                    </div>

                    <form @submit.prevent="login" class="login-form">
                        <div class="mb-3 field-group">
                            <label class="form-label">Correo electrónico</label>
                            <input
                                v-model="ema"
                                type="email"
                                class="form-control custom-input"
                                placeholder="tucorreo@ejemplo.com"
                                required
                            >
                        </div>

                        <div class="mb-4 field-group">
                            <label class="form-label">Contraseña</label>
                            <input
                                v-model="pas"
                                type="password"
                                class="form-control custom-input"
                                placeholder="••••••••"
                                required
                            >
                        </div>

                        <button
                                                    type="submit"
                                                    class="btn btn-primary w-100 custom-button"
                                                    :disabled="loading"
                                                    :class="{ 'btn-loading': loading }"
                                                >
                                                    <span v-if="loading" class="btn-spinner" aria-hidden="true"></span>
                                                    <span class="btn-text">{{ loading ? 'Ingresando...' : 'Ingresar' }}</span>
                                                </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

import { useRouter } from 'vue-router';
import auth from '../Services/auth.js';
import { initAuthUser } from '../Composables/useAuthUser.js';

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

        // Inicializar usuario global (una sola vez)
        await initAuthUser();

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

<style scoped>
:global(body) {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(244, 230, 216, 0.8), transparent 28%),
        linear-gradient(135deg, #f8f5f1 0%, #f3e9e0 40%, #edf0ea 100%);
}

.login-shell {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 20px;
    background:
        linear-gradient(135deg, rgba(242, 233, 224, 0.85), rgba(223, 231, 222, 0.7)),
        #f8f5f1;
}

.login-panel {
    width: min(1100px, 100%);
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    border-radius: 28px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.38);
    backdrop-filter: blur(10px);
    box-shadow: 0 28px 60px rgba(93, 75, 65, 0.14);
    border: 1px solid rgba(186, 154, 110, 0.25);
    margin: auto;
}

.brand-panel {
    position: relative;
    padding: 60px 44px;
    background:
        linear-gradient(160deg, rgba(244, 230, 216, 0.96), rgba(255, 255, 255, 0.7) 55%, rgba(201, 209, 194, 0.7));
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 310px;
}

.brand-panel::before,
.brand-panel::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
}

.brand-panel::before {
    width: 220px;
    height: 220px;
    right: -80px;
    top: -60px;
}

.brand-panel::after {
    width: 170px;
    height: 170px;
    left: -60px;
    bottom: -30px;
}

.brand-mark {
    position: relative;
    z-index: 1;
    width: 78px;
    height: 78px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #d7b77a, #f0d9a7);
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    box-shadow: 0 16px 32px rgba(167, 126, 77, 0.28);
    font-family: Georgia, 'Times New Roman', serif;
}

.eyebrow,
.card-kicker {
    position: relative;
    z-index: 1;
    margin: 18px 0 10px;
    color: #876a52;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    font-size: 0.72rem;
    font-weight: 600;
}

.brand-panel h1 {
    position: relative;
    z-index: 1;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.3rem, 4vw, 4rem);
    color: #5d4b41;
    line-height: 1.08;
    letter-spacing: -0.03em;
    margin: 0 0 18px;
}

.brand-text {
    position: relative;
    z-index: 1;
    max-width: 400px;
    font-size: 1.04rem;
    line-height: 1.7;
    color: rgba(93, 75, 65, 0.8);
    margin: 0;
}

.palette-preview {
    position: relative;
    z-index: 1;
    display: flex;
    gap: 14px;
    margin-top: 30px;
    align-items: center;
}

.swatch {
    display: inline-block;
    width: 54px;
    height: 54px;
    border-radius: 16px;
    border: 1px solid rgba(93, 75, 65, 0.12);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
}

.swatch.champagne {
    background: linear-gradient(135deg, #f4e6d8, #e9d8c6);
}

.swatch.sage {
    background: linear-gradient(135deg, #bfd0c1, #9eaf9d);
}

.swatch.gold {
    background: linear-gradient(135deg, #e7cf9d, #d1b06f);
}

.form-panel {
    background: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
}

.login-card {
    width: min(100%, 430px);
    background: rgba(255, 255, 255, 0.74);
    border: 1px solid rgba(186, 154, 110, 0.15);
    border-radius: 24px;
    box-shadow: 0 18px 40px rgba(95, 82, 70, 0.08);
    padding: 30px 26px;
}

.login-card h2 {
    margin: 0 0 22px;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2rem, 3vw, 2.7rem);
    color: #5d4b41;
    letter-spacing: -0.03em;
}

.custom-alert {
    border-radius: 12px;
    font-size: 0.95rem;
    margin-bottom: 18px;
    border: 1px solid rgba(220, 53, 69, 0.18);
    background: rgba(220, 53, 69, 0.08);
    color: #8b1d2d;
}

.login-form {
    margin-top: 10px;
}

.field-group {
    text-align: left;
}

.form-label {
    color: #5d4b41;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.custom-input {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(169, 150, 135, 0.3);
    border-radius: 12px;
    padding: 0.8rem 0.95rem;
    color: #4c3a32;
    box-shadow: none;
    transition: all 0.2s ease;
}

.custom-input:focus {
    border-color: rgba(194, 167, 122, 0.9);
    box-shadow: 0 0 0 0.2rem rgba(215, 183, 122, 0.2);
    background: #fff;
}

.custom-button {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #d7b77a 0%, #c9a15c 100%);
    color: #fff;
    padding: 0.9rem 1rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 14px 24px rgba(199, 160, 91, 0.28);
}

.custom-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 18px 28px rgba(199, 160, 91, 0.3);
}

.custom-button:disabled {
    opacity: 0.8;
    cursor: wait;
}

/* Spinner en botón de login */
.custom-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-spinner {
    width: 20px;
    height: 20px;
    border: 2.5px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: btnSpin 0.75s linear infinite;
    flex-shrink: 0;
}

.btn-loading .btn-text {
    opacity: 0.9;
}

@keyframes btnSpin {
    to { transform: rotate(360deg); }
}

@media (max-width: 900px) {
    .login-shell {
        padding: 10px 12px;
        align-items: center;
    }

    .login-panel {
        grid-template-columns: 1fr;
        width: 100%;
        margin: 0;
    }

    .brand-panel {
        display: none;
    }

    .form-panel {
        padding: 20px 14px 22px;
    }
}
</style>
