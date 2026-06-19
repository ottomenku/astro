<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
            <div class="text-sm text-gray-500">OpenAI modell: <span id="modelHint">...</span></div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <x-input-label for="modelSelect" value="Modell" />
                        <select id="modelSelect" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></select>
                    </div>
                    <div>
                        <x-input-label for="promptInput" value="Kérdés" />
                        <textarea
                            id="promptInput"
                            rows="5"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Írd be a kérdésed..."
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button type="button" id="sendButton">Küldés</x-primary-button>
                        <div class="text-sm text-gray-500" id="loadingHint" style="display:none;">Küldés...</div>
                    </div>

                    <div class="hidden p-3 rounded border border-red-200 bg-red-50 text-red-800" id="errorBox"></div>

                    <div>
                        <div class="text-xs uppercase text-gray-500 mb-2">Válasz</div>
                        <pre class="whitespace-pre-wrap break-words p-3 rounded bg-gray-50 border" id="responseBox"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
            const modelSelect = document.getElementById('modelSelect');
            const promptInput = document.getElementById('promptInput');
            const sendButton = document.getElementById('sendButton');
            const responseBox = document.getElementById('responseBox');
            const errorBox = document.getElementById('errorBox');
            const modelHint = document.getElementById('modelHint');
            const loadingHint = document.getElementById('loadingHint');

            async function loadModels() {
                try {
                    const response = await fetch('{{ route('chat.models') }}');
                    const data = await response.json();
                    modelSelect.innerHTML = '';
                    (data.models || []).forEach((model) => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        modelSelect.appendChild(option);
                    });
                    modelHint.textContent = modelSelect.value || '-';
                } catch (error) {
                    console.error('Modellek betöltése sikertelen', error);
                }
            }

            async function sendPrompt() {
                errorBox.classList.add('hidden');
                responseBox.textContent = '';

                const prompt = promptInput.value.trim();
                if (!prompt) {
                    errorBox.textContent = 'Adj meg egy promptot.';
                    errorBox.classList.remove('hidden');
                    return;
                }

                sendButton.disabled = true;
                loadingHint.style.display = 'block';

                try {
                    const response = await fetch('{{ route('chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            prompt,
                            model: modelSelect.value,
                        }),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || 'Ismeretlen hiba');
                    }

                    responseBox.textContent = data.response || '';
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('hidden');
                } finally {
                    sendButton.disabled = false;
                    loadingHint.style.display = 'none';
                }
            }

            sendButton.addEventListener('click', sendPrompt);
            modelSelect.addEventListener('change', () => {
                modelHint.textContent = modelSelect.value || '-';
            });
            loadModels();
    </script>
</x-app-layout>