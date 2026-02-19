<?php

declare(strict_types=1);

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role->value, $roles, true)) {
            return new JsonResponse([
                'message' => 'Forbidden. You do not have the required role.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
