@extends('translation::layout')
@inject('translationFrontend', 'Arm092\Translation\Support\Frontend')
@inject('routeNames', 'Arm092\Translation\Support\RouteNames')

@section('body')

    <section class="page">
        <div class="page-heading">
            <div>
                <h1>{{ __('translation::translation.translations') }}</h1>
                <span class="locale-code">{{ $language }}</span>
            </div>

            <a href="{{ route($routeNames->get('languages.translations.create'), $language) }}" class="button button-primary">
                {{ __('translation::translation.add_translation') }}
            </a>
        </div>

        @if($translationFrontend->usesLivewire())
            @php
                echo app('livewire')->mount('translation-manager::translation-table', [
                    'language' => $language,
                ], 'translation-table-'.$language);
            @endphp
        @else
        <form
            action="{{ route($routeNames->get('languages.translations.index'), ['language' => $language]) }}"
            method="get"
            x-data
        >
            <div class="filter-bar">
                @include('translation::forms.search', ['name' => 'filter', 'value' => Request::get('filter')])
                @include('translation::forms.select', ['name' => 'language', 'label' => __('translation::translation.language_filter'), 'items' => $languages, 'submit' => true, 'selected' => $language])
                @include('translation::forms.select', ['name' => 'group', 'label' => __('translation::translation.group_filter'), 'items' => $groups, 'submit' => true, 'selected' => Request::get('group'), 'optional' => true])
                @include('translation::forms.select', ['name' => 'per_page', 'label' => __('translation::translation.rows_per_page'), 'items' => collect([10 => 10, 25 => 25, 50 => 50, 100 => 100]), 'submit' => true, 'selected' => $perPage])
            </div>

            <div data-translation-results>
                <div class="panel data-surface">
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
                                @php
                                    $type = $row['type'];
                                    $group = $row['group'];
                                    $key = $row['key'];
                                    $value = $row['value'];
                                @endphp
                                            <tr>
                                                <td>{{ $group }}</td>
                                                <td>{{ $key }}</td>
                                                <td>{{ $value[$sourceLocale] }}</td>
                                                <td>
                                                    @include('translation::components.translation-input', [
                                                        'initialTranslation' => $value[$language],
                                                        'language' => $language,
                                                        'group' => $group,
                                                        'translationKey' => $key,
                                                        'endpoint' => route($routeNames->get('languages.translations.update'), $language),
                                                    ])
                                                </td>
                                            </tr>
                            @endforeach
                        </tbody>

                    </table>

                @endif

                    </div>
                </div>
                <x-translation::pagination :paginator="$translations" />
            </div>
        </form>
        @endif
    </section>

@endsection
