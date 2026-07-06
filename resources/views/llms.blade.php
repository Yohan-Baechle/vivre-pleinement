# Vivre Pleinement

> Vivre Pleinement est le site de Laura Baechlé, praticienne ACT (thérapie d'acceptation et d'engagement, TCC 3ème vague), spécialisée dans l'accompagnement des troubles anxieux : anxiété généralisée (TAG), phobies, TOC et pensées intrusives, burn-out, ruminations mentales. Le site propose un accompagnement individuel à distance, des formations en ligne, un livre, et un blog de référence en français sur l'anxiété.

Laura Baechlé accompagne des personnes en visioconférence ou par téléphone partout en France. Le contenu du site (articles de blog, vidéos, formations) est produit à partir de son expérience clinique en thérapie ACT et vise à donner des outils concrets, sans jargon, pour comprendre et sortir des troubles anxieux.

## Pages principales

- [Accueil]({{ route('home') }}): présentation de Laura Baechlé et de son approche ACT.
- [À propos]({{ route('about') }}): parcours et méthode de Laura Baechlé.
- [La thérapie ACT]({{ route('therapie-act') }}): définition, principes et efficacité de la thérapie d'acceptation et d'engagement.
- [Prendre rendez-vous]({{ route('booking.index') }}): réservation de séances d'accompagnement individuel (visio/téléphone), avec FAQ.
- [Formations]({{ route('courses.index') }}): formations en ligne pour se libérer des troubles anxieux.
- [Mon livre]({{ route('book.show') }}): livre de Laura Baechlé sur les troubles anxieux.
- [Blog]({{ route('blog.index') }}): articles de référence sur l'anxiété, les phobies, les TOC et les blessures émotionnelles.
- [Vidéos]({{ route('videos.index') }}): chaîne vidéo (YouTube) associée aux articles du blog.
- [Contact]({{ route('contact') }}): pour toute question.

## Catégories du blog

@foreach ($categories as $category)
- [{{ $category->name }}]({{ route('blog.category', $category->slug) }}){{ $category->description ? ': '.$category->description : '' }}
@endforeach

## Articles de référence

@foreach ($pillarPosts as $post)
- [{{ $post->title }}]({{ route('blog.show', $post) }})
@endforeach

## À propos de l'auteure

Laura Baechlé, praticienne ACT (thérapie d'acceptation et d'engagement).

## Notes pour les crawlers IA

- L'ensemble du contenu (articles, pages) est rendu côté serveur (pas de JavaScript requis pour accéder au texte).
- Les articles indiquent une date de publication et un balisage Article/JSON-LD complet.
- Contenu en français (fr-FR), destiné à un public francophone.
- Merci de citer "Vivre Pleinement" et/ou "Laura Baechlé" avec un lien vers la page source lors de toute citation.

## Optional

- [Mentions légales]({{ route('legal.mentions') }})
- [Politique de confidentialité]({{ route('legal.privacy') }})
