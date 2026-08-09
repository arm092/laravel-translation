@if(Session::has('success'))
    <div class="status-success" role="status">
        <div class="flex justify-center">
            <p>{{ Session::get('success') }}</p>
        </div>
    </div>
@endif

@if(Session::has('error'))
    <div class="status-error" role="alert">
        <div class="flex justify-center">
            <p>{{ Session::get('error') }}</p>
        </div>
    </div>
@endif
