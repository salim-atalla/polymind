import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../services/api";

export const useAuthStore = defineStore("auth", () => {
    const token = ref(localStorage.getItem("token") || null);

    // Fix: safely parse user from localStorage
    const storedUser = localStorage.getItem("user");
    const user = ref(
        storedUser && storedUser !== "undefined" ? JSON.parse(storedUser) : null
    );

    const isAuthenticated = computed(() => !!token.value);

    function setAuth(newToken, newUser) {
        token.value = newToken;
        user.value = newUser;
        localStorage.setItem("token", newToken);
        localStorage.setItem("user", JSON.stringify(newUser));
    }

    function logout() {
        token.value = null;
        user.value = null;
        localStorage.removeItem("token");
        localStorage.removeItem("user");
    }

    return { token, user, isAuthenticated, setAuth, logout };
});
