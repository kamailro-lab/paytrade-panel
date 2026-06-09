<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integracja z DealerHub.ie feed API.
 *
 * DealerHub to irlandzki marketplace dealerów (połączony z DoneDeal).
 * Dostarcza pełne dane aut + zdjęcia (CDN media.dealerhub.ie).
 *
 * Nasza aplikacja jest CORE (zakup, koszty, P&L, faktury VAT Margin Scheme).
 * DealerHub używamy jako:
 * 1. Źródło zdjęć (CDN URL embeddable bez auth)
 * 2. Auto-uzupełnianie brakujących pól (NCT, mileage, kolor)
 * 3. Synchronizacja statusu (sold/available na DealerHub).
 */
class DealerHubFeedService
{
    private string $apiKey;
    private string $dealerId;
    private string $baseUrl = 'https://feeds.dealerhub.ie/api/v1';

    public function __construct()
    {
        $this->apiKey = (string) config('services.dealerhub.api_key', env('DEALERHUB_API_KEY', ''));
        $this->dealerId = (string) config('services.dealerhub.dealer_id', env('DEALERHUB_DEALER_ID', ''));
    }

    /**
     * Pobierz cały stock z DealerHub.
     * Cache 30 minut żeby nie spamować API.
     *
     * @return array Lista aut (każde z polami: id, title, price, attributes, images, ...)
     */
    public function getStock(bool $forceRefresh = false): array
    {
        if (empty($this->apiKey) || empty($this->dealerId)) {
            Log::warning('DealerHub: missing API key or dealer ID in .env');
            return [];
        }

        if ($forceRefresh) {
            Cache::forget('dealerhub.stock');
        }

        return Cache::remember('dealerhub.stock', now()->addMinutes(30), function () {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['X-API-Key' => $this->apiKey])
                    ->get("{$this->baseUrl}/stock", [
                        'dealer_id' => $this->dealerId,
                    ]);

                if (!$response->successful()) {
                    Log::error('DealerHub API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return [];
                }

                return $response->json('stock', []);
            } catch (\Exception $e) {
                Log::error('DealerHub fetch failed: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Znajdź auto po numerze rejestracyjnym.
     * Normalizuje rejestrację (usuwa myślniki/spacje, uppercase).
     */
    public function findByRegistration(string $registration): ?array
    {
        $needle = $this->normalizeRegistration($registration);
        if (empty($needle)) {
            return null;
        }

        foreach ($this->getStock() as $car) {
            $apiReg = $this->normalizeRegistration($car['attributes']['registration'] ?? '');
            if ($apiReg === $needle) {
                return $car;
            }
        }
        return null;
    }

    /**
     * Wyciągnij listę URL zdjęć posortowaną po sortOrder.
     */
    public function extractPhotos(array $car): array
    {
        $images = $car['images'] ?? [];

        return collect($images)
            ->sortBy('sortOrder')
            ->pluck('url')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Wyciągnij dane do aktualizacji Vehicle model.
     * Mapuje DealerHub attributes na nasze kolumny DB.
     */
    public function extractVehicleData(array $car): array
    {
        $attrs = $car['attributes'] ?? [];

        // Fuel mapping (DealerHub używa 'Diesel'/'Petrol'/'Electric'/'Hybrid')
        $fuelMap = [
            'Diesel' => 'diesel',
            'Petrol' => 'petrol',
            'Electric' => 'electric',
            'Hybrid' => 'hybrid',
            'LPG' => 'lpg',
        ];

        // Body mapping
        $bodyMap = [
            'Saloon' => 'sedan',
            'Hatchback' => 'hatchback',
            'SUV' => 'suv',
            'Coupe' => 'coupe',
            'Estate' => 'estate',
            'MPV' => 'mpv',
            'Convertible' => 'convertible',
            'Pickup' => 'pickup',
        ];

        return [
            'make' => $attrs['make'] ?? null,
            'model' => trim(($attrs['model'] ?? '') . ' ' . ($attrs['trim'] ?? '')),
            'year' => $attrs['year'] ?? null,
            'engine_cc' => $attrs['engineSizeCC'] ?? null,
            'fuel' => $fuelMap[$attrs['fuelType'] ?? ''] ?? null,
            'color' => $attrs['colour'] ?? null,
            'mileage_km' => $attrs['mileage'] ?? null,
            'body' => $bodyMap[$attrs['bodyType'] ?? ''] ?? null,
            'doors' => $attrs['numDoors'] ?? null,
            'nct_expiry' => $attrs['nctDueDate'] ?? null,
        ];
    }

    /**
     * Normalizuje rejestrację do porównania.
     * "151-D-28132" → "151D28132"
     */
    private function normalizeRegistration(string $reg): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $reg));
    }

    /**
     * Status feed-a (dla diagnostyki UI).
     */
    public function status(): array
    {
        $configured = !empty($this->apiKey) && !empty($this->dealerId);
        $stock = $configured ? $this->getStock() : [];

        return [
            'configured' => $configured,
            'dealer_id' => $configured ? $this->dealerId : null,
            'stock_count' => count($stock),
            'cached_at' => Cache::get('dealerhub.stock') !== null ? 'tak (30 min cache)' : 'brak',
        ];
    }
}
