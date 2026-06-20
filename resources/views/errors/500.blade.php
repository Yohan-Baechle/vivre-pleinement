@include('errors.layout', [
    'code' => '500',
    'title' => 'Une erreur est survenue',
    'message' => "Un problème inattendu s'est produit de notre côté. L'incident a été enregistré, merci de réessayer dans quelques instants.",
])
