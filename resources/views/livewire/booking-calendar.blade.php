@php
    use Carbon\CarbonImmutable;

    $service = $this->service;
    $weekDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $weekDaysFull = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    $monthNames = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    $first = CarbonImmutable::create($year, $month, 1);
    $daysInMonth = $first->daysInMonth;
    $leadingBlanks = $first->dayOfWeekIso - 1;
    $available = collect($this->availableDays);
    $today = CarbonImmutable::now()->toDateString();
    $isCurrentOrPastMonth = $first->startOfMonth()->lessThanOrEqualTo(CarbonImmutable::now()->startOfMonth());
@endphp

<div class="mx-auto max-w-2xl space-y-6">
    <p class="sr-only" aria-live="polite">
        @if ($selectedSlot)
            Créneau sélectionné : {{ CarbonImmutable::parse($selectedSlot)->locale('fr')->isoFormat('dddd D MMMM') }} à {{ CarbonImmutable::parse($selectedSlot)->format('H\hi') }}.
        @elseif ($selectedDate)
            {{ count($this->slots) }} créneaux disponibles le {{ CarbonImmutable::parse($selectedDate)->locale('fr')->isoFormat('dddd D MMMM') }}.
        @else
            {{ $monthNames[$month] }} {{ $year }} : {{ $available->count() }} jours disponibles.
        @endif
    </p>

    {{-- Étape 1 - Calendrier + créneaux --}}
    <section class="ring-ink/5 relative rounded-4xl bg-white p-6 shadow-xs ring-1 sm:p-8"
             aria-label="Choisir une date et un horaire">

        {{-- Overlay de chargement --}}
        <div wire:loading.flex wire:target="previousMonth,nextMonth,selectDate"
             class="absolute inset-0 z-10 items-center justify-center rounded-4xl bg-white/60 backdrop-blur-[1px]"
             aria-hidden="true">
            <svg class="size-6 animate-spin text-teal-700" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/>
            </svg>
        </div>

        {{-- Navigation mois --}}
        <div class="flex items-center justify-between">
            <button type="button" wire:click="previousMonth" @disabled($isCurrentOrPastMonth)
                    wire:loading.attr="disabled" wire:target="previousMonth,nextMonth"
                    class="text-ink ring-ink/10 hover:bg-cream-50 flex size-9 items-center justify-center rounded-full ring-1 transition focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-30"
                    aria-label="Mois précédent">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <h3 class="text-ink font-serif text-lg font-medium" aria-live="off">{{ $monthNames[$month] }} {{ $year }}</h3>
            <button type="button" wire:click="nextMonth"
                    wire:loading.attr="disabled" wire:target="previousMonth,nextMonth"
                    class="text-ink ring-ink/10 hover:bg-cream-50 flex size-9 items-center justify-center rounded-full ring-1 transition focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:outline-hidden disabled:opacity-30"
                    aria-label="Mois suivant">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        {{-- Grille des jours --}}
        <div class="mt-6 grid grid-cols-7 gap-1.5 text-center" role="group" aria-label="Jours du mois">
            @foreach ($weekDays as $i => $wd)
                <div class="text-ink-muted pb-2 text-xs font-medium tracking-wider uppercase" aria-hidden="true">{{ $wd }}</div>
            @endforeach

            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div aria-hidden="true"></div>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateObj = $first->setDay($day);
                    $date = $dateObj->toDateString();
                    $isAvailable = $available->contains($date);
                    $isSelected = $selectedDate === $date;
                    $isToday = $date === $today;
                    $fullLabel = $dateObj->locale('fr')->isoFormat('dddd D MMMM YYYY');
                @endphp
                @if ($isAvailable)
                    <button type="button" wire:click="selectDate('{{ $date }}')"
                            wire:loading.attr="disabled" wire:target="selectDate,previousMonth,nextMonth"
                            @class([
                                'aspect-square rounded-xl text-sm font-medium transition focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-teal-500',
                                'bg-teal-700 text-white shadow' => $isSelected,
                                'bg-teal-50 text-teal-800 ring-1 ring-teal-200 hover:bg-teal-100' => ! $isSelected,
                            ])
                            aria-label="{{ $fullLabel }}, disponible"
                            @if ($isSelected) aria-pressed="true" @else aria-pressed="false" @endif>
                        {{ $day }}
                    </button>
                @else
                    <div @class([
                            'flex aspect-square items-center justify-center rounded-xl text-sm text-ink-muted line-through decoration-ink-muted/30',
                            'ring-1 ring-teal-100' => $isToday,
                        ])
                        title="{{ $fullLabel }}, indisponible" aria-hidden="true">
                        {{ $day }}
                    </div>
                @endif
            @endfor
        </div>

        @if ($available->isEmpty())
            <p class="bg-cream-50 text-ink-soft mt-6 rounded-2xl px-4 py-3 text-center text-sm">
                Aucune disponibilité ce mois-ci.
                <button type="button" wire:click="nextMonth" class="font-medium text-teal-700 underline-offset-2 hover:underline">Voir le mois suivant →</button>
            </p>
        @endif

        {{-- Créneaux du jour --}}
        @if ($selectedDate)
            <div class="border-ink/5 mt-8 border-t pt-6"
                 x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })">
                <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">
                    Créneaux le {{ CarbonImmutable::parse($selectedDate)->locale('fr')->isoFormat('dddd D MMMM') }}
                    <span class="text-ink-muted/70 ml-1 tracking-normal normal-case">· {{ $service->duration_minutes }} min chacun</span>
                </p>
                @if (count($this->slots) > 0)
                    <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5" role="group" aria-label="Créneaux horaires disponibles">
                        @foreach ($this->slots as $slot)
                            <button type="button" wire:click="selectSlot('{{ $slot['value'] }}')"
                                    @class([
                                        'rounded-xl px-2 py-2.5 text-sm font-medium transition focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-teal-500',
                                        'bg-teal-700 text-white shadow' => $selectedSlot === $slot['value'],
                                        'bg-cream-50 text-ink ring-1 ring-ink/10 hover:ring-teal-300' => $selectedSlot !== $slot['value'],
                                    ])
                                    aria-label="Réserver le créneau de {{ $slot['label'] }}"
                                    @if ($selectedSlot === $slot['value']) aria-pressed="true" @else aria-pressed="false" @endif>
                                {{ $slot['label'] }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-ink-soft mt-4 text-sm">Plus de créneau disponible ce jour-là. Choisissez une autre date.</p>
                @endif
            </div>
        @endif
    </section>

    {{-- Étape 2 - Récap + coordonnées --}}
    @if ($selectedSlot)
        @php $slotStart = CarbonImmutable::parse($selectedSlot); @endphp
        <section id="booking-form" class="ring-ink/5 rounded-4xl bg-white p-6 shadow-xs ring-1 sm:p-8"
                 x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                 aria-label="Récapitulatif et coordonnées">

            {{-- Carte récapitulative --}}
            <div class="rounded-3xl bg-teal-50/60 p-5 ring-1 ring-teal-100">
                <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">Votre rendez-vous</p>
                <p class="text-ink mt-2 font-serif text-xl font-medium">{{ $service->name }}</p>
                <dl class="text-ink-soft mt-3 space-y-1.5 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <dt class="sr-only">Date et heure</dt>
                        <dd>{{ $slotStart->locale('fr')->isoFormat('dddd D MMMM YYYY') }} à {{ $slotStart->format('H\hi') }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <dt class="sr-only">Durée</dt>
                        <dd>{{ $service->duration_minutes }} minutes · En visioconférence</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 3.5M12 17h.01"/></svg>
                        <dt class="sr-only">Tarif</dt>
                        <dd>{{ $service->isFree() ? 'Gratuit · Sans engagement' : number_format($service->price, 2, ',', ' ').' €' }}</dd>
                    </div>
                </dl>
                <button type="button" wire:click="$set('selectedSlot', null)"
                        class="mt-4 text-xs font-medium text-teal-700 underline-offset-2 hover:underline focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:outline-hidden">
                    ← Changer de créneau
                </button>
            </div>

            @if ($this->isRescheduling)
                <form wire:submit="book" class="mt-7 space-y-4" novalidate>
                    @error('selectedSlot')<p class="bg-rose-soft/40 text-ink ring-rose-soft rounded-2xl px-4 py-3 text-sm ring-1" role="alert">{{ $message }}</p>@enderror
                    <button type="submit"
                            wire:loading.attr="disabled" wire:target="book"
                            class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:opacity-60">
                        <svg wire:loading wire:target="book" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/>
                        </svg>
                        <span wire:loading.remove wire:target="book">Confirmer le nouveau créneau</span>
                        <span wire:loading wire:target="book">Enregistrement…</span>
                        <span class="transition group-hover:translate-x-0.5" wire:loading.remove wire:target="book" aria-hidden="true">→</span>
                    </button>
                </form>
            @else
            {{-- Format du rendez-vous --}}
            <fieldset class="mt-7">
                <legend class="text-ink font-serif text-2xl font-medium">Comment souhaitez-vous être accompagné·e ?</legend>
                <p class="text-ink-soft mt-1 text-sm">Téléphone ou visioconférence : à vous de choisir, le créneau reste le même.</p>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['value' => 'phone', 'label' => 'Par téléphone', 'desc' => 'Je vous appelle', 'img' => 'consultation-telephone'],
                        ['value' => 'video', 'label' => 'En visioconférence', 'desc' => 'On se voit en visio', 'img' => 'consultation-visio'],
                    ] as $option)
                        <label class="group bg-cream-50 ring-ink/10 relative flex cursor-pointer items-center gap-3 rounded-2xl p-3 ring-1 transition hover:ring-teal-300 has-[:checked]:bg-white has-[:checked]:shadow-md has-[:checked]:ring-2 has-[:checked]:shadow-teal-700/10 has-[:checked]:ring-teal-600">
                            <input type="radio" wire:model="channel" name="channel" value="{{ $option['value'] }}" class="peer sr-only">
                            <img
                                src="{{ asset('images/'.$option['img'].'-400.webp') }}"
                                width="56" height="56" alt=""
                                class="size-14 shrink-0 rounded-full object-cover object-top shadow-xs ring-2 ring-white"
                                loading="lazy" decoding="async">
                            <span class="min-w-0 flex-1">
                                <span class="text-ink block text-sm leading-tight font-medium">{{ $option['label'] }}</span>
                                <span class="text-ink-muted block text-xs">{{ $option['desc'] }}</span>
                            </span>
                            <span class="ring-ink/20 flex size-5 shrink-0 items-center justify-center rounded-full text-white ring-1 transition peer-checked:bg-teal-600 peer-checked:ring-teal-600" aria-hidden="true">
                                <svg class="size-3 opacity-0 transition peer-checked:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('channel')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            </fieldset>

            <h2 class="text-ink mt-8 font-serif text-2xl font-medium">Vos coordonnées</h2>

            <form wire:submit="book" class="mt-6 space-y-5" novalidate>
                {{-- Honeypot --}}
                <div aria-hidden="true" class="absolute top-auto -left-[9999px] size-px overflow-hidden">
                    <label for="website">Site web (ne pas remplir)</label>
                    <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="firstName" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Prénom *</label>
                        <input type="text" id="firstName" wire:model="firstName" autocomplete="given-name"
                               @error('firstName') aria-invalid="true" aria-describedby="firstName-error" @enderror
                               class="bg-cream-50 text-ink ring-ink/10 @error('firstName') @enderror mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 ring-rose-400 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                        @error('firstName')<p id="firstName-error" class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="lastName" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Nom</label>
                        <input type="text" id="lastName" wire:model="lastName" autocomplete="family-name"
                               class="bg-cream-50 text-ink ring-ink/10 mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="email" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Email *</label>
                        <input type="email" id="email" wire:model="email" autocomplete="email"
                               @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                               class="bg-cream-50 text-ink ring-ink/10 @error('email') @enderror mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 ring-rose-400 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                        @error('email')<p id="email-error" class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Téléphone</label>
                        <input type="tel" id="phone" wire:model="phone" autocomplete="tel" placeholder="Optionnel"
                               class="bg-cream-50 text-ink ring-ink/10 mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                    </div>
                </div>

                <div>
                    <label for="notes" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Message (optionnel)</label>
                    <textarea id="notes" wire:model="notes" rows="3" maxlength="2000" placeholder="Un mot sur ce qui vous amène ?"
                              class="bg-cream-50 text-ink ring-ink/10 mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden"></textarea>
                </div>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="consent" value="1"
                           class="border-ink/20 bg-cream-50 mt-1 size-4 rounded-sm text-teal-700 focus:ring-2 focus:ring-teal-500">
                    <span class="text-ink-soft text-xs">
                        J'accepte que mes informations soient utilisées uniquement pour gérer mon rendez-vous. Aucune diffusion à des tiers.
                    </span>
                </label>
                @error('consent')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                @error('selectedSlot')<p class="bg-rose-soft/40 text-ink ring-rose-soft rounded-2xl px-4 py-3 text-sm ring-1" role="alert">{{ $message }}</p>@enderror

                <div class="border-ink/5 sticky bottom-0 -mx-6 border-t bg-white/90 px-6 py-4 backdrop-blur-sm sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:p-0 sm:backdrop-blur-none">
                    <button type="submit"
                            wire:loading.attr="disabled" wire:target="book"
                            class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:opacity-60">
                        <svg wire:loading wire:target="book" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/>
                        </svg>
                        <span wire:loading.remove wire:target="book">Confirmer mon rendez-vous</span>
                        <span wire:loading wire:target="book">Enregistrement…</span>
                        <span class="transition group-hover:translate-x-0.5" wire:loading.remove wire:target="book" aria-hidden="true">→</span>
                    </button>
                </div>

                {{-- Réassurance --}}
                <ul class="text-ink-muted flex flex-wrap items-center justify-center gap-x-5 gap-y-2 pt-1 text-xs">
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                        Sans engagement
                    </li>
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                        Annulable à tout moment
                    </li>
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                        Lien visio envoyé avant
                    </li>
                </ul>
            </form>
            @endif
        </section>
    @endif
</div>
