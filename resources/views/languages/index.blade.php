@extends('translation::layout')
@inject('routeNames', 'Arm092\Translation\Support\RouteNames')

@section('body')

    <section class="page">
        <div class="page-heading">
            <div class="page-heading-copy">
                <h1>{{ __('translation::translation.languages') }}</h1>
                <p class="page-subtitle">{{ __('translation::translation.language_management_hint') }}</p>
            </div>

            <a href="{{ route($routeNames->get('languages.create')) }}" class="button button-primary">
                {{ __('translation::translation.add_language') }}
            </a>
        </div>

        <div class="panel data-surface">
            <div class="panel-body">
                @if(count($languages))

                <table class="language-table">

                    <thead>
                        <tr>
                            <th>{{ __('translation::translation.language_name') }}</th>
                            <th>{{ __('translation::translation.locale') }}</th>
                            <th><span class="sr-only">{{ __('translation::translation.translations') }}</span></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($languages as $language => $name)
                            <tr>
                                <td>
                                    {{ $name }}
                                </td>
                                <td>
                                    <span class="locale-code">{{ $language }}</span>
                                </td>
                                <td class="text-right">
                                    <a class="row-action" href="{{ route($routeNames->get('languages.translations.index'), $language) }}">
                                        {{ __('translation::translation.translations') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="empty-state">
                        <p>{{ __('translation::translation.no_languages') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

@endsection
