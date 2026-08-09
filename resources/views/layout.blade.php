<!DOCTYPE html>
@inject('translationFrontend', 'JoeDixon\Translation\Support\Frontend')
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/vendor/translation/css/main.css') }}">
</head>
<body>
    
    <div id="app">
        
        @include('translation::nav')
        @include('translation::notifications')
        
        @yield('body')
        
    </div>
    
    @if($translationFrontend->usesLivewire())
        @php(app('livewire')->forceAssetInjection())
    @else
        <script type="module" src="{{ asset('/vendor/translation/js/app.js') }}"></script>
    @endif
</body>
</html>
