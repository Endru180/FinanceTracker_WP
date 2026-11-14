<section aria-label="Advisor chat"
         x-data="{
             messages: [],
             input: '',
             loading: false,
             error: '',
             scroll() {
                 this.$nextTick(() => { this.$refs.list.scrollTop = this.$refs.list.scrollHeight; });
             },
             async send() {
                 const text = this.input.trim();
                 if (!text || this.loading) return;
                 this.error = '';
                 this.loading = true;
                 this.messages.push({ id: Date.now(), role: 'user', text });
                 this.input = '';
                 this.scroll();
                 try {
                     const response = await fetch('{{ route('advisor.chat') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                         },
                         body: JSON.stringify({ message: text }),
                     });
                     const data = await response.json().catch(() => ({}));
                     if (!response.ok) {
                         const fallback = response.status === 401 ? 'Sign in to use the advisor.'
                             : response.status === 419 ? 'Your session expired. Refresh the page and sign in again.'
                             : response.status === 429 ? 'Too many messages. Please wait a minute.'
                             : 'The advisor is unavailable. Please try again.';
                         throw new Error(data.message || fallback);
                     }
                     if (!data.reply) throw new Error('No reply received. Please try again.');
                     this.messages.push({ id: Date.now(), role: 'assistant', text: data.reply });
                 } catch (error) {
                     this.error = error instanceof TypeError ? 'Connection failed. Please try again.' : error.message;
                     this.messages.pop();
                     this.input = text;
                 } finally {
                     this.loading = false;
                     this.scroll();
                 }
             }
         }"
         class="flex h-full flex-col rounded-md border border-border bg-surface">
    <header class="border-b border-border px-5 py-4">
        <h2 class="font-display text-base font-semibold text-accent">Advisor</h2>
        <p class="mt-0.5 text-xs text-muted">Ask about your spending.</p>
        <p class="mt-1 text-xs text-muted">Your message and a financial summary are sent to Groq. AI suggestions may be inaccurate.</p>
    </header>

    <div x-ref="list" aria-live="polite" class="flex-1 space-y-3 overflow-y-auto px-5 py-5">
        <p x-show="messages.length === 0" class="text-sm text-muted">
            Ask how your spending compares with your budget, or where you could save this month. Sign in to chat.
        </p>
        <template x-for="m in messages" :key="m.id">
            <div class="flex" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                <div class="max-w-[85%] rounded-md border border-border bg-surface-raised px-3 py-2 text-sm text-ink">
                    <p class="mb-1 text-xs text-muted" x-text="m.role === 'user' ? 'You' : 'Advisor'"></p>
                    <p class="whitespace-pre-wrap break-words" x-text="m.text"></p>
                </div>
            </div>
        </template>
        <p x-show="loading" role="status" class="text-sm text-muted">Advisor is thinking...</p>
    </div>

    <p x-show="error" x-text="error" role="alert" class="px-4 py-2 text-sm text-ink"></p>

    <form @submit.prevent="send()" class="flex gap-2 border-t border-border p-4">
        <input type="text" x-model="input" maxlength="2000" :disabled="loading" placeholder="Ask the advisor..." aria-label="Message the advisor"
               class="min-w-0 flex-1 rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none">
        <button type="submit" :disabled="loading || !input.trim()" class="shrink-0 rounded-md bg-accent px-4 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90 disabled:opacity-50">Send</button>
    </form>
</section>
