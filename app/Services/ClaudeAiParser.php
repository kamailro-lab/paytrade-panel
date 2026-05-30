<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiParser
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.anthropic.key');
        $this->model = (string) config('services.anthropic.model', 'claude-sonnet-4-6');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function parseDescription(string $registration, string $description): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $prompt = <<<PROMPT
Z poniższego opisu auta wyciągnij dane i zwróć WYŁĄCZNIE JSON (bez komentarzy, markdown ani tekstu wokół).

Rejestracja: {$registration}
Opis: {$description}

Schemat JSON:
{
  "make": "marka, np. Volkswagen",
  "model": "model, np. Passat",
  "year": liczba 1950-2030 lub null,
  "engine_cc": liczba pojemność w ccm lub null,
  "fuel": "petrol|diesel|hybrid|electric|lpg" lub null,
  "color": "kolor po polsku lub null",
  "mileage_km": liczba w km lub null,
  "body": "sedan|hatchback|suv|coupe|estate|mpv|convertible|pickup" lub null,
  "doors": liczba 2-7 lub null,
  "logbook_no": "numer logbooka VRC lub null"
}

Jeśli czegoś nie da się ustalić, użyj null. Nie zgaduj. Zwróć WYŁĄCZNIE czysty JSON.
PROMPT;

        return $this->callApi($prompt);
    }

    public function parseLogbookImage(string $base64Image, string $mimeType = 'image/jpeg'): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $prompt = <<<PROMPT
Z załączonego zdjęcia logbooka V5C/VRC (irlandzki dokument auta) wyciągnij dane i zwróć WYŁĄCZNIE JSON.

Schemat JSON:
{
  "registration": "numer rejestracyjny, format YY-CC-NNNNN",
  "logbook_no": "numer logbooka (zwykle prawy górny róg, format alfanumeryczny)",
  "make": "marka",
  "model": "model",
  "year": liczba lub null,
  "engine_cc": liczba w ccm lub null,
  "fuel": "petrol|diesel|hybrid|electric|lpg" lub null,
  "color": "kolor po polsku" lub null,
  "body": "sedan|hatchback|suv|coupe|estate|mpv|convertible|pickup" lub null,
  "doors": liczba lub null
}

Jeśli czegoś nie da się odczytać, użyj null. Nie zgaduj. Zwróć WYŁĄCZNIE czysty JSON.
PROMPT;

        $payload = [
            'model' => $this->model,
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $base64Image,
                        ],
                    ],
                    ['type' => 'text', 'text' => $prompt],
                ],
            ]],
        ];

        return $this->postAndExtract($payload);
    }

    private function callApi(string $prompt): ?array
    {
        return $this->postAndExtract([
            'model' => $this->model,
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);
    }

    private function postAndExtract(array $payload): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post(self::ENDPOINT, $payload);

            if (!$response->successful()) {
                Log::warning('Claude API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = $response->json('content.0.text');
            if (!$text) {
                return null;
            }

            $json = $this->extractJson($text);
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::error('Claude API exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    private function extractJson(string $text): ?array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text);

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }
}
