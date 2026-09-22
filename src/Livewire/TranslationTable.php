<?php

namespace Arm092\Translation\Livewire;

use Arm092\Translation\Authorization\TranslationManagerAuthorizer;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Support\RouteNames;
use Arm092\Translation\Support\SourceLocale;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TranslationTable extends Component
{
    use WithPagination;

    #[Locked]
    public string $language;

    #[Url(except: '')]
    public string $filter = '';

    #[Url(except: '')]
    public string $group = '';

    #[Url(as: 'per_page', except: 50)]
    public int $perPage = 50;

    public function mount(string $language): void
    {
        app(TranslationManagerAuthorizer::class)->authorize();

        $this->language = $language;
        $this->perPage = $this->validPerPage($this->perPage);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGroup(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = $this->validPerPage($this->perPage);
        $this->resetPage();
    }

    public function changeLanguage(string $language): void
    {
        app(TranslationManagerAuthorizer::class)->authorize();

        if (! app(Translation::class)->allLanguages()->has($language)) {
            abort(404);
        }

        $this->redirectRoute(
            app(RouteNames::class)->get('languages.translations.index'),
            ['language' => $language],
        );
    }

    public function render(): View
    {
        app(TranslationManagerAuthorizer::class)->authorize();

        $translation = app(Translation::class);
        $sourceLocale = app(SourceLocale::class)->get();
        $languages = $translation->allLanguages();
        $groups = $translation->getGroupsFor($sourceLocale)->merge('single');
        $translations = $translation->filterTranslationsFor($this->language, $this->filter);

        if ($this->group !== '') {
            if ($this->group === 'single') {
                $translations = new Collection(['single' => $translations->get('single')]);
            } else {
                $translations = new Collection([
                    'group' => $translations->get('group')->filter(
                        fn ($values, $group): bool => $group === $this->group,
                    ),
                ]);
            }
        }

        $rows = collect();
        foreach ($translations as $type => $items) {
            foreach ($items as $group => $values) {
                foreach ($values as $key => $value) {
                    if (! is_array($value[$sourceLocale] ?? null)) {
                        $rows->push(compact('type', 'group', 'key', 'value'));
                    }
                }
            }
        }

        $page = $this->getPage();
        $translations = new LengthAwarePaginator(
            $rows->forPage($page, $this->perPage)->values(),
            $rows->count(),
            $this->perPage,
            $page,
        );

        return view('translation::livewire.translation-table', compact(
            'languages',
            'groups',
            'translations',
            'sourceLocale',
        ));
    }

    private function validPerPage(int $perPage): int
    {
        return in_array($perPage, [25, 50, 100], true) ? $perPage : 50;
    }
}
