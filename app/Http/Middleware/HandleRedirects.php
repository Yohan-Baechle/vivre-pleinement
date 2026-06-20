<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $path = '/'.ltrim($request->path(), '/');

        $redirect = Redirect::where('from_path', $path)->first();
        if (! $redirect) {
            return $response;
        }

        $redirect->increment('hit_count');
        $redirect->forceFill(['last_hit_at' => now()])->saveQuietly();

        $target = str_starts_with($redirect->to_path, 'http')
            ? $redirect->to_path
            : url($redirect->to_path);

        return redirect($target, $redirect->status_code);
    }
}
