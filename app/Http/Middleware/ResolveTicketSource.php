<?php

namespace App\Http\Middleware;

use App\Models\TicketSource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ResolveTicketSource
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            $token = $request->header('X-Import-Token');
        }

        if (! $token) {
            //abort(401, 'Import token is missing.');
            return response()->json([
                'success' => false,
                'message' => 'Import token is missing.',
            ], 401, [], JSON_UNESCAPED_UNICODE);

        }

        $sources = TicketSource::query()
            ->where('is_active', true)
            ->whereNotNull('api_token_hash')
            ->get();

        $source = $sources->first(function (TicketSource $source) use ($token) {
            return Hash::check($token, $source->api_token_hash);
        });

        if (! $source) {
            abort(401, 'Invalid import token.');
        }

        $request->attributes->set('ticket_source', $source);

        return $next($request);
    }
}