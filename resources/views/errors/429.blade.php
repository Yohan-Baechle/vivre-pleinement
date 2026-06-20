@include('errors.layout', [
    'code' => '429',
    'title' => 'Trop de requêtes',
    'message' => "Vous avez effectué trop d'actions en peu de temps. Patientez quelques instants avant de réessayer.",
])
