import { describe, expect, it, vi } from 'vitest';

import { refreshTranslationSearch } from '../../resources/js/translation-search.js';

describe('refreshTranslationSearch', () => {
    it('updates only the results and keeps the active search input in fallback mode', async () => {
        const results = { innerHTML: '<p>Old results</p>' };
        const replaceHistory = vi.fn();

        await refreshTranslationSearch({
            url: 'https://example.test/translations/en?filter=foo',
            results,
            fetchPage: vi.fn().mockResolvedValue('<div>Filtered results</div>'),
            replaceHistory,
        });

        expect(results.innerHTML).toBe('<div>Filtered results</div>');
        expect(replaceHistory).toHaveBeenCalledWith('https://example.test/translations/en?filter=foo');
    });

});
