import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../services/api";

export const useChatStore = defineStore("chat", () => {
    const conversations = ref([]);
    const activeConversation = ref(null);
    const messages = ref([]);
    const loading = ref(false);

    async function fetchConversations() {
        const { data } = await api.get("/api/conversations");
        conversations.value = data;
    }

    async function fetchConversation(id) {
        const { data } = await api.get(`/api/conversations/${id}`);
        activeConversation.value = data;
        messages.value = data.messages;
    }

    async function sendMessage(prompt, conversationId = null) {
        loading.value = true;
        try {
            const { data } = await api.post("/api/chat", {
                prompt,
                conversation_id: conversationId,
            });

            if (!conversationId) {
                await fetchConversations();
            }

            messages.value.push(data.user_message);
            messages.value.push(data.assistant_message);

            return data;
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
        fetchConversations,
        fetchConversation,
        sendMessage,
        deleteConversation,
    };
});
