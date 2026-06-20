<div class="mt-10 {{ $rootCount > 0 ? 'border-cream-200 border-t pt-10' : '' }}">
    <h3 class="text-ink font-serif text-xl font-medium">Laisser un commentaire</h3>
    <p class="text-ink-muted mt-1 text-sm">
        Votre adresse email ne sera pas publiée. Votre commentaire apparaîtra après validation.
    </p>

    @if (session('comment_status'))
        <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">
            {{ session('comment_status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('blog.comments.store', $post->slug) }}#commentaires" class="mt-6 space-y-5" novalidate>
        @csrf
        <input type="hidden" name="ts" value="{{ time() }}">

        {{-- Honeypot anti-spam --}}
        <div aria-hidden="true" class="absolute -left-[9999px] top-auto size-px overflow-hidden">
            <label for="c_website">Site web (ne pas remplir)</label>
            <input type="text" id="c_website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="author_name" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Nom *</label>
                <input type="text" id="author_name" name="author_name" value="{{ old('author_name') }}" required autocomplete="name"
                       class="bg-white text-ink ring-ink/10 placeholder:text-ink-muted mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden @error('author_name') ring-rose-400 @enderror">
                @error('author_name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="author_email" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Email *</label>
                <input type="email" id="author_email" name="author_email" value="{{ old('author_email') }}" required autocomplete="email"
                       class="bg-white text-ink ring-ink/10 placeholder:text-ink-muted mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden @error('author_email') ring-rose-400 @enderror">
                @error('author_email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="content" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Commentaire *</label>
            <textarea id="content" name="content" rows="5" required minlength="5" maxlength="5000"
                      class="bg-white text-ink ring-ink/10 placeholder:text-ink-muted mt-2 w-full rounded-2xl border-0 px-4 py-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden @error('content') ring-rose-400 @enderror"
                      placeholder="Partagez votre ressenti…">{{ old('content') }}</textarea>
            @error('content')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            @error('ts')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                   class="border-ink/20 bg-white mt-1 size-4 rounded text-teal-700 focus:ring-2 focus:ring-teal-500">
            <span class="text-ink-soft text-xs">
                J'accepte que mon nom et mon commentaire soient publiés sur cette page. Mon email reste confidentiel.
            </span>
        </label>
        @error('consent')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror

        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800">
            Publier mon commentaire
            <span aria-hidden="true">→</span>
        </button>
    </form>
</div>
