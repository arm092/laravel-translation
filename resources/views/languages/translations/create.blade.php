@extends('translation::layout')
@inject('routeNames', 'Arm092\Translation\Support\RouteNames')

@section('body')

    <section class="page">
        <div class="page-heading">
            <h1>{{ __('translation::translation.add_translation') }}</h1>
        </div>

        <div class="panel panel-narrow">

        <form action="{{ route($routeNames->get('languages.translations.store'), $language) }}" method="POST" x-data="{ showAdvancedOptions: false }">

            <fieldset>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="panel-body p-4">

                    @include('translation::forms.text', ['field' => 'group', 'label' => __('translation::translation.group_label'), 'placeholder' => __('translation::translation.group_placeholder')])
                    
                    @include('translation::forms.text', ['field' => 'key', 'label' => __('translation::translation.key_label'), 'placeholder' => __('translation::translation.key_placeholder')])

                    @include('translation::forms.text', ['field' => 'value', 'label' => __('translation::translation.value_label'), 'placeholder' => __('translation::translation.value_placeholder')])
                    
                    <div class="input-group">

                        <button type="button" x-on:click="showAdvancedOptions = ! showAdvancedOptions" class="text-primary">{{ __('translation::translation.advanced_options') }}</button>

                    </div>

                    <div x-cloak x-show="showAdvancedOptions">

                        @include('translation::forms.text', ['field' => 'namespace', 'label' => __('translation::translation.namespace_label'), 'placeholder' => __('translation::translation.namespace_placeholder')])
                    
                    </div>

  
                </div>

            </fieldset>

            <div class="panel-footer flex flex-row-reverse">

                <button class="button button-primary">
                    {{ __('translation::translation.save') }}
                </button>

            </div>

        </form>

        </div>
    </section>

@endsection
