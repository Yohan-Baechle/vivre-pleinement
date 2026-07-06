<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseDemoSeeder extends Seeder
{
    /**
     * Données fictives pour tester le parcours formation de bout en bout :
     * deux formations (dont un brouillon), deux comptes élèves (l'un inscrit
     * avec une progression entamée, l'autre vierge pour tester l'achat).
     */
    public function run(): void
    {
        $course = Course::query()->updateOrCreate(
            ['slug' => 'apaiser-son-anxiete'],
            [
                'title' => 'Apaiser son anxiété au quotidien',
                'subtitle' => 'Un parcours pas à pas pour comprendre vos mécanismes d\'anxiété et retrouver le calme, à votre rythme.',
                'description' => '<p>Cette formation vous accompagne, étape par étape, pour mieux comprendre l\'anxiété et apprendre des outils concrets issus de la thérapie ACT. Aucune connaissance préalable n\'est nécessaire.</p><p>Avancez à votre rythme : chaque leçon associe une vidéo courte et un texte de mise en pratique.</p>',
                'outcomes' => [
                    'Comprendre ce qui déclenche et entretient l\'anxiété',
                    'Identifier vos pensées automatiques et prendre du recul',
                    'Pratiquer des exercices de respiration et d\'ancrage',
                    'Construire votre plan personnel anti-anxiété',
                ],
                'price_cents' => 14900,
                'currency' => 'EUR',
                'intro_video_provider' => 'youtube',
                'intro_video_id' => 'inpok4MKVLM',
                'level' => 'Tous niveaux',
                'duration_minutes' => 95,
                'status' => CourseStatus::Published,
                'published_at' => now()->subWeek(),
                'seo_title' => 'Formation : apaiser son anxiété au quotidien',
                'seo_description' => 'Un parcours en ligne pas à pas pour comprendre et apaiser l\'anxiété avec des outils concrets de la thérapie ACT.',
                'position' => 0,
            ],
        );

        $this->seedCurriculum($course);

        // Deuxième formation, en brouillon : invisible au public, visible en admin.
        Course::query()->updateOrCreate(
            ['slug' => 'mieux-dormir'],
            [
                'title' => 'Mieux dormir, naturellement',
                'subtitle' => 'Retrouver un sommeil réparateur sans médicament.',
                'description' => '<p>Un programme court pour comprendre les troubles du sommeil et adopter une routine apaisante.</p>',
                'outcomes' => ['Comprendre les cycles du sommeil', 'Mettre en place une routine du soir'],
                'price_cents' => 9900,
                'currency' => 'EUR',
                'level' => 'Débutant',
                'status' => CourseStatus::Draft,
                'published_at' => null,
                'position' => 1,
            ],
        );

        // Élève déjà inscrit, avec la première leçon terminée (pour voir la progression).
        $enrolled = Student::query()->updateOrCreate(
            ['email' => 'eleve@example.com'],
            [
                'name' => 'Camille Élève',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        Enrollment::query()->updateOrCreate(
            ['student_id' => $enrolled->id, 'course_id' => $course->id],
            [
                'status' => EnrollmentStatus::Active,
                'amount_paid_cents' => $course->price_cents,
                'currency' => 'EUR',
                'stripe_payment_intent_id' => 'pi_demo_seeded',
                'purchased_at' => now()->subDays(3),
            ],
        );

        $firstLesson = $course->lessons()->orderBy('position')->first();
        if ($firstLesson) {
            $enrolled->lessonProgress()->updateOrCreate(
                ['lesson_id' => $firstLesson->id],
                ['completed_at' => now()->subDays(2)],
            );
        }

        // Élève sans achat : pour tester le parcours d'achat complet.
        Student::query()->updateOrCreate(
            ['email' => 'prospect@example.com'],
            [
                'name' => 'Alex Prospect',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }

    private function seedCurriculum(Course $course): void
    {
        $curriculum = [
            'Comprendre l\'anxiété' => [
                ['Bienvenue dans la formation', 'inpok4MKVLM', true],
                ['Qu\'est-ce que l\'anxiété ?', 'O-6f5wQXSu8', false],
                ['Le cercle vicieux de l\'évitement', 'O-6f5wQXSu8', false],
            ],
            'Les outils concrets' => [
                ['La respiration apaisante', 'inpok4MKVLM', false],
                ['Défusionner de ses pensées', 'O-6f5wQXSu8', false],
            ],
            'Mettre en pratique' => [
                ['Construire votre plan anti-anxiété', 'inpok4MKVLM', false],
            ],
        ];

        $modulePosition = 0;

        foreach ($curriculum as $moduleTitle => $lessons) {
            $module = Module::query()->updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleTitle],
                ['position' => $modulePosition++],
            );

            $lessonPosition = 0;

            foreach ($lessons as [$title, $videoId, $isFreePreview]) {
                Lesson::query()->updateOrCreate(
                    ['module_id' => $module->id, 'title' => $title],
                    [
                        'slug' => str($title)->slug().'-'.$module->id,
                        'content' => '<p>Contenu de démonstration pour la leçon « '.$title.' ». Remplacez-le par votre propre texte de mise en pratique.</p>',
                        'video_provider' => 'youtube',
                        'video_id' => $videoId,
                        'duration_seconds' => fake()->numberBetween(180, 900),
                        'position' => $lessonPosition++,
                        'is_free_preview' => $isFreePreview,
                    ],
                );
            }
        }
    }
}
