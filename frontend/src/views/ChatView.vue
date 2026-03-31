<template>
    <div class="chat-layout">
        <!-- Orbs -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <!-- Sidebar -->
        <aside class="glass-dark sidebar">
            <div class="sidebar-header">
                <div class="brand">
                    <span class="brand-icon">🧠</span>
                    <span class="brand-name">PolyMind</span>
                </div>
                <button class="new-chat-btn" @click="startNewChat">
                    <span>+</span> New chat
                </button>
            </div>

            <div class="conversations-list">
                <p class="list-label">Recent</p>
                <div
                    v-for="conv in chat.conversations"
                    :key="conv.id"
                    class="conv-item"
                    :class="{ active: activeId === conv.id }"
                    @click="loadConversation(conv.id)"
                >
                    <div class="conv-title">{{ conv.title }}</div>
                    <div class="conv-meta">
                        <span class="conv-date">{{
                            formatDate(conv.created_at)
                        }}</span>
                        <button
                            class="delete-btn"
                            @click.stop="deleteConv(conv.id)"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <div v-if="chat.conversations.length === 0" class="empty-convs">
                    No conversations yet
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ auth.user?.fullName?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth.user?.fullName }}</div>
                        <div class="user-email">{{ auth.user?.email }}</div>
                    </div>
                </div>
                <button class="logout-btn" @click="handleLogout">↪</button>
            </div>
        </aside>

        <!-- Main chat area -->
        <main class="chat-main">
            <!-- Empty state -->
            <div
                v-if="!activeId && chat.messages.length === 0"
                class="empty-state"
            >
                <div class="empty-icon">🧠</div>
                <h2>What can I help you with?</h2>
                <p>PolyMind automatically routes your prompt to the best AI</p>
                <div class="suggestions">
                    <button
                        v-for="s in suggestions"
                        :key="s.text"
                        class="suggestion-chip"
                        @click="useSuggestion(s.text)"
                    >
                        <span>{{ s.icon }}</span> {{ s.text }}
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div v-else class="messages-container" ref="messagesEl">
                <div
                    v-for="msg in chat.messages"
                    :key="msg.id"
                    class="message-wrapper"
                    :class="msg.role"
                >
                    <!-- User message -->
                    <div
                        v-if="msg.role === 'user'"
                        class="message user-message"
                    >
                        <div class="message-content">{{ msg.content }}</div>
                    </div>

                    <!-- Assistant message -->
                    <div v-else class="message assistant-message">
                        <div class="ai-badge-row">
                            <span
                                class="ai-badge"
                                :class="getBadgeClass(msg.provider)"
                            >
                                {{ getModelIcon(msg.provider) }}
                                {{ msg.model_used }}
                            </span>
                            <span class="intent-badge">{{
                                msg.detected_intent
                            }}</span>
                            <span class="time-badge"
                                >{{ msg.response_time_ms }}ms</span
                            >
                        </div>
                        <div class="message-content">{{ msg.content }}</div>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div v-if="chat.loading" class="message-wrapper assistant">
                    <div class="message assistant-message">
                        <div class="typing">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input area -->
            <div class="input-area">
                <div class="glass input-wrapper">
                    <textarea
                        v-model="prompt"
                        class="chat-input"
                        placeholder="Ask anything — PolyMind picks the best AI for you..."
                        rows="1"
                        @keydown.enter.exact.prevent="send"
                        @input="autoResize"
                        ref="inputEl"
                    ></textarea>
                    <button
                        class="send-btn"
                        :disabled="!prompt.trim() || chat.loading"
                        @click="send"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path
                                d="M22 2L11 13M22 2L15 22L11 13L2 9L22 2Z"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
                <p class="input-hint">
                    Enter to send · Shift+Enter for new line
                </p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useChatStore } from "../stores/chat";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const chat = useChatStore();

const prompt = ref("");
const activeId = ref(null);
const messagesEl = ref(null);
const inputEl = ref(null);

const suggestions = [
    { icon: "💻", text: "Write a Python function to sort a list" },
    { icon: "✉️", text: "Write a professional follow-up email" },
    { icon: "🎨", text: "Generate an image of a futuristic city" },
    { icon: "🔍", text: "Explain how transformers work in AI" },
];

onMounted(async () => {
    await chat.fetchConversations();
    if (route.params.id) {
        await loadConversation(route.params.id);
    }
});

watch(
    () => route.params.id,
    async (id) => {
        if (id) await loadConversation(id);
    }
);

async function loadConversation(id) {
    activeId.value = id;
    await chat.fetchConversation(id);
    await scrollToBottom();
}

function startNewChat() {
    activeId.value = null;
    chat.messages = [];
    router.push("/chat");
}

async function send() {
    const text = prompt.value.trim();
    if (!text || chat.loading) return;

    prompt.value = "";
    resetInputHeight();

    try {
        const result = await chat.sendMessage(text, activeId.value);

        if (!activeId.value) {
            activeId.value = result.conversation_id;
            router.push(`/chat/${result.conversation_id}`);
        }

        await scrollToBottom();
    } catch (e) {
        console.error(e);
    }
}

function useSuggestion(text) {
    prompt.value = text;
    inputEl.value?.focus();
}

async function deleteConv(id) {
    await chat.deleteConversation(id);
    if (activeId.value === id) {
        startNewChat();
    }
}

function handleLogout() {
    auth.logout();
    router.push("/login");
}

async function scrollToBottom() {
    await nextTick();
    if (messagesEl.value) {
        messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
    }
}

function autoResize(e) {
    e.target.style.height = "auto";
    e.target.style.height = Math.min(e.target.scrollHeight, 160) + "px";
}

function resetInputHeight() {
    if (inputEl.value) inputEl.value.style.height = "auto";
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("en", {
        month: "short",
        day: "numeric",
    });
}

function getBadgeClass(provider) {
    return {
        "badge-openai": provider === "openai",
        "badge-anthropic": provider === "anthropic",
        "badge-google": provider === "google",
    };
}

function getModelIcon(provider) {
    const icons = { openai: "⚡", anthropic: "🔮", google: "✨" };
    return icons[provider] || "🤖";
}
</script>

<style scoped>
.chat-layout {
    display: flex;
    height: 100vh;
    overflow: hidden;
    position: relative;
}

.orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.2;
    pointer-events: none;
    z-index: 0;
}

.orb-1 {
    width: 500px;
    height: 500px;
    background: #6b8cff;
    top: -150px;
    left: -150px;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: #a855f7;
    bottom: -100px;
    right: -100px;
}

/* ── Sidebar ── */
.sidebar {
    width: 280px;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    height: 100vh;
    border-radius: 0;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    z-index: 10;
}

.sidebar-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.brand-icon {
    font-size: 1.4rem;
}

.brand-name {
    font-size: 1.1rem;
    font-weight: 700;
    background: linear-gradient(135deg, #6b8cff, #a855f7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.new-chat-btn {
    width: 100%;
    background: rgba(107, 140, 255, 0.15);
    border: 1px solid rgba(107, 140, 255, 0.3);
    border-radius: 8px;
    color: #6b8cff;
    padding: 0.6rem 1rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.new-chat-btn:hover {
    background: rgba(107, 140, 255, 0.25);
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.75rem;
}

.list-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255, 255, 255, 0.3);
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.5rem;
}

.conv-item {
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
    margin-bottom: 0.25rem;
    border: 1px solid transparent;
}

.conv-item:hover {
    background: rgba(255, 255, 255, 0.06);
}

.conv-item.active {
    background: rgba(107, 140, 255, 0.15);
    border-color: rgba(107, 140, 255, 0.3);
}

.conv-title {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.85);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
}

.conv-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.conv-date {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.3);
}

.delete-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.2);
    cursor: pointer;
    font-size: 0.7rem;
    padding: 0.1rem 0.3rem;
    border-radius: 4px;
    transition: all 0.15s;
    opacity: 0;
}

.conv-item:hover .delete-btn {
    opacity: 1;
}

.delete-btn:hover {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.empty-convs {
    text-align: center;
    color: rgba(255, 255, 255, 0.25);
    font-size: 0.8rem;
    padding: 2rem 1rem;
}

.sidebar-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6b8cff, #a855f7);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-details {
    min-width: 0;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.4);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    font-size: 1.1rem;
    padding: 0.25rem;
    transition: color 0.15s;
    flex-shrink: 0;
}

.logout-btn:hover {
    color: rgba(255, 255, 255, 0.7);
}

/* ── Main ── */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    position: relative;
    z-index: 10;
}

/* ── Empty state ── */
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.empty-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
}

.empty-state h2 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

.suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
    max-width: 600px;
}

.suggestion-chip {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: rgba(255, 255, 255, 0.75);
    padding: 0.6rem 1.1rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.suggestion-chip:hover {
    background: rgba(107, 140, 255, 0.15);
    border-color: rgba(107, 140, 255, 0.35);
    color: #fff;
}

/* ── Messages ── */
.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-wrapper {
    display: flex;
}
.message-wrapper.user {
    justify-content: flex-end;
}
.message-wrapper.assistant {
    justify-content: flex-start;
}

.message {
    max-width: 75%;
}

.user-message .message-content {
    background: linear-gradient(135deg, #6b8cff, #4f6ef7);
    border-radius: 18px 18px 4px 18px;
    padding: 0.85rem 1.1rem;
    font-size: 0.95rem;
    line-height: 1.6;
}

.assistant-message {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 4px 18px 18px 18px;
    padding: 0.85rem 1.1rem;
}

.ai-badge-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
    flex-wrap: wrap;
}

.ai-badge {
    font-size: 0.72rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
}

.badge-openai {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #6ee7b7;
}

.badge-anthropic {
    background: rgba(168, 85, 247, 0.15);
    border: 1px solid rgba(168, 85, 247, 0.3);
    color: #d8b4fe;
}

.badge-google {
    background: rgba(59, 130, 246, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #93c5fd;
}

.intent-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.07);
    color: rgba(255, 255, 255, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.time-badge {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.25);
}

.assistant-message .message-content {
    font-size: 0.95rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.88);
    white-space: pre-wrap;
}

/* Typing indicator */
.typing {
    display: flex;
    gap: 5px;
    padding: 0.4rem 0;
}

.typing span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    animation: bounce 1.2s ease-in-out infinite;
}

.typing span:nth-child(2) {
    animation-delay: 0.2s;
}
.typing span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    0%,
    60%,
    100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-8px);
    }
}

/* ── Input area ── */
.input-area {
    padding: 1rem 1.5rem 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 0.75rem;
    padding: 0.75rem 0.75rem 0.75rem 1.1rem;
    border-radius: 14px;
}

.chat-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: #fff;
    font-size: 0.95rem;
    line-height: 1.5;
    resize: none;
    max-height: 160px;
    font-family: inherit;
}

.chat-input::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.send-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6b8cff, #4f6ef7);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.send-btn svg {
    width: 16px;
    height: 16px;
    color: #fff;
}

.send-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(107, 140, 255, 0.4);
}

.send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.input-hint {
    text-align: center;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.2);
    margin-top: 0.6rem;
}
</style>
