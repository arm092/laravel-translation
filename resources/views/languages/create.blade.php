@extends('translation::layout')
@inject('routeNames', 'Arm092\Translation\Support\RouteNames')

@section('body')

    <section class="page">
        <div class="page-heading">
            <h1>{{ __('translation::translation.add_language') }}</h1>
        </div>

        <div class="panel panel-narrow">

        <form action="{{ route($routeNames->get('languages.store')) }}" method="POST">

            <fieldset>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="panel-body p-4">

                    @include('translation::forms.text', ['field' => 'name', 'label' => __('translation::translation.language_name'), ])

                    @include('translation::forms.text', ['field' => 'locale', 'label' => __('translation::translation.locale'), ])

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
