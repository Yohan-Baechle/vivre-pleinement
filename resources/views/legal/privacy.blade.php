@php
    $editor = config('legal.editor');
    $controller = config('legal.data_controller');
    $cnil = config('legal.cnil');
    $site = config('legal.site');

    $title = 'Politique de confidentialité';
    $intro = "Nous attachons une grande importance au respect de votre vie privée. Cette politique décrit comment nous collectons, utilisons et protégeons vos données personnelles, conformément au Règlement Général sur la Protection des Données (RGPD - UE 2016/679) et à la loi Informatique et Libertés modifiée.";
    $breadcrumb = [['label' => 'Politique de confidentialité']];
@endphp

@extends('layouts.legal')

@section('legal-content')
    <h2>1. Responsable du traitement</h2>
    <p>
        Le responsable du traitement de vos données personnelles est :
    </p>
    <ul>
        <li><strong>{{ $controller['name'] }}</strong></li>
        <li>Adresse : {{ $controller['address'] }}</li>
        <li>Email : <a href="mailto:{{ $controller['email'] }}">{{ $controller['email'] }}</a></li>
    </ul>
    <p>
        Compte tenu de la nature et du volume des traitements, la désignation d'un Délégué à la
        Protection des Données (DPO) n'est pas obligatoire. Toute question relative au traitement de
        vos données peut être adressée directement à l'adresse ci-dessus.
    </p>

    <h2>2. Données collectées et finalités</h2>
    <p>
        Nous ne collectons que les données strictement nécessaires aux finalités décrites ci-dessous,
        conformément au principe de minimisation (RGPD art. 5.1.c).
    </p>

    <h3>2.1 Formulaire de contact</h3>
    <ul>
        <li><strong>Données collectées</strong> : prénom, nom (optionnel), email, téléphone (optionnel), objet du message, contenu du message.</li>
        <li><strong>Finalité</strong> : répondre à votre demande de contact ou de rendez-vous.</li>
        <li><strong>Base légale</strong> : consentement (RGPD art. 6.1.a) recueilli via la case à cocher dédiée.</li>
        <li><strong>Durée de conservation</strong> : 3 ans à compter du dernier échange, puis archivage anonymisé ou suppression.</li>
    </ul>

    <h3>2.2 Newsletter et ressources gratuites</h3>
    <ul>
        <li><strong>Données collectées</strong> : prénom, adresse email.</li>
        <li><strong>Finalité</strong> : envoi de la ressource demandée (vidéo offerte) puis envoi de newsletters et conseils relatifs aux troubles anxieux.</li>
        <li><strong>Base légale</strong> : consentement libre, spécifique, éclairé et univoque, manifesté par l'inscription au formulaire.</li>
        <li><strong>Durée de conservation</strong> : jusqu'à votre désinscription (lien présent dans chaque email) ou 3 ans d'inactivité.</li>
    </ul>

    <h3>2.3 Commentaires sur les articles</h3>
    <ul>
        <li><strong>Données collectées</strong> : pseudonyme, email (non publié), contenu du commentaire, adresse IP (à des fins de modération).</li>
        <li><strong>Finalité</strong> : permettre l'expression des lecteurs, modérer les contenus.</li>
        <li><strong>Base légale</strong> : intérêt légitime à modérer les contenus publiés sur le site.</li>
        <li><strong>Durée de conservation</strong> : tant que l'article est en ligne, ou jusqu'à demande de suppression.</li>
    </ul>

    <h3>2.4 Données de navigation</h3>
    <ul>
        <li><strong>Données collectées</strong> : adresse IP, type de navigateur, pages consultées, durée de visite, source de la visite.</li>
        <li><strong>Finalité</strong> : mesurer l'audience du site et améliorer son contenu.</li>
        <li><strong>Base légale</strong> : consentement (cookies analytiques, cf. politique cookies).</li>
        <li><strong>Durée de conservation</strong> : 13 mois maximum, conformément aux recommandations CNIL.</li>
    </ul>

    <h2>3. Destinataires des données</h2>
    <p>
        Vos données sont uniquement traitées par {{ $controller['name'] }} dans le cadre de ses
        activités d'accompagnement.
    </p>
    <p>
        Certains sous-traitants techniques peuvent avoir un accès limité à vos données pour les seuls
        besoins du service :
    </p>
    <ul>
        <li><strong>Hetzner Online GmbH</strong> (hébergement) - UE, conforme RGPD ;</li>
        <li><strong>Brevo</strong> (envoi des emails marketing et transactionnels) - UE, conforme RGPD ;</li>
        <li><strong>Google Ireland Limited</strong> (mesure d'audience via Google Analytics) - sous réserve de votre consentement.</li>
    </ul>
    <p>
        Aucune donnée n'est vendue ou cédée à des tiers à des fins commerciales.
    </p>

    <h2>4. Transferts hors UE</h2>
    <p>
        Lorsque vous acceptez les cookies Google Analytics, des données techniques peuvent être
        transférées vers les serveurs de Google aux États-Unis. Ces transferts sont encadrés par les
        clauses contractuelles types de la Commission européenne et par le Data Privacy Framework
        (décision d'adéquation du 10 juillet 2023).
    </p>

    <h2>5. Vos droits</h2>
    <p>
        Conformément aux articles 15 à 22 du RGPD, vous disposez des droits suivants sur vos données
        personnelles :
    </p>
    <ul>
        <li><strong>Droit d'accès</strong> : obtenir la confirmation que vos données sont traitées et en recevoir une copie ;</li>
        <li><strong>Droit de rectification</strong> : corriger des données inexactes ou incomplètes ;</li>
        <li><strong>Droit à l'effacement</strong> (« droit à l'oubli ») : demander la suppression de vos données ;</li>
        <li><strong>Droit à la limitation du traitement</strong> : suspendre temporairement le traitement ;</li>
        <li><strong>Droit d'opposition</strong> : vous opposer au traitement de vos données pour des motifs légitimes ;</li>
        <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré ;</li>
        <li><strong>Droit de retirer votre consentement</strong> à tout moment, sans que cela n'affecte la licéité du traitement antérieur ;</li>
        <li><strong>Droit de définir des directives</strong> relatives au sort de vos données après votre décès.</li>
    </ul>
    <p>
        Pour exercer ces droits, contactez-nous à l'adresse
        <a href="mailto:{{ $controller['email'] }}">{{ $controller['email'] }}</a> en joignant
        un justificatif d'identité. Nous nous engageons à répondre dans un délai d'un mois maximum
        (prorogeable à trois mois si la demande est complexe).
    </p>

    <h2>6. Réclamation auprès de la CNIL</h2>
    <p>
        Si vous estimez, après nous avoir contactés, que vos droits ne sont pas respectés, vous pouvez
        introduire une réclamation auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL) :
    </p>
    <ul>
        <li>Adresse : 3 Place de Fontenoy, TSA 80715, 75334 Paris Cedex 07</li>
        <li>Téléphone : 01 53 73 22 22</li>
        <li>Site : <a href="{{ $cnil['complaint_url'] }}" target="_blank" rel="noopener">{{ $cnil['complaint_url'] }}</a></li>
    </ul>

    <h2>7. Sécurité des données</h2>
    <p>
        Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger
        vos données contre la perte, l'utilisation abusive, l'accès non autorisé, la divulgation,
        l'altération ou la destruction : chiffrement HTTPS sur l'ensemble du site, hébergement
        sécurisé, accès aux données protégés par mot de passe et limités aux strictes nécessités.
    </p>

    <h2>8. Cookies</h2>
    <p>
        L'usage des cookies est détaillé dans notre
        <a href="{{ route('legal.cookies') }}">politique cookies</a> dédiée. Vous pouvez à tout moment
        modifier vos choix via le lien « Gérer les cookies » présent en pied de page.
    </p>

    <h2>9. Modifications</h2>
    <p>
        Cette politique de confidentialité peut être amenée à évoluer pour refléter les évolutions
        légales, jurisprudentielles ou techniques. La date de dernière mise à jour est indiquée en
        haut de cette page.
    </p>
@endsection
