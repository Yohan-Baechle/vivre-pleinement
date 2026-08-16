<?php

namespace App\Filament\Admin\Resources\Students\RelationManagers;

use App\Enums\EnrollmentStatus;
use App\Mail\CourseAccessGranted;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Inscriptions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune inscription')
            ->emptyStateDescription('Cet élève n\'a encore acheté aucune '
                .'formation. Vous pouvez lui en offrir une.')
            ->columns([
                TextColumn::make('course.title')
                    ->label('Formation'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('amount_paid_cents')
                    ->label('Montant')
                    ->money('eur', divideBy: 100),
                TextColumn::make('purchased_at')
                    ->label('Acheté le')
                    ->dateTime('d/m/Y')
                    ->placeholder('–'),
            ])
            ->headerActions([
                $this->grantAccessAction(),
            ])
            ->filters([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * Ouvre l'accès à une formation sans passer par un paiement : geste
     * commercial, litige réglé hors ligne, ou accès de test. L'inscription
     * est créée à 0 €, ce qui la distingue d'une vente dans les statistiques.
     */
    private function grantAccessAction(): Action
    {
        return Action::make('grantAccess')
            ->label('Offrir une formation')
            ->icon(Heroicon::OutlinedGift)
            ->modalHeading('Offrir l\'accès à une formation')
            ->modalDescription('L\'élève accède immédiatement à la formation. '
                .'L\'inscription est enregistrée à 0 €, hors chiffre d\'affaires.')
            ->modalSubmitActionLabel('Offrir l\'accès')
            ->schema([
                Select::make('course_id')
                    ->label('Formation')
                    ->options(fn () => $this->grantableCourses())
                    ->required()
                    ->native(false)
                    ->helperText('Les formations déjà accessibles à cet élève '
                        .'ne sont pas proposées.'),

                Toggle::make('notify')
                    ->label('Prévenir l\'élève par email')
                    ->default(true)
                    ->onColor('success')
                    ->inline(false),
            ])
            ->action(function (array $data): void {
                /** @var Student $student */
                $student = $this->getOwnerRecord();

                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $data['course_id'],
                    'status' => EnrollmentStatus::Active,
                    'amount_paid_cents' => 0,
                    'currency' => 'EUR',
                    'purchased_at' => now(),
                ]);

                if ($data['notify'] ?? false) {
                    Mail::to($student->email)->send(
                        new CourseAccessGranted($enrollment->fresh(['student', 'course'])),
                    );
                }

                Notification::make()
                    ->success()
                    ->title('Accès ouvert')
                    ->body($student->name.' peut désormais suivre cette formation.')
                    ->send();
            });
    }

    /**
     * @return array<int, string>
     */
    private function grantableCourses(): array
    {
        /** @var Student $student */
        $student = $this->getOwnerRecord();

        $alreadyActive = $student->enrollments()
            ->where('status', EnrollmentStatus::Active)
            ->pluck('course_id');

        return Course::query()
            ->whereNotIn('id', $alreadyActive)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }
}
