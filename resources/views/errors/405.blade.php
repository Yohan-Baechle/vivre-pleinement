@include('errors.layout', [
    'code' => '405',
    'title' => 'Action non autorisée',
    'message' => "Cette action n'est pas possible de cette manière. Revenez en arrière puis réessayez depuis la page concernée.",
])
