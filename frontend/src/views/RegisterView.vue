<template>
    <div class="auth-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="auth-container">
            <div class="logo">
                <div class="logo-icon">🧠</div>
                <h1>PolyMind</h1>
                <p>One chat. Multiple AI brains.</p>
            </div>

            <div class="glass auth-card">
                <h2>Create account</h2>
                <p class="subtitle">Start chatting with multiple AIs</p>

                <div v-if="error" class="error-banner">{{ error }}</div>

                <form @submit.prevent="handleRegister">
                    <div class="field">
                        <label>Full Name</label>
                        <input
                            v-model="form.fullName"
                            type="text"
                            class="glass-input"
                            placeholder="John Doe"
                            required
                        />
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="glass-input"
                            placeholder="you@example.com"
                            required
                        />
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="glass-input"
                            placeholder="••••••••"
                            required
                            minlength="6"
                        />
                    </div>

                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="loading"
                    >
                        <span v-if="loading">Creating account...</span>
                        <span v-else>Create account</span>
                    </button>
                </form>

                <p class="switch-link">
                    Already have an account?
                    <RouterLink to="/login">Sign in</RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import api from "../services/api";

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const error = ref("");

const form = ref({
    fullName: "",
    email: "",
    password: "",
});

async function handleRegister() {
    loading.value = true;
    error.value = "";
    try {
        const { data } = await api.post("/api/auth/register", form.value);
        auth.setAuth(data.token, data.user);
        router.push("/chat");
    } catch (e) {
        error.value = e.response?.data?.error || "Registration failed";
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-bg {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.35;
    animation: float 8s ease-in-out infinite;
}

.orb-1 {
    width: 400px;
    height: 400px;
    background: #a855f7;
    top: -100px;
    right: -100px;
    animation-delay: 0s;
}

.orb-2 {
    width: 300px;
    height: 300px;
    background: #6b8cff;
    bottom: -80px;
    left: -80px;
    animation-delay: 3s;
}

.orb-3 {
    width: 200px;
    height: 200px;
    background: #06b6d4;
    top: 40%;
    right: 20%;
    animation-delay: 5s;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px) scale(1);
    }
    50% {
        transform: translateY(-30px) scale(1.05);
    }
}

.auth-container {
    width: 100%;
    max-width: 420px;
    padding: 1.5rem;
    position: relative;
    z-index: 10;
}

.logo {
    text-align: center;
    margin-bottom: 2rem;
}

.logo-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.logo h1 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #a855f7, #6b8cff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.logo p {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.auth-card {
    padding: 2rem;
}

.auth-card h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.9rem;
    margin-bottom: 1.75rem;
}

.error-banner {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #fca5a5;
    margin-bottom: 1.25rem;
}

.field {
    margin-bottom: 1.25rem;
}

.field label {
    display: block;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 0.4rem;
}

.btn-primary {
    margin-top: 0.5rem;
}

.switch-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.5);
}

.switch-link a {
    color: #a855f7;
    text-decoration: none;
    font-weight: 500;
}

.switch-link a:hover {
    text-decoration: underline;
}
</style>
