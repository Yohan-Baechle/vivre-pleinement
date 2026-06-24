<?php

namespace App\Support;

/**
 * Questions fréquentes propres à la prise de rendez-vous, affichées sur la
 * page de réservation (déroulement, paiement, confidentialité, etc.).
 */
class BookingFaq
{
    /**
     * Liste complète des questions/réponses.
     *
     * @return list<array{q: string, a: string}>
     */
    public static function all(): array
    {
        return [
            [
                'q' => 'Comment cela va-t-il se passer pour prendre rendez-vous ?',
                'a' => "Lorsque vous cliquerez sur le bouton « prendre rendez-vous », vous allez pouvoir choisir le type de prestation sollicité (téléphone ou visio). Puis, vous serez redirigé vers une autre page où vous devrez entrer vos coordonnées (mail et numéro de portable notamment), et régler le montant de 50 €, par carte bancaire ou PayPal. Si vous choisissez le rendez-vous téléphonique, je vous appellerai directement le jour J à l'heure convenue. Si vous optez pour un rendez-vous en visio, nous conviendrons par courriel de la plateforme qui sera utilisée pour la communication : Zoom, Skype, WhatsApp, etc.",
            ],
            [
                'q' => "Comment se passe le règlement ? Aurai-je un justificatif d'achat ?",
                'a' => 'Dès lors que vous aurez réglé le montant de la séance, vous recevrez un courrier électronique de confirmation de paiement immédiatement dans votre boîte mail.',
            ],
            [
                'q' => "Je n'aime pas acheter des prestations sur Internet, car je n'ai pas confiance. L'achat est-il vraiment sécurisé ?",
                'a' => 'Vous ne risquez absolument rien. Vous pouvez régler soit par PayPal, soit par carte bancaire. Les plateformes de paiement en ligne Stripe et PayPal sont réputées pour effectuer des paiements simples et sécurisés.',
            ],
            [
                'q' => 'Quelle mention va apparaître sur mon relevé bancaire ?',
                'a' => "Les données remplies lors de la commande restent anonymes pour votre banque. L'achat sur votre compte bancaire portera la mention : vivre pleinement.",
            ],
            [
                'q' => "Est-ce qu'un accompagnement à distance est aussi efficace qu'un accompagnement en présentiel ?",
                'a' => "Les accompagnements à distance se font de plus en plus. Certains praticiens ou thérapeutes arrivent même à utiliser l'hypnose à distance, par exemple. Donc, hormis cas particuliers où une séance en présentiel doit obligatoirement se faire (comme aller se promener avec son praticien ou thérapeute dans le cadre d'un exercice pour lutter contre la timidité), l'accompagnement à distance vaut autant qu'un accompagnement en présentiel.",
            ],
            [
                'q' => 'Est-ce que tout ce que je dis restera confidentiel ?',
                'a' => "Bien évidemment ! Je comprends que vous souhaitiez que les informations que vous me communiquerez restent confidentielles. Je m'engage à respecter votre vie privée à 100 %. Je ne divulguerai donc aucune information à votre sujet à qui que ce soit.",
            ],
            [
                'q' => 'Quelle crédibilité avez-vous pour proposer des accompagnements à des personnes souffrant de troubles anxieux ?',
                'a' => "Je suis praticienne ACT, laquelle est une approche thérapeutique dont l'efficacité est validée scientifiquement. Elle fait partie des TCC de 3ᵉ vague, qui enrichissent les thérapies cognitives et comportementales classiques. De plus, j'estime que personne ne peut en comprendre une autre sans avoir expérimenté la même chose.",
            ],
        ];
    }
}
