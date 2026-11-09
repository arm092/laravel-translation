import { describe, expect, it, vi } from 'vitest';
import { livewireTranslationEditor } from '../../resources/js/livewire-editor.js';

describe('livewireTranslationEditor', () => {
    it('exposes CSP-safe presentation properties for the Blade bindings', () => {
        const editor = livewireTranslationEditor();

        editor.$root = {
            dataset: {
                editLabel: 'Edit',
                saveLabel: 'Save',
                savedMessage: 'Saved',
                errorMessage: 'Error',
            },
        };
        editor.$wire = { status: 'idle' };

        expect(editor.buttonLabel).toBe('Edit');
        expect(editor.showPencil).toBe(true);
        expect(editor.editorClass).toBe('');
        expect(editor.liveMessage).toBe('');

        editor.active = true;
        expect(editor.buttonLabel).toBe('Save');
        expect(editor.showCheck).toBe(true);
        expect(editor.editorClass).toBe('active');

        editor.active = false;
        editor.$wire.status = 'saved';
        expect(editor.showSaved).toBe(true);
        expect(editor.liveMessage).toBe('Saved');

        editor.$wire.status = 'error';
        expect(editor.showError).toBe(true);
        expect(editor.liveMessage).toBe('Error');
    });

    it('lets the native blur perform the only save when the active check button is clicked', () => {
        const save = vi.fn();
        const focus = vi.fn();
        const editor = livewireTranslationEditor();

        editor.$wire = { save, status: 'idle' };
        editor.$refs = { input: { focus } };
        editor.$nextTick = (callback) => callback();

        editor.toggle();

        expect(editor.active).toBe(true);
        expect(focus).toHaveBeenCalledOnce();

        editor.prepareToggle();
        editor.handleBlur();
        editor.toggle();

        expect(editor.active).toBe(false);
        expect(save).toHaveBeenCalledOnce();
    });
});
