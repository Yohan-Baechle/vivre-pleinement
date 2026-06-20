@extends('layouts.site')

@section('title', "Pensées intrusives & TOC : le livre | Laura Baechlé")
@section('description', "Pensées intrusives violentes, phobie d'impulsion, TOC : un guide pratique de 77 pages avec 12 fiches pour vous libérer, sans médicaments. Écrit par Laura Baechlé.")
@section('keywords', "pensées intrusives, phobie d'impulsion, TOC, livre TOC, soigner TOC naturellement, pensées obsessionnelles, livre Laura Baechlé")
@section('og_type', 'book')
@section('og_title', 'Soigner les pensées intrusives & le TOC, naturellement')
@section('og_description', '77 pages, 12 fiches pratiques pour se libérer des pensées intrusives, sans médicaments. Par Laura Baechlé.')
@section('og_image', asset('images/book-cover.webp'))

@push('head')
    @php
        $bookUrl = route('book.show');
        $offerSolo = 37;
        $offerCoaching = 70;

        $productLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            '@id' => $bookUrl.'#livre',
            'name' => 'Soigner les pensées intrusives & le TOC, naturellement',
            'alternateName' => 'Soigner le TOC et la phobie d\'impulsion à l\'aide de traitements naturels',
            'description' => "Guide pratique de 77 pages avec 12 fiches pour se libérer des pensées intrusives, de la phobie d'impulsion et des TOC sans médicaments.",
            'author' => [
                '@type' => 'Person',
                'name' => 'Laura Baechlé',
                'url' => route('home'),
            ],
            'inLanguage' => 'fr-FR',
            'bookFormat' => 'https://schema.org/EBook',
            'numberOfPages' => 77,
            'image' => asset('images/book-cover.webp'),
            'url' => $bookUrl,
            'offers' => [
                [
                    '@type' => 'Offer',
                    'name' => 'Le livre seul (PDF)',
                    'price' => (string) $offerSolo,
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('book.checkout', 'livre'),
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Le livre + 1h de coaching',
                    'price' => (string) $offerCoaching,
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('book.checkout', 'livre-coaching'),
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($productLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">

    @include('book.sections.hero')
    @include('book.sections.recognition')
    @include('book.sections.empathy')
    @include('book.sections.solution')
    @include('book.sections.contents')
    @include('book.sections.benefits')
    @include('book.sections.author')
    @include('book.sections.offers')
    @include('book.sections.guarantee')
    @include('book.sections.faq')
    @include('book.sections.final-cta')
    </main>

    @include('home.sections.footer')
@endsection
