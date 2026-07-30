@if ($paginator->hasPages())
<nav class="d-flex align-items-center justify-content-between" aria-label="Navigasi halaman">
    <div style="font-size:0.78rem;color:#6b7280;">
        Menampilkan {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}
        dari {{ $paginator->total() }} data
    </div>
    <ul class="pagination pagination-sm mb-0" style="gap:4px;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#4b5563;border-radius:8px;">‹</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}"
               style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;border-radius:8px;"
               rel="prev">‹</a>
        </li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
            <li class="page-item disabled">
                <span class="page-link" style="background:transparent;border:none;color:#4b5563;">{{ $element }}</span>
            </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <li class="page-item active">
                    <span class="page-link" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:8px;">{{ $page }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}"
                       style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;border-radius:8px;">{{ $page }}</a>
                </li>
                @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}"
               style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;border-radius:8px;"
               rel="next">›</a>
        </li>
        @else
        <li class="page-item disabled">
            <span class="page-link" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#4b5563;border-radius:8px;">›</span>
        </li>
        @endif
    </ul>
</nav>
@endif
