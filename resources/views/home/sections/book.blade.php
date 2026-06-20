<section class="relative overflow-hidden bg-white py-20 sm:py-24 lg:py-32">
    <div class="site-container">
        <div class="group relative overflow-hidden rounded-4xl bg-linear-to-br from-teal-700 to-teal-800 shadow-2xl shadow-teal-700/20">
            <div class="pointer-events-none absolute inset-0 -z-0 overflow-hidden opacity-50">
                <div class="cloud-r cloud-d-160 absolute top-8 -left-20">
                    <div class="cloud-sway cloud-s-15 text-white/20">
                        <svg class="size-32" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">
                            <path d="M160.06,40A88.1,88.1,0,0,0,81.29,88.67h0A87.48,87.48,0,0,0,72,127.73,8.18,8.18,0,0,1,64.57,136,8,8,0,0,1,56,128a103.66,103.66,0,0,1,5.34-32.92,4,4,0,0,0-4.75-5.18A64.09,64.09,0,0,0,8,152c0,35.19,29.75,64,65,64H160a88.09,88.09,0,0,0,87.93-91.48C246.11,77.54,207.07,40,160.06,40Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative grid grid-cols-1 items-center gap-10 px-8 py-12 sm:px-10 sm:py-16 lg:grid-cols-5 lg:gap-12 lg:px-14 lg:py-20">
                <div class="flex justify-center lg:col-span-2">
                    <div class="relative w-full max-w-[240px] sm:max-w-[280px]">
                        @if (file_exists(public_path('images/book-cover.webp')))
                            <img
                                src="{{ asset('images/book-cover.webp') }}"
                                alt="Le livre de Laura Baechlé sur les pensées intrusives et le TOC"
                                width="450"
                                height="788"
                                class="w-full rotate-3 [filter:drop-shadow(0_25px_25px_rgb(0_0_0/0.25))] transition duration-500 group-hover:rotate-0"
                                loading="lazy"
                            >
                        @else
                            <div class="from-ink relative flex aspect-[3/4] rotate-3 flex-col items-center justify-center overflow-hidden rounded-2xl bg-linear-to-br to-teal-900 p-6 text-center text-white shadow-2xl ring-4 ring-white transition duration-500 group-hover:rotate-0">
                                <p class="text-[10px] font-medium tracking-[0.2em] text-teal-100 uppercase">Laura Baechlé</p>
                                <div class="bg-rose-soft my-4 h-px w-10"></div>
                                <h3 class="font-serif text-lg leading-tight font-medium">
                                    Soigner les pensées intrusives & le TOC
                                </h3>
                                <p class="mt-2 font-serif text-xs text-teal-100 italic">naturellement</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center lg:col-span-3 lg:text-left">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-medium text-white ring-1 ring-white/20 backdrop-blur-sm">
                        <span class="bg-rose-soft size-1.5 rounded-full"></span>
                        Le livre · 77 pages · PDF
                    </p>
                    <h2 class="mt-5 font-serif text-3xl leading-tight font-medium tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Pensées intrusives, TOC, phobie d'impulsion&nbsp;?
                    </h2>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-teal-50 sm:text-lg lg:mx-0">
                        77 pages claires et 12 fiches pratiques pour comprendre ce qui vous arrive et vous en libérer, sans médicaments, à votre rythme.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3 sm:gap-4 lg:justify-start">
                        <a href="{{ route('book.show') }}" class="group hover:bg-cream-50 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-800 shadow-lg transition sm:text-base">
                            Découvrir le livre &mdash; 37&nbsp;€
                            <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                        <span class="inline-flex items-center gap-1.5 text-xs text-teal-100 sm:text-sm">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 12 2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
                            Garantie 30 jours satisfait ou remboursé
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
