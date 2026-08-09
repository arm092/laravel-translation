<nav class="header" aria-label="{{ config('app.name') }}">
    <div class="header-inner">
        <a href="{{ route('languages.index') }}" class="header-brand">
            <span>{{ config('app.name') }}</span>
        </a>

        <ul>
        <li>
            <a href="{{ route('languages.index') }}" class="{{ request()->is(config('translation.ui_url')) || request()->is(config('translation.ui_url').'/create') ? 'active' : '' }}">
                @include('translation::icons.globe')
                {{ __('translation::translation.languages') }}
            </a>
        </li>
        <li>
            <a href="{{ route('languages.translations.index', config('app.locale')) }}" class="{{ request()->is(config('translation.ui_url').'/*/translations') ? 'active' : '' }}">
                @include('translation::icons.translate')
                {{ __('translation::translation.translations') }}
            </a>
        </li>
        </ul>
    </div>
</nav>
