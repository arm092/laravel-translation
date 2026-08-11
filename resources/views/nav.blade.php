@inject('routeNames', 'Arm092\Translation\Support\RouteNames')
@inject('sourceLocale', 'Arm092\Translation\Support\SourceLocale')
<nav class="header" aria-label="{{ config('app.name') }}">
    <div class="header-inner">
        <a href="{{ route($routeNames->get('languages.index')) }}" class="header-brand">
            <span>{{ config('app.name') }}</span>
        </a>

        <ul>
        <li>
            <a href="{{ route($routeNames->get('languages.index')) }}" class="{{ request()->is(config('translation.ui_url')) || request()->is(config('translation.ui_url').'/create') ? 'active' : '' }}">
                @include('translation::icons.globe')
                {{ __('translation::translation.languages') }}
            </a>
        </li>
        <li>
            <a href="{{ route($routeNames->get('languages.translations.index'), $sourceLocale->get()) }}" class="{{ request()->is(config('translation.ui_url').'/*/translations') ? 'active' : '' }}">
                @include('translation::icons.translate')
                {{ __('translation::translation.translations') }}
            </a>
        </li>
        <li><a href="{{ route($routeNames->get('quality.index')) }}" class="{{ request()->is(config('translation.ui_url').'/quality/*') ? 'active' : '' }}">Quality</a></li>
        </ul>
    </div>
</nav>
