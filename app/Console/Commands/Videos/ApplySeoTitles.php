<?php

namespace App\Console\Commands\Videos;

use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('videos:apply-seo-titles {--dry-run : Affiche les changements sans les enregistrer}')]
#[Description('Remplace les titres YouTube (majuscules, emojis) par des titres orientés recherche, puis verrouille le champ contre la sync YouTube.')]
class ApplySeoTitles extends Command
{
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $changed = 0;

        foreach ($this->titles() as $slug => $title) {
            $video = Video::query()->where('slug', $slug)->first();

            if (! $video) {
                $this->warn("Vidéo introuvable : {$slug}");

                continue;
            }

            if ($video->title === $title && $video->isLocked('title')) {
                continue;
            }

            $changed++;
            $this->line(($dry ? '[dry] ' : '')."✓ {$slug}");
            $this->line("      {$video->title} → {$title}");

            if ($dry) {
                continue;
            }

            $video->title = $title;
            $video->sync_locked_fields = array_values(array_unique([...($video->sync_locked_fields ?? []), 'title']));
            $video->save();
        }

        $this->newLine();
        $this->comment(($dry ? '[dry] ' : '')."{$changed} titre(s) réécrit(s) et verrouillé(s) contre la sync YouTube.");

        return self::SUCCESS;
    }

    /**
     * Titres SERP par slug : casse normale, mot-clé en tête, sans emoji, ≤ 60
     * caractères.
     *
     * @return array<string, string>
     */
    private function titles(): array
    {
        return [
            'angoisse-matinale-comment-je-men-suis-sortie' => "Angoisse matinale : comment je m'en suis sortie",
            'peur-de-conduire-amaxophobie-comment-je-men-suis-liberee' => "Amaxophobie (peur de conduire) : je m'en suis libérée",
            'comment-soigner-la-blessure-de-trahison-11-conseils-precieux' => 'Soigner la blessure de trahison : mes conseils en vidéo',
            'blessure-de-rejet-9-symptomes-pour-la-reconnaitre' => 'Les signes de la blessure de rejet, expliqués en vidéo',
            'depersonnalisation-derealisation-mes-conseils-pour-sen-liberer' => "Dépersonnalisation, déréalisation : comment s'en libérer",
            'antidepresseur-et-anxiolytique-mon-experience-et-mon-avis' => 'Antidépresseurs et anxiolytiques : mon expérience, mon avis',
            'toc-homosexuel-mon-experience-et-mes-conseils-pour-sen-liberer' => 'TOC homosexuel : mon expérience et mes conseils',
            'la-phobie-dimpulsion-une-peur-ou-une-envie' => "La phobie d'impulsion expliquée en vidéo",
            'comment-soigner-la-thanatophobie-peur-de-la-mort' => 'Thanatophobie : comment soigner la peur de la mort',
            'reconnaitre-la-blessure-de-trahison-en-9-symptomes' => 'Les signes de la blessure de trahison, expliqués en vidéo',
            'toc-et-pensees-intrusives-les-comprendre-et-sen-liberer' => 'TOC et pensées intrusives : mes conseils en vidéo',
            'ne-rien-attendre-des-autres-la-cle-du-bonheur' => 'Ne rien attendre des autres : la clé du bonheur',
            'les-secrets-pour-vaincre-lhypocondrie-et-retrouver-la-serenite' => "Vaincre l'hypocondrie et retrouver la sérénité",
            'exercices-de-defusion-cognitive-pour-se-liberer-des-pensees-intrusives' => 'Défusion cognitive : exercices contre les pensées intrusives',
            'blessure-dinjustice-comment-la-soigner-8-facons-dy-parvenir' => "Soigner la blessure d'injustice : mes conseils en vidéo",
            'comment-vaincre-la-cardiophobie-peur-de-faire-une-crise-cardiaque' => 'Cardiophobie : mes conseils en vidéo',
            'comment-soigner-la-blessure-de-rejet-8-conseils-utiles' => 'Soigner la blessure de rejet : mes conseils en vidéo',
            'ergophobie-peur-du-travail-comment-sen-sortir' => 'Ergophobie : mes conseils en vidéo contre la peur du travail',
            'le-besoin-detre-rassure-dans-lanxiete-le-meilleur-moyen-de-ne-pas-guerir' => "Besoin d'être rassuré : le piège qui entretient l'anxiété",
            'fatigue-mentale-comment-retrouver-lenergie' => "Fatigue mentale : comment retrouver l'énergie",
            'comment-aimer-et-accepter-sa-personnalite-casaniere' => 'Être casanier : aimer et accepter sa personnalité',
            'blessure-dinjustice-11-symptomes-qui-la-caracterisent' => "Les signes de la blessure d'injustice, expliqués en vidéo",
            'toc-religieux-comment-je-men-suis-sortie' => "TOC religieux : comment je m'en suis sortie",
            'comment-arreter-detre-susceptible-mon-experience-et-mes-conseils' => "Comment arrêter d'être susceptible : mes conseils",
            'alcool-et-anxiete-stoppez-ce-comportement-des-maintenant' => 'Alcool et anxiété : pourquoi ce réflexe aggrave tout',
            'le-mal-a-dit-le-lien-entre-emotions-et-maladies' => 'Le mal a dit : le lien entre émotions et maladies',
            'guerir-dun-trouble-anxieux-generalise-tag-cest-possible' => 'Guérir du trouble anxieux généralisé : mon témoignage',
            'blessure-dabandon-9-symptomes-pour-la-reconnaitre' => "Les signes de la blessure d'abandon, expliqués en vidéo",
            'comment-guerir-la-blessure-dabandon-8-moyens-dy-arriver' => "Comment guérir la blessure d'abandon : 8 moyens",
            'trouble-anxio-depressif-comment-je-lai-surmonte' => "Trouble anxio-dépressif : comment je l'ai surmonté",
            'aquaphobie-peur-de-leau-comment-je-men-suis-liberee' => "Aquaphobie (peur de l'eau) : comment je m'en suis libérée",
            'se-liberer-du-perfectionnisme-et-de-linsatisfaction-chronique' => "Se libérer du perfectionnisme et de l'insatisfaction",
            'controler-ses-pensees-intrusives-evitez-ces-strategies-qui-vous-maintiennent-dans-le-mal-etre' => 'Contrôler ses pensées intrusives : les stratégies à éviter',
            'peur-de-sortir-les-solutions-contre-lagoraphobie' => 'Agoraphobie : les solutions contre la peur de sortir',
            'comment-se-defaire-des-sentiments-de-rancune-et-de-rancoeur' => 'Comment se défaire de la rancune et de la rancœur',
            'comment-arreter-detre-indecis-mon-experience-et-mon-avis' => "Comment arrêter d'être indécis : mon expérience",
            'lacceptation-de-soi-la-cle-de-la-guerison' => "L'acceptation de soi : la clé de la guérison",
            'appliquer-les-5-accords-tolteques-au-quotidien-pour-plus-de-bien-etre' => 'Appliquer les 5 accords toltèques au quotidien',
            'exercices-de-defusion-cognitive-pour-se-liberer-des-images-intrusives' => 'Défusion cognitive : exercices contre les images intrusives',
            'estime-de-soi-et-confiance-en-soi-comment-les-renforcer' => 'Estime de soi et confiance en soi : comment les renforcer',
            'peur-de-conduire-sur-lautoroute-comment-je-men-suis-sortie' => "Peur de l'autoroute : comment je m'en suis sortie",
            'comment-se-liberer-de-la-jalousie' => 'Comment se libérer de la jalousie',
            'toc-homosexuel-ou-homosexualite-refoulee-la-verite' => 'TOC homosexuel ou homosexualité refoulée ? La vérité',
            'seance-de-relaxation-methode-jacobson' => 'Séance de relaxation guidée : la méthode Jacobson',
            'comment-se-liberer-des-ruminations-mentales' => 'Comment se libérer des ruminations mentales',
            '3-exercices-pour-se-liberer-des-pensees-intrusives-et-images-desagreables' => '3 exercices pour se libérer des pensées intrusives',
            'angoisse-nocturne-comment-enfin-bien-dormir' => 'Angoisse nocturne : comment enfin bien dormir',
            'comment-se-liberer-de-lanxiete-anticipatoire' => "Comment se libérer de l'anxiété anticipatoire",
            'se-liberer-du-sentiment-de-culpabilite-dans-le-trouble-anxieux' => "Anxiété et culpabilité : comment s'en libérer",
            'comment-retrouver-la-paix-apres-un-burn-out' => 'Comment retrouver la paix après un burn-out',
            'le-perfectionnisme-et-les-troubles-anxieux-sont-lies' => 'Perfectionnisme et troubles anxieux : un lien étroit',
            'comment-avoir-une-meilleure-estime-de-soi-ce-quil-faut-savoir' => 'Comment avoir une meilleure estime de soi',
            'comprendre-les-ruminations-mentales-pour-sen-liberer' => "Comprendre les ruminations mentales pour s'en libérer",
            'peur-de-devenir-fou-ce-que-ca-dit-vraiment-sur-vous' => 'Peur de devenir fou : ce que ça dit vraiment de vous',
            'dependance-affective-la-comprendre-et-la-soigner' => 'Dépendance affective : la comprendre et la soigner',
            'vous-netes-pas-votre-anxiete-arretez-de-vous-identifier-a-elle' => "Vous n'êtes pas votre anxiété : cessez de vous y identifier",
            'stress-anxiete-ou-angoisse-les-differences-a-comprendre-pour-guerir' => 'Stress, anxiété, angoisse : les différences en vidéo',
            'emetophobie-peur-de-vomir-mon-experience-et-mes-conseils' => 'Émétophobie (peur de vomir) : mon expérience, mes conseils',
            'comment-se-detacher-du-jugement-des-autres' => 'Comment se détacher du jugement des autres',
            '14-anxiolytiques-naturels-pour-soulager-vos-troubles-anxieux' => '14 anxiolytiques naturels contre les troubles anxieux',
            'apprendre-a-gerer-ses-emotions-pour-enfin-vivre-sereinement' => 'Apprendre à gérer ses émotions pour vivre sereinement',
            'leffet-miroir-le-meilleur-moyen-de-guerir-notre-inconscient' => "L'effet miroir : un chemin pour guérir notre inconscient",
            'comment-se-liberer-de-la-peur-de-perdre-le-controle' => 'Comment se libérer de la peur de perdre le contrôle',
            'pourquoi-connaitre-la-cause-de-ses-troubles-anxieux-ne-sert-a-rien' => "Chercher la cause de son anxiété : pourquoi c'est inutile",
            'maitriser-le-lacher-prise-emotionnel-la-cle-essentielle-du-bonheur' => 'Lâcher-prise émotionnel : la clé essentielle du bonheur',
            'comment-surmonter-la-peur-du-changement' => 'Comment surmonter la peur du changement',
            'conseils-pour-vaincre-la-phobie-sociale-anxiete-sociale' => "Phobie sociale : conseils pour vaincre l'anxiété sociale",
            'comment-calmer-une-crise-dangoisse' => "Comment calmer une crise d'angoisse",
            'prendre-ses-responsabilites-le-meilleur-moyen-detre-heureux' => 'Prendre ses responsabilités pour être heureux',
            'comprendre-et-soulager-les-troubles-de-la-personnalite-groupe-c' => 'Troubles de la personnalité (groupe C) : les comprendre',
            'comment-aller-bien-quand-tout-va-mal' => 'Comment aller bien quand tout va mal',
            'quelles-sont-les-causes-de-la-depersonnalisation-derealisation' => 'Dépersonnalisation, déréalisation : quelles causes ?',
            'les-etapes-pour-se-reconstruire-apres-un-traumatisme-2eme-partie' => 'Se reconstruire après un traumatisme : les étapes (2/2)',
            'comprendre-et-soulager-les-troubles-de-la-personnalite-groupe-a' => 'Troubles de la personnalité (groupe A) : les comprendre',
            'decouvrez-la-chaine-laura-vivre-pleinement-le-secret-pour-vaincre-lanxiete' => "Laura Vivre Pleinement : la chaîne pour vaincre l'anxiété",
            'limportance-de-laffirmation-de-soi-pour-sepanouir' => "L'importance de l'affirmation de soi pour s'épanouir",
            'comprendre-et-vaincre-linsomnie' => "Comprendre et vaincre l'insomnie",
            'angoisse-matinale-sans-raison-10-causes-cachees' => 'Angoisse matinale sans raison : 10 causes cachées',
            'angoisse-matinale-mal-le-matin-mais-mieux-le-soir' => 'Angoisse matinale : mal le matin mais mieux le soir ?',
            'les-etapes-pour-se-reconstruire-apres-un-traumatisme-1ere-partie' => 'Se reconstruire après un traumatisme : les étapes (1/2)',
            'vaincre-la-phobie-de-lavion-pour-enfin-voyager-sereinement' => "Vaincre la phobie de l'avion pour voyager sereinement",
            'se-liberer-de-la-comparaison-sociale-pour-une-vie-paisible' => 'Se libérer de la comparaison sociale',
            'comment-gerer-le-sentiment-de-colere' => 'Comment gérer le sentiment de colère',
            'etre-vrai-et-authentique-le-veritable-bonheur' => 'Être vrai et authentique : le véritable bonheur',
            'comprendre-les-addictions-pour-sen-liberer' => "Les addictions expliquées en vidéo : s'en libérer",
            'que-cache-la-peur-de-la-mort' => 'Que cache la peur de la mort ?',
            'comment-se-liberer-des-croyances-limitantes' => 'Comment se libérer des croyances limitantes',
            'bien-vivre-son-hypersensibilite-pour-en-faire-une-force' => 'Hypersensibilité : en faire une force au quotidien',
            'je-suis-devenue-praticienne-act-tcc-3eme-vague' => 'Je suis devenue praticienne ACT (TCC 3e vague)',
            'comprendre-et-soulager-les-troubles-de-la-personnalite-groupe-b' => 'Troubles de la personnalité (groupe B) : les comprendre',
            '6-causes-de-votre-fatigue-mentale-que-vous-ignorez' => '6 causes de votre fatigue mentale que vous ignorez',
            'vivre-en-accord-avec-ses-valeurs-personnelles-pour-une-vie-epanouie' => 'Vivre en accord avec ses valeurs personnelles',
            '15-aliments-anti-angoisse-a-integrer-dans-votre-quotidien' => '15 aliments anti-angoisse à intégrer au quotidien',
            'enneagramme-type-2-laltruiste-vous-reconnaissez-vous-dans-cette-personnalite' => "Ennéagramme type 2, l'altruiste : le portrait complet",
            'comment-lact-tcc-3e-vague-a-change-mes-troubles-anxieux' => "Comment l'ACT a changé mes troubles anxieux (TCC 3e vague)",
            'syndrome-du-sauveur-etes-vous-concerne-par-ce-trouble' => 'Syndrome du sauveur : êtes-vous concerné ?',
            'crise-dangoisse-au-travail-14-choses-a-faire-absolument' => "Crise d'angoisse au travail : 14 choses à faire",
            'enneagramme-type-1-le-perfectionniste-vous-reconnaissez-vous-dans-cette-personnalite' => 'Ennéagramme type 1, le perfectionniste : le portrait complet',
            'comment-soigner-la-depression-saisonniere' => 'Comment soigner la dépression saisonnière',
            'hypocondrie-obsessionnelle-nallez-surtout-pas-sur-internet' => 'Hypocondrie obsessionnelle : pourquoi éviter internet',
            'remerciements-1-000-abonnes-faq-et-projets-a-venir' => '1 000 abonnés : remerciements, FAQ et projets à venir',
            'comment-surmonter-le-syndrome-du-dimanche-mes-conseils-incontournables' => 'Syndrome du dimanche : comment le surmonter',
            'ce-que-cachent-vraiment-les-ruminations-mentales' => 'Ce que cachent vraiment les ruminations mentales',
            'hypocondrie-pourquoi-vous-doutez-toujours-de-votre-sante' => 'Hypocondrie : pourquoi vous doutez toujours de votre santé',
            'le-vrai-probleme-du-trouble-anxieux-generalise-nest-pas-lanxiete' => 'Trouble anxieux généralisé : le vrai problème à comprendre',
            'pourquoi-les-compulsions-soulagent-mais-aggravent-le-toc' => 'TOC : pourquoi les compulsions soulagent mais aggravent tout',
        ];
    }
}
