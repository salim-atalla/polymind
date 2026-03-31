import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const routes = [
    {
        path: "/",
        redirect: "/chat",
    },
    {
        path: "/login",
        name: "Login",
        component: () => import("../views/LoginView.vue"),
        meta: { guest: true },
    },
    {
        path: "/register",
        name: "Register",
        component: () => import("../views/RegisterView.vue"),
        meta: { guest: true },
    },
    {
        path: "/chat",
        name: "Chat",
        component: () => import("../views/ChatView.vue"),
        meta: { requiresAuth: true },
    },
    {
        path: "/chat/:id",
        name: "ChatConversation",
        component: () => import("../views/ChatView.vue"),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.token) {
        next("/login");
    } else if (to.meta.guest && auth.token) {
        next("/chat");
    } else {
        next();
    }
});

export default router;
