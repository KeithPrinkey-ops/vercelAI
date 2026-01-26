<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';

/**
 * Message shape expected by the Vercel AI protocol
 */
type Message = {
    role: 'user' | 'assistant';
    content: string;
};

/**
 * Reactive state
 */
const messages = ref<Message[]>([]);
const input = ref('');
const isLoading = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);

/**
 * Send a message and stream the response
 */
const handleSubmit = async () => {
    if (!input.value.trim()) return;

    // Push user message
    messages.value.push({
        role: 'user',
        content: input.value,
    });
    input.value = '';
    isLoading.value = true;

    // Prepare assistant placeholder
    const assistantMessage: Message = {
        role: 'assistant',
        content: '',
    };
    messages.value.push(assistantMessage);

    const response = await fetch('/api/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            messages: messages.value.slice(0, -1), // exclude placeholder
        }),
    });

    if (!response.body) {
        isLoading.value = false;
        return;
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();

    while (true) {
        const { value, done } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value);
        const lines = chunk.split('\n');

        for (const line of lines) {
            if (line.startsWith('0:')) {
                assistantMessage.content += JSON.parse(line.slice(2));
            }
        }
    }

    isLoading.value = false;
};

/**
 * Auto-scroll on updates
 */
watch(messages, async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
});
</script>

<template>
    <div class="mx-auto flex h-screen max-w-2xl flex-col p-4">
        <div
            class="mb-4 flex-1 overflow-y-auto rounded border border-gray-200 p-4"
            ref="messagesContainer"
        >
            <div
                v-for="(message, index) in messages"
                :key="index"
                :class="[
                    'mb-3 rounded px-3 py-2 wrap-break-words text-black',
                    message.role === 'user'
                        ? 'bg-blue-100 text-right'
                        : 'bg-gray-100 text-left',
                ]"
            >
                {{ message.content }}
            </div>

            <div
                v-if="isLoading"
                class="rounded bg-gray-100 px-3 py-2 text-black italic"
            >
                AI is typing…
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="flex gap-2">
            <input
                v-model="input"
                type="text"
                placeholder="Ask about Laravel 12…"
                :disabled="isLoading"
                class="flex-1 rounded border border-gray-300 p-2 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-300 focus:outline-none disabled:opacity-50"
            />
            <button
                type="submit"
                :disabled="isLoading"
                class="rounded bg-blue-600 px-4 py-2 text-black disabled:cursor-not-allowed disabled:bg-gray-400"
            >
                Send
            </button>
        </form>
    </div>
</template>

<style scoped></style>
