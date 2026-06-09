<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\DealerHubFeedService;
use Illuminate\Console\Command;

/**
 * Sync DealerHub stock — match po rejestracji + zapisz zdjęcia i brakujące dane.
 *
 * Użycie:
 *   php artisan dealerhub:sync                    — sync wszystkie auta z DealerHub
 *   php artisan dealerhub:sync --reg=151D28132    — sync konkretne auto
 *   php artisan dealerhub:sync --photos-only      — tylko zdjęcia (nie nadpisuj danych)
 *   php artisan dealerhub:sync --force-refresh    — wyczyść cache i pobierz świeże dane
 */
class SyncDealerHubStock extends Command
{
    protected $signature = 'dealerhub:sync
                            {--reg= : Konkretna rejestracja do synchronizacji}
                            {--photos-only : Tylko zdjęcia, nie nadpisuj danych vehicle}
                            {--force-refresh : Wymuś świeży request do API (cache clear)}';

    protected $description = 'Synchronizuje stock z DealerHub.ie API — zdjęcia + dane techniczne';

    public function handle(DealerHubFeedService $feed): int
    {
        $this->info('🔄 Pobieranie stocku z DealerHub.ie...');

        $stock = $feed->getStock($this->option('force-refresh'));

        if (empty($stock)) {
            $this->error('❌ Brak danych z API (sprawdź .env DEALERHUB_API_KEY/DEALERHUB_DEALER_ID).');
            return Command::FAILURE;
        }

        $this->info("✓ Pobrano {$this->countLabel(count($stock))} z DealerHub.");
        $this->newLine();

        // Filtruj jeśli --reg
        if ($reg = $this->option('reg')) {
            $car = $feed->findByRegistration($reg);
            if (!$car) {
                $this->error("❌ Brak auta z rejestracją '{$reg}' w DealerHub.");
                return Command::FAILURE;
            }
            $stock = [$car];
        }

        $matched = 0;
        $created = 0;
        $skipped = 0;
        $photoCount = 0;
        $photosOnly = $this->option('photos-only');

        $progressBar = $this->output->createProgressBar(count($stock));
        $progressBar->start();

        foreach ($stock as $car) {
            $progressBar->advance();

            $apiReg = $car['attributes']['registration'] ?? null;
            if (!$apiReg) {
                $skipped++;
                continue;
            }

            // Match z istniejącym Vehicle (po znormalizowanej rejestracji)
            $vehicle = $this->findVehicleByReg($apiReg);

            if (!$vehicle) {
                $skipped++;
                continue;
            }

            $matched++;

            // Auto-uzupełnij brakujące dane (chyba że --photos-only)
            if (!$photosOnly) {
                $dataFromApi = $feed->extractVehicleData($car);
                $updateData = [];

                foreach ($dataFromApi as $field => $value) {
                    if (!empty($value) && empty($vehicle->{$field})) {
                        $updateData[$field] = $value;
                    }
                }

                if (!empty($updateData)) {
                    $vehicle->update($updateData);
                }
            }

            // Zdjęcia (zawsze nadpisuj — DealerHub jest "source of truth")
            $photos = $feed->extractPhotos($car);
            if (!empty($photos)) {
                $vehicle->update(['photos' => $photos]);
                $photoCount += count($photos);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("📊 Statystyki synchronizacji:");
        $this->table(['Metryka', 'Wartość'], [
            ['Aut z API', count($stock)],
            ['Dopasowanych do DB', $matched],
            ['Bez dopasowania', $skipped],
            ['Zdjęć zaimportowanych', $photoCount],
        ]);

        if ($skipped > 0) {
            $this->warn("⚠️ {$skipped} aut z DealerHub NIE ma odpowiednika w lokalnej DB.");
            $this->line("   Dodaj je przez 'Dodaj auto' i uruchom 'php artisan dealerhub:sync' ponownie.");
        }

        return Command::SUCCESS;
    }

    private function findVehicleByReg(string $apiReg): ?Vehicle
    {
        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $apiReg));

        return Vehicle::all()->first(function ($v) use ($normalized) {
            $vehicleNorm = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $v->registration));
            return $vehicleNorm === $normalized;
        });
    }

    private function countLabel(int $count): string
    {
        return match (true) {
            $count === 1 => '1 auto',
            $count >= 2 && $count <= 4 => "{$count} auta",
            default => "{$count} aut",
        };
    }
}
