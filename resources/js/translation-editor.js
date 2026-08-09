export async function persistTranslation({ endpoint, csrfToken, payload, fetchImpl = fetch }) {
    const response = await fetchImpl(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    });

    if (! response.ok) {
        throw new Error(`Translation update failed with status ${response.status}.`);
    }

    return response;
}

export function translationEditor() {
    return {
        value: '',
        persistedValue: '',
        active: false,
        status: 'idle',
        resetTimer: null,
        endpoint: '',
        language: '',
        group: '',
        key: '',

        init() {
            this.value = this.$el.dataset.value ?? '';
            this.persistedValue = this.value;
            this.endpoint = this.$el.dataset.endpoint ?? '';
            this.language = this.$el.dataset.language ?? '';
            this.group = this.$el.dataset.group ?? '';
            this.key = this.$el.dataset.key ?? '';
        },

        get changed() {
            return this.value !== this.persistedValue;
        },

        activate() {
            this.active = true;
            this.$nextTick(() => this.$refs.input.focus());
        },

        async save() {
            this.active = false;

            if (! this.changed || this.status === 'loading') {
                return;
            }

            this.status = 'loading';
            clearTimeout(this.resetTimer);

            try {
                await persistTranslation({
                    endpoint: this.endpoint,
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    payload: {
                        language: this.language,
                        group: this.group,
                        key: this.key,
                        value: this.value,
                    },
                });

                this.persistedValue = this.value;
                this.status = 'saved';
            } catch (error) {
                this.status = 'error';
            }

            this.resetTimer = setTimeout(() => {
                this.status = 'idle';
            }, 3000);
        },
    };
}
