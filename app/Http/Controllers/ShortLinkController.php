<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\ShortLink;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id' => ['nullable', 'integer', 'exists:guests,id'],
            'confirmation_token' => ['nullable', 'string'],
        ]);

        $token = null;

        if (! empty($data['guest_id'])) {
            $guest = Guest::findOrFail($data['guest_id']);
            $token = $guest->confirmation_token;
        }

        if (empty($token) && ! empty($data['confirmation_token'])) {
            $token = $data['confirmation_token'];
        }

        if (empty($token)) {
            return response()->json(['message' => 'No token provided'], 422);
        }

        $targetUrl = url("/rsvp/{$token}");

        $link = ShortLink::create([
            'guest_id' => $data['guest_id'] ?? null,
            'target_url' => $targetUrl,
        ]);

        return response()->json([
            'short_url' => url("/s/{$link->code}"),
        ]);
    }

    public function redirect(string $code)
        {
            $link = ShortLink::where('code', $code)->firstOrFail();

            $title = '💍 Invitación de Boda';
            $description = 'Confirma tu asistencia';
            $image = url('/images/invitacion.png');
            $currentUrl = url()->current();

            $html = '<!doctype html><html><head>' .
                "<meta charset=\"utf-8\">" .
                "<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">" .
                "<title>{$title}</title>" .
                "<meta property=\"og:type\" content=\"website\">" .
                "<meta property=\"og:title\" content=\"{$title}\">" .
                "<meta property=\"og:description\" content=\"{$description}\">" .
                "<meta property=\"og:image\" content=\"{$image}\">" .
                "<meta property=\"og:url\" content=\"{$currentUrl}\">" .
                "<meta property=\"og:image:width\" content=\"1200\">" .
                "<meta property=\"og:image:height\" content=\"630\">" .
                "<meta name=\"twitter:card\" content=\"summary_large_image\">" .
                "<meta name=\"twitter:title\" content=\"{$title}\">" .
                "<meta name=\"twitter:description\" content=\"{$description}\">" .
                "<meta name=\"twitter:image\" content=\"{$image}\">" .
                // meta refresh as a fallback for non-JS clients/crawlers
                "<meta http-equiv=\"refresh\" content=\"0;url={$link->target_url}\">" .
                '</head><body>' .
                "<p>Redirigiendo a la invitación... <a href=\"{$link->target_url}\">Abrir</a></p>" .
                "<script>window.location.replace(" . json_encode($link->target_url) . ");</script>" .
                '</body></html>';

            return response($html, 200)->header('Content-Type', 'text/html');
        }
}
