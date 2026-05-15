@if ($paginator->hasPages())
<nav aria-label="Logs navigation" class="d-flex justify-content-center mt-4">
    <ul class="pagination pagination-sm mb-0">
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">‹</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="font-size: 30px;line-height: 20px;">‹</a>
            </li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="font-size: 30px;line-height: 20px;">›</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">›</span>
            </li>
        @endif
    </ul>
</nav>
@endif

<style>

    .pagination li { 
        text-align: center;
        width: 25px;
        height: 25px;
    }
    .pagination .page-link {
        color: #495057;
        border-radius: 6px;
        margin: 0 2px;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        font-weight: 600;
    }
    
    .pagination .page-link:hover {
        background-color: #f1f3f5;
    }
    
</style>