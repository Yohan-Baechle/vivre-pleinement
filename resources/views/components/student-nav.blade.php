@props(['student'])

@php
    $tabs = [
        ['label' => 'Mes formations', 'href' => route('student.dashboard'), 'active' => request()->routeIs('student.dashboard') || request()->routeIs('student.course') || request()->routeIs('student.lesson')],
        ['label' => 'Mon compte', 'href' => route('student.account.edit'), 'active' => request()->routeIs('student.account.*') || request()->routeIs('student.verification.*')],
    ];
@endphp

<nav aria-label="Navigation de l'espace élève" class="border-ink/5 mb-10 border-b">
    <div class="flex flex-wrap items-center justify-between gap-4 pb-px">
        <ul class="-mb-px flex gap-1">
            @foreach ($tabs as $tab)
                <li>
                    <a href="{{ $tab['href'] }}" @class([
                        'inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition',
                        'border-teal-700 text-teal-700' => $tab['active'],
                        'border-transparent text-ink-muted hover:border-ink/15 hover:text-ink' => ! $tab['active'],
                    ]) @if($tab['active']) aria-current="page" @endif>
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('student.logout') }}" class="pb-2">
            @csrf
            <button type="submit" class="text-ink-muted inline-flex items-center gap-1.5 text-sm font-medium transition hover:text-rose-700">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                Se déconnecter
            </button>
        </form>
    </div>
</nav>
