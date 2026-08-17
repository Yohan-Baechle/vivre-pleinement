<?php

namespace App\Observers;

use App\Http\Middleware\HandleRedirects;
use App\Models\Redirect;

/**
 * Invalide la table des redirections mise en cache par HandleRedirects dès
 * qu'une entrée est créée, modifiée ou supprimée depuis l'admin.
 */
class RedirectObserver
{
    public function saved(Redirect $redirect): void
    {
        HandleRedirects::flushCache();
    }

    public function deleted(Redirect $redirect): void
    {
        HandleRedirects::flushCache();
    }
}
