<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
            <div class="text-sm text-gray-500">OpenAI modell: <span id="modelHint">...</span></div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Thread lista -->
                        <div class="md:col-span-1">
                            <div class="flex items-center justify-between">
                                <div class="text-xs uppercase text-gray-500">Beszélgetések</div>
                                <button type="button" id="newThreadBtn" class="text-sm underline text-gray-600">Új</button>
                            </div>
                            <div class="mt-3 border rounded divide-y" id="threadsBox"></div>
                        </div>

                        <!-- Üzenetek + input -->
                        <div class="md:col-span-3 space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-medium text-gray-500">Aktív thread</div>
                                    <div class="mt-1 text-sm" id="activeThreadHint">-</div>
                                </div>
                                <div class="text-xs text-gray-500">Modell: <span id="modelHint">{{ config('services.openai.model') }}</span></div>
                            </div>

                            <div class="hidden p-3 rounded border border-red-200 bg-red-50 text-red-800" id="errorBox"></div>

                            <div class="border rounded bg-gray-50 p-3 h-[420px] overflow-y-auto" id="messagesBox"></div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500" for="promptInput">Üzenet</label>
                                <textarea
                                    id="promptInput"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Írd be a kérdésed..."
                                ></textarea>

                                <div class="flex items-center gap-3 mt-3">
                                    <x-primary-button type="button" id="sendButton">Küldés</x-primary-button>
                                    <div class="text-sm text-gray-500" id="loadingHint" style="display:none;">Küldés...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Relatív URL-ek: így mindegy, hogy localhost vagy 127.0.0.1 alatt nyitod meg az oldalt,
        // a fetch mindig ugyanarra az originre megy (nem lesz auth/cookie gond).
        const threadsIndexUrl = '{{ route('chat.threads.index', [], false) }}';
        const threadsStoreUrl = '{{ route('chat.threads.store', [], false) }}';
        const threadShowBase = threadsIndexUrl; // /chat/threads
        const modelsUrl = '{{ route('chat.models', [], false) }}';
        const sendUrl = '{{ route('chat.send', [], false) }}';
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const modelHint = document.getElementById('modelHint');
        const threadsBox = document.getElementById('threadsBox');
        const messagesBox = document.getElementById('messagesBox');
        const promptInput = document.getElementById('promptInput');
        const sendButton = document.getElementById('sendButton');
        const loadingHint = document.getElementById('loadingHint');
        const errorBox = document.getElementById('errorBox');
        const activeThreadHint = document.getElementById('activeThreadHint');
        const newThreadBtn = document.getElementById('newThreadBtn');

        let activeThreadId = null;
        let threads = [];

        async function readJson(res) {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (!ct.includes('application/json')) {
                const text = await res.text();
                // segít debugolni, ha auth redirect / HTML hibaoldal jön vissza
                throw new Error(`Nem JSON válasz (${res.status}). Kezdet: ${text.slice(0, 120)}`);
            }
            return await res.json();
        }

        function showError(msg) {
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        }

        function clearError() {
            errorBox.classList.add('hidden');
            errorBox.textContent = '';
        }

        async function loadModels() {
            // A modell kiválasztót kivettük a chat UI-ból (admin beállítás lesz).
            // Itt csak a backend default modell nevét mutatjuk.
            try {
                const res = await fetch(modelsUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await readJson(res);
                modelHint.textContent = (data.models && data.models[0]) ? data.models[0] : (modelHint.textContent || '-');
            } catch (e) {
                console.error(e);
            }
        }

        function renderThreads() {
            threadsBox.innerHTML = '';
            if (!threads.length) {
                threadsBox.innerHTML = '<div class="p-3 text-sm text-gray-500">Nincs még thread.</div>';
                return;
            }

            threads.forEach((t) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3 py-2 hover:bg-gray-100 ' + (String(t.id) === String(activeThreadId) ? 'bg-indigo-50' : '');
                btn.textContent = t.title || ('Thread #' + t.id);
                btn.addEventListener('click', async () => {
                    await setActiveThread(t.id);
                });
                threadsBox.appendChild(btn);
            });
        }

        function renderMessages(messages) {
            messagesBox.innerHTML = '';
            if (!messages.length) {
                messagesBox.innerHTML = '<div class="text-sm text-gray-500">Nincs üzenet.</div>';
                return;
            }

            messages.forEach((m) => {
                const wrap = document.createElement('div');
                wrap.className = 'mb-3';
                const role = document.createElement('div');
                role.className = 'text-[11px] uppercase text-gray-500';
                role.textContent = m.role;
                const body = document.createElement('pre');
                body.className = 'whitespace-pre-wrap break-words p-3 rounded border bg-white';
                body.textContent = m.content;
                wrap.appendChild(role);
                wrap.appendChild(body);
                messagesBox.appendChild(wrap);
            });

            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        async function loadThreads() {
            const res = await fetch(threadsIndexUrl, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await readJson(res);
            threads = data.threads || [];
            renderThreads();
            return threads;
        }

        async function createThread(title = null) {
            const res = await fetch(threadsStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ title }),
            });
            const data = await readJson(res);
            if (!res.ok) throw new Error(data.error || 'Thread létrehozása sikertelen');
            return data.thread;
        }

        async function setActiveThread(threadId) {
            activeThreadId = threadId;
            const active = threads.find((t) => String(t.id) === String(threadId));
            activeThreadHint.textContent = active ? (active.title || ('Thread #' + active.id)) : ('Thread #' + threadId);
            renderThreads();

            const res = await fetch(`${threadShowBase}/${threadId}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await readJson(res);
            if (!res.ok) throw new Error(data.error || 'Thread betöltése sikertelen');
            renderMessages(data.messages || []);
        }

        async function sendPrompt() {
            clearError();
            const prompt = promptInput.value.trim();
            if (!prompt) {
                showError('Adj meg egy üzenetet.');
                return;
            }

            sendButton.disabled = true;
            loadingHint.style.display = 'block';

            try {
                if (!activeThreadId) {
                    const t = await createThread('Új beszélgetés');
                    await loadThreads();
                    await setActiveThread(t.id);
                }

                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        thread_id: activeThreadId,
                        prompt,
                        model: null,
                    }),
                });
                const data = await readJson(res);
                if (!res.ok) throw new Error(data.error || 'Ismeretlen hiba');

                promptInput.value = '';
                // frissítjük a thread listát (updated_at változik)
                await loadThreads();
                await setActiveThread(data.thread_id || activeThreadId);
            } catch (e) {
                console.error(e);
                showError(e.message);
            } finally {
                sendButton.disabled = false;
                loadingHint.style.display = 'none';
            }
        }

        newThreadBtn.addEventListener('click', async () => {
            clearError();
            try {
                const t = await createThread('Új beszélgetés');
                await loadThreads();
                await setActiveThread(t.id);
            } catch (e) {
                showError(e.message);
            }
        });

        sendButton.addEventListener('click', sendPrompt);
        (async () => {
            await loadModels();
            await loadThreads();
            if (threads.length) {
                await setActiveThread(threads[0].id);
            }
        })();
    </script>
</x-app-layout>
