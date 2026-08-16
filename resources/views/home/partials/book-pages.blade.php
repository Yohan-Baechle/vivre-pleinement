@php
    /**
     * Tranches de pages en éventail, en fond de la carte du livre.
     *
     * Chaque feuille est la même forme, pivotée autour du coin bas gauche du
     * dessin. L'opacité décroît vers l'extérieur pour donner la profondeur
     * d'une pile qui s'ouvre, sans dégradé ni filtre.
     *
     * Motif papier et non motif nature : l'accueil porte déjà les oiseaux,
     * les nuages et le pissenlit, et c'est la seule section de la page qui
     * vend un objet imprimé.
     *
     * @var list<array{angle: int, opacity: string}>
     */
    $sheets = [
        ['angle' => 0, 'opacity' => '0.9'],
        ['angle' => -9, 'opacity' => '0.78'],
        ['angle' => -18, 'opacity' => '0.66'],
        ['angle' => -27, 'opacity' => '0.54'],
        ['angle' => -36, 'opacity' => '0.42'],
        ['angle' => -45, 'opacity' => '0.3'],
    ];
@endphp

<div class="pointer-events-none absolute -right-16 -bottom-16 w-[26rem] text-white opacity-[0.12] sm:-right-10 sm:-bottom-10 sm:w-[32rem] lg:w-[38rem]" aria-hidden="true">
    {{-- viewBox resserré sur l'emprise réelle de l'éventail, marge d'un
         demi-trait comprise, pour que la largeur du conteneur pilote
         directement la taille du motif. --}}
    <svg viewBox="16 66 224 184" fill="none" class="w-full">
        <g stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round">
            @foreach ($sheets as $sheet)
                <path
                    transform="rotate({{ $sheet['angle'] }} 34 226) translate(34 207)"
                    opacity="{{ $sheet['opacity'] }}"
                    d="M0 0 H186 Q200 0 200 14 V24 Q200 38 186 38 H0 Z"
                />
            @endforeach
        </g>
    </svg>
</div>
