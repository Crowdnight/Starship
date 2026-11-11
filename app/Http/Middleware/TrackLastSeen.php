<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $now = now();

        if (
            $user
            && $user->is_active
            && (! $user->last_seen_at || $user->last_seen_at->lte($now->copy()->subMinutes(5)))
        ) {
            DB::table('users')
                ->where('id', $user->getKey())
                ->update(['last_seen_at' => $now]);
        }

        return $next($request);
    }
}
