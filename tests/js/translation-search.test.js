import { describe, expect, it, vi } from 'vitest';

import { refreshTranslationSearch } from '../../resources/js/translation-search.js';

describe('refreshTranslationSearch', () => {
    it('updates only the results and keeps the active search input in fallback mode', async () => {
        const searchInput = {
            focus: vi.fn(),
            selectionStart: 3,
            selectionEnd: 3,
            setSelectionRange: vi.fn(),
        };
        const results = { innerHTML: '<p>Old results</p>' };
        const replaceHistory = vi.fn();

        await refreshTranslationSearch({
            url: 'https://example.test/translations/en?filter=foo',
            searchInput,
            results,
            fetchPage: vi.fn().mockResolvedValue('<div>Filtered results</div>'),
            replaceHistory,
        });

        expect(results.innerHTML).toBe('<div>Filtered results</div>');
        expect(replaceHistory).toHaveBeenCalledWith('https://example.test/translations/en?filter=foo');
        expect(searchInput.focus).not.toHaveBeenCalled();
    });

    it('restores focus and the caret after a Livewire navigation', async () => {
        const searchInput = {
            focus: vi.fn(),
            selectionStart: 2,
            selectionEnd: 4,
            setSelectionRange: vi.fn(),
        };
        let navigationFinished;

        const livewireNavigate = vi.fn();

        await refreshTranslationSearch({
            url: 'https://example.test/translations/en?filter=foo',
            searchInput,
            livewireNavigate,
            afterLivewireNavigation: (callback) => {
                navigationFinished = callback;
            },
            findSearchInput: () => searchInput,
        });

        navigationFinished();

        expect(livewireNavigate).toHaveBeenCalledWith('https://example.test/translations/en?filter=foo');
        expect(searchInput.focus).toHaveBeenCalledOnce();
        expect(searchInput.setSelectionRange).toHaveBeenCalledWith(2, 4);
    });
});
