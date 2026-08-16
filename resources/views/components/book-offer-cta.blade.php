@props([
    'slug',
    'available' => false,
    'label',
    'class' => '',
])

{{--
    Appel à l'action d'une formule du livre. Quand le fichier vendu manque,
    le bouton n'est pas simplement désactivé : il redirige vers le contact,
    pour qu'un visiteur motivé ne se retrouve pas devant une impasse.
--}}
@if ($available)
    <a href="{{ route('book.checkout', $slug) }}" class="group {{ $class }}">
        {!! $label !!}
        <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
    </a>
@else
    <a href="{{ route('contact') }}"
       class="group ring-ink/10 text-ink-soft hover:bg-cream-50 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium ring-1 transition sm:text-base">
        Être prévenu de la sortie
        <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
    </a>
    <p class="text-ink-muted mt-3 text-center text-xs">
        Cette formule n'est pas encore disponible à l'achat.
    </p>
@endif
