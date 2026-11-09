export function livewireTranslationEditor() {
    return {
        active: false,
        toggleWasActive: false,
        resetTimer: null,

        get buttonLabel() {
            return this.active ? this.$root.dataset.saveLabel : this.$root.dataset.editLabel;
        },

        get editorClass() {
            return this.active ? 'active' : '';
        },

        get showPencil() {
            return !this.active && this.$wire.status === 'idle';
        },

        get showCheck() {
            return this.active;
        },

        get showSaved() {
            return !this.active && this.$wire.status === 'saved';
        },

        get showError() {
            return !this.active && this.$wire.status === 'error';
        },

        get liveMessage() {
            if (this.$wire.status === 'saved') {
                return this.$root.dataset.savedMessage;
            }

            if (this.$wire.status === 'error') {
                return this.$root.dataset.errorMessage;
            }

            return '';
        },

        init() {
            this.$watch('$wire.status', (status) => this.handleStatus(status));
        },

        handleStatus(status) {
            if (status !== 'saved' && status !== 'error') {
                return;
            }

            clearTimeout(this.resetTimer);
            this.resetTimer = setTimeout(() => {
                this.$wire.status = 'idle';
            }, 3000);
        },

        prepareToggle() {
            this.toggleWasActive = this.active;
        },

        toggle() {
            if (this.toggleWasActive) {
                this.toggleWasActive = false;

                return;
            }

            this.active = true;
            this.$nextTick(() => this.$refs.input.focus());
        },

        activate() {
            this.active = true;
        },

        handleBlur() {
            this.active = false;
            this.$wire.save();
        },
    };
}

export function registerLivewireTranslationEditor(Alpine) {
    Alpine.data('translationLivewireEditor', livewireTranslationEditor);
}

if (typeof document !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        registerLivewireTranslationEditor(window.Alpine);
    }, { once: true });
}
