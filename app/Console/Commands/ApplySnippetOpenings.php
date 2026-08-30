<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:apply-snippet-openings {--dry-run : Affiche les changements sans les enregistrer}')]
#[Description('Ajoute un encart « définition rapide » en ouverture, un titre orienté réponse et une FAQ aux articles captant des requêtes définitionnelles (données GSC).')]
class ApplySnippetOpenings extends Command
{
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        foreach ($this->openings() as $slug => $opt) {
            $post = Post::query()->where('slug', $slug)->first();

            if (! $post) {
                $this->warn("Article introuvable : {$slug}");

                continue;
            }

            if (str_contains($post->content, $opt['marker'])) {
                $this->line("• {$slug} : encart déjà présent, métadonnées mises à jour.");
            } else {
                $post->content = $opt['opening_html']."\n".$post->content;
            }

            $post->seo_title = $opt['seo_title'];
            $post->seo_description = $opt['seo_description'];
            $post->faq = $opt['faq'];

            if (! $dry) {
                Post::withoutTimestamps(fn () => $post->save());
            }

            $this->info(($dry ? '[dry] ' : '')."✓ {$slug} — title ".mb_strlen($opt['seo_title']).' car., '.count($opt['faq']).' questions FAQ');
        }

        $this->newLine();
        $this->comment($dry ? 'Dry-run terminé (rien enregistré).' : 'Ouvertures snippet appliquées.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{
     *     marker: string,
     *     opening_html: string,
     *     seo_title: string,
     *     seo_description: string,
     *     faq: list<array{question: string, answer: string}>,
     * }>
     */
    private function openings(): array
    {
        return [
            'etre-casanier' => [
                'marker' => 'que veut dire « casanier »',
                'opening_html' => <<<'HTML'
<div class="key-answer">
<p><strong>Définition rapide : que veut dire « casanier » ?</strong></p>
<p>Une personne <b>casanière</b> est une personne qui aime rester chez elle et qui y trouve son équilibre : elle préfère le confort de son foyer aux sorties et aux grandes réunions sociales. Le mot vient de « case », au sens de maison. Être casanier n'est <b>ni une maladie ni un défaut</b> : c'est un trait de tempérament, souvent lié à l'introversion, qui ne devient un problème que lorsque rester chez soi n'est plus un choix mais une contrainte. C'est là toute la différence avec l'<b>agoraphobie</b> ou la <b>phobie sociale</b>, où l'on reste enfermé par angoisse et non par plaisir. Si votre vie à la maison vous ressource, que vos activités vous rendent heureux et que vous sortez quand vous le décidez, votre tempérament casanier est une préférence légitime — pas un trouble à corriger.</p>
</div>
HTML,
                'seo_title' => "Casanier, casanière : définition et pourquoi l'assumer",
                'seo_description' => "Que veut dire casanier ? Définition simple, différence avec l'agoraphobie, et pourquoi ce tempérament n'est pas un défaut. Apprenez à l'assumer.",
                'faq' => [
                    [
                        'question' => "C'est quoi, une personne casanière ?",
                        'answer' => "Une personne casanière aime rester chez elle et y trouve son équilibre : elle préfère son foyer aux sorties. C'est un trait de tempérament, pas une maladie — le mot vient de « case », la maison.",
                    ],
                    [
                        'question' => 'Être casanier, est-ce un défaut ?',
                        'answer' => "Non. Tant que rester chez vous est un choix qui vous ressource, c'est une préférence légitime, souvent liée à l'introversion. Cela ne devient un problème que si l'isolement est subi ou source de souffrance.",
                    ],
                    [
                        'question' => 'Quelle différence entre casanier et agoraphobe ?',
                        'answer' => "Le casanier reste chez lui par plaisir ; l'agoraphobe par angoisse. Si sortir déclenche une peur intense ou des évitements répétés, il ne s'agit plus d'un tempérament mais d'un trouble anxieux, qui se soigne.",
                    ],
                    [
                        'question' => 'Comment assumer son tempérament casanier ?',
                        'answer' => 'En cessant de vous comparer : votre équilibre ne ressemble pas à celui des autres. Posez vos limites avec bienveillance, choisissez les sorties qui ont du sens pour vous et savourez sans culpabilité celles que vous déclinez.',
                    ],
                ],
            ],
            'rancune-et-rancoeur' => [
                'marker' => 'rancune et rancœur, quelle différence',
                'opening_html' => <<<'HTML'
<div class="key-answer">
<p><strong>Définition rapide : rancune et rancœur, quelle différence ?</strong></p>
<p>La <b>rancœur</b> est une amertume profonde et diffuse que l'on garde après une injustice, une trahison ou une déception : un ressentiment qui « reste sur le cœur », sans forcément viser une personne précise. La <b>rancune</b>, elle, est dirigée : c'est le souvenir tenace d'une offense particulière, accompagné d'hostilité envers celui ou celle qui l'a causée, parfois d'un désir de revanche. En résumé, la rancœur est un <b>état émotionnel</b> — une amertume que l'on porte —, la rancune une <b>attitude envers quelqu'un</b> — le refus de pardonner. Les deux se nourrissent du même mécanisme : la rumination d'une blessure passée. Et les deux s'apaisent avec les mêmes outils — accueillir l'émotion, prendre de la distance avec les pensées, puis pardonner : non pour excuser l'autre, mais pour vous libérer vous-même.</p>
</div>
HTML,
                'seo_title' => "Rancune et rancœur : définition, différence, s'en libérer",
                'seo_description' => "Rancœur : amertume diffuse qui reste sur le cœur. Rancune : hostilité tenace envers quelqu'un. Comprenez la différence et libérez-vous de ces émotions.",
                'faq' => [
                    [
                        'question' => 'Que veut dire « rancœur » ?',
                        'answer' => "La rancœur est une amertume profonde qui persiste après une injustice ou une déception. C'est un ressentiment diffus, qui « reste sur le cœur », sans toujours viser une personne en particulier.",
                    ],
                    [
                        'question' => 'Quelle est la différence entre rancune et rancœur ?',
                        'answer' => "La rancœur est un état émotionnel : une amertume que l'on porte en soi. La rancune est une attitude dirigée contre quelqu'un : le souvenir tenace d'une offense et le refus de pardonner, parfois avec un désir de revanche.",
                    ],
                    [
                        'question' => 'Pourquoi est-il si difficile de pardonner ?',
                        'answer' => "Parce que la blessure est réelle et que la rumination l'entretient : chaque rappel du souvenir réactive la colère. Le pardon ne consiste pas à excuser l'autre, mais à cesser de laisser le passé décider de votre présent.",
                    ],
                    [
                        'question' => 'Comment se libérer de la rancune et de la rancœur ?',
                        'answer' => "En trois mouvements : accueillir l'émotion au lieu de la refouler, prendre de la distance avec les pensées de vengeance, puis choisir le pardon pour vous-même. L'article détaille chacune de ces étapes.",
                    ],
                ],
            ],
        ];
    }
}
