<div class="search">
    <input type="text" class="search-input" placeholder="{{ __('translation::translation.search') }}" name="{{ $name }}" value="{{ $value }}" x-on:input.debounce.300ms="$el.form.requestSubmit()">
</div>
