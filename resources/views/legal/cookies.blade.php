@php
    $controller = config('legal.data_controller');

    $title = 'Politique cookies';
    $intro = "Cette politique explique quels cookies et traceurs sont utilisés sur le site et comment vous pouvez les gérer, conformément à la recommandation CNIL du 17 septembre 2020 (délibération 2020-091).";
    $breadcrumb = [['label' => 'Politique cookies']];
@endphp

@extends('layouts.legal')

@section('legal-content')
    <h2>1. Qu'est-ce qu'un cookie ?</h2>
    <p>
        Un cookie est un petit fichier texte déposé sur votre terminal (ordinateur, tablette,
        smartphone) lors de votre visite sur un site web. Il permet de stocker des informations qui
        seront utilisées lors de visites ultérieures (préférences, suivi de session, mesure d'audience).
    </p>

    <h2>2. Cookies utilisés sur ce site</h2>

    <h3>2.1 Cookies strictement nécessaires (exemptés de consentement)</h3>
    <p>
        Ces cookies sont indispensables au fonctionnement du site et ne peuvent pas être désactivés.
        Ils ne stockent aucune information personnelle identifiante.
    </p>
    <ul>
        <li><strong>laravel_session</strong> – Sessions utilisateur, sécurité (durée : 2 heures).</li>
        <li><strong>XSRF-TOKEN</strong> – Protection contre les attaques CSRF (durée : 2 heures).</li>
        <li><strong>cookie-consent</strong> – Mémorise vos préférences de consentement (durée : 6 mois).</li>
    </ul>

    <h3>2.2 Cookies de mesure d'audience (soumis à consentement)</h3>
    <p>
        Ces cookies nous aident à comprendre comment les visiteurs interagissent avec le site, en
        collectant et rapportant des informations de manière anonyme.
    </p>
    <ul>
        <li><strong>_ga, _ga_*</strong> (Google Analytics) – Distinguer les utilisateurs et sessions
            (durée : 13 mois maximum). Anonymisation d'IP activée.</li>
    </ul>

    <h3>2.3 Cookies de marketing et personnalisation</h3>
    <p>
        À ce jour, le site n'utilise <strong>aucun cookie publicitaire</strong> et ne fait pas appel
        au pixel Meta, Google Ads, ou similaires. Cette politique sera mise à jour si cela devait
        évoluer, et un nouveau consentement vous serait demandé.
    </p>

    <h2>3. Gestion de votre consentement</h2>
    <p>
        Lors de votre première visite, une bannière vous permet d'accepter, refuser ou personnaliser
        les cookies non essentiels. Votre choix est conservé pendant 6 mois maximum, conformément aux
        recommandations CNIL.
    </p>
    <p>
        Vous pouvez à tout moment modifier vos choix en cliquant sur le lien
        <strong>« Gérer les cookies »</strong> en pied de page, ou en supprimant les cookies de votre
        navigateur.
    </p>

    <h2>4. Configuration de votre navigateur</h2>
    <p>
        Vous pouvez également configurer votre navigateur pour qu'il vous notifie de la réception
        des cookies et vous demande de les accepter ou non, ou pour bloquer certains cookies. La
        plupart des navigateurs proposent une rubrique dédiée dans leurs paramètres :
    </p>
    <ul>
        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
        <li><a href="https://support.mozilla.org/fr/kb/protection-renforcee-contre-pistage-firefox-ordinateur" target="_blank" rel="noopener">Mozilla Firefox</a></li>
        <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Apple Safari</a></li>
        <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/" target="_blank" rel="noopener">Microsoft Edge</a></li>
    </ul>
    <p>
        Le refus des cookies peut limiter l'accès à certaines fonctionnalités du site mais ne vous
        empêche pas de le consulter.
    </p>

    <h2>5. Cookies tiers</h2>
    <p>
        Les cookies déposés par Google Analytics sont gérés par Google Ireland Limited. Pour plus
        d'informations sur l'utilisation de ces données par Google :
        <a href="https://policies.google.com/privacy?hl=fr" target="_blank" rel="noopener">policies.google.com/privacy</a>.
    </p>

    <h2>6. Vos droits</h2>
    <p>
        Conformément au RGPD, vous disposez d'un droit d'accès, de rectification, d'effacement,
        d'opposition et de portabilité sur vos données collectées via les cookies. Ces droits sont
        détaillés dans notre <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
    </p>

    <h2>7. Contact</h2>
    <p>
        Pour toute question relative à l'usage des cookies, vous pouvez nous contacter à
        <a href="mailto:{{ $controller['email'] }}">{{ $controller['email'] }}</a>.
    </p>
@endsection
