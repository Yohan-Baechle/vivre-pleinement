<?php

namespace App\Enums;

use App\Models\Course;

/**
 * Objets proposés dans le formulaire de contact.
 *
 * La liste vivait en trois exemplaires — la règle de validation, la table des
 * libellés de l'email et les options du select — qui divergeaient au premier
 * ajout. Elle tient désormais ici, et `available()` en est la seule porte
 * d'entrée : ce que le visiteur voit et ce que le serveur accepte ne peuvent
 * plus se contredire.
 */
enum ContactSubject: string
{
    case Rdv = 'rdv';
    case Question = 'question';
    case Formation = 'formation';
    case Partenariat = 'partenariat';
    case Media = 'media';
    case Autre = 'autre';

    /**
     * Libellé lu par le visiteur dans le formulaire, à l'impératif.
     */
    public function label(): string
    {
        return match ($this) {
            self::Rdv => 'Prendre rendez-vous',
            self::Question => 'Question sur l\'accompagnement',
            self::Formation => 'Question sur une formation',
            self::Partenariat => 'Partenariat',
            self::Media => 'Demande presse / média',
            self::Autre => 'Autre',
        };
    }

    /**
     * Libellé repris dans l'email de notification, sous forme nominale : il y
     * décrit un motif reçu, pas une action à faire.
     */
    public function notificationLabel(): string
    {
        return match ($this) {
            self::Rdv => 'Prise de rendez-vous',
            self::Question => 'Question sur l\'accompagnement',
            self::Formation => 'Question sur une formation',
            self::Partenariat => 'Partenariat',
            self::Media => 'Demande presse / média',
            self::Autre => 'Autre',
        };
    }

    /**
     * Objets réellement proposables : sans formation publiée, le motif
     * correspondant n'aurait aucun objet, comme l'entrée du menu et la
     * connexion élève qu'on masque déjà dans le même cas.
     *
     * @return list<self>
     */
    public static function available(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $subject): bool => $subject !== self::Formation || Course::hasPublished(),
        ));
    }

    /**
     * @return list<string>
     */
    public static function availableValues(): array
    {
        return array_map(fn (self $subject): string => $subject->value, self::available());
    }
}
