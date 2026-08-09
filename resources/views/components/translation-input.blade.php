<div
    class="translation-editor"
    x-data="translationEditor"
    x-cloak
    data-endpoint="{{ $endpoint }}"
    data-language="{{ $language }}"
    data-group="{{ $group }}"
    data-key="{{ $translationKey }}"
    data-value="{{ $initialTranslation }}"
>
    <button type="button" x-on:click="activate" aria-label="{{ __('translation::translation.edit_translation') }}">
        <svg x-show="status === 'idle'" class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"/></svg>
        <svg x-show="status === 'loading'" class="h-5 w-5 animate-spin fill-current text-info" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 2a8 8 0 1 0 8 8h-2a6 6 0 1 1-6-6V2z"/></svg>
        <svg x-show="status === 'saved'" class="h-5 w-5 fill-current text-success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm3.77-7.78L5.3 10.7 9 14.4l5.7-5.68-1.4-1.42L9 11.6 6.7 9.29z"/></svg>
        <svg x-show="status === 'error'" class="h-5 w-5 fill-current text-error" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm4.24-9.9L10 10l2.83-2.83 1.41 1.41L11.41 11.4l2.83 2.83-1.41 1.41L10 12.82l-2.83 2.83-1.41-1.41 2.83-2.83-2.83-2.83 1.41-1.41z"/></svg>
    </button>

    <textarea
        x-ref="input"
        x-model="value"
        x-bind:class="{ active }"
        x-on:focus="active = true"
        x-on:blur="save"
        rows="1"
        aria-label="{{ __('translation::translation.translation_value') }}"
    ></textarea>

    <span
        class="sr-only"
        aria-live="polite"
        x-text="status === 'saved' ? @js(__('translation::translation.translation_saved')) : (status === 'error' ? @js(__('translation::translation.translation_save_failed')) : '')"
    ></span>
</div>
