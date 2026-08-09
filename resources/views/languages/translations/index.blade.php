@extends('translation::layout')
@inject('translationFrontend', 'Arm092\Translation\Support\Frontend')

@section('body')

    <section class="page">
        <div class="page-heading">
            <div>
                <h1>{{ __('translation::translation.translations') }}</h1>
                <span class="locale-code">{{ $language }}</span>
            </div>

            <a href="{{ route('languages.translations.create', $language) }}" class="button button-primary">
                {{ __('translation::translation.add_translation') }}
            </a>
        </div>

        <form action="{{ route('languages.translations.index', ['language' => $language]) }}" method="get">
            <div class="filter-bar">
                @include('translation::forms.search', ['name' => 'filter', 'value' => Request::get('filter')])
                @include('translation::forms.select', ['name' => 'language', 'items' => $languages, 'submit' => true, 'selected' => $language])
                @include('translation::forms.select', ['name' => 'group', 'items' => $groups, 'submit' => true, 'selected' => Request::get('group'), 'optional' => true])
            </div>

            <div class="panel data-surface">
                <div class="panel-body translations-table">

                @if(count($translations))

                    <table>

                        <thead>
                            <tr>
                                <th class="w-1/5 uppercase font-thin">{{ __('translation::translation.group_single') }}</th>
                                <th class="w-1/5 uppercase font-thin">{{ __('translation::translation.key') }}</th>
                                <th class="uppercase font-thin">{{ config('app.locale') }}</th>
                                <th class="uppercase font-thin">{{ $language }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($translations as $type => $items)
                                
                                @foreach($items as $group => $translations)

                                    @foreach($translations as $key => $value)

                                        @if(!is_array($value[config('app.locale')]))
                                            <tr>
                                                <td>{{ $group }}</td>
                                                <td>{{ $key }}</td>
                                                <td>{{ $value[config('app.locale')] }}</td>
                                                <td>
                                                    @if($translationFrontend->usesLivewire())
                                                        @php
                                                            echo app('livewire')->mount('translation-manager::translation-input', [
                                                                'initialTranslation' => $value[$language],
                                                                'language' => $language,
                                                                'group' => $group,
                                                                'translationKey' => $key,
                                                            ], 'translation-input-'.hash('sha256', $language."\0".$group."\0".$key));
                                                        @endphp
                                                    @else
                                                        @include('translation::components.translation-input', [
                                                            'initialTranslation' => $value[$language],
                                                            'language' => $language,
                                                            'group' => $group,
                                                            'translationKey' => $key,
                                                            'endpoint' => route('languages.translations.update', $language),
                                                        ])
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif

                                    @endforeach

                                @endforeach
                                           
                            @endforeach
                        </tbody>

                    </table>

                @endif

                </div>
            </div>
        </form>
    </section>

@endsection
