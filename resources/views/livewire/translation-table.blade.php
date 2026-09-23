<div>
    <div class="filter-bar">
        <div class="filter-field filter-field-search">
            <label for="translation-filter-search">{{ __('translation::translation.search_label') }}</label>
            <div class="search">
                <input
                    id="translation-filter-search"
                    type="text"
                    class="search-input"
                    placeholder="{{ __('translation::translation.search') }}"
                    wire:model.live.debounce.300ms="filter"
                >
            </div>
        </div>

        <div class="filter-field">
            <label for="translation-filter-language">{{ __('translation::translation.language_filter') }}</label>
            <div class="select-group">
                <select id="translation-filter-language" wire:change="changeLanguage($event.target.value)">
                    @foreach($languages as $locale => $name)
                        <option value="{{ $locale }}" @selected($language === $locale)>{{ $name }}</option>
                    @endforeach
                </select>
                <span class="caret" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </span>
            </div>
        </div>

        <div class="filter-field">
            <label for="translation-filter-group">{{ __('translation::translation.group_filter') }}</label>
            <div class="select-group">
                <select id="translation-filter-group" wire:model.live="group">
                    <option value> ----- </option>
                    @foreach($groups as $key => $value)
                        <option value="{{ is_numeric($key) ? $value : $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <span class="caret" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </span>
            </div>
        </div>

        <div class="filter-field">
            <label for="translation-filter-per-page">{{ __('translation::translation.rows_per_page') }}</label>
            <div class="select-group">
                <select id="translation-filter-per-page" wire:model.live="perPage">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
                <span class="caret" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </span>
            </div>
        </div>
    </div>

    <div class="panel data-surface" wire:loading.class="opacity-60" wire:target="filter,group,perPage">
        <div class="panel-body translations-table">
            @if(count($translations))
                <table>
                    <thead>
                        <tr>
                            <th class="w-1/5 uppercase font-thin">{{ __('translation::translation.group_single') }}</th>
                            <th class="w-1/5 uppercase font-thin">{{ __('translation::translation.key') }}</th>
                            <th class="uppercase font-thin">{{ $sourceLocale }}</th>
                            <th class="uppercase font-thin">{{ $language }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($translations as $row)
                            <tr wire:key="translation-row-{{ hash('sha256', $language."\0".$row['group']."\0".$row['key']) }}">
                                <td>{{ $row['group'] }}</td>
                                <td>{{ $row['key'] }}</td>
                                <td>{{ $row['value'][$sourceLocale] }}</td>
                                <td>
                                    @php
                                        echo app('livewire')->mount('translation-manager::translation-input', [
                                            'initialTranslation' => $row['value'][$language],
                                            'language' => $language,
                                            'group' => $row['group'],
                                            'translationKey' => $row['key'],
                                        ], 'translation-input-'.hash('sha256', $language."\0".$row['group']."\0".$row['key']));
                                    @endphp
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <x-translation::pagination :paginator="$translations" :livewire="true" />
</div>
