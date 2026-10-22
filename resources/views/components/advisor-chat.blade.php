<section aria-label="Advisor chat"
         x-data="{
             messages: [],
             input: '',
             send() {
                 const text = this.input.trim();
                 if (!text) return;
                 this.messages.push({ id: Date.now(), text });
                 this.input = '';
                 this.$nextTick(() => { this.$refs.list.scrollTop = this.$refs.list.scrollHeight; });
             }
         }"
         class="flex h-full flex-col rounded-md border border-border bg-surface">
    <header class="border-b border-border px-5 py-4">
        <h2 class="font-display text-base font-semibold text-accent">Advisor</h2>
        <p class="mt-0.5 text-xs text-muted">Ask about your spending.</p>
    </header>

    <div x-ref="list" aria-live="polite" class="flex-1 space-y-3 overflow-y-auto px-5 py-5">
        <p x-show="messages.length === 0" class="text-sm text-muted">
            This is a placeholder. Your messages show up here, but there is no reply yet.
        </p>
        <template x-for="m in messages" :key="m.id">
            <div class="flex justify-end">
                <div class="max-w-[85%] rounded-md border border-border bg-surface-raised px-3 py-2 text-sm text-ink" x-text="m.text"></div>
            </div>
        </template>
    </div>

    <form @submit.prevent="send()" class="flex gap-2 border-t border-border p-4">
        <input type="text" x-model="input" placeholder="Ask the advisor..." aria-label="Message the advisor"
               class="min-w-0 flex-1 rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none">
        <button type="submit" class="shrink-0 rounded-md bg-accent px-4 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90">Send</button>
    </form>
</section>