<div class="filter-field filter-field-search">
    <label for="translation-filter-{{ $name }}">{{ $label ?? __('translation::translation.search_label') }}</label>
    <div class="search">
        <input id="translation-filter-{{ $name }}" type="text" class="search-input" placeholder="{{ __('translation::translation.search') }}" name="{{ $name }}" value="{{ $value }}" data-translation-search>
    </div>
</div>
