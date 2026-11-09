import { describe, expect, it, vi } from 'vitest';
import { persistTranslation, translationEditor } from '../../resources/js/translation-editor.js';

describe('persistTranslation', () => {
    it('posts JSON with CSRF and same-origin credentials', async () => {
        const fetchImpl = vi.fn().mockResolvedValue({ ok: true, status: 200 });
        const payload = { language: 'en', group: 'messages', key: 'welcome', value: 'Hello' };

        await persistTranslation({
            endpoint: 'https://example.test/languages/en',
            csrfToken: 'csrf-token',
            payload,
            fetchImpl,
        });

        expect(fetchImpl).toHaveBeenCalledWith('https://example.test/languages/en', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': 'csrf-token',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });
    });

    it('throws when the server rejects an update', async () => {
        const fetchImpl = vi.fn().mockResolvedValue({ ok: false, status: 403 });

        await expect(persistTranslation({
            endpoint: '/languages/en',
            csrfToken: 'csrf-token',
            payload: {},
            fetchImpl,
        })).rejects.toThrow('status 403');
    });
});

describe('translationEditor', () => {
    it('lets blur save once when the active check button is clicked', async () => {
        const fetchImpl = vi.fn().mockResolvedValue({ ok: true, status: 200 });
        vi.stubGlobal('fetch', fetchImpl);
        vi.stubGlobal('document', {
            querySelector: () => ({ content: 'csrf-token' }),
        });

        const editor = translationEditor();
        editor.value = 'Updated translation';
        editor.persistedValue = 'Original translation';
        editor.endpoint = '/languages/en';
        editor.language = 'en';
        editor.group = 'messages';
        editor.key = 'welcome';
        editor.active = true;

        editor.prepareToggle();
        await editor.handleBlur();
        editor.toggle();

        expect(editor.active).toBe(false);
        expect(fetchImpl).toHaveBeenCalledTimes(1);
        expect(editor.status).toBe('saved');

        vi.unstubAllGlobals();
    });
});
