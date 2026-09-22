@props(['paginator', 'livewire' => false])

@if($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if($livewire)
            <button type="button" class="pagination-control" wire:click="previousPage" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) aria-label="Previous page">
                <span aria-hidden="true">&larr;</span><span class="pagination-label">{{ __('pagination.previous') }}</span>
            </button>
        @elseif($paginator->onFirstPage())
            <span class="pagination-control" aria-disabled="true"><span aria-hidden="true">&larr;</span><span class="pagination-label">{{ __('pagination.previous') }}</span></span>
        @else
            <a class="pagination-control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><span aria-hidden="true">&larr;</span><span class="pagination-label">{{ __('pagination.previous') }}</span></a>
        @endif

        <div class="pagination-pages">
            @foreach($paginator->linkCollection()->slice(1, -1) as $link)
                @if(! isset($link['page']))
                    <span class="pagination-ellipsis" aria-hidden="true">&hellip;</span>
                @elseif($link['active'])
                    <span class="pagination-page is-active" aria-current="page" aria-label="Page {{ $link['page'] }}">{{ $link['page'] }}</span>
                @elseif($livewire)
                    <button type="button" class="pagination-page" wire:click="gotoPage({{ $link['page'] }})" wire:loading.attr="disabled" aria-label="Go to page {{ $link['page'] }}">{{ $link['page'] }}</button>
                @else
                    <a class="pagination-page" href="{{ $link['url'] }}" aria-label="Go to page {{ $link['page'] }}">{{ $link['page'] }}</a>
                @endif
            @endforeach
        </div>

        @if($livewire)
            <button type="button" class="pagination-control" wire:click="nextPage" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) aria-label="Next page">
                <span class="pagination-label">{{ __('pagination.next') }}</span><span aria-hidden="true">&rarr;</span>
            </button>
        @elseif($paginator->hasMorePages())
            <a class="pagination-control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><span class="pagination-label">{{ __('pagination.next') }}</span><span aria-hidden="true">&rarr;</span></a>
        @else
            <span class="pagination-control" aria-disabled="true"><span class="pagination-label">{{ __('pagination.next') }}</span><span aria-hidden="true">&rarr;</span></span>
        @endif
    </nav>
@endif
