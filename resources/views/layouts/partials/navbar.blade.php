@php
    $links = [
        ['label' => 'Qui suis-je',  'href' => route('home').'#a-propos', 'active' => false],
        ['label' => 'Mon livre',    'href' => route('book.show'),        'active' => request()->routeIs('book.*')],
        ['label' => 'Blog',         'href' => route('blog.index'),       'active' => request()->routeIs('blog.*')],
        ['label' => 'Vidéos',       'href' => route('videos.index'),     'active' => request()->routeIs('videos.*')],
        ['label' => 'FAQ',          'href' => route('faq'),              'active' => request()->routeIs('faq')],
        ['label' => 'Me contacter', 'href' => route('contact'),          'active' => request()->routeIs('contact*')],
    ];

    $deskLink = fn (bool $active) => implode(' ', [
        'relative py-1 transition-colors hover:text-teal-700',
        'after:absolute after:inset-x-0 after:-bottom-0.5 after:h-0.5 after:origin-left after:rounded-full after:bg-teal-700 after:transition-transform after:duration-300',
        $active
            ? 'text-teal-700 after:scale-x-100'
            : 'after:scale-x-0 hover:after:scale-x-100',
    ]);
@endphp

<header data-navbar class="fixed inset-x-0 top-0 z-50 pt-4 transition-transform duration-300 ease-out will-change-transform motion-reduce:transition-none sm:pt-6">
    <div class="site-container">
    <details name="mobile-nav" class="group rounded-3xl bg-white/70 shadow-xs ring-1 ring-white backdrop-blur-md sm:rounded-full md:open:rounded-full">
        <summary class="flex list-none items-center justify-between px-4 py-2.5 sm:px-5 sm:py-3 md:pointer-events-none [&::-webkit-details-marker]:hidden">
            <a href="{{ route('home') }}" class="flex items-center md:pointer-events-auto" aria-label="Accueil">
                <x-logo class="h-9 w-auto sm:h-11 lg:h-12" />
            </a>

            {{-- Navigation desktop --}}
            <ul class="text-ink-soft hidden items-center gap-8 text-sm font-medium md:pointer-events-auto md:flex">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="{{ $deskLink($link['active']) }}" @if($link['active']) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center gap-2 md:pointer-events-auto">
                <a href="{{ route('booking.index') }}" @class([
                    'inline-flex items-center gap-2 rounded-full bg-teal-700 px-4 py-2 text-xs font-medium text-white shadow transition hover:bg-teal-800 sm:px-5 sm:text-sm',
                    'bg-teal-800' => request()->routeIs('booking.*'),
                ])>
                    Prendre RDV
                    <span aria-hidden="true">→</span>
                </a>

                <span class="text-ink hover:bg-ink/5 flex size-9 cursor-pointer items-center justify-center rounded-full transition md:hidden" role="button" aria-label="Ouvrir le menu">
                    <svg class="size-5 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <svg class="hidden size-5 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </span>
            </div>
        </summary>

        {{-- Navigation mobile --}}
        <ul class="border-ink/5 text-ink-soft flex flex-col border-t p-2 text-sm font-medium md:hidden">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}" @class([
                        'block rounded-2xl px-4 py-3 transition hover:bg-teal-50 hover:text-teal-700',
                        'text-teal-700' => $link['active'],
                    ]) @if($link['active']) aria-current="page" @endif>
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </details>
    </div>
</header>
