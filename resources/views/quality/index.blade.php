@extends('translation::layout')
@inject('routeNames', 'Arm092\Translation\Support\RouteNames')
@section('body')
<section class="page">
    <div class="page-heading"><div><h1>Translation quality</h1><span class="locale-code">{{ $locale }}</span></div><a class="button button-primary" href="{{ route($routeNames->get('quality.export'), request()->query()) }}">Export CSV</a></div>
    <div class="quality-summary">@foreach($summary as $name => $count)<a href="{{ route($routeNames->get('quality.index'), ['locale' => $locale, 'status' => $name]) }}" class="quality-card"><strong>{{ $count }}</strong><span>{{ ucfirst($name) }}</span></a>@endforeach</div>
    <form method="get" class="filter-bar"><input type="hidden" name="status" value="{{ $status }}"><input name="locale" value="{{ $locale }}" aria-label="Locale"><input name="search" value="{{ request('search') }}" placeholder="Search"><input name="path" value="{{ request('path') }}" placeholder="Path"><button class="button button-primary">Filter</button></form>
    <div class="panel data-surface"><div class="panel-body"><table><thead><tr><th>Status</th><th>Key / expression</th><th>Path</th><th>Line</th></tr></thead><tbody>@forelse($results as $row)<tr><td><span class="status-info">{{ $row['status'] }}</span></td><td>{{ $row['key'] }}</td><td>{{ $row['file'] }}</td><td>{{ $row['line'] }}</td></tr>@empty<tr><td colspan="4">No results.</td></tr>@endforelse</tbody></table></div></div>
</section>
@endsection
