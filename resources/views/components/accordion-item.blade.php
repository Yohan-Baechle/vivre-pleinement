@props([
    'question',
    'open' => false,
])

<details {{ $attributes->class(['accordion-item group block']) }} @if($open) open @endif>
    {{-- Question --}}
    <summary class="flex cursor-pointer list-none justify-end">
        <div class="relative w-fit max-w-prose rounded-3xl rounded-br-md bg-teal-700 px-5 py-3 text-white shadow-md shadow-teal-700/20 transition group-hover:bg-teal-800">
            <div class="flex items-start gap-3">
                <h3 class="font-serif text-base leading-snug font-medium sm:text-lg">{{ $question }}</h3>
                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20 transition duration-300 group-open:rotate-45" aria-hidden="true">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </span>
            </div>
        </div>
    </summary>

    {{-- Réponse --}}
    <div class="accordion-content">
        <div class="accordion-inner">
            <div class="mt-2 flex w-fit max-w-prose items-end gap-3 pb-1">
                <div class="bg-cream-100 flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full ring-2 ring-white" aria-hidden="true">
                    <img src="{{ asset('images/laura-faq-200.webp') }}" alt="" width="36" height="36" class="size-full object-cover" loading="lazy">
                </div>
                <div class="text-ink-soft ring-ink/5 rounded-3xl rounded-bl-md bg-white px-5 py-3 text-sm leading-relaxed shadow-xs ring-1 sm:text-base">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</details>
