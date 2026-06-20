<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Article pilier « Thérapie ACT » — cible SEO « thérapie act » (vol. 800, KD 7)
 * et « act thérapie » (vol. 200, KD 7), avec le cluster TCC / exercices anxiété.
 *
 * Idempotent : relancer le seeder met à jour l'article sans le dupliquer.
 * Aucune image featured n'est attachée (à ajouter via Filament).
 */
class ActArticleSeeder extends Seeder
{
    private const SLUG = 'therapie-act';

    public function run(): void
    {
        $post = Post::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'title' => 'La Thérapie ACT : Accepter pour Mieux Avancer',
                'excerpt' => "La thérapie ACT (thérapie d'acceptation et d'engagement) ne cherche pas à supprimer vos pensées anxieuses, mais à changer votre relation avec elles. Voici comment elle fonctionne, et des exercices concrets pour commencer.",
                'content' => $this->content(),
                'status' => PostStatus::Published,
                'comments_enabled' => true,
                'seo_title' => "Thérapie ACT : Qu'est-ce que c'est et Comment ça Marche ?",
                'seo_description' => 'La thérapie ACT vous apprend à accepter vos pensées anxieuses au lieu de lutter contre elles. Principes, différences avec la TCC et exercices concrets pour débuter.',
                'published_at' => Carbon::now(),
            ]
        );

        $category = Category::where('slug', 'comprendre-et-se-soigner')->first();

        if ($category) {
            $post->categories()->syncWithoutDetaching([$category->id]);
        }

        $tagNames = ['Thérapie ACT', 'TCC', 'Acceptation', 'Pleine conscience'];
        $tagIds = collect($tagNames)
            ->map(fn (string $name) => Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            )->id)
            ->all();

        $post->tags()->syncWithoutDetaching($tagIds);
    }

    /**
     * Corps HTML de l'article (~2 600 mots), au format des articles existants :
     * H2 « réponse directe » en tête, listes, H3 numérotés, liens internes
     * en /blog/{slug}, CTA RDV intégré.
     */
    private function content(): string
    {
        return <<<'HTML'
<h2>La thérapie ACT, qu'est-ce que c'est, en une phrase ?</h2>
<p>La <b>thérapie ACT</b> (pour <i>Acceptance and Commitment Therapy</i>, ou <b>thérapie d'acceptation et d'engagement</b>) est une approche qui ne vous demande pas de faire taire vos pensées anxieuses, mais d'<b>arrêter de lutter contre elles</b> pour réinvestir votre énergie dans ce qui compte vraiment pour vous. Autrement dit : on ne cherche pas à se débarrasser de l'anxiété à tout prix, on apprend à <b>vivre pleinement malgré elle</b>.</p>
<p>Si vous avez l'impression d'avoir tout essayé pour ne plus penser à vos angoisses — et que plus vous luttez, plus elles reviennent —, l'ACT pourrait bien changer votre regard. C'est une approche que j'utilise dans mes accompagnements, parce qu'elle est à la fois douce, concrète et étonnamment libératrice. Voyons ensemble ce qu'elle est, en quoi elle diffère des autres thérapies, et surtout : comment commencer dès aujourd'hui.</p>

<h2>D'où vient la thérapie d'acceptation et d'engagement ?</h2>
<p>L'ACT a été développée à la fin des années 1980 par le psychologue américain <b>Steven C. Hayes</b>. Elle fait partie de ce qu'on appelle les <b>thérapies cognitivo-comportementales de la troisième vague</b> — une évolution des fameuses <b>thérapies cognitivo-comportementales (TCC)</b> classiques.</p>
<p>Pour bien comprendre, résumons les trois grandes vagues :</p>
<ul>
<li>La <b>première vague</b> (comportementale) s'intéressait surtout aux comportements observables : on modifie ce qu'on fait pour aller mieux.</li>
<li>La <b>deuxième vague</b> (cognitive) ajoutait le travail sur les pensées : on identifie et on transforme les pensées négatives.</li>
<li>La <b>troisième vague</b>, dont fait partie l'ACT, ne cherche plus à <i>changer</i> le contenu des pensées, mais à <b>changer la relation</b> que l'on entretient avec elles, grâce à l'acceptation et à la pleine conscience.</li>
</ul>
<p>C'est une nuance essentielle. Là où une TCC classique vous aiderait à remplacer une pensée comme « je vais forcément échouer » par une pensée plus réaliste, l'ACT vous apprend à <b>laisser cette pensée exister sans qu'elle ne dirige votre vie</b>. Elle perd ainsi son pouvoir, non pas parce qu'elle disparaît, mais parce que vous cessez de vous battre contre elle.</p>

<h2>Thérapie ACT ou TCC : quelle différence ?</h2>
<p>C'est sans doute la question que vous vous posez si vous avez déjà entendu parler des TCC. Voici l'essentiel :</p>
<ul>
<li>La <b>TCC classique</b> cherche à <b>modifier ou corriger</b> les pensées dysfonctionnelles. Objectif : penser plus juste pour ressentir mieux.</li>
<li>L'<b>ACT</b> cherche à <b>accepter</b> les pensées et les émotions telles qu'elles sont, sans les juger, pour ne plus les laisser dicter vos choix. Objectif : agir selon vos valeurs, même en présence d'inconfort.</li>
</ul>
<p>Aucune des deux n'est « meilleure » dans l'absolu : tout dépend de vous et de votre fonctionnement. Beaucoup de personnes anxieuses, épuisées d'avoir « combattu » leurs pensées pendant des années, trouvent dans l'ACT un soulagement profond, parce qu'on leur dit enfin : <b>vous n'avez plus à gagner cette bataille intérieure</b>.</p>

<h2>Les 6 piliers de la thérapie ACT</h2>
<p>L'ACT repose sur six processus complémentaires, que l'on regroupe souvent sous l'image de la <b>flexibilité psychologique</b> : cette capacité à rester présent et à agir selon ses valeurs, même quand surgissent des pensées ou des émotions difficiles. Découvrons-les un à un.</p>

<h3>1 – L'acceptation</h3>
<p>Accepter, ce n'est pas se résigner ni aimer ce qui fait mal. C'est <b>arrêter de lutter</b> contre les émotions désagréables (anxiété, peur, tristesse) et leur faire de la place, plutôt que de dépenser une énergie folle à les fuir. Paradoxalement, c'est en cessant de combattre une émotion qu'on lui permet de s'apaiser. Ce travail rejoint celui du <a href="/blog/lacher-prise-et-acceptation">lâcher-prise et de l'acceptation</a>.</p>

<h3>2 – La défusion cognitive</h3>
<p>La <b>défusion cognitive</b> consiste à prendre du recul par rapport à vos pensées, à les voir comme de simples <b>productions mentales</b> et non comme des vérités absolues. Une pensée comme « je suis nul·le » n'est qu'un assemblage de mots qui traverse votre esprit, pas un fait. Apprendre à se « défusionner » de ses pensées, c'est cesser d'être collé·e à elles. C'est l'un des outils les plus puissants contre les <a href="/blog/ruminations">ruminations mentales</a>.</p>

<h3>3 – Le contact avec l'instant présent</h3>
<p>L'anxiété nous projette sans cesse dans un futur menaçant (« et si… ? ») ou dans un passé regretté. L'ACT nous ramène à l'<b>ici et maintenant</b>, le seul endroit où la vie se déroule réellement. C'est tout l'enjeu de <a href="/blog/vivre-linstant-present">vivre l'instant présent</a> : revenir au moment qui se déroule, par les sens, plutôt que de se laisser emporter par le flot mental.</p>

<h3>4 – Le soi observateur</h3>
<p>Derrière vos pensées et vos émotions, il existe une part de vous qui <b>observe</b>, stable et inchangée : le « soi observateur ». Vous n'êtes pas vos pensées, vous êtes <b>celui ou celle qui les remarque</b>. Cette prise de conscience apporte un immense apaisement : les tempêtes intérieures passent, mais l'observateur, lui, demeure.</p>

<h3>5 – Les valeurs</h3>
<p>Que voulez-vous que votre vie représente ? Quelle personne souhaitez-vous être, dans vos relations, votre travail, votre rapport à vous-même ? Les <b>valeurs</b> sont la boussole de l'ACT. Elles donnent une direction, un sens — et c'est en agissant dans leur sens que l'on retrouve de l'élan, même quand l'anxiété est présente.</p>

<h3>6 – L'action engagée</h3>
<p>Enfin, l'ACT est une thérapie tournée vers l'<b>action</b>. Une fois vos valeurs clarifiées, il s'agit de poser des <b>pas concrets</b> dans leur direction, même petits, même en présence de la peur. C'est l'engagement : avancer vers ce qui compte, sans attendre que l'inconfort disparaisse.</p>

<h2>À qui s'adresse la thérapie ACT ?</h2>
<p>L'ACT a fait l'objet de nombreuses études et montre son efficacité sur un large éventail de difficultés. Elle peut vous aider si vous traversez :</p>
<ul>
<li>un <a href="/blog/trouble-anxieux-generalise">trouble anxieux généralisé</a>, avec ce besoin de tout anticiper et contrôler ;</li>
<li>des <a href="/blog/toc-troubles-obsessionnels-compulsifs">TOC et des pensées intrusives</a> qui vous envahissent ;</li>
<li>une <a href="/blog/phobie-sociale">phobie sociale</a> ou la peur du regard des autres ;</li>
<li>du stress chronique, un <a href="/blog/burn-out">burn-out</a> ou un épuisement ;</li>
<li>une tendance à la <a href="/blog/ruminations">rumination</a> et au perfectionnisme ;</li>
<li>une dépression légère à modérée, une perte de sens ;</li>
<li>des douleurs chroniques que l'on apprend à mieux accueillir.</li>
</ul>
<p>Plus largement, l'ACT s'adresse à toute personne qui sent qu'elle <b>passe à côté de sa vie</b> à force de lutter contre son monde intérieur. Si vous vous reconnaissez dans la <a href="/blog/angoisse-stress-anxiete">distinction entre stress, anxiété et angoisse</a>, cette approche peut vous offrir un nouveau cap.</p>

<h2>3 exercices ACT à pratiquer dès aujourd'hui</h2>
<p>L'un des grands atouts de l'ACT, c'est qu'elle propose des <b>exercices concrets</b>, à faire seul·e, pour s'entraîner. En voici trois, simples et puissants, pour goûter à l'approche.</p>

<h3>Exercice 1 : nommer ses pensées (défusion)</h3>
<p>La prochaine fois qu'une pensée anxieuse surgit — « je vais rater » —, ne la prenez pas de front. Reformulez-la mentalement ainsi : <b>« Je remarque que j'ai la pensée que je vais rater. »</b> Puis allez encore plus loin : <b>« Je remarque que je suis en train de me dire que j'ai la pensée que je vais rater. »</b></p>
<p>Cela peut sembler étrange, mais en quelques secondes, vous créez une distance. La pensée est toujours là, mais vous n'êtes plus <i>dedans</i> : vous l'<i>observez</i>. Elle redevient ce qu'elle est — un événement mental passager.</p>

<h3>Exercice 2 : l'ancrage dans le présent (5-4-3-2-1)</h3>
<p>Quand l'anxiété vous emporte, ramenez-vous au présent par vos cinq sens. Nommez, calmement :</p>
<ul>
<li><b>5</b> choses que vous voyez,</li>
<li><b>4</b> choses que vous entendez,</li>
<li><b>3</b> choses que vous touchez,</li>
<li><b>2</b> choses que vous sentez (odeurs),</li>
<li><b>1</b> chose que vous goûtez.</li>
</ul>
<p>Cet exercice coupe court à la spirale des « et si » et vous reconnecte à l'instant. Vous pouvez le combiner avec un travail sur la <a href="/blog/respiration">respiration</a> pour amplifier l'effet d'apaisement.</p>

<h3>Exercice 3 : la boussole des valeurs</h3>
<p>Prenez quelques minutes et posez-vous cette question : <b>« Si l'anxiété n'était plus un obstacle, qu'est-ce que je ferais de plus dans ma vie ? »</b> Notez ce qui vient. Une relation à nourrir, un projet à oser, une activité abandonnée…</p>
<p>Puis choisissez <b>une seule petite action</b>, réalisable cette semaine, qui va dans cette direction. L'objectif n'est pas d'attendre de ne plus avoir peur, mais d'agir <b>avec</b> la peur, vers ce qui compte. C'est exactement là que l'ACT devient transformatrice.</p>

<h2>Que change concrètement la thérapie ACT dans votre quotidien ?</h2>
<p>Au-delà de la théorie, ce sont les <b>changements concrets</b> qui comptent. Voici ce que les personnes que j'accompagne observent, souvent, au fil des semaines :</p>
<ul>
<li><b>Moins de temps passé à ruminer.</b> En apprenant à se défusionner de ses pensées, on cesse de tourner en boucle. Le mental continue de produire des pensées, mais elles glissent au lieu de coller.</li>
<li><b>Une anxiété qui fait moins peur.</b> Quand on arrête de fuir une émotion, elle devient beaucoup moins menaçante. Une crise d'angoisse qui terrifiait devient un orage que l'on sait traverser.</li>
<li><b>Des décisions plus alignées.</b> En se reconnectant à ses valeurs, on fait des choix qui nous ressemblent, au lieu de tout organiser autour de l'évitement de la peur.</li>
<li><b>Un quotidien à nouveau vivant.</b> Les activités mises de côté « en attendant d'aller mieux » reprennent leur place. On recommence à oser, à sortir, à créer, à se relier aux autres.</li>
</ul>
<p>L'ACT ne supprime pas l'inconfort d'un coup de baguette magique. Mais elle desserre l'étau : peu à peu, l'anxiété cesse d'être le centre de gravité de votre vie. C'est un changement discret au début, puis de plus en plus net.</p>

<h2>Faut-il être accompagné·e pour faire de l'ACT ?</h2>
<p>Ces exercices vous donnent un avant-goût de l'approche, et beaucoup de personnes constatent déjà un mieux en les pratiquant régulièrement. Mais quand l'anxiété est ancrée, qu'elle dure depuis des années ou qu'elle empiète sur votre quotidien, un <b>accompagnement personnalisé</b> fait toute la différence.</p>
<p>Un thérapeute formé à l'ACT vous aide à clarifier vos valeurs, à repérer vos schémas d'évitement et à avancer pas à pas, sans vous juger. C'est précisément ce que je propose dans mes accompagnements, par téléphone ou en visioconférence, dans un cadre bienveillant et sans pression.</p>
<p>Si vous sentez que c'est le bon moment pour vous, vous pouvez <a href="/reservation">réserver un premier rendez-vous</a> : nous prendrons le temps de faire connaissance et de voir, ensemble, comment l'ACT peut vous aider à <b>vous libérer durablement de l'anxiété</b>.</p>

<h2>Questions fréquentes sur la thérapie ACT</h2>

<h3>La thérapie ACT est-elle vraiment efficace ?</h3>
<p>Oui. L'ACT s'appuie sur plus de trois décennies de recherche et figure parmi les approches validées scientifiquement, notamment pour les troubles anxieux, la dépression, le stress chronique et la douleur. Son efficacité tient à un point clé : plutôt que de viser la disparition des symptômes, elle développe votre <b>flexibilité psychologique</b>, c'est-à-dire votre capacité à vivre pleinement même en présence de difficultés. Les bénéfices ont tendance à durer dans le temps, car vous repartez avec des outils que vous gardez pour la vie.</p>

<h3>Combien de séances faut-il pour ressentir des effets ?</h3>
<p>Cela dépend de chacun, de l'ancienneté des difficultés et de vos objectifs. Beaucoup de personnes ressentent un premier soulagement dès les premières séances, simplement en cessant de lutter contre leurs pensées. Un accompagnement s'étale en général sur plusieurs semaines à quelques mois, à un rythme que l'on adapte ensemble. L'idée n'est pas de s'installer dans une thérapie sans fin, mais de vous rendre <b>autonome</b> le plus vite possible.</p>

<h3>Quelle différence entre l'ACT et la méditation de pleine conscience ?</h3>
<p>La pleine conscience (mindfulness) est l'un des ingrédients de l'ACT, mais elle n'en est qu'une partie. Là où la méditation développe surtout la présence et l'observation, l'ACT y ajoute un travail sur les <b>valeurs</b> et l'<b>action engagée</b> : il ne s'agit pas seulement d'observer son monde intérieur avec bienveillance, mais aussi de s'en servir comme tremplin pour <b>agir</b> dans le sens d'une vie qui compte.</p>

<h3>Peut-on pratiquer l'ACT seul·e, sans thérapeute ?</h3>
<p>Vous pouvez tout à fait commencer seul·e, avec les exercices présentés plus haut, des livres ou des applications. C'est une excellente porte d'entrée. Cependant, lorsque l'anxiété est profondément installée ou liée à des blessures anciennes, un accompagnement permet d'aller plus loin, plus en sécurité, et d'éviter les écueils — comme transformer l'acceptation en une nouvelle forme de lutte déguisée.</p>

<h2>En résumé : accepter pour mieux avancer</h2>
<p>La <b>thérapie ACT</b> ne promet pas une vie sans pensées difficiles — aucune approche honnête ne le pourrait. Elle propose quelque chose de plus profond et de plus réaliste : <b>cesser la guerre intérieure</b>, faire de la place à ce qui est là, et réorienter votre énergie vers une vie qui a du sens pour vous.</p>
<p>Accepter ce que l'on ne peut pas contrôler, s'engager vers ce qui compte vraiment : voilà le cœur de l'ACT. Et c'est peut-être le chemin le plus doux pour, enfin, recommencer à <b>vivre pleinement</b>.</p>
HTML;
    }
}
