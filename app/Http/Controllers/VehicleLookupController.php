<?php

namespace App\Http\Controllers;

use App\Services\ClaudeAiParser;
use App\Services\IeRegistrationDecoder;
use App\Services\MotorCheckLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleLookupController extends Controller
{
    public function __construct(
        private readonly ClaudeAiParser $ai,
        private readonly IeRegistrationDecoder $decoder,
        private readonly MotorCheckLookup $motorcheck
    ) {
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'ai_configured' => $this->ai->isConfigured(),
        ]);
    }

    public function decodeRegistration(Request $request): JsonResponse
    {
        $reg = (string) $request->query('reg', '');
        $decoded = $this->decoder->decode($reg);

        return $decoded
            ? response()->json(['ok' => true, 'data' => $decoded])
            : response()->json(['ok' => false, 'error' => 'Nie rozpoznano formatu rejestracji.'], 422);
    }

    public function fromMotorCheck(Request $request): JsonResponse
    {
        $reg = (string) $request->query('reg', '');
        if (strlen($reg) < 4) {
            return response()->json(['ok' => false, 'error' => 'Podaj rejestrację.'], 422);
        }

        $data = $this->motorcheck->lookup($reg);

        return $data
            ? response()->json(['ok' => true, 'data' => $data])
            : response()->json(['ok' => false, 'error' => 'MotorCheck nie zwrócił danych dla tej rejestracji.'], 404);
    }

    public function fromDescription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'registration' => ['required', 'string', 'max:12'],
            'description' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json([
                'ok' => false,
                'error' => 'Brak klucza Claude API. Ustaw ANTHROPIC_API_KEY w pliku .env',
            ], 503);
        }

        $result = $this->ai->parseDescription($data['registration'], $data['description']);

        return $result
            ? response()->json(['ok' => true, 'data' => $result])
            : response()->json(['ok' => false, 'error' => 'AI nie zwróciło danych. Spróbuj inaczej opisać.'], 422);
    }

    public function fromLogbookImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json([
                'ok' => false,
                'error' => 'Brak klucza Claude API. Ustaw ANTHROPIC_API_KEY w pliku .env',
            ], 503);
        }

        $file = $request->file('image');
        $base64 = base64_encode(file_get_contents($file->getRealPath()));
        $mime = $file->getMimeType() ?: 'image/jpeg';

        $result = $this->ai->parseLogbookImage($base64, $mime);

        return $result
            ? response()->json(['ok' => true, 'data' => $result])
            : response()->json(['ok' => false, 'error' => 'AI nie odczytało zdjęcia. Zrób ostrzejsze zdjęcie.'], 422);
    }
}
