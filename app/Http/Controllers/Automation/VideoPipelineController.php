<?php

namespace App\Http\Controllers\Automation;

use App\Filament\Admin\Resources\Videos\VideoResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\StoreFormattedTranscriptFormRequest;
use App\Http\Requests\Automation\StoreVideoEnrichmentFormRequest;
use App\Models\Category;
use App\Models\Video;
use App\Services\VideoEnrichment;
use App\Support\TranscriptChunks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints pilotés par n8n pour la mise en forme IA des vidéos.
 *
 * n8n récupère le travail en attente, le confie à `claude -p` sur le LXC
 * dédié, puis renvoie le résultat ici. Chaque charge utile destinée à Claude
 * est un JSON compressé en gzip puis encodé en base64 : n8n la transmet telle
 * quelle en argument SSH, où la taille d'un argument est plafonnée à 128 Ko.
 */
class VideoPipelineController extends Controller
{
    /** Taille cible d'un morceau de transcription, en mots. */
    private const CHUNK_WORDS = 1200;

    /**
     * Écart de nombre de mots toléré entre transcription brute et
     * reponctuée : au-delà, l'IA a tronqué ou inventé du texte.
     */
    private const WORD_COUNT_TOLERANCE = 0.15;

    public function pendingTranscripts(Request $request): JsonResponse
    {
        $videos = Video::query()
            ->awaitingTranscriptFormatting()
            ->orderByDesc('youtube_published_at')
            ->limit($this->limit($request))
            ->get();

        return response()->json(['data' => $videos->map(
            function (Video $video): array {
                $chunks = TranscriptChunks::split(
                    $video->transcript,
                    self::CHUNK_WORDS,
                );

                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'chunks' => array_map(
                        fn (string $text, int $index): string => $this->encode([
                            'title' => $video->title,
                            'part' => $index + 1,
                            'parts' => count($chunks),
                            'text' => $text,
                        ]),
                        $chunks,
                        array_keys($chunks),
                    ),
                ];
            },
        )->all()]);
    }

    public function storeTranscript(
        StoreFormattedTranscriptFormRequest $request,
        Video $video,
    ): JsonResponse {
        $html = TranscriptChunks::assemble($request->validated('chunks'));

        $expected = TranscriptChunks::wordCount((string) $video->transcript);
        $actual = TranscriptChunks::wordCount($html);
        $drift = $expected > 0 ? abs($actual - $expected) / $expected : 1;

        if ($drift > self::WORD_COUNT_TOLERANCE) {
            return response()->json([
                'message' => "Nombre de mots incohérent : {$actual} reçus "
                    ."pour {$expected} attendus.",
            ], 422);
        }

        $video->update([
            'transcript' => $html,
            'transcript_formatted_at' => now(),
        ]);

        return response()->json([
            'id' => $video->id,
            'words' => $actual,
            'paragraphs' => substr_count($html, '<p>'),
        ]);
    }

    public function pendingEnrichments(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (Category $category): array => [
                'slug' => $category->slug,
                'name' => $category->name,
            ])
            ->all();

        $videos = Video::query()
            ->awaitingEnrichment()
            ->orderByDesc('youtube_published_at')
            ->limit($this->limit($request))
            ->get();

        return response()->json(['data' => $videos->map(
            fn (Video $video): array => [
                'id' => $video->id,
                'title' => $video->title,
                'payload' => $this->encode([
                    'title' => $video->title,
                    'duration_seconds' => $video->duration_seconds,
                    'youtube_description' => $video->description,
                    'transcript' => TranscriptChunks::plainText(
                        (string) $video->transcript,
                    ),
                    'available_categories' => $categories,
                ]),
            ],
        )->all()]);
    }

    public function storeEnrichment(
        StoreVideoEnrichmentFormRequest $request,
        Video $video,
        VideoEnrichment $enrichment,
    ): JsonResponse {
        if ($video->intro !== null || $video->summary !== null) {
            return response()->json([
                'message' => 'Vidéo déjà enrichie : rien n\'est écrasé.',
            ], 409);
        }

        $result = $enrichment->apply($video, $request->validated());

        return response()->json([
            'id' => $video->id,
            'title' => $video->title,
            'public_url' => route('videos.show', $video),
            'admin_url' => VideoResource::getUrl(
                'edit',
                ['record' => $video],
                panel: 'admin',
            ),
            'unknown_categories' => $result['unknown_categories'],
        ]);
    }

    private function limit(Request $request): int
    {
        return max(1, min(10, $request->integer('limit', 1)));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        return base64_encode((string) gzencode((string) json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        )));
    }
}
