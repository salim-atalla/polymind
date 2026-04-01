import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../services/api";

export const useChatStore = defineStore("chat", () => {
    const conversations = ref([]);
    const activeConversation = ref(null);
    const messages = ref([]);
    const loading = ref(false);
    const error = ref("");

    async function fetchConversations() {
        try {
            const { data } = await api.get("/api/conversations");
            conversations.value = data;
        } catch (e) {
            console.error("fetchConversations error:", e);
        }
    }

    async function fetchConversation(id) {
        try {
            const { data } = await api.get(`/api/conversations/${id}`);
            activeConversation.value = data;
            messages.value = data.messages;
        } catch (e) {
            console.error("fetchConversation error:", e);
        }
    }

    async function sendMessage(prompt, conversationId = null) {
        loading.value = true;
        error.value = "";

        // Show user message immediately — keep it even on error
        const tempUserMsg = {
            id: "temp-" + Date.now(),
            role: "user",
            content: prompt,
            created_at: new Date().toISOString(),
        };
        messages.value.push(tempUserMsg);

        try {
            const { data } = await api.post("/api/chat", {
                prompt,
                conversation_id: conversationId,
            });

            // Replace temp message with real one
            const idx = messages.value.findIndex(
                (m) => m.id === tempUserMsg.id
            );
            if (idx !== -1) messages.value.splice(idx, 1, data.user_message);

            // Add assistant message
            messages.value.push(data.assistant_message);

            if (!conversationId) {
                await fetchConversations();
            }

            return data;
        } catch (e) {
            error.value = e.response?.data?.error || "Something went wrong";
            // Don't remove the user message — keep it visible
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function deleteConversation(id) {
        await api.delete(`/api/conversations/${id}`);
        conversations.value = conversations.value.filter((c) => c.id !== id);
        if (activeConversation.value?.id === id) {
            activeConversation.value = null;
            messages.value = [];
        }
    }

    return {
        conversations,
        activeConversation,
        messages,
        loading,
        error,
        fetchConversations,
        fetchConversation,
        sendMessage,
        deleteConversation,
    };
});
