@php
    $editor = config('legal.editor');
    $mediator = config('legal.mediator');

    $title = 'Conditions générales de vente';
    $intro = "Les présentes conditions générales de vente (CGV) régissent les prestations d'accompagnement proposées par {$editor['name']} dans le cadre de son activité d'auto-entrepreneur.";
    $breadcrumb = [['label' => 'CGV']];
@endphp

@extends('layouts.legal')

@section('legal-content')
    <h2>1. Objet</h2>
    <p>
        Les présentes conditions générales de vente définissent les droits et obligations des parties
        dans le cadre des prestations d'accompagnement individuel proposées par {{ $editor['name'] }}
        (ci-après « le Prestataire »), à toute personne physique majeure (ci-après « le Client »).
    </p>

    <h2>2. Nature des prestations</h2>
    <p>
        Le Prestataire propose un accompagnement orienté solution pour les personnes souffrant de
        troubles anxieux (anxiété généralisée, phobies, troubles obsessionnels compulsifs, burnout,
        attaques de panique, etc.).
    </p>
    <p>
        <strong>Les prestations proposées ne constituent pas un acte médical, un acte
        psychothérapeutique réglementé, ni un substitut à un suivi médical ou psychologique.</strong>
        Le Client reste libre et responsable du suivi de tout traitement médical en cours. En cas de
        détresse psychologique, le Client est invité à consulter un médecin ou les services
        d'urgence (15, 112, 3114 pour la prévention du suicide).
    </p>

    <h2>3. Modalités</h2>
    <p>
        Les séances se déroulent exclusivement à distance, en visioconférence, sur des créneaux
        convenus à l'avance entre le Prestataire et le Client. Le Client doit disposer du matériel
        nécessaire (ordinateur ou smartphone, connexion internet, espace calme).
    </p>
    <p>
        Le premier rendez-vous (« rendez-vous découverte ») d'une durée de 30 minutes est <strong>gratuit
        et sans engagement</strong>. Il permet de définir si l'accompagnement est adapté à la situation
        du Client.
    </p>

    <h2>4. Tarifs</h2>
    <p>
        Les tarifs en vigueur sont communiqués au Client lors du rendez-vous découverte ou sur demande.
        Ils sont exprimés en euros, toutes taxes comprises. {{ $editor['name'] }} bénéficie de la
        franchise en base de TVA (article 293 B du Code général des impôts) ; la TVA n'est donc pas
        applicable.
    </p>
    <p>
        Les prestations ne sont pas remboursées par la Sécurité sociale. Certaines mutuelles peuvent
        prendre en charge tout ou partie des séances de coaching ; il appartient au Client de se
        renseigner auprès de la sienne.
    </p>

    <h2>5. Commande et paiement</h2>
    <p>
        La commande est validée par l'acceptation expresse du devis ou de la proposition par le
        Client. Le paiement s'effectue par virement bancaire ou autre moyen accepté par le
        Prestataire, selon les conditions précisées dans la proposition.
    </p>
    <p>
        En cas de forfait de plusieurs séances, le paiement peut être échelonné selon les modalités
        convenues. Une facture est émise pour chaque paiement.
    </p>

    <h2>6. Droit de rétractation</h2>
    <p>
        Conformément à l'article L.221-18 du Code de la consommation, le Client dispose d'un délai
        de <strong>14 jours</strong> à compter de la conclusion du contrat pour exercer son droit de
        rétractation, sans avoir à motiver sa décision.
    </p>
    <p>
        Pour exercer ce droit, le Client doit notifier sa décision par email à
        <a href="mailto:{{ $editor['email'] }}">{{ $editor['email'] }}</a>.
    </p>
    <p>
        Si le Client demande expressément le commencement de la prestation avant la fin du délai de
        rétractation, il sera redevable du paiement correspondant aux services déjà fournis. En cas
        d'exécution intégrale de la prestation pendant le délai de rétractation, ce droit ne pourra
        plus être exercé (article L.221-28 du Code de la consommation).
    </p>

    <h2>7. Annulation et report</h2>
    <p>
        Toute séance peut être annulée ou reportée sans frais à condition que le Client en informe le
        Prestataire <strong>au moins 48 heures à l'avance</strong>.
    </p>
    <p>
        Passé ce délai, la séance pourra être considérée comme due, sauf cas de force majeure dûment
        justifié.
    </p>

    <h2>8. Obligations des parties</h2>
    <p>
        Le Prestataire s'engage à mettre en œuvre tous les moyens nécessaires à la bonne exécution
        de sa prestation. Il s'agit d'une obligation de moyens et non de résultat.
    </p>
    <p>
        Le Client s'engage à fournir au Prestataire toutes les informations utiles à
        l'accompagnement, à participer activement aux séances et à respecter les rendez-vous
        convenus.
    </p>

    <h2>9. Confidentialité</h2>
    <p>
        Le Prestataire est tenu à une stricte confidentialité concernant l'ensemble des informations
        partagées par le Client durant les séances. Aucune information ne sera divulguée à des tiers
        sans l'accord exprès du Client, sauf obligation légale.
    </p>

    <h2>10. Responsabilité</h2>
    <p>
        La responsabilité du Prestataire ne pourra être engagée que pour les dommages directs résultant
        de manquements à ses obligations contractuelles. En aucun cas, le Prestataire ne saurait être
        tenu responsable des décisions prises par le Client suite à l'accompagnement.
    </p>

    <h2>11. Propriété intellectuelle</h2>
    <p>
        Tous les supports remis au Client (documents, exercices, enregistrements) restent la propriété
        intellectuelle du Prestataire. Ils sont destinés à un usage strictement personnel et ne
        peuvent être reproduits, diffusés ou commercialisés sans autorisation écrite.
    </p>

    <h2>12. Données personnelles</h2>
    <p>
        Les données personnelles collectées dans le cadre de la prestation sont traitées
        conformément au RGPD et à notre
        <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
    </p>

    <h2>13. Médiation de la consommation</h2>
    <p>
        Conformément aux articles L.611-1 et suivants du Code de la consommation, en cas de litige
        n'ayant pas pu être résolu à l'amiable, le Client consommateur peut recourir gratuitement au
        service de médiation suivant :
    </p>
    <ul>
        <li><strong>{{ $mediator['name'] }}</strong></li>
        <li>Adresse : {{ $mediator['address'] }}</li>
        <li>Site : <a href="{{ $mediator['website'] }}" target="_blank" rel="noopener">{{ $mediator['website'] }}</a></li>
    </ul>
    <p>
        Le Client peut également recourir à la plateforme européenne de Règlement en Ligne des
        Litiges (RLL) :
        <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.
    </p>

    <h2>14. Loi applicable et juridiction</h2>
    <p>
        Les présentes CGV sont soumises au droit français. En cas de litige et après échec de toute
        tentative de résolution amiable, les tribunaux français seront seuls compétents, conformément
        aux règles de droit commun en vigueur.
    </p>

    <h2>15. Modifications des CGV</h2>
    <p>
        Le Prestataire se réserve le droit de modifier les présentes CGV à tout moment. Les CGV
        applicables sont celles en vigueur à la date de la conclusion du contrat.
    </p>
@endsection
