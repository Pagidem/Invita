
<template>
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand-wrap">
                    <span class="brand-mark">I</span>
                    <span class="brand-name">Gestión de invitados</span>
                </div>

                <div class="topbar-actions">
                    <span v-if="user" class="user-pill">
                        {{ user.name }}
                    </span>

                    <button class="logout-toggle" @click="logout" type="button">
                        <span class="toggle-icon">⇠</span>
                        <span class="toggle-text">Salir</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="page-content container-fluid">
            <div class="row g-3 align-items-start">
                <aside class="col-md-3 col-lg-2">
                    <div class="sidebar-card">
                        <p class="sidebar-label">Menú</p>

                        <nav class="sidebar-nav" aria-label="Navegación lateral">
                            <RouterLink
                                to="/dashboard"
                                class="sidebar-link"
                                active-class="active"
                            >
                                Dashboard
                            </RouterLink>

                            <RouterLink
                                to="/guests"
                                class="sidebar-link"
                                active-class="active"
                            >
                                Invitados
                            </RouterLink>

                            <button
                                class="sidebar-action"
                                type="button"
                                @click="goToCreateGuest"
                                aria-label="Nuevo invitado"
                                title="Nuevo invitado"
                            >
                                <span class="action-text">Nuevo invitado</span>
                                <span class="action-plus" aria-hidden="true">+</span>
                            </button>
                        </nav>
                    </div>
                </aside>

                <section class="col-md-9 col-lg-10">
                    <div class="content-panel">
                        <slot />
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import auth from '../Services/auth.js';

const router = useRouter();
const user = ref(null);

const goToCreateGuest = async () => {
    if (router.currentRoute.value.path !== '/guests') {
        await router.push({ path: '/guests' });
    }

    window.dispatchEvent(new CustomEvent('open-create-guest'));
};

onMounted(async () => {
    try {
        const profile = await auth.profile();
        user.value = profile;
        console.log('Perfil del usuario :', profile);
    } catch (err) {
        console.error('Error al obtener el perfil del usuario', err);
        router.push({ name: 'login' });
    }
});

const logout = async () => {
    try {
        await auth.logout();
        console.log('Sesión cerrada correctamente');
        router.push({ name: 'login' });
    } catch (err) {
        console.error('Error al cerrar sesión', err);
        localStorage.removeItem('token');
    }
};

</script>

<style scoped>
:global(body) {
    margin: 0;
    background: linear-gradient(135deg, #f7f5f0 0%, #edf0ea 100%);
    color: #2d463e;
}

* {
    box-sizing: border-box;
}

.app-shell {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(170, 190, 170, 0.2), transparent 25%),
        linear-gradient(135deg, #f7f5f0 0%, #edf0ea 100%);
}

.topbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: rgba(135, 163, 145, 0.96);
    border-bottom: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 6px 18px rgba(88, 113, 95, 0.12);
}

.topbar-inner {
    max-width: 1400px;
    margin: 0 auto;
    min-height: 54px;
    padding: 8px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.brand-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.brand-mark {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #f0dcb0, #d9bb74);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    font-family: Georgia, 'Times New Roman', serif;
    box-shadow: 0 8px 16px rgba(185, 149, 83, 0.2);
}

.brand-name {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(1.3rem, 1.8vw, 1.7rem);
    line-height: 1;
    color: #fff;
    letter-spacing: -0.04em;
    white-space: nowrap;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.user-pill {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.9);
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.02em;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.logout-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border-radius: 999px;
    padding: 5px 10px 5px 8px;
    font-size: 0.72rem;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

.logout-toggle:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-1px);
}

.toggle-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    font-size: 0.9rem;
}

.toggle-text {
    line-height: 1;
}

.page-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 22px 18px 28px;
}

.sidebar-card {
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(120, 147, 128, 0.18);
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(95, 120, 104, 0.08);
    padding: 16px 14px;
    min-height: 200px;
}

.sidebar-label {
    margin: 0 0 12px;
    color: #6d8878;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sidebar-link,
.sidebar-action {
    display: block;
    width: 100%;
    padding: 11px 12px;
    border-radius: 12px;
    text-decoration: none;
    color: #425b52;
    font-weight: 600;
    transition: all 0.2s ease;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sidebar-link:hover,
.sidebar-link.active {
    background: linear-gradient(135deg, rgba(176, 201, 181, 0.22), rgba(214, 228, 217, 0.2));
    color: #2a443d;
    box-shadow: inset 0 0 0 1px rgba(130, 160, 138, 0.18);
}

.sidebar-action {
    border: 1px solid rgba(122, 155, 128, 0.18);
    background: linear-gradient(135deg, #a9c0ac 0%, #7f9d88 100%);
    color: #fff;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 10px 18px rgba(115, 146, 125, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.action-text {
    display: inline;
}

.action-plus {
    display: none;
}

.sidebar-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 20px rgba(115, 146, 125, 0.2);
    color: #fff;
}

.content-panel {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(120, 147, 128, 0.12);
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(95, 120, 104, 0.08);
    padding: 20px;
    min-height: 440px;
}

@media (max-width: 767.98px) {
    .topbar-inner {
        min-height: 52px;
        padding: 7px 10px;
        gap: 8px;
    }

    .brand-wrap {
        flex: 1;
        min-width: 0;
    }

    .brand-mark {
        width: 26px;
        height: 26px;
        font-size: 0.95rem;
    }

    .brand-name {
        font-size: 1.25rem;
    }

    .topbar-actions {
        gap: 6px;
    }

    .user-pill {
        max-width: 110px;
        padding: 5px 8px;
        font-size: 0.72rem;
    }

    .logout-toggle {
        padding: 6px;
        min-width: 32px;
        width: 32px;
        height: 32px;
        justify-content: center;
        border-radius: 50%;
    }

    .toggle-text {
        display: none;
    }

    .page-content {
        padding: 12px 10px 18px;
    }

    .sidebar-card {
        min-height: auto;
        padding: 8px 8px 10px;
        border-radius: 14px;
    }

    .sidebar-label {
        display: none;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 6px;
        width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .sidebar-nav::-webkit-scrollbar {
        display: none;
    }

    .sidebar-link,
    .sidebar-action {
        width: auto;
        min-width: 0;
        flex: 1 1 0;
        text-align: center;
        padding: 8px 8px;
        font-size: 0.72rem;
        border-radius: 10px;
        background: rgba(135, 163, 145, 0.08);
        border: 1px solid rgba(135, 163, 145, 0.14);
        white-space: nowrap;
    }

    .sidebar-link.active {
        background: linear-gradient(135deg, rgba(135, 163, 145, 0.2), rgba(214, 228, 217, 0.28));
        border-color: rgba(135, 163, 145, 0.22);
    }

    .sidebar-action {
        position: fixed;
        right: 18px;
        bottom: 20px;
        z-index: 1200;
        width: 60px;
        min-width: 60px;
        height: 60px;
        padding: 0;
        border-radius: 50%;
        flex: 0 0 60px;
        background: linear-gradient(135deg, #c7d9c8 0%, #7fa287 35%, #6c8e76 100%);
        border: 1px solid rgba(255, 255, 255, 0.55);
        color: #fff;
        box-shadow: 0 14px 26px rgba(83, 118, 92, 0.32), 0 0 0 7px rgba(169, 195, 173, 0.18);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        filter: saturate(1.08);
    }

    .sidebar-action:hover {
        filter: brightness(1.05);
    }

    .sidebar-action:active {
        transform: scale(0.96);
    }

    .action-text {
        display: none;
    }

    .action-plus {
        display: inline-block;
        font-size: 2.7rem;
        line-height: 1;
        font-weight: 300;
        transform: translateY(-1px);
        letter-spacing: -0.06em;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .content-panel {
        min-height: auto;
        padding: 12px;
        border-radius: 14px;
    }
}
</style>
