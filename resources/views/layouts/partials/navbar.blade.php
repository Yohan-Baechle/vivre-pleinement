@php
    $links = [
        ['label' => 'À propos',     'href' => route('about'),            'active' => request()->routeIs('about')],
        ['label' => 'Formations',   'href' => route('courses.index'),    'active' => request()->routeIs('courses.*') || request()->routeIs('student.*')],
        ['label' => 'Mon livre',    'href' => route('book.show'),        'active' => request()->routeIs('book.*')],
        ['label' => 'Blog',         'href' => route('blog.index'),       'active' => request()->routeIs('blog.*')],
        ['label' => 'Vidéos',       'href' => route('videos.index'),     'active' => request()->routeIs('videos.*')],
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
                @auth('student')
                    @php $studentUser = auth('student')->user(); @endphp
                    {{-- Menu utilisateur (desktop) --}}
                    <details name="student-menu" class="group/user relative hidden md:block">
                        <summary @class([
                            'flex cursor-pointer list-none items-center gap-2 rounded-full px-3 py-2 text-xs font-medium text-ink-soft transition hover:bg-teal-50 hover:text-teal-700 sm:text-sm [&::-webkit-details-marker]:hidden',
                            'bg-teal-50 text-teal-700' => request()->routeIs('student.*'),
                        ])>
                            <span class="flex size-6 items-center justify-center rounded-full bg-teal-100 text-[0.7rem] font-semibold text-teal-800">
                                {{ Str::upper(Str::substr($studentUser->name, 0, 1)) }}
                            </span>
                            <span class="max-w-[8rem] truncate">{{ Str::before($studentUser->name, ' ') ?: 'Mon espace' }}</span>
                            <svg class="size-4 transition group-open/user:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>

                        {{-- Calque de fermeture au clic extérieur --}}
                        <label class="fixed inset-0 z-40 hidden cursor-default group-open/user:block" aria-hidden="true"></label>

                        <div class="ring-ink/5 absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl bg-white p-2 shadow-lg ring-1">
                            <div class="px-3 py-2">
                                <p class="text-ink truncate text-sm font-medium">{{ $studentUser->name }}</p>
                                <p class="text-ink-muted truncate text-xs">{{ $studentUser->email }}</p>
                            </div>
                            <div class="border-ink/5 my-1 border-t"></div>
                            <a href="{{ route('student.dashboard') }}" @class([
                                'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-teal-50 hover:text-teal-700',
                                'text-teal-700' => request()->routeIs('student.dashboard') || request()->routeIs('student.course') || request()->routeIs('student.lesson'),
                                'text-ink-soft' => ! (request()->routeIs('student.dashboard') || request()->routeIs('student.course') || request()->routeIs('student.lesson')),
                            ])>
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9 12 2l9 7"/><path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10"/></svg>
                                Mes formations
                            </a>
                            <a href="{{ route('student.account.edit') }}" @class([
                                'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-teal-50 hover:text-teal-700',
                                'text-teal-700' => request()->routeIs('student.account.*') || request()->routeIs('student.verification.*'),
                                'text-ink-soft' => ! (request()->routeIs('student.account.*') || request()->routeIs('student.verification.*')),
                            ])>
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/><path d="M3 21a9 9 0 0 1 18 0"/></svg>
                                Mon compte
                            </a>
                            <div class="border-ink/5 my-1 border-t"></div>
                            <form method="POST" action="{{ route('student.logout') }}">
                                @csrf
                                <button type="submit" class="text-ink-soft flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-rose-50 hover:text-rose-700">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                                    Se déconnecter
                                </button>
                            </form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('student.login') }}" class="text-ink-soft hidden rounded-full px-3 py-2 text-xs font-medium transition hover:bg-teal-50 hover:text-teal-700 md:inline-flex sm:text-sm">
                        Connexion élève
                    </a>
                @endauth

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

            <li class="border-ink/5 mt-1 border-t pt-1">
                @auth('student')
                    <p class="text-ink-muted px-4 pt-2 pb-1 text-xs font-semibold tracking-wider uppercase">Mon espace</p>
                    <a href="{{ route('student.dashboard') }}" @class([
                        'flex items-center gap-2 rounded-2xl px-4 py-3 transition hover:bg-teal-50 hover:text-teal-700',
                        'text-teal-700' => request()->routeIs('student.dashboard') || request()->routeIs('student.course') || request()->routeIs('student.lesson'),
                    ])>
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9 12 2l9 7"/><path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10"/></svg>
                        Mes formations
                    </a>
                    <a href="{{ route('student.account.edit') }}" @class([
                        'flex items-center gap-2 rounded-2xl px-4 py-3 transition hover:bg-teal-50 hover:text-teal-700',
                        'text-teal-700' => request()->routeIs('student.account.*') || request()->routeIs('student.verification.*'),
                    ])>
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/><path d="M3 21a9 9 0 0 1 18 0"/></svg>
                        Mon compte
                    </a>
                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="text-ink-soft flex w-full items-center gap-2 rounded-2xl px-4 py-3 text-left transition hover:bg-rose-50 hover:text-rose-700">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            Se déconnecter
                        </button>
                    </form>
                @else
                    <a href="{{ route('student.login') }}" class="block rounded-2xl px-4 py-3 transition hover:bg-teal-50 hover:text-teal-700">
                        Connexion élève
                    </a>
                @endauth
            </li>
        </ul>
    </details>
    </div>
</header>
