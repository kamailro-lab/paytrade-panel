<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Scraper darmowego "Free Car Check" z motorcheck.ie.
 *
 * URL: https://www.motorcheck.ie/free-car-check/{REG}
 *
 * Zwraca: make, model, year, engine_cc, fuel, color, body
 *
 * Brak API key. Cache 30 dni (mało zmian per auto).
 */
class MotorCheckLookup
{
    private const BASE_URL = 'https://www.motorcheck.ie/free-car-check/';
    private const CACHE_TTL = 60 * 60 * 24 * 30; // 30 dni

    private const FUEL_MAP = [
        'petrol' => 'petrol',
        'diesel' => 'diesel',
        'electric' => 'electric',
        'hybrid' => 'hybrid',
        'plug-in hybrid' => 'hybrid',
        'phev' => 'hybrid',
        'lpg' => 'lpg',
    ];

    private const BODY_MAP = [
        'estate' => 'estate',
        'saloon' => 'sedan',
        'sedan' => 'sedan',
        'hatchback' => 'hatchback',
        'suv' => 'suv',
        '4x4' => 'suv',
        'coupe' => 'coupe',
        'mpv' => 'mpv',
        'convertible' => 'convertible',
        'cabriolet' => 'convertible',
        'pickup' => 'pickup',
        'pick-up' => 'pickup',
    ];

    public function lookup(string $registration): ?array
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $registration));
        if ($clean === '' || strlen($clean) < 4) {
            return null;
        }

        return Cache::remember("motorcheck:{$clean}", self::CACHE_TTL, function () use ($clean) {
            return $this->fetch($clean);
        });
    }

    private function fetch(string $reg): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
                    'Accept-Language' => 'en-IE,en;q=0.9',
                ])
                ->get(self::BASE_URL . $reg);

            if (!$response->successful()) {
                Log::warning('MotorCheck non-200', ['reg' => $reg, 'status' => $response->status()]);
                return null;
            }

            return $this->parse($response->body(), $reg);
        } catch (\Throwable $e) {
            Log::error('MotorCheck exception', ['reg' => $reg, 'msg' => $e->getMessage()]);
            return null;
        }
    }

    private function parse(string $html, string $reg): ?array
    {
        $title = $this->extractTitle($html);
        if (!$title) {
            return null; // Auto nie znalezione (zwykle pokazuje search form)
        }

        [$make, $model, $year] = $this->parseTitle($title);

        $body = $this->extractTableValue($html, 'Body');
        $engineCc = $this->extractTableValue($html, 'CC');
        $fuel = $this->extractTableValue($html, 'Fuel');
        $colour = $this->extractTableValue($html, 'Colour');

        return [
            'source' => 'motorcheck.ie',
            'registration' => $reg,
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'engine_cc' => $engineCc ? (int) $engineCc : null,
            'fuel' => $this->normalizeFuel($fuel),
            'color' => $colour ? ucfirst(strtolower($colour)) : null,
            'body' => $this->normalizeBody($body),
            'body_raw' => $body,
            'title_full' => $title,
        ];
    }

    private function extractTitle(string $html): ?string
    {
        // <h3 class="dark-left vehicle-title mb-0">Dacia Logan MCV Alternative SCE 75 4, 2019
        if (preg_match('/<h3[^>]*vehicle-title[^>]*>\s*([^<]+)/i', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function parseTitle(string $title): array
    {
        // "Dacia Logan MCV Alternative SCE 75 4, 2019"
        $year = null;
        if (preg_match('/,\s*(19\d{2}|20\d{2})\s*$/', $title, $m)) {
            $year = (int) $m[1];
            $title = trim(preg_replace('/,\s*\d{4}\s*$/', '', $title));
        }

        // Pierwszy token to make, reszta to model
        $parts = preg_split('/\s+/', $title, 2);
        $make = ucfirst(strtolower($parts[0] ?? ''));
        $model = trim($parts[1] ?? '');

        return [$make, $model, $year];
    }

    private function extractTableValue(string $html, string $label): ?string
    {
        // <td>Body</td><td><strong>4 Door Estate</strong></td>
        $pattern = '/<td[^>]*>\s*' . preg_quote($label, '/') . '\s*<\/td>\s*<td[^>]*>\s*(?:<strong[^>]*>)?\s*([^<]+?)\s*(?:<\/strong>)?\s*<\/td>/i';
        if (preg_match($pattern, $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function normalizeFuel(?string $fuel): ?string
    {
        if (!$fuel) return null;
        $key = strtolower(trim($fuel));
        return self::FUEL_MAP[$key] ?? $key;
    }

    private function normalizeBody(?string $body): ?string
    {
        if (!$body) return null;
        $lower = strtolower($body);
        foreach (self::BODY_MAP as $needle => $type) {
            if (str_contains($lower, $needle)) {
                return $type;
            }
        }
        return null;
    }
}
