const SEARCH_SELECTOR = '[data-translation-search]';
const RESULTS_SELECTOR = '[data-translation-results]';

export async function refreshTranslationSearch({
    url,
    searchInput,
    results,
    fetchPage,
    replaceHistory,
    livewireNavigate,
    afterLivewireNavigation,
    findSearchInput,
}) {
    if (livewireNavigate) {
        const selectionStart = searchInput.selectionStart;
        const selectionEnd = searchInput.selectionEnd;

        afterLivewireNavigation(() => {
            const nextInput = findSearchInput();

            if (! nextInput) {
                return;
            }

            nextInput.focus({ preventScroll: true });
            nextInput.setSelectionRange(selectionStart, selectionEnd);
        });

        livewireNavigate(url);

        return;
    }

    results.innerHTML = await fetchPage(url);
    replaceHistory(url);
}

function filterUrl(form) {
    const url = new URL(form.action, window.location.href);
    const query = new URLSearchParams(new FormData(form));

    query.delete('page');
    url.search = query.toString();

    return url.toString();
}

async function fetchResults(url, signal) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest',
        },
        signal,
    });

    if (! response.ok) {
        throw new Error(`Translation search failed with status ${response.status}.`);
    }

    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
    const results = page.querySelector(RESULTS_SELECTOR);

    if (! results) {
        throw new Error('Translation search response did not contain a results region.');
    }

    return results.innerHTML;
}

let debounceTimer;
let activeRequest;

export function registerTranslationSearch(documentRef = document) {
    documentRef.addEventListener('input', (event) => {
        const searchInput = event.target.closest?.(SEARCH_SELECTOR);

        if (! searchInput) {
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const form = searchInput.form;
            const results = form?.querySelector(RESULTS_SELECTOR);

            if (! form || ! results) {
                return;
            }

            const url = filterUrl(form);
            const usesLivewireNavigation = form.dataset.translationFrontend === 'livewire'
                && typeof window.Livewire?.navigate === 'function';

            activeRequest?.abort();
            activeRequest = new AbortController();
            results.setAttribute('aria-busy', 'true');

            try {
                await refreshTranslationSearch({
                    url,
                    searchInput,
                    results,
                    fetchPage: (targetUrl) => fetchResults(targetUrl, activeRequest.signal),
                    replaceHistory: (targetUrl) => window.history.replaceState({}, '', targetUrl),
                    livewireNavigate: usesLivewireNavigation
                        ? (targetUrl) => window.Livewire.navigate(targetUrl)
                        : null,
                    afterLivewireNavigation: (callback) => documentRef.addEventListener('livewire:navigated', callback, { once: true }),
                    findSearchInput: () => documentRef.querySelector(SEARCH_SELECTOR),
                });
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            } finally {
                results.removeAttribute('aria-busy');
            }
        }, 300);
    });
}

if (typeof document !== 'undefined' && ! window.translationSearchRegistered) {
    window.translationSearchRegistered = true;
    registerTranslationSearch();
}
