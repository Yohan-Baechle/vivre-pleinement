@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">

        <p class="text-sm text-ink-soft">
            @if ($paginator->firstItem())
                <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>
                à <span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
                sur <span class="font-medium text-ink">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }} sur {{ $paginator->total() }}
            @endif
        </p>

        <ul class="flex flex-wrap items-center justify-center gap-1">
            @if ($paginator->onFirstPage())
                <li aria-hidden="true">
                    <span
                          class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-full bg-white text-ink-muted/40 ring-1 ring-ink/5">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Page précédente"
                       class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-ink-soft ring-1 ring-ink/5 transition hover:bg-teal-700 hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-sm text-ink-muted" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span aria-current="page"
                                      class="flex h-10 min-w-10 items-center justify-center rounded-full bg-teal-700 px-3 text-sm font-medium text-white shadow shadow-teal-700/20">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" aria-label="Aller à la page {{ $page }}"
                                   class="flex h-10 min-w-10 items-center justify-center rounded-full bg-white px-3 text-sm font-medium text-ink-soft ring-1 ring-ink/5 transition hover:bg-teal-700 hover:text-white">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Page suivante"
                       class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-ink-soft ring-1 ring-ink/5 transition hover:bg-teal-700 hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                </li>
            @else
                <li aria-hidden="true">
                    <span
                          class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-full bg-white text-ink-muted/40 ring-1 ring-ink/5">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
