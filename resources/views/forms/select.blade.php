<div class="filter-field">
    <label for="translation-filter-{{ $name }}">{{ $label }}</label>
    <div class="select-group">

    <select id="translation-filter-{{ $name }}" name="{{ $name }}" @if(isset($submit) && $submit) x-on:change="$el.form.requestSubmit()" @endif>
        @if(isset($optional) && $optional)<option value> ----- </option>@endif
        @foreach($items as $key => $value)
            @if(is_numeric($key))
                <option value="{{ $value }}" @if(isset($selected) && $selected === $value) selected="selected" @endif>{{ $value }}</option>
            @else
                <option value="{{ $key }}" @if(isset($selected) && $selected === $key) selected="selected" @endif>{{ $value }}</option>
            @endif
        @endforeach
    </select>

    <span class="caret" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
    </span>

    </div>
</div>
