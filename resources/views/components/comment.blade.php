@props([
    'comment',
    'isReply' => false,
])

@php
    $isAuthor = $comment->isFromAuthor();
    $initial = \Illuminate\Support\Str::substr(trim($comment->author_name), 0, 1);
@endphp

<div class="flex items-start gap-3 sm:gap-4">
    @if ($isAuthor)
        <img src="{{ asset('images/laura-faq-200.webp') }}"
             alt="Laura Baechlé"
             width="40" height="40"
             loading="lazy"
             class="size-9 shrink-0 rounded-full object-cover ring-2 ring-teal-200 sm:size-10">
    @else
        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-medium text-teal-700 sm:size-10">
            {{ $initial }}
        </div>
    @endif

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <span class="text-ink text-sm font-medium">{{ $comment->author_name }}</span>

            @if ($isAuthor)
                <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[0.65rem] font-medium tracking-wide text-teal-700 uppercase ring-1 ring-teal-200">
                    Auteure
                </span>
            @endif

            <time datetime="{{ $comment->posted_at?->toIso8601String() }}" class="text-ink-muted text-xs">
                {{ $comment->posted_at?->locale('fr')->diffForHumans() }}
            </time>
        </div>

        <p class="text-ink-soft mt-1.5 text-sm leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>

        @if (! $isReply && $comment->replies->isNotEmpty())
            <div class="border-cream-200 mt-5 space-y-5 border-l-2 pl-4 sm:pl-6">
                @foreach ($comment->replies as $reply)
                    <x-comment :comment="$reply" :is-reply="true" />
                @endforeach
            </div>
        @endif
    </div>
</div>
