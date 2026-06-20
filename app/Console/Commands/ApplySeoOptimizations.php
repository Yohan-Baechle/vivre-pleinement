<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('seo:optimize-priority-articles {--dry-run : Affiche les changements sans les enregistrer}')]
#[Description('Applique les optimisations SEO (title, description, section réponse directe, maillage, tags) aux 7 articles prioritaires.')]
class ApplySeoOptimizations extends Command
{
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        foreach ($this->optimizations() as $slug => $opt) {
            $post = Post::query()->where('slug', $slug)->first();

            if (! $post) {
                $this->warn("Article introuvable : {$slug}");

                continue;
            }

            if (str_contains($post->content, $opt['intro_html'])) {
                $this->line("• {$slug} : section déjà présente, métadonnées mises à jour.");
            } else {
                $post->content = $opt['intro_html']."\n".$post->content;
            }

            $post->seo_title = $opt['seo_title'];
            $post->seo_description = $opt['seo_description'];

            if (! $dry) {
                $post->save();

                $tagIds = collect($opt['tags'])->map(fn (string $name) => Tag::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                )->id);
                $post->tags()->syncWithoutDetaching($tagIds);
            }

            $this->info(($dry ? '[dry] ' : '')."✓ {$slug} — title ".mb_strlen($opt['seo_title']).' car., '.count($opt['tags']).' tags');
        }

        $this->newLine();
        $this->comment($dry ? 'Dry-run terminé (rien enregistré).' : 'Optimisations appliquées.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{seo_title: string, seo_description: string, intro_html: string, tags: list<string>}>
     */
    private function optimizations(): array
    {
        return [
            'susceptibilite' => [
                'seo_title' => "Susceptibilité : Comment Arrêter d'Être Susceptible et S'en Libérer",
                'seo_description' => "Vous prenez tout mal, vous ruminez la moindre remarque ? La susceptibilité cache souvent d'anciennes blessures. Voici comment vous en libérer, pas à pas.",
                'tags' => ['susceptibilité', 'confiance en soi', 'hypersensibilité', 'émotions'],
                'intro_html' => <<<'HTML'
<h2>Comment arrêter d'être susceptible ?</h2>
<p>Si vous êtes ici, c'est sans doute que vous en avez assez de vous sentir blessé pour un rien, de ressasser une phrase pendant des heures, de gâcher de belles relations à cause d'une réaction que vous regrettez ensuite. La bonne nouvelle, c'est que la susceptibilité n'est pas une fatalité gravée dans votre caractère : c'est une <b>réaction de protection</b> que vous avez apprise, et que vous pouvez désapprendre.</p>
<p>En quelques mots, arrêter d'être susceptible passe par trois prises de conscience :</p>
<ul>
<li><b>Comprendre que la remarque de l'autre parle de lui, pas de vous.</b> Ce qui vous blesse, ce n'est pas la phrase : c'est l'écho qu'elle réveille en vous.</li>
<li><b>Aller voir la blessure qui se cache dessous.</b> La susceptibilité est presque toujours la partie visible d'une <a href="/blog/blessure-de-rejet">blessure plus ancienne</a> — de rejet, d'abandon ou d'<a href="/blog/soigner-blessure-dinjustice">injustice</a>.</li>
<li><b>Renforcer votre socle intérieur</b>, en travaillant votre <a href="/blog/confiance-et-estime-de-soi">confiance en vous</a>, pour que le regard des autres cesse d'avoir autant de pouvoir sur vous.</li>
</ul>
<p>C'est exactement ce que nous allons voir, étape par étape, dans cet article.</p>
HTML,
            ],
            'accords-tolteques' => [
                'seo_title' => 'Les 5 Accords Toltèques : Résumé et Comment les Appliquer',
                'seo_description' => 'Quels sont les 5 accords toltèques de Don Miguel Ruiz ? Résumé clair de chaque accord et conseils concrets pour les appliquer au quotidien et vivre plus librement.',
                'tags' => ['accords toltèques', 'développement personnel', 'lâcher-prise', 'sagesse'],
                'intro_html' => <<<'HTML'
<h2>Quels sont les 5 accords toltèques, en résumé ?</h2>
<p>Avant d'entrer dans le détail, voici les <b>5 accords toltèques</b> popularisés par Don Miguel Ruiz, ces règles de vie issues de la sagesse mexicaine ancestrale pour vous libérer des croyances qui vous font souffrir :</p>
<ol>
<li><b>Que votre parole soit impeccable</b> — parlez avec intégrité, ne dites que ce que vous pensez vraiment.</li>
<li><b>Quoi qu'il arrive, n'en faites pas une affaire personnelle</b> — ce que les autres disent parle d'eux, pas de vous.</li>
<li><b>Ne faites aucune supposition</b> — osez poser des questions plutôt que d'imaginer le pire et de <a href="/blog/ruminations">ruminer</a>.</li>
<li><b>Faites toujours de votre mieux</b> — un mieux qui change selon les jours, sans <a href="/blog/perfectionnisme">perfectionnisme</a> ni culpabilité.</li>
<li><b>Soyez sceptique, mais apprenez à écouter</b> — le cinquième accord : gardez votre discernement.</li>
</ol>
<p>Appliqués au quotidien, ces accords sont de véritables outils contre le <a href="/blog/jugement-des-autres">jugement des autres</a> et l'anxiété sociale. Voyons maintenant chacun d'eux en détail, avec des exemples concrets.</p>
HTML,
            ],
            'blessure-de-trahison' => [
                'seo_title' => 'Blessure de Trahison : 9 Signes pour la Reconnaître (et Guérir)',
                'seo_description' => 'Contrôle, méfiance, anxiété : la blessure de trahison laisse des traces. Découvrez les 9 signes qui la révèlent et le chemin pour vous en libérer enfin.',
                'tags' => ['blessure de trahison', 'blessures de l\'âme', 'confiance en soi', 'développement personnel'],
                'intro_html' => <<<'HTML'
<h2>C'est quoi, la blessure de trahison ?</h2>
<p>La <b>blessure de trahison</b> est l'une des cinq blessures de l'âme décrites par Lise Bourbeau. Elle naît souvent dans l'enfance, lorsqu'un parent en qui l'on avait une confiance absolue n'a pas tenu ses promesses, a déçu, ou a manqué à sa parole. L'enfant en tire une conviction profonde : <i>« on ne peut compter que sur soi »</i>.</p>
<p>Devenu adulte, on porte cette blessure sans toujours la reconnaître. Elle se traduit par un besoin de <b>tout contrôler</b>, une grande <b>méfiance</b>, de l'impatience, parfois de l'agressivité — et une <a href="/blog/trouble-anxieux-generalise">anxiété</a> qui ne dit pas son nom. Si vous vous demandez si vous en souffrez, les <b>9 signes</b> qui suivent vont vous parler. Et surtout, sachez qu'on peut <a href="/blog/comment-soigner-la-blessure-de-trahison">en guérir</a>.</p>
HTML,
            ],
            'vivre-linstant-present' => [
                'seo_title' => "Vivre l'Instant Présent : 11 Exercices Concrets pour y Arriver",
                'seo_description' => "Vivre l'instant présent, ça veut dire quoi, et comment faire vraiment ? 11 exercices simples de pleine conscience pour sortir du mental et apaiser l'anxiété.",
                'tags' => ['instant présent', 'pleine conscience', 'méditation', 'lâcher-prise'],
                'intro_html' => <<<'HTML'
<h2>Comment vivre l'instant présent ?</h2>
<p>« Vis l'instant présent », « lâche prise », « savoure le moment »… On vous le répète sans jamais vous expliquer <i>comment</i> faire concrètement. Pourtant, vivre le moment présent n'est pas un don réservé aux moines bouddhistes : c'est une compétence qui se cultive, par de petits exercices accessibles à tous.</p>
<p>Voici l'essentiel, en trois idées :</p>
<ul>
<li><b>Ramenez votre attention à vos sens.</b> Ce que vous voyez, entendez, touchez maintenant : le présent passe toujours par le corps, jamais par le mental.</li>
<li><b>Acceptez vos pensées sans les suivre.</b> Vivre l'instant présent, ce n'est pas faire le vide, c'est cesser de courir derrière le passé et le futur — souvent la vraie source de l'<a href="/blog/angoisse-stress-anxiete">anxiété</a>.</li>
<li><b>Entraînez-vous chaque jour</b>, par la <a href="/blog/respiration">respiration</a>, la gratitude ou le <a href="/blog/lacher-prise-et-acceptation">lâcher-prise</a>.</li>
</ul>
<p>Plus bas, vous trouverez <b>11 exercices concrets</b> pour y parvenir, à intégrer à votre quotidien dès aujourd'hui.</p>
HTML,
            ],
            'trouble-anxieux-generalise' => [
                'seo_title' => "Trouble Anxieux Généralisé : Comment S'en Sortir Durablement",
                'seo_description' => 'Le trouble anxieux généralisé (TAG) vous épuise à force de tout anticiper ? Comprenez ses causes et découvrez comment vous en sortir, naturellement et durablement.',
                'tags' => ['trouble anxieux généralisé', 'TAG', 'anxiété', 'gestion du stress'],
                'intro_html' => <<<'HTML'
<h2>Trouble anxieux généralisé : comment s'en sortir ?</h2>
<p>Vous vivez avec une inquiétude permanente, une boule au ventre qui ne vous lâche pas, l'impression d'anticiper sans cesse le pire. Le <b>trouble anxieux généralisé</b> (TAG) est épuisant — mais ce n'est ni une fatalité, ni un trait de caractère immuable. On peut s'en sortir, et le plus souvent <b>sans s'enfermer à vie dans les médicaments</b>.</p>
<p>S'en libérer durablement repose sur trois leviers complémentaires :</p>
<ul>
<li><b>Comprendre le mécanisme de l'anxiété</b> pour cesser de la nourrir, plutôt que de lutter contre elle.</li>
<li><b>Apaiser le corps</b>, par la <a href="/blog/respiration">respiration</a> et la relaxation, car le TAG s'ancre autant dans le corps que dans le mental.</li>
<li><b>Travailler le terrain en profondeur</b> : <a href="/blog/cause-des-troubles-anxieux">les causes</a>, le rapport à l'incertitude, et parfois un accompagnement thérapeutique.</li>
</ul>
<p>Voyons d'abord ce qu'est précisément le TAG, puis comment en guérir, étape par étape.</p>
HTML,
            ],
            'ergophobie-peur-du-travail' => [
                'seo_title' => "Ergophobie : Comprendre et Vaincre la Peur d'Aller au Travail",
                'seo_description' => "L'idée d'aller travailler vous noue l'estomac ? L'ergophobie est une vraie phobie, à ne pas confondre avec le burn-out. Symptômes, causes et solutions concrètes.",
                'tags' => ['ergophobie', 'phobie', 'burn-out', 'anxiété au travail'],
                'intro_html' => <<<'HTML'
<h2>Ergophobie : qu'est-ce que c'est, et est-ce un burn-out ?</h2>
<p>L'idée de retourner travailler vous noue l'estomac, au point d'en ressentir une <b>angoisse intense et incontrôlable</b> ? Vous souffrez peut-être d'<b>ergophobie</b>, la phobie du travail. Attention : il ne s'agit ni de paresse, ni d'un simple manque de motivation, mais d'une <b>véritable phobie</b>, avec ses symptômes physiques et sa charge d'anxiété.</p>
<p>On la confond souvent avec le <a href="/blog/burn-out">burn-out</a>, mais les deux sont distincts : le burn-out est un <b>épuisement</b> qui survient après avoir trop donné, tandis que l'ergophobie est une <b>peur anticipée</b> du travail, qui peut exister même sans surmenage. Comprendre cette différence est la première étape pour vous en libérer. Voyons les symptômes, les causes, puis les solutions concrètes.</p>
HTML,
            ],
            'symbolique-des-reves' => [
                'seo_title' => "Signification des Rêves : Comment Interpréter ce qu'ils Disent",
                'seo_description' => "Que signifient vos rêves et cauchemars ? Découvrez les origines des rêves, comment les interpréter et ce qu'ils révèlent de vos émotions et de votre anxiété.",
                'tags' => ['signification des rêves', 'rêves', 'inconscient', 'sommeil'],
                'intro_html' => <<<'HTML'
<h2>Comment interpréter la signification de ses rêves ?</h2>
<p>Pourquoi rêve-t-on, et nos rêves veulent-ils dire quelque chose ? Depuis toujours, leur <b>signification</b> fascine. S'il n'existe pas de dictionnaire universel des rêves — chaque image vous appartient —, ils ne sont pas non plus le fruit du hasard : ils sont une <b>fenêtre sur vos émotions</b>, en particulier celles que vous refoulez à l'éveil.</p>
<p>Pour interpréter un rêve, trois clés :</p>
<ul>
<li><b>Reliez-le à ce que vous vivez.</b> Un rêve récurrent ou marquant fait souvent écho à une émotion non digérée, un <a href="/blog/traumatisme">traumatisme</a> ou un <a href="/blog/angoisse-stress-anxiete">stress</a> actuel.</li>
<li><b>Observez la charge émotionnelle</b> plutôt que les détails : c'est ce que vous avez ressenti qui compte, pas seulement le scénario.</li>
<li><b>Méfiez-vous des interprétations toutes faites</b> : un même rêve peut avoir plusieurs sens selon votre histoire.</li>
</ul>
<p>Dans cet article, nous verrons les origines des rêves, comment interpréter les cauchemars, et ce qu'ils révèlent de votre monde intérieur.</p>
HTML,
            ],
        ];
    }
}
