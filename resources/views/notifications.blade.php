@if(Session::has('success'))
    <div class="status-message status-success" role="status">
        <p>{{ Session::get('success') }}</p>
    </div>
@endif

@if(Session::has('error'))
    <div class="status-message status-error" role="alert">
        <p>{{ Session::get('error') }}</p>
    </div>
@endif
