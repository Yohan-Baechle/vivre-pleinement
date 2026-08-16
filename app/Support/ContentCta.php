<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Course;

class ContentCta
{
    /**
     * Catégories dont l'offre la plus pertinente est le livre (pensées
     * intrusives & TOC) plutôt que la formation sur l'anxiété.
     */
    private const BOOK_CATEGORY_SLUGS = ['toc-et-pensees-intrusives'];

    /**
     * Offre à mettre en avant en fin de contenu selon la catégorie :
     * livre pour les TOC/pensées intrusives, formation phare sinon,
     * repli sur le livre quand aucune formation n'est publiée.
     *
     * @return array{
     *     kind: string,
     *     title: string,
     *     description: string,
     *     url: string,
     *     label: string,
     * }
     */
    public static function offerFor(?Category $category): array
    {
        $wantsBook = $category !== null && in_array($category->slug, self::BOOK_CATEGORY_SLUGS, true);

        $course = $wantsBook ? null : Course::query()->published()->orderBy('position')->first();

        if ($course !== null) {
            return [
                'kind' => 'course',
                'title' => $course->title,
                'description' => $course->subtitle ?: 'La formation en ligne de Laura pour avancer pas à pas, à votre rythme.',
                'url' => route('courses.show', $course),
                'label' => 'Découvrir la formation',
            ];
        }

        return [
            'kind' => 'book',
            'title' => 'Soigner les pensées intrusives & le TOC, naturellement',
            'description' => 'Le livre de Laura pour comprendre vos pensées intrusives et vous en libérer, étape par étape.',
            'url' => route('book.show'),
            'label' => 'Découvrir le livre',
        ];
    }
}
