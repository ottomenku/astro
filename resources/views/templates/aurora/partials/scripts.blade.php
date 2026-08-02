<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function openModal(id) {
            document.getElementById(id)?.classList.add('is-open');
        }

        function closeModal(modal) {
            modal?.classList.remove('is-open');
        }

        document.querySelectorAll('[data-aurora-modal-close]').forEach((el) => {
            el.addEventListener('click', () => closeModal(el.closest('.aurora-modal')));
        });

        document.querySelectorAll('.aurora-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.querySelectorAll('.aurora-open-auth').forEach((btn) => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.authTab || 'login';
                const redirect = btn.dataset.authRedirect || '';
                if (redirect) {
                    sessionStorage.setItem('auroraPostAuthRedirect', redirect);
                }
                window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab } }));
            });
        });

        document.querySelectorAll('.aurora-topic-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const label = btn.dataset.topicLabel || '';
                const store = document.getElementById('auroraTopicStore');
                const topic = btn.dataset.topic || '';
                const datasetKey = 'topic' + topic.charAt(0).toUpperCase() + topic.slice(1);
                let content = btn.dataset.topicContent || store?.dataset[datasetKey] || '';

                if (!content) {
                    return;
                }

                document.getElementById('auroraTopicModalTitle').textContent = label;
                document.getElementById('auroraTopicModalBody').textContent = content;
                openModal('auroraTopicModal');
            });
        });

        document.querySelectorAll('.aurora-read-more-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const store = document.getElementById('auroraTopicStore');
                const text = (store?.dataset.readMore || '').trim();

                if (!text) {
                    return;
                }

                document.getElementById('auroraReadMoreModalBody').textContent = text;
                openModal('auroraReadMoreModal');
            });
        });

        document.querySelectorAll('.aurora-locale-pill-form input[name="locale"]').forEach((input) => {
            input.addEventListener('change', () => {
                input.form?.requestSubmit?.() ?? input.form?.submit();
            });
        });

        @if (($mode ?? 'home') === 'personal')
        const HAS_BIRTH_CHART = @json($hasBirthChart ?? false);
        let selectedPeriod = @json($period ?? 'daily');
        const dailyUrl = @json(route('horoscope.daily-message', [], false));
        const i18n = @json(__('horoscope.js'));
        const birthChartSelect = document.getElementById('messageBirthChartSelect');
        const hiddenFocus = document.getElementById('personalMessageUserFocus');
        const modalFocus = document.getElementById('auroraOwnQuestionFocus');
        const messageBody = document.getElementById('auroraMessageBody');
        const messageLoading = document.getElementById('auroraMessageLoading');
        const messageEmpty = document.getElementById('auroraMessageEmpty');
        const messageError = document.getElementById('auroraMessageError');
        const topicStore = document.getElementById('auroraTopicStore');
        const mottoEl = document.getElementById('auroraMotto');

        document.querySelectorAll('#auroraPeriodSelector input[name="period"]').forEach((input) => {
            input.addEventListener('change', () => {
                selectedPeriod = input.value;
                generateMessage();
            });
        });

        document.getElementById('auroraOwnQuestionBtn')?.addEventListener('click', () => {
            if (!HAS_BIRTH_CHART) {
                window.showBirthChartRequiredModal?.();
                return;
            }

            if (modalFocus && hiddenFocus) {
                modalFocus.value = hiddenFocus.value || '';
            }

            openModal('auroraOwnQuestionModal');
        });

        document.getElementById('auroraGenerateFromModalBtn')?.addEventListener('click', () => {
            if (hiddenFocus && modalFocus) {
                hiddenFocus.value = modalFocus.value.trim();
            }

            closeModal(document.getElementById('auroraOwnQuestionModal'));
            generateMessage();
        });

        function tr(key, params = {}) {
            let text = i18n[key] ?? key;
            for (const [name, value] of Object.entries(params)) {
                text = text.replace(`:${name}`, value);
            }
            return text;
        }

        function collectTopics() {
            return Array.from(document.querySelectorAll('.horoscope-topic-checkbox[data-topic-group="personalMessageTopics"]:checked')).map((el) => el.value);
        }

        function setTopicButtonsEnabled(enabled) {
            document.querySelectorAll('.aurora-topic-btn').forEach((btn) => {
                btn.disabled = !enabled;
            });
        }

        function renderMessage(data) {
            const summary = data.summary || '';
            messageBody.innerHTML = summary ? `<p>${summary.replace(/\n/g, '<br>')}</p>` : '';
            messageBody.classList.remove('aurora-hidden-panel');

            if (data.motto) {
                const parts = String(data.motto).split('.');
                const line1 = (parts.shift() || '').trim();
                const line2 = parts.join('.').trim();
                mottoEl.innerHTML = `${line1}${line2 ? '<br>' + line2 : ''}`;
            }

            const topics = {
                health: data.health || '',
                money: data.money || '',
                relationships: data.relationships || '',
                work: data.work || '',
            };

            Object.entries(topics).forEach(([key, value]) => {
                topicStore.dataset['topic' + key.charAt(0).toUpperCase() + key.slice(1)] = value;
                const btn = document.querySelector(`.aurora-topic-btn[data-topic="${key}"]`);
                if (btn) {
                    btn.dataset.topicContent = value;
                }
            });

            const readMoreParts = [summary];
            if (topics.health) readMoreParts.push(`${@json(__('daily.section_health'))}\n${topics.health}`);
            if (topics.money) readMoreParts.push(`${@json(__('daily.section_money'))}\n${topics.money}`);
            if (topics.relationships) readMoreParts.push(`${@json(__('daily.section_relationships'))}\n${topics.relationships}`);
            if (topics.work) readMoreParts.push(`${@json(__('public.aurora_section_work'))}\n${topics.work}`);
            topicStore.dataset.readMore = readMoreParts.filter(Boolean).join('\n\n');

            setTopicButtonsEnabled(true);
            document.querySelector('.aurora-read-more-btn')?.removeAttribute('disabled');
        }

        async function generateMessage() {
            if (!HAS_BIRTH_CHART) {
                window.showBirthChartRequiredModal?.();
                return;
            }

            const chartId = Number(birthChartSelect?.value);
            if (!chartId) {
                window.showBirthChartRequiredModal?.();
                return;
            }

            messageLoading?.classList.remove('aurora-hidden-panel');
            messageEmpty?.classList.add('aurora-hidden-panel');
            messageError?.classList.add('aurora-hidden-panel');
            messageBody?.classList.add('aurora-hidden-panel');
            setTopicButtonsEnabled(false);

            try {
                const response = await fetch(dailyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        mode: 'single',
                        birth_chart_id: chartId,
                        period: selectedPeriod,
                        user_focus: hiddenFocus?.value?.trim() || '',
                        detail_level: document.getElementById('personalMessageDetailLevel')?.value || 'normal',
                        topics: collectTopics(),
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.error || @json(__('public.error')));
                }

                renderMessage(data);
            } catch (error) {
                messageError.textContent = error?.message || @json(__('public.error'));
                messageError.classList.remove('aurora-hidden-panel');
            } finally {
                messageLoading?.classList.add('aurora-hidden-panel');
            }
        }

        if (HAS_BIRTH_CHART) {
            generateMessage();
        } else {
            messageLoading?.classList.add('aurora-hidden-panel');
            messageEmpty?.classList.remove('aurora-hidden-panel');
        }
        @endif
    })();
</script>
