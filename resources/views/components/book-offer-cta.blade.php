@props([
    'slug',
    'available' => false,
    'label',
    'class' => '',
])

{{--
    Appel à l'action d'une formule du livre.

    Une offre indisponible affiche un état, pas un second bouton : répéter
    « Être prévenu » sur chaque carte donnerait quatre appels à l'action
    identiques sur la page. L'invitation à laisser ses coordonnées est
    rendue une seule fois, sous la grille des offres.
--}}
@if ($available)
    <a href="{{ route('book.checkout', $slug) }}" class="group {{ $class }}">
        {!! $label !!}
        <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
    </a>
@else
    <span class="{{ $class }} cursor-default opacity-55" aria-disabled="true">
        Bientôt disponible
    </span>
@endif
