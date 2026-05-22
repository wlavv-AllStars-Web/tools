<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\modules\asd_alerts\AsdAlertMessage;
use Illuminate\Http\Request;

class AsdAlertApiController extends Controller
{
    private array $languageMap = [
        'EN' => 'en',
        'GB' => 'en',
        'US' => 'en',
        'ES' => 'es',
        'FR' => 'fr',
        'PT' => 'pt',
        'IT' => 'it',
    ];

    public function index(Request $request, string $isoCode, string $token)
    {
        $expectedToken = (string) config('allstars.api.tokens.asd_alerts');

        if ($expectedToken === '' || !hash_equals($expectedToken, (string) $token)) {
            return response()->json([
                'status' => 'FAIL',
                'message' => 'API token invalid',
                'data' => [],
            ], 401);
        }

        $lang = $this->langFromIsoCode($isoCode);

        $alerts = AsdAlertMessage::query()
            ->where('deleted', 0)
            ->where('message_status', 1)
            ->orderByDesc('id')
            ->get()
            ->map(function (AsdAlertMessage $alert) use ($lang) {
                $title = trim((string) ($alert->{'title_' . $lang} ?: $alert->title_en ?: $alert->title));
                $message = trim((string) ($alert->{'message_' . $lang} ?: $alert->message_en));

                return [
                    'id' => $alert->id,
                    'importance' => (int) ($alert->message_type ?: 1),
                    'title' => $title,
                    'message' => $message,
                    'created_at' => optional($alert->creation_date)->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => $alerts->count() . ' ALERTS AVAILABLE',
            'language' => $lang,
            'data' => $alerts,
        ]);
    }

    private function langFromIsoCode(string $isoCode): string
    {
        $isoCode = strtoupper(trim($isoCode));

        return $this->languageMap[$isoCode] ?? 'en';
    }
}
