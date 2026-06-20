@php
    $editor = config('legal.editor');
    $host = config('legal.host');
    $webmaster = config('legal.webmaster');
    $site = config('legal.site');

    $title = 'Mentions légales';
    $intro = "Informations légales relatives au site « {$site['name']} » conformément à l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique (LCEN).";
    $breadcrumb = [['label' => 'Mentions légales']];
@endphp

@extends('layouts.legal')

@section('legal-content')
    <h2>1. Éditeur du site</h2>
    <p>Le site <strong>{{ $site['name'] }}</strong> (<a href="{{ $site['url'] }}">{{ $site['domain'] }}</a>) est édité par :</p>
    <ul>
        <li><strong>{{ $editor['name'] }}</strong>, exerçant en tant que {{ strtolower($editor['type']) }}</li>
        <li>Adresse : {{ $editor['address'] }}</li>
        <li>SIRET : {{ $editor['siret'] }}</li>
        <li>Email : <a href="mailto:{{ $editor['email'] }}">{{ $editor['email'] }}</a></li>
        @if ($editor['phone'])
            <li>Téléphone : <a href="tel:{{ str_replace(' ', '', $editor['phone']) }}">{{ $editor['phone'] }}</a></li>
        @endif
    </ul>

    <h2>2. Directrice de la publication</h2>
    <p>{{ $editor['publication_director'] }}, joignable à l'adresse <a href="mailto:{{ $editor['email'] }}">{{ $editor['email'] }}</a>.</p>

    <h2>3. Hébergement</h2>
    <p>Le site est hébergé par :</p>
    <ul>
        <li><strong>{{ $host['name'] }}</strong></li>
        <li>Adresse : {{ $host['address'] }}</li>
        <li>Site web : <a href="{{ $host['website'] }}" target="_blank" rel="noopener">{{ $host['website'] }}</a></li>
        <li>Téléphone : {{ $host['phone'] }}</li>
    </ul>

    <h2>4. Conception & maintenance</h2>
    <p>
        Site conçu et maintenu par <strong>{{ $webmaster['name'] }}</strong>
        - <a href="mailto:{{ $webmaster['email'] }}">{{ $webmaster['email'] }}</a>.
    </p>

    <h2>5. Propriété intellectuelle</h2>
    <p>
        L'ensemble des contenus présents sur le site {{ $site['name'] }} (textes, images, vidéos,
        logo, charte graphique, structure du site) est la propriété exclusive de {{ $editor['name'] }}
        ou de ses partenaires, et est protégé par le Code de la propriété intellectuelle (articles
        L.111-1 et suivants).
    </p>
    <p>
        Toute reproduction, représentation, modification, publication, transmission, dénaturation,
        totale ou partielle du site ou de son contenu, par quelque procédé que ce soit, et sur quelque
        support que ce soit est interdite sans l'autorisation écrite préalable de {{ $editor['name'] }}.
    </p>

    <h2>6. Liens hypertextes</h2>
    <p>
        Le site peut contenir des liens vers d'autres sites internet. {{ $editor['name'] }} ne peut être
        tenue responsable du contenu de ces sites tiers, ni de l'usage qui pourrait en être fait par
        les utilisateurs.
    </p>
    <p>
        La création de liens vers le site {{ $site['name'] }} est libre, à condition que cela ne porte
        pas atteinte aux intérêts de l'éditeur et que la source soit clairement indiquée.
    </p>

    <h2>7. Limitation de responsabilité</h2>
    <p>
        Les informations diffusées sur le site sont fournies à titre indicatif. {{ $editor['name'] }}
        s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site, mais
        ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à
        disposition.
    </p>
    <p>
        Les conseils, outils et témoignages présentés sur ce site ne constituent en aucun cas un avis
        médical ou un substitut à une consultation auprès d'un professionnel de santé. En cas de
        détresse psychologique, contactez votre médecin traitant ou les services d'urgence.
    </p>

    <h2>8. Données personnelles et cookies</h2>
    <p>
        Le traitement des données personnelles est détaillé dans notre
        <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
        L'usage des cookies est détaillé dans notre
        <a href="{{ route('legal.cookies') }}">politique cookies</a>.
    </p>

    <h2>9. Droit applicable</h2>
    <p>
        Les présentes mentions légales sont régies par le droit français. En cas de litige, et après
        échec de toute tentative de résolution amiable, les tribunaux français seront seuls compétents.
    </p>
@endsection
